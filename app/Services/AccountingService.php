<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya jalur penulisan ke jurnal_umum (double-entry ledger).
 * postJournal() adalah primitif dasarnya - setiap baris debet/kredit yang
 * masuk WAJIB balance (total debet == total kredit) atau dilempar exception,
 * supaya tidak mungkin ada entri jurnal yang timpang tersimpan. Method
 * lainnya (postPenjualan, dst) cuma menyusun $lines lalu memanggil
 * postJournal() - tidak ada logika akuntansi yang menulis ke tabel manapun
 * di luar method ini.
 *
 * Dipanggil dari DALAM DB::transaction() yang sudah ada di controller
 * masing-masing (PenjualanController::store, KasirController::prosesBayar,
 * PembelianBahanController::store/pelunasan). postJournal() SENDIRI juga
 * membungkus loop insert baris debet/kreditnya dengan DB::transaction()
 * (pakai SAVEPOINT kalau dipanggil dari dalam transaction controller yang
 * sudah aktif - tidak mengubah perilaku atomicity gabungan dengan data
 * pemicunya, savepoint tetap ikut roll back kalau transaction luar gagal)
 * supaya jurnal tetap balance (tidak ada baris debet tanpa pasangan
 * kreditnya) bahkan kalau suatu saat method ini dipanggil tanpa transaction
 * pembungkus dari luar.
 */
class AccountingService
{
    private const AKUN_KAS = '1000';
    private const AKUN_BANK = '1010';
    private const AKUN_PIUTANG_USAHA = '1100';
    private const AKUN_PERSEDIAAN_BAHAN = '1200';
    private const AKUN_HUTANG_USAHA = '2000';
    private const AKUN_HUTANG_DEPOSIT = '2100';
    private const AKUN_PENDAPATAN_PENJUALAN = '4000';
    private const AKUN_PENDAPATAN_JASA = '4100';
    private const AKUN_PENDAPATAN_LAIN_LAIN = '4200';
    private const AKUN_HPP = '5000';
    private const AKUN_BIAYA_LAIN_LAIN = '5100';

