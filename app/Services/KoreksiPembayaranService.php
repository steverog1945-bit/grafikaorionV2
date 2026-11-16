<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Koreksi metode pembayaran nota - mis. uang TUNAI yang salah diinput
 * sebagai Transfer. Kalau dibiarkan, salahnya menjalar ke banyak tempat
 * sekaligus: jurnal (Tunai -> akun Kas, lainnya -> akun Bank), saldo laci
 * (saldo_akhir) vs total_transfer di kas_harian, hitungan laci saat closing
 * (KasSesiService::saldoLaci membaca pembayaran_riwayat.metode), rekap metode
 * di Laporan/Dashboard/Audit Owner, sampai transaksi.metode_pembayaran yang
 * tercetak di nota.
 *
 * Pilihannya BUKAN mengedit baris pembayaran (catatan uang tidak pernah
 * disunting di aplikasi ini) dan BUKAN void saja (status nota akan mundur
 * Lunas -> DP di sela-selanya). Yang dilakukan: SATU transaksi yang
 *   1. membalik jurnal pembayaran lama (AccountingService::reverseByReferensi -
 *      jurnal asli tetap menjadi jejak audit, pembalikan selalu entri baru),
 *   2. menandai baris lama voided (pola void_pembayaran_hutang),
 *   3. memindahkan nominal antara saldo_akhir dan total_transfer di sesi kas
 *      yang benar,
 *   4. memasukkan baris pembayaran BARU dengan metode yang benar + jurnalnya,
 *   5. menyinkronkan transaksi.metode_pembayaran dari baris aktif terbaru.
 * Nominal pembayaran tidak pernah berubah, jadi status pembayaran nota
 * (Lunas/DP/Belum Bayar) tidak perlu - dan tidak boleh - disentuh.
 *
 * Koreksi yang menyentuh Deposit DITOLAK secara sengaja: di sisi pembayaran
 * itu ada baris konsumen_deposit (Pemakaian) yang menarik saldo nyata milik
 * pelanggan, dan pemulihannya (Setoran balik) adalah keputusan uang fisik
 * yang harus lewat jalur deposit manual (KonsumenDepositController), bukan
 * ditebak otomatis.
 */
class KoreksiPembayaranService
{
    /** Metode yang boleh jadi TUJUAN koreksi (Deposit sengaja tidak termasuk). */
    public const METODE_TUJUAN = ['Tunai', 'Debit', 'Transfer', 'QRIS BRI'];

    /** Metode yang uangnya masuk LACI (satu-satunya yang menyentuh saldo_akhir). */
    private const METODE_LACI = ['Tunai'];

    /** Metode yang uangnya masuk rekening (kolom total_transfer di kas_harian). */
    private const METODE_TRANSFER = ['Transfer', 'QRIS BRI'];

