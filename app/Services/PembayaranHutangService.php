<?php

namespace App\Services;

use App\Models\PembelianBahan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Accounts Payable & Payment Management sisi supplier (roadmap LEVEL 1).
 * Semua perubahan status/sisa hutang faktur pembelian lewat sini: bayar
 * (cicilan atau pelunasan), void pembayaran, dan pembatalan faktur - masing-
 * masing memakai lockForUpdate pada faktur dan menjurnal lewat
 * AccountingService (yang menolak posting ke periode yang sudah ditutup).
 *
 * Void & pembatalan faktur dijalankan HANYA lewat persetujuan (lihat
 * ApprovalController) - bukan dipanggil langsung dari tombol.
 */
class PembayaranHutangService
{
    private const TOLERANSI = 0.01;

    public static function totalDibayar(int $idPembelian): float
    {
        return (float) DB::table('pembayaran_hutang')
            ->where('id_pembelian', $idPembelian)
            ->whereNull('voided_at')
            ->sum('nominal');
    }

    public static function sisa(object $pembelian): float
    {
        return max(0.0, round((float) $pembelian->grand_total - self::totalDibayar((int) $pembelian->id_pembelian), 2));
    }

    /** Pembelian Cash: uang langsung keluar saat faktur dibuat (jurnal sudah di referensi faktur). */
    public static function catatPembayaranAwal(int $idPembelian, float $nominal, string $tanggal): void
    {
        DB::table('pembayaran_hutang')->insert([
            'id_pembelian' => $idPembelian,
            'tanggal' => $tanggal,
            'nominal' => $nominal,
            'metode' => 'Tunai',
            'keterangan' => 'Dibayar tunai saat faktur dibuat',
            'bagian_faktur' => true,
            'id_user' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public static function bayar(int $idPembelian, float $nominal, string $metode, ?string $tanggal = null, ?string $keterangan = null): void
    {
        DB::transaction(function () use ($idPembelian, $nominal, $metode, $tanggal, $keterangan) {
            $pembelian = DB::table('pembelian_bahan')->where('id_pembelian', $idPembelian)->whereNull('deleted_at')->lockForUpdate()->first();

            if (!$pembelian) {
                throw new \RuntimeException('Faktur pembelian tidak ditemukan.');
            }

            $nominal = round($nominal, 2);
            $sisa = self::sisa($pembelian);

            if ($sisa <= self::TOLERANSI) {
                throw new \RuntimeException('Faktur ini sudah lunas.');
            }

            if ($nominal <= 0) {
                throw new \RuntimeException('Nominal pembayaran harus lebih dari 0.');
            }

            if ($nominal > $sisa + self::TOLERANSI) {
                throw new \RuntimeException('Nominal melebihi sisa hutang (Rp ' . number_format($sisa, 0, ',', '.') . ').');
            }

            $idPembayaran = DB::table('pembayaran_hutang')->insertGetId([
                'id_pembelian' => $idPembelian,
                'tanggal' => $tanggal ?: now()->toDateString(),
                'nominal' => $nominal,
                'metode' => $metode,
                'keterangan' => $keterangan,
                'id_user' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $ref = "{$pembelian->no_faktur}/BYR-{$idPembayaran}";
            DB::table('pembayaran_hutang')->where('id', $idPembayaran)->update(['no_referensi_jurnal' => $ref]);

            AccountingService::postPembayaranHutang($ref, $pembelian->no_faktur, $nominal, $metode);

            self::sinkronStatus($idPembelian);
        });
    }

    /** Dipanggil lewat approval 'void_pembayaran_hutang'. */
    public static function voidPembayaran(int $idPembayaran, string $alasan): void
    {
        DB::transaction(function () use ($idPembayaran, $alasan) {
            $bayar = DB::table('pembayaran_hutang')->where('id', $idPembayaran)->lockForUpdate()->first();

            if (!$bayar) {
                throw new \RuntimeException('Pembayaran tidak ditemukan.');
            }

            if ($bayar->voided_at) {
                throw new \RuntimeException('Pembayaran ini sudah di-void sebelumnya.');
            }

            if ($bayar->bagian_faktur || !$bayar->no_referensi_jurnal) {
                throw new \RuntimeException('Pembayaran awal faktur tidak bisa di-void sendiri - batalkan fakturnya.');
            }

            // Kunci faktur dulu supaya urutan lock selalu faktur -> pembayaran lain.
            DB::table('pembelian_bahan')->where('id_pembelian', $bayar->id_pembelian)->lockForUpdate()->first();

            AccountingService::reverseByReferensi($bayar->no_referensi_jurnal);

            DB::table('pembayaran_hutang')->where('id', $idPembayaran)->update([
                'voided_at' => now(),
                'void_alasan' => $alasan,
                'updated_at' => now(),
            ]);

            self::sinkronStatus((int) $bayar->id_pembelian);
        });
    }

    /** Dipanggil lewat approval 'batalkan_pembelian'. Faktur di-soft-delete; jurnalnya dibalik (bukan dihapus). */
    public static function batalkanFaktur(int $idPembelian, string $alasan): void
    {
        DB::transaction(function () use ($idPembelian, $alasan) {
            $pembelian = PembelianBahan::whereKey($idPembelian)->lockForUpdate()->first();

            if (!$pembelian) {
                throw new \RuntimeException('Faktur pembelian tidak ditemukan atau sudah dibatalkan.');
            }

            $adaCicilanAktif = DB::table('pembayaran_hutang')
                ->where('id_pembelian', $idPembelian)
                ->whereNull('voided_at')
                ->where('bagian_faktur', false)
                ->exists();

            if ($adaCicilanAktif) {
                throw new \RuntimeException("Faktur {$pembelian->no_faktur} masih punya pembayaran aktif - void pembayarannya dulu.");
            }

            // Membalik semua jurnal berreferensi no_faktur: pencatatan pembelian
            // + pembayaran awal/pelunasan lama yang ikut di referensi itu.
            AccountingService::reverseByReferensi($pembelian->no_faktur);

            DB::table('pembayaran_hutang')
                ->where('id_pembelian', $idPembelian)
                ->whereNull('voided_at')
                ->update(['voided_at' => now(), 'void_alasan' => 'Faktur dibatalkan: ' . $alasan, 'updated_at' => now()]);

            $pembelian->delete();
        });
    }

    private static function sinkronStatus(int $idPembelian): void
    {
        $pembelian = DB::table('pembelian_bahan')->where('id_pembelian', $idPembelian)->first();
        $lunas = self::sisa($pembelian) <= self::TOLERANSI;

        DB::table('pembelian_bahan')->where('id_pembelian', $idPembelian)->update([
            'status_pembayaran' => $lunas ? 'Lunas' : 'Hutang',
            'tgl_pelunasan' => $lunas ? now() : null,
            'updated_at' => now(),
        ]);
    }
}
