<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Services\AuditLogger;

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
    private const AKUN_MODAL = '3000';
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

        // Accounting Period (roadmap LEVEL 1) - postJournal() adalah
        // satu-satunya jalur tulis jurnal_umum, jadi ini satu-satunya titik
        // yang perlu menolak posting ke periode yang sudah ditutup.
        if (\App\Services\AccountingPeriodService::isClosed($tglJurnal)) {
            throw new \App\Exceptions\PeriodeAkuntansiTertutupException(
                "Periode akuntansi untuk tanggal {$tglJurnal} sudah ditutup (Closed) - tidak bisa memposting jurnal baru ke periode ini."
            );
        }

        $now = now();
        $baris = [];

        DB::transaction(function () use ($lines, $tglJurnal, $noReferensi, $keterangan, $now, &$baris) {
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

                $baris[] = ['kode_akun' => $line['kode_akun'], 'debet' => $debet, 'kredit' => $kredit];
            }
        });

        // Satu titik pencatatan Audit Trail untuk SEMUA posting akuntansi di
        // seluruh aplikasi (postJournal() adalah satu-satunya jalur tulis
        // jurnal_umum - lihat docblock class ini) - lebih efisien &
        // menjamin cakupan penuh dibanding memanggil AuditLogger di tiap
        // controller yang memicu postingan (PenjualanController,
        // KasirController, PembelianBahanController, dst).
        if (!empty($baris)) {
            AuditLogger::log('created', 'jurnal_umum', null, null, ['no_referensi' => $noReferensi, 'lines' => $baris], $keterangan);
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
     * Pembayaran (cicilan/pelunasan) hutang pembelian lewat
     * PembayaranHutangService. Referensi jurnal dibuat unik per pembayaran
     * (mis. "FAK-001/BYR-7") supaya bisa di-void sendiri tanpa ikut
     * membalik jurnal faktur atau pembayaran lain. Tunai -> Kas, Transfer -> Bank.
     */
    public static function postPembayaranHutang(string $noReferensi, string $noFaktur, float $nominal, string $metode): void
    {
        $akunKeluar = $metode === 'Transfer' ? self::AKUN_BANK : self::AKUN_KAS;

        self::postJournal(now()->toDateString(), $noReferensi, "Pembayaran hutang {$noFaktur} ({$metode})", [
            ['kode_akun' => self::AKUN_HUTANG_USAHA, 'debet' => $nominal],
            ['kode_akun' => $akunKeluar, 'kredit' => $nominal],
        ]);
    }

    /**
     * Beban rework & susut bahan (QcService, WasteService): material yang
     * keluar dari gudang tanpa menjadi barang terjual. Dr Beban Rework &
     * Susut Bahan (5200) / Cr Persediaan Bahan Baku.
     */
    /** HPP kain order jersey (Dr HPP / Cr Persediaan); nilai negatif = pembalikan (stok dikembalikan). */
    public static function postHppJersey(string $noOrder, float $biaya): void
    {
        if ($biaya == 0.0) {
            return;
        }
        $abs = abs($biaya);
        $lines = $biaya > 0
            ? [['kode_akun' => self::AKUN_HPP, 'debet' => $abs], ['kode_akun' => self::AKUN_PERSEDIAAN_BAHAN, 'kredit' => $abs]]
            : [['kode_akun' => self::AKUN_PERSEDIAAN_BAHAN, 'debet' => $abs], ['kode_akun' => self::AKUN_HPP, 'kredit' => $abs]];

        self::postJournal(now()->toDateString(), $noOrder, ($biaya > 0 ? 'HPP kain jersey ' : 'Pembalikan HPP kain jersey ') . $noOrder, $lines);
    }

    public static function postBebanRework(string $noReferensi, string $keterangan, float $nominal): void
    {
        if ($nominal <= 0) {
            return;
        }

        self::postJournal(now()->toDateString(), $noReferensi, $keterangan, [
            ['kode_akun' => '5200', 'debet' => $nominal],
            ['kode_akun' => self::AKUN_PERSEDIAAN_BAHAN, 'kredit' => $nominal],
        ]);
    }

    /**
     * Pengakuan/koreksi pendapatan Order Jersey (JerseyAccountingService).
     * $selisih positif = pendapatan bertambah (Dr Piutang / Cr Pendapatan
     * Jersey), negatif = berkurang (dibalik). Pendapatan Jersey memakai
     * akun 4300 terpisah dari Pendapatan Penjualan.
     */
    public static function postPendapatanJersey(string $noOrder, float $selisih): void
    {
        $nilai = abs($selisih);

        if ($nilai < 0.01) {
            return;
        }

        $lines = $selisih > 0
            ? [['kode_akun' => self::AKUN_PIUTANG_USAHA, 'debet' => $nilai], ['kode_akun' => '4300', 'kredit' => $nilai]]
            : [['kode_akun' => '4300', 'debet' => $nilai], ['kode_akun' => self::AKUN_PIUTANG_USAHA, 'kredit' => $nilai]];

        self::postJournal(now()->toDateString(), $noOrder, ($selisih > 0 ? 'Pendapatan' : 'Koreksi pendapatan') . " order jersey {$noOrder}", $lines);
    }

    /** Pembayaran Order Jersey: Tunai -> Kas, metode lain (Transfer/QRIS/Lainnya) -> Bank. */
    public static function postPembayaranJersey(string $noReferensi, string $noOrder, float $nominal, string $metode): void
    {
        $akunMasuk = $metode === 'Tunai' ? self::AKUN_KAS : self::AKUN_BANK;

        self::postJournal(now()->toDateString(), $noReferensi, "Pembayaran order jersey {$noOrder} ({$metode})", [
            ['kode_akun' => $akunMasuk, 'debet' => $nominal],
            ['kode_akun' => self::AKUN_PIUTANG_USAHA, 'kredit' => $nominal],
        ]);
    }

    /**
     * Saldo awal persediaan saat bahan baku baru didaftarkan dengan stok awal
     * DAN harga beli (BahanBakuController::store): Dr Persediaan Bahan Baku /
     * Cr Modal. Barang sudah ada secara fisik tanpa dibeli lewat faktur, jadi
     * lawannya modal pemilik, sama seperti saldo awal di akuntansi:saldo-awal.
     */
    public static function postSaldoAwalPersediaan(string $noReferensi, string $keterangan, float $nominal): void
    {
        self::postJournal(now()->toDateString(), $noReferensi, $keterangan, [
            ['kode_akun' => self::AKUN_PERSEDIAAN_BAHAN, 'debet' => $nominal],
            ['kode_akun' => self::AKUN_MODAL, 'kredit' => $nominal],
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

    /** Pemasukan manual di Kas Kecil: Dr Kas / Cr akun pendapatan (atau modal) sesuai kategorinya. */
    public static function postKasKecilMasuk(string $noReferensi, string $kodeAkunLawan, float $nominal): void
    {
        if ($nominal <= 0) {
            return;
        }

        self::postJournal(now()->toDateString(), $noReferensi, "Pemasukan Kas Kecil {$noReferensi}", [
            ['kode_akun' => self::AKUN_KAS, 'debet' => $nominal],
            ['kode_akun' => $kodeAkunLawan, 'kredit' => $nominal],
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