    /**
     * Jalankan koreksi metode satu pembayaran. Dipanggil langsung dari
     * KasirController (sesi kas masih terbuka) atau lewat approval
     * 'koreksi_pembayaran_nota' (sesi kas sudah ditutup).
     *
     * @return int id baris pembayaran baru yang menggantikan baris lama
     */
    public static function koreksiMetode(int $idPembayaran, string $metodeBaru, ?int $idBankBaru, string $alasan): int
    {
        return DB::transaction(function () use ($idPembayaran, $metodeBaru, $idBankBaru, $alasan) {
            // Lock transaksi DULU (urutan lock yang sama dengan
            // KasirController::prosesBayar: transaksi -> kas_harian) supaya
            // koreksi yang berjalan bareng prosesBayar tidak deadlock ABBA.
            $pembayaran = DB::table('pembayaran_riwayat')->where('id_pembayaran', $idPembayaran)->first();
            if (!$pembayaran) {
                throw new \RuntimeException('Pembayaran tidak ditemukan.');
            }

            $transaksi = DB::table('transaksi')->where('id_transaksi', $pembayaran->id_transaksi)->lockForUpdate()->first();
            if (!$transaksi) {
                throw new \RuntimeException('Nota dari pembayaran ini tidak ditemukan.');
            }

            if ($pembayaran->voided_at) {
                throw new \RuntimeException('Pembayaran ini sudah pernah di-void/dikoreksi sebelumnya.');
            }

            if ($transaksi->status_nota === 'Dibatalkan') {
                throw new \RuntimeException('Nota ini sudah DIBATALKAN - metode pembayarannya tidak bisa dikoreksi lagi.');
            }

            $metodeLama = (string) $pembayaran->metode;
            if ($metodeBaru === $metodeLama) {
                throw new \RuntimeException('Metode baru sama dengan metode lama - tidak ada yang perlu dikoreksi.');
            }

            if (!in_array($metodeBaru, self::METODE_TUJUAN, true)) {
                throw new \RuntimeException("Metode '{$metodeBaru}' tidak dikenal untuk koreksi pembayaran.");
            }

            if ($metodeLama === 'Deposit' || $metodeBaru === 'Deposit') {
                throw new \RuntimeException(
                    'Koreksi yang menyentuh Deposit tidak didukung otomatis karena menarik saldo deposit nyata pelanggan. '
                    . 'Gunakan jalur deposit manual (Pengembalian/Setoran deposit) dengan bantuan Administrator.'
                );
            }

            if ($metodeBaru === 'Transfer') {
                if (!$idBankBaru) {
                    throw new \RuntimeException('Koreksi ke Transfer wajib memilih rekening bank.');
                }
                if (!DB::table('bank_perusahaan')->where('id_bank', $idBankBaru)->exists()) {
                    throw new \RuntimeException('Rekening bank tidak ditemukan.');
                }
            } else {
                $idBankBaru = null;
            }

            $nominal = (float) $pembayaran->nominal;
            if ($nominal <= 0) {
                throw new \RuntimeException('Nominal pembayaran tidak valid untuk dikoreksi.');
            }

            $noNota = (string) $transaksi->nomor_nota;

            // Sesi kas pembayaran harus pasti sebelum apa pun disentuh -
            // tanpa itu tidak ada koreksi laci/transfer yang bisa dipercaya.
            $kas = self::sesiPembayaran($pembayaran);
            if (!$kas) {
                throw new \RuntimeException(
                    'Sesi kas dari pembayaran ini tidak bisa dipastikan (data lama sebelum kolom id_kas). '
                    . 'Minta Administrator mengoreksinya manual lewat jurnal umum.'
                );
            }

            // Referensi jurnal lama: dari kolom (baris baru), atau ditebak
            // dari jurnal untuk baris lama yang dibuat sebelum kolom ada.
            $referensiLama = $pembayaran->no_referensi_jurnal
                ?: self::tebakReferensiJurnal($pembayaran, $noNota, $metodeLama);
            if (!$referensiLama) {
                throw new \RuntimeException(
                    'Jurnal pembayaran ini tidak bisa dipastikan (beberapa pembayaran mirip di hari yang sama). '
                    . 'Minta Administrator mengoreksinya manual lewat jurnal umum.'
                );
            }

            // Referensi baru dihitung SEBELUM pembalikan supaya penghitung
            // "nota-BYR-n" stabil (baris jurnal lama tetap ada setelah dibalik).
            $referensiBaru = AccountingService::referensiPembayaran($noNota);

            // 1. Jurnal lama dibalik (Kas/Bank <-> Piutang tertukar kembali).
            AccountingService::reverseByReferensi($referensiLama);

            // 2. Sesi kas: pindahkan nominal antara komponen laci dan rekening.
            DB::table('kas_harian')->where('id_kas', $kas->id_kas)->lockForUpdate()->first();
            $deltaLaci = (in_array($metodeBaru, self::METODE_LACI, true) ? $nominal : 0)
                - (in_array($metodeLama, self::METODE_LACI, true) ? $nominal : 0);
            $deltaTransfer = (in_array($metodeBaru, self::METODE_TRANSFER, true) ? $nominal : 0)
                - (in_array($metodeLama, self::METODE_TRANSFER, true) ? $nominal : 0);
            self::geserKolomKas($kas, 'saldo_akhir', $deltaLaci);
            self::geserKolomKas($kas, 'total_transfer', $deltaTransfer);

            // 3. Void baris lama - pola sama dengan PembayaranHutangService::voidPembayaran.
            DB::table('pembayaran_riwayat')->where('id_pembayaran', $idPembayaran)->update([
                'voided_at'   => now(),
                'voided_by'   => Auth::id(),
                'void_alasan' => $alasan,
            ]);

            // 4. Baris baru dengan metode yang benar. tgl_bayar, uang_diterima
            // dan kembalian dipertahankan: itu kenyataan fisik saat uang
            // diterima, yang salah hanya klasifikasinya.
            $idBaru = (int) DB::table('pembayaran_riwayat')->insertGetId([
                'id_transaksi'        => $pembayaran->id_transaksi,
                'nominal'             => $pembayaran->nominal,
                'metode'              => $metodeBaru,
                'id_bank'             => $idBankBaru,
                'tgl_bayar'           => $pembayaran->tgl_bayar,
                'keterangan'          => "Koreksi metode ({$metodeLama} -> {$metodeBaru}): {$alasan}",
                'uang_diterima'       => $pembayaran->uang_diterima,
                'kembalian'           => $pembayaran->kembalian,
                'no_referensi_jurnal' => $referensiBaru,
                'id_kas'              => $pembayaran->id_kas ?: $kas->id_kas,
            ]);

            AccountingService::postPembayaran($noNota, $nominal, $metodeBaru, $referensiBaru);

            // Jejak audit - keterangan sengaja memuat kata "koreksi" supaya
            // pantauan AuditOwnerService (filter koreksi/void) menangkapnya.
            AuditLogger::log(
                'updated',
                'pembayaran_riwayat',
                $idPembayaran,
                ['metode' => $metodeLama],
                ['voided_at' => now()->toDateTimeString(), 'void_alasan' => $alasan],
                "Koreksi pembayaran nota {$noNota}: {$metodeLama} -> {$metodeBaru} Rp " . number_format($nominal, 0, ',', '.')
            );
            AuditLogger::log(
                'created',
                'pembayaran_riwayat',
                $idBaru,
                null,
                ['nomor_nota' => $noNota, 'nominal' => $nominal, 'metode' => $metodeBaru, 'id_bank' => $idBankBaru, 'no_referensi' => $referensiBaru],
                "Pembayaran nota {$noNota} dikoreksi ({$metodeLama} -> {$metodeBaru}) Rp " . number_format($nominal, 0, ',', '.')
            );

            // 5. metode_pembayaran nota = metode baris aktif TERBARU, bukan
            // selalu metode baru (koreksi bisa menyasar pembayaran lama
            // di nota yang kemudian dibayar lagi dengan metode lain).
            $terbaru = DB::table('pembayaran_riwayat')
                ->where('id_transaksi', $pembayaran->id_transaksi)
                ->whereNull('voided_at')
                ->orderByDesc('tgl_bayar')
                ->orderByDesc('id_pembayaran')
                ->first();
            DB::table('transaksi')->where('id_transaksi', $pembayaran->id_transaksi)->update([
                'metode_pembayaran' => $terbaru->metode ?? null,
                'updated_at'        => now(),
            ]);

            return $idBaru;
        });
    }

