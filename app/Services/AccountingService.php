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
 * PembelianBahanController::store/pelunasan) - service ini sendiri tidak
 * membuka transaction baru supaya jurnal selalu atomik dengan perubahan
 * data yang memicunya.
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
    private const AKUN_HPP = '5000';

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
}
