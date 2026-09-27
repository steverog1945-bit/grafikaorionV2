<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Mengembalikan stok bahan baku yang sudah dipotong BOM saat sebuah item
 * transaksi_detail dibatalkan - dipakai baik dari pembatalan SPK per-item
 * (ProduksiController::cancel) maupun pembatalan nota penuh
 * (PenjualanController::batalkanNota). Diekstrak jadi satu tempat supaya
 * rumus pengembalian stok tidak dobel-ditulis di dua controller dan
 * berisiko diperbaiki di satu tempat tapi lupa di tempat lain.
 *
 * Hardening P0-1: SEBELUMNYA method ini SELALU menghitung ulang qty yang
 * harus dikembalikan dari BomCalculationService::komponen() - yang membaca
 * bom_detail/pemakaian_tinta LIVE (tabel yang sama yang di-delete-lalu-
 * insert-ulang tiap kali BOM produk diedit, lihat ProdukController::update).
 * Kalau BOM produk berubah di antara order dibuat dan item itu dibatalkan,
 * reversal akan memakai formula BARU (bahan berbeda dan/atau qty berbeda),
 * BUKAN yang benar-benar dipotong dulu.
 *
 * Sekarang: kalau baris item ini punya snapshot di
 * transaksi_detail_bahan_pakai (dicatat persis saat qty dipotong, lihat
 * PenjualanController::store), snapshot itu yang dipakai - imun terhadap
 * BOM yang berubah belakangan. Transaksi LAMA (sebelum tabel snapshot ini
 * ada) tidak punya baris di sana, jadi jatuh ke rumus lama (recompute dari
 * BOM live) sebagai fallback - satu-satunya cara yang tersedia untuk data
 * yang memang tidak punya snapshot.
 */
class StokReversalService
{
    /**
     * @param  object  $detail  Baris transaksi_detail (butuh id_detail, id_produk, p_ukuran, l_ukuran, qty)
     * @param  string  $referensi  Kode referensi untuk kartu_stok, mis. "BATAL-SPK-12" atau "BATAL-NOTA-5"
     * @param  string  $keterangan  Teks keterangan untuk kartu_stok
     * @return array<int, string>  Ringkasan "Nama Bahan +qty" per bahan yang dikembalikan
     */
    public static function kembalikan(object $detail, string $referensi, string $keterangan): array
    {
        if (!$detail->id_produk) {
            return [];
        }

        $produk = DB::table('produk')->where('id_produk', $detail->id_produk)->first();
        if (!$produk) {
            return [];
        }

        // Dibungkus DB::transaction() sendiri (bukan cuma mengandalkan
        // transaction milik pemanggil) - dua alasan: (1) lockForUpdate() di
        // bawah TIDAK berarti apa-apa di MySQL kalau dipanggil di luar
        // transaksi aktif (row lock cuma berlaku selama transaksi
        // berjalan), dan (2) method ini loop per-bahan BOM melakukan UPDATE
        // bahan_baku + INSERT kartu_stok berpasangan - tanpa transaction,
        // pemanggil baru di masa depan yang lupa membungkusnya sendiri bisa
        // meninggalkan stok berubah tanpa jejak kartu_stok kalau gagal di
        // tengah loop (multi-bahan). DB::transaction() Laravel aman
        // di-nest (pakai SAVEPOINT) kalau pemanggil sudah py transaction
        // sendiri, jadi ini tidak mengubah perilaku 3 pemanggil yang ada
        // sekarang.
        return DB::transaction(function () use ($detail, $referensi, $keterangan) {
            $snapshot = DB::table('transaksi_detail_bahan_pakai')->where('id_detail', $detail->id_detail ?? 0)->get();

            if ($snapshot->isNotEmpty()) {
                return self::kembalikanDariSnapshot($snapshot, $referensi, $keterangan);
            }

            return self::kembalikanRecomputeBomLama($detail, $referensi, $keterangan);
        });
    }

    /** Jalur akurat: qty persis seperti yang dipotong saat order dibuat, sudah dalam satuan stok - tanpa konversi/rumus BOM apa pun lagi. */
    private static function kembalikanDariSnapshot($snapshot, string $referensi, string $keterangan): array
    {
        $ringkasan = [];
        foreach ($snapshot as $baris) {
            $hasil = self::tambahkanKembali((int) $baris->id_bahan, (float) $baris->qty_pakai, $referensi, $keterangan);
            if ($hasil !== null) {
                $ringkasan[] = $hasil;
            }
        }

        return $ringkasan;
    }

    /** Fallback untuk transaksi sebelum snapshot ini ada - PERSIS rumus lama. */
    private static function kembalikanRecomputeBomLama(object $detail, string $referensi, string $keterangan): array
    {
        $ringkasan = [];
        $noNota = DB::table('transaksi')->where('id_transaksi', $detail->id_transaksi ?? 0)->value('nomor_nota');

        foreach (BomCalculationService::komponen((int) $detail->id_produk, (float) $detail->p_ukuran, (float) $detail->l_ukuran, (float) $detail->qty) as $bom) {
            $qtyPakai = $bom->qty_pakai;
            if ($qtyPakai <= 0) {
                continue;
            }

            // Tinta baru ikut dipotong sejak fitur potong-tinta aktif: nota lama (tanpa catatan OUT tinta) tidak dikembalikan.
            if ($bom->is_tinta && !DB::table('kartu_stok')->where('id_bahan', $bom->id_bahan)->where('tipe', 'OUT')->where('referensi', $noNota)->exists()) {
                continue;
            }

            $bahan = DB::table('bahan_baku')->where('id_bahan', $bom->id_bahan)->lockForUpdate()->first();
            if (!$bahan) {
                continue;
            }

            $nilaiKonversi = (float) ($bahan->nilai_konversi ?: 1);
            if ($nilaiKonversi <= 0) {
                $nilaiKonversi = 1;
            }
            $qtyKembali = $qtyPakai / $nilaiKonversi;

            $hasil = self::tambahkanKembali((int) $bom->id_bahan, $qtyKembali, $referensi, $keterangan, $bahan);
            if ($hasil !== null) {
                $ringkasan[] = $hasil;
            }
        }

        return $ringkasan;
    }

    /** Tambah balik ke bahan_baku.stok + catat kartu_stok IN - dipakai kedua jalur (snapshot & fallback). */
    private static function tambahkanKembali(int $idBahan, float $qtyKembali, string $referensi, string $keterangan, ?object $bahanSudahDikunci = null): ?string
    {
        $bahan = $bahanSudahDikunci ?? DB::table('bahan_baku')->where('id_bahan', $idBahan)->lockForUpdate()->first();
        if (!$bahan) {
            return null;
        }

        $stokSebelum = (float) $bahan->stok;
        $stokSesudah = $stokSebelum + $qtyKembali;

        DB::table('bahan_baku')->where('id_bahan', $idBahan)->update(['stok' => $stokSesudah]);

        DB::table('kartu_stok')->insert([
            'id_bahan'     => $idBahan,
            'tanggal'      => now(),
            'tipe'         => 'IN',
            'qty'          => $qtyKembali,
            'stok_sebelum' => $stokSebelum,
            'stok_sesudah' => $stokSesudah,
            'referensi'    => $referensi,
            'keterangan'   => $keterangan,
            'created_by'   => Auth::id(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return $bahan->nama_bahan . ' +' . angka($qtyKembali, 2);
    }
}