    /**
     * Sesi kas pemilik sebuah pembayaran: dari kolom id_kas (baris yang
     * dibuat setelah migrasi), atau dicocokkan ke jendela sesi (mulai s.d.
     * tutup) untuk baris lama. Null bila tidak bisa dipastikan.
     */
    public static function sesiPembayaran(object $pembayaran): ?object
    {
        if (!empty($pembayaran->id_kas)) {
            $kas = DB::table('kas_harian')->where('id_kas', $pembayaran->id_kas)->first();

            return $kas ?: null;
        }

        $tgl = Carbon::parse($pembayaran->tgl_bayar);
        $kandidat = DB::table('kas_harian')
            ->orderBy('id_kas')
            ->get()
            ->filter(function ($k) use ($tgl) {
                return KasSesiService::mulai($k)->lte($tgl)
                    && KasSesiService::akhir($k)->gte($tgl);
            });

        return $kandidat->count() === 1 ? $kandidat->first() : null;
    }

    /**
     * Untuk baris pembayaran LAMA (sebelum kolom no_referensi_jurnal ada):
     * cari kelompok jurnal "nota-BYR-n" yang keterangan & tanggalnya cocok
     * dan total debetnya sama dengan nominal pembayaran. Null bila tidak
     * unik (tidak ketemu / lebih dari satu kandidat) - jangan menebak.
     */
    private static function tebakReferensiJurnal(object $pembayaran, string $noNota, string $metodeLama): ?string
    {
        $tgl = Carbon::parse($pembayaran->tgl_bayar)->toDateString();

        $grup = DB::table('jurnal_umum')
            ->where('no_referensi', 'like', $noNota . '-BYR-%')
            // Keterangan pembalikan ("Koreksi/pembalikan ...") berbeda, jadi
            // kelompok yang sudah pernah dibalik tidak ikut jadi kandidat.
            ->where('keterangan', "Pembayaran {$metodeLama} {$noNota}")
            ->whereDate('tgl_jurnal', $tgl)
            ->select('no_referensi', DB::raw('SUM(debet) as total_debet'))
            ->groupBy('no_referensi')
            ->get()
            ->filter(fn ($g) => abs((float) $g->total_debet - (float) $pembayaran->nominal) < 0.005);

        return $grup->count() === 1 ? $grup->first()->no_referensi : null;
    }

    /**
     * Geser kolom kas sesi (saldo_akhir / total_transfer) dengan guard:
     * hasil tidak boleh minus. Koreksi memindahkan klasifikasi uang, bukan
     * mengeluarkan uang - saldo minus berarti datanya sudah tidak konsisten
     * dan lebih baik ditolak daripada diam-diam dikoreksi.
     */
    private static function geserKolomKas(object $kas, string $kolom, float $delta): void
    {
        if (abs($delta) < 0.005) {
            return;
        }

        $kini = (float) DB::table('kas_harian')->where('id_kas', $kas->id_kas)->lockForUpdate()->value($kolom);
        if ($kini + $delta < -0.005) {
            throw new \RuntimeException(
                "Koreksi membuat {$kolom} sesi kas menjadi minus (Rp " . number_format($kini + $delta, 0, ',', '.')
                . ') - data kas sesi ini tidak konsisten, koreksi ditolak.'
            );
        }

        DB::table('kas_harian')->where('id_kas', $kas->id_kas)->increment($kolom, $delta);
    }
}
