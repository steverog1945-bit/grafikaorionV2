<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Mengembalikan stok bahan baku yang sudah dipotong BOM saat sebuah item
 * transaksi_detail dibatalkan - dipakai baik dari pembatalan SPK per-item
 * (ProduksiController::cancel) maupun pembatalan nota penuh
 * (PenjualanController::batalkanNota). Diekstrak jadi satu tempat supaya
 * rumus pengembalian stok (recompute dari p_ukuran/l_ukuran/qty + BOM,
 * PERSIS sama dengan rumus potong stok saat order dibuat) tidak dobel-
 * ditulis di dua controller dan berisiko diperbaiki di satu tempat tapi
 * lupa di tempat lain.
 *
 * Ledger kartu_stok bersifat level NOTA/referensi, bukan per-item, jadi
 * pengembalian stok tidak bisa menelusuri baris lama - satu-satunya cara
 * yang benar adalah menghitung ulang qty yang dipakai dari data yang
 * sudah tersimpan di baris transaksi_detail itu sendiri.
 */
class StokReversalService
{
    /**
     * @param  object  $detail  Baris transaksi_detail (butuh id_produk, p_ukuran, l_ukuran, qty)
     * @param  string  $referensi  Kode referensi untuk kartu_stok, mis. "BATAL-SPK-12" atau "BATAL-NOTA-5"
     * @param  string  $keterangan  Teks keterangan untuk kartu_stok
     * @return array<int, string>  Ringkasan "Nama Bahan +qty" per bahan yang dikembalikan
     */
    public static function kembalikan(object $detail, string $referensi, string $keterangan): array
    {
        $ringkasan = [];

        if (!$detail->id_produk) {
            return $ringkasan;
        }

        $produk = DB::table('produk')->where('id_produk', $detail->id_produk)->first();
        if (!$produk) {
            return $ringkasan;
        }

        $qtyHitung = $produk->metode_hitung === 'ukuran'
            ? ((float) $detail->p_ukuran * (float) $detail->l_ukuran / 10000) * (float) $detail->qty
            : (float) $detail->qty;

        $boms = DB::table('bom_detail')->where('id_produk', $detail->id_produk)->get();

        foreach ($boms as $bom) {
            $qtyPakai = $bom->cara_hitung_pemakaian === '(P*L)/10000' ? $qtyHitung : (float) $detail->qty;
            if ($qtyPakai <= 0) {
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

            $stokSebelum = (float) $bahan->stok;
            $stokSesudah = $stokSebelum + $qtyKembali;

            DB::table('bahan_baku')->where('id_bahan', $bom->id_bahan)->update(['stok' => $stokSesudah]);

            DB::table('kartu_stok')->insert([
                'id_bahan'     => $bom->id_bahan,
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

            $ringkasan[] = $bahan->nama_bahan . ' +' . number_format($qtyKembali, 2);
        }

        return $ringkasan;
    }
}