    /**
     * @param array<int, array{kode_akun: string, debet?: float, kredit?: float}> $lines
     */
    public static function postJournal(string $tglJurnal, string $noReferensi, string $keterangan, array $lines): void
    {
        if (count($lines) < 2) {
            throw new \InvalidArgumentException('Jurnal minimal butuh 2 baris (debet & kredit).');
        }

        $totalDebet = 0.0;
        $totalKredit = 0.0;

        foreach ($lines as $line) {
            $totalDebet += (float) ($line['debet'] ?? 0);
            $totalKredit += (float) ($line['kredit'] ?? 0);
        }

        // Toleransi pembulatan sangat kecil (rounding decimal(15,2)), bukan
        // celah untuk entri yang benar-benar timpang.
        if (abs($totalDebet - $totalKredit) > 0.01) {
            throw new \RuntimeException(
                "Jurnal tidak balance untuk referensi {$noReferensi}: debet {$totalDebet} != kredit {$totalKredit}"
            );
        }

        if ($totalDebet <= 0) {
            // Tidak ada nilai ekonomis untuk dicatat (mis. transaksi Rp 0) -
            // bukan error, cukup dilewati saja.
            return;
        }

        $now = now();

        DB::transaction(function () use ($lines, $tglJurnal, $noReferensi, $keterangan, $now) {
            foreach ($lines as $line) {
                $debet = (float) ($line['debet'] ?? 0);
                $kredit = (float) ($line['kredit'] ?? 0);

                if ($debet <= 0 && $kredit <= 0) {
                    continue;
                }

                DB::table('jurnal_umum')->insert([
                    'tgl_jurnal'    => $tglJurnal,
                    'no_referensi'  => $noReferensi,
                    'keterangan'    => $keterangan,
                    'kode_akun'     => $line['kode_akun'],
                    'debet'         => $debet,
                    'kredit'        => $kredit,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }
        });
    }

    /**
     * Order penjualan dibuat (PenjualanController::store). Dua entri jurnal
     * terpisah tapi masing-masing balance sendiri:
     *  1. Pengakuan pendapatan: Dr Piutang Usaha / Cr Pendapatan Penjualan
     *  2. Pengakuan HPP (kalau ada biaya material yang bisa dihitung dari
     *     BOM x harga_rata_rata bahan): Dr HPP / Cr Persediaan Bahan Baku
     *
     * $totalHppMaterial sengaja HANYA komponen bahan baku (bukan
     * biaya_finishing) - lihat catatan di transaksi_detail migration:
     * biaya_finishing tidak punya dasar akun hutang/persediaan yang nyata
     * di skema saat ini, jadi tidak dipaksakan masuk jurnal supaya tidak
     * ada akun fiktif yang tidak pernah bisa dilunasi.
     */
    public static function postPenjualan(string $noNota, float $grandTotal, float $totalHppMaterial): void
    {
        $tgl = now()->toDateString();

        self::postJournal($tgl, $noNota, "Penjualan {$noNota}", [
            ['kode_akun' => self::AKUN_PIUTANG_USAHA, 'debet' => $grandTotal],
            ['kode_akun' => self::AKUN_PENDAPATAN_PENJUALAN, 'kredit' => $grandTotal],
        ]);

        if ($totalHppMaterial > 0) {
            self::postJournal($tgl, $noNota, "HPP bahan baku {$noNota}", [
                ['kode_akun' => self::AKUN_HPP, 'debet' => $totalHppMaterial],
                ['kode_akun' => self::AKUN_PERSEDIAAN_BAHAN, 'kredit' => $totalHppMaterial],
            ]);
        }
    }

    /**
     * Pembayaran diterima dari konsumen (KasirController::prosesBayar).
     * Mengurangi Piutang Usaha yang dibuat di postPenjualan().
     */
    public static function postPembayaran(string $noNota, float $nominal, string $metode): void
    {
        if ($nominal <= 0) {
            return;
        }

        $akunKasMasuk = $metode === 'Tunai' ? self::AKUN_KAS : self::AKUN_BANK;

        self::postJournal(now()->toDateString(), $noNota, "Pembayaran {$metode} {$noNota}", [
            ['kode_akun' => $akunKasMasuk, 'debet' => $nominal],
            ['kode_akun' => self::AKUN_PIUTANG_USAHA, 'kredit' => $nominal],
        ]);
    }

    /**
     * "Kas Tidak Terduga" (KasirController::storeKasManual) - mutasi kas
     * tunai yang TIDAK terikat nota penjualan (mis. pengeluaran operasional
     * dadakan, kas masuk di luar penjualan). $status: 'Masuk' -> Dr Kas /
     * Cr Pendapatan Lain-lain, 'Keluar' -> Dr Biaya Lain-lain / Cr Kas.
     */
    public static function postKasManual(string $noReferensi, float $nominal, string $status): void
    {
        if ($nominal <= 0) {
            return;
        }

        $lines = $status === 'Masuk'
            ? [
                ['kode_akun' => self::AKUN_KAS, 'debet' => $nominal],
                ['kode_akun' => self::AKUN_PENDAPATAN_LAIN_LAIN, 'kredit' => $nominal],
            ]
            : [
                ['kode_akun' => self::AKUN_BIAYA_LAIN_LAIN, 'debet' => $nominal],
                ['kode_akun' => self::AKUN_KAS, 'kredit' => $nominal],
            ];

        self::postJournal(now()->toDateString(), $noReferensi, "Kas Tidak Terduga ({$status}) {$noReferensi}", $lines);
    }

    /**
     * Faktur pembelian bahan baku dicatat (PembelianBahanController::store).
     * Cash -> langsung Cr Kas. Tempo -> Cr Hutang Usaha (dilunasi belakangan
     * lewat postPelunasanHutang).
     */
    public static function postPembelianBahan(string $noFaktur, float $grandTotal, string $tipePembelian): void
    {
        $akunLawan = $tipePembelian === 'Cash' ? self::AKUN_KAS : self::AKUN_HUTANG_USAHA;

        self::postJournal(now()->toDateString(), $noFaktur, "Pembelian bahan baku {$noFaktur}", [
            ['kode_akun' => self::AKUN_PERSEDIAAN_BAHAN, 'debet' => $grandTotal],
            ['kode_akun' => $akunLawan, 'kredit' => $grandTotal],
        ]);
    }

    /**
     * Pelunasan hutang pembelian (PembelianBahanController::pelunasan).
     * Tidak ada pilihan Kas/Bank di form pelunasan saat ini, jadi
     * disederhanakan ke Kas - lihat catatan laporan ke user.
     */
    public static function postPelunasanHutang(string $noFaktur, float $grandTotal): void
    {
        self::postJournal(now()->toDateString(), $noFaktur, "Pelunasan hutang {$noFaktur}", [
            ['kode_akun' => self::AKUN_HUTANG_USAHA, 'debet' => $grandTotal],
            ['kode_akun' => self::AKUN_KAS, 'kredit' => $grandTotal],
        ]);
    }

    /**
     * Setoran deposit konsumen diterima (KonsumenDepositController::store).
     * Uang benar-benar masuk (Kas/Bank), tapi belum jadi pendapatan - masih
     * kewajiban (Hutang Deposit Konsumen) sampai dipakai bayar nota.
     */
    public static function postSetoranDeposit(string $noReferensi, float $nominal, string $metode): void
    {
        $akunSumber = $metode === 'Tunai' ? self::AKUN_KAS : self::AKUN_BANK;

        self::postJournal(now()->toDateString(), $noReferensi, "Setoran deposit {$noReferensi}", [
            ['kode_akun' => $akunSumber, 'debet' => $nominal],
            ['kode_akun' => self::AKUN_HUTANG_DEPOSIT, 'kredit' => $nominal],
        ]);
    }

    /**
     * Deposit dipakai sebagai pembayaran nota (KasirController::prosesBayar,
     * metode 'Deposit'). Tidak ada Kas/Bank baru yang masuk di sini - uangnya
     * sudah masuk saat setoran - jadi ini murni memindahkan saldo dari
     * kewajiban deposit ke pelunasan piutang.
     */
    public static function postPembayaranDeposit(string $noNota, float $nominal): void
    {
        self::postJournal(now()->toDateString(), $noNota, "Pembayaran via deposit {$noNota}", [
            ['kode_akun' => self::AKUN_HUTANG_DEPOSIT, 'debet' => $nominal],
            ['kode_akun' => self::AKUN_PIUTANG_USAHA, 'kredit' => $nominal],
        ]);
    }

    /**
     * Penjualan Jasa (dipisah dari postPenjualan karena Jasa tidak punya
     * BOM/HPP bahan baku - langsung Dr Piutang / Cr Pendapatan Jasa, tanpa
     * baris HPP kedua seperti penjualan produk fisik).
     */
    public static function postPenjualanJasa(string $noNota, float $grandTotal): void
    {
        self::postJournal(now()->toDateString(), $noNota, "Penjualan jasa {$noNota}", [
            ['kode_akun' => self::AKUN_PIUTANG_USAHA, 'debet' => $grandTotal],
            ['kode_akun' => self::AKUN_PENDAPATAN_JASA, 'kredit' => $grandTotal],
        ]);
    }

    /**
     * Pengeluaran Kas Kecil dicatat (KasKecilController::store()) - akun
     * lawan (Beban) ditentukan oleh kategori yang dipilih user
     * (kategori_kas_kecil.kode_akun), BUKAN dihardcode seperti
     * postKasManual() - satu akun Beban generik tidak cukup lagi sekarang
     * ada rincian kategori (ATK, listrik, marketing, dst). Selalu Cr Kas
     * (1000) - Kas Kecil memang representasi laci kas fisik kecil, tidak
     * ada pilihan Bank di form-nya.
     */
    public static function postKasKecil(string $noReferensi, string $kodeAkunBeban, float $nominal): void
    {
        if ($nominal <= 0) {
            return;
        }

        self::postJournal(now()->toDateString(), $noReferensi, "Pengeluaran Kas Kecil {$noReferensi}", [
            ['kode_akun' => $kodeAkunBeban, 'debet' => $nominal],
            ['kode_akun' => self::AKUN_KAS, 'kredit' => $nominal],
        ]);
    }

    /**
     * Versi generik dari reversePenjualan() (nama lama dipertahankan apa
     * adanya supaya alur Edit Nota yang sudah ada tidak perlu disentuh) -
     * membalik SEMUA baris jurnal untuk satu no_referensi apa pun
     * sumbernya, dipakai KasKecilController::destroy() saat sebuah catatan
     * Kas Kecil dihapus (jurnal lama tidak dihapus/diubah, tetap jadi jejak
     * audit - pembalikan selalu berupa entri baru yang saling meniadakan).
     */
    public static function reverseByReferensi(string $noReferensi): void
    {
        $baris = DB::table('jurnal_umum')->where('no_referensi', $noReferensi)->get();

        if ($baris->isEmpty()) {
            return;
        }

        $lines = $baris->map(fn ($b) => [
            'kode_akun' => $b->kode_akun,
            'debet' => (float) $b->kredit,
            'kredit' => (float) $b->debet,
        ])->all();

        self::postJournal(now()->toDateString(), $noReferensi, "Koreksi/pembalikan {$noReferensi}", $lines);
    }

    /**
     * Membalik SEMUA baris jurnal untuk satu no_referensi (nota) dengan
     * entri kontra (debet<->kredit ditukar) - dipakai saat sebuah nota
     * dikoreksi lewat alur Edit Nota (PenjualanController::applyApprovedEdit).
     * SENGAJA tidak pernah menghapus/mengubah baris jurnal asli (itu jejak
     * audit permanen) - pembalikan akuntansi yang benar selalu berupa entri
     * baru yang saling meniadakan, persis pola ini.
     */
    public static function reversePenjualan(string $noNota): void
    {
        $baris = DB::table('jurnal_umum')->where('no_referensi', $noNota)->get();

        if ($baris->isEmpty()) {
            return;
        }

        // Semua baris asli (bisa berasal dari beberapa panggilan postJournal
        // terpisah, mis. postPenjualan menulis 2 baris Piutang/Pendapatan +
        // 2 baris HPP/Persediaan kalau ada HPP) digabung jadi SATU baris
        // pembalikan sekaligus - tiap baris cuma diisi salah satu dari
        // debet/kredit (tidak pernah dua-duanya), jadi menukar posisi nilai
        // per baris otomatis tetap balance selama set aslinya balance.
        $lines = $baris->map(fn ($b) => [
            'kode_akun' => $b->kode_akun,
            'debet' => (float) $b->kredit,
            'kredit' => (float) $b->debet,
        ])->all();

        self::postJournal(now()->toDateString(), $noNota, "Koreksi/pembalikan nota {$noNota}", $lines);
    }
}
