<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Penjaga sinkron kolom `transaksi.total_terbayar`.
 *
 * DIPANGGIL WAJIB di setiap titik yang menulis pembayaran_riwayat (insert
 * bayar, void, koreksi metode). Metodenya recompute dari sumber kebenaran
 * (SUM pembayaran sah) - bukan += nominal - supaya idempoten dan tidak bisa
 * menyimpang walau dipanggil dua kali.
 *
 * KEBERANGKASAN DEPLOYMENT: kolom ini baru ada sejak migrasi
 * 2026_11_18_100000. Pada runtime yang BELUM dimigrasi, UPDATE akan gagal
 * (SQL 1054 unknown column) dan - karena dipanggil DI DALAM transaksi
 * pembayaran - dulu seluruh pembayaran IKUT GAGAL ("Terjadi kesalahan
 * sistem. Tidak ada data yang tersimpan"). Sekarang:
 *   - kolom tidak ada  -> recompute dilewati diam-diam (bukan exception),
 *   - kegagalan teknis lain -> dilaporkan ke log, TIDAK melempar ulang ke
 *     transaksi pemanggil (mirror monitoring tidak boleh menggagalkan
 *     pencatatan uang).
 * Kegagalan sinkronisasi tetap terdeteksi oleh `AuditIntegritasCommand`
 * (temuan `total_terbayar_tidak_sinkron`).
 */
class TerbayarSinkron
{
    /** Cache hasil hasColumn per proses - sekali per request, bukan per panggilan. */
    private static ?bool $kolomAda = null;

    public static function sinkronkan(int $idTransaksi): void
    {
        if (self::$kolomAda === null) {
            self::$kolomAda = Schema::hasColumn('transaksi', 'total_terbayar');
        }
        if (! self::$kolomAda) {
            return;
        }

        try {
            $total = (float) DB::table('pembayaran_riwayat')
                ->where('id_transaksi', $idTransaksi)
                ->whereNull('voided_at')
                ->sum('nominal');

            DB::table('transaksi')
                ->where('id_transaksi', $idTransaksi)
                ->update(['total_terbayar' => round($total, 2)]);
        } catch (\Throwable $e) {
            // Mirror monitoring BOLEH gagal, uang TIDAK BOLEH ikut gagal.
            report($e);
        }
    }
}
