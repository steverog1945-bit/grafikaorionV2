<?php

namespace App\Services;

use App\Models\KasKecil;
use Illuminate\Support\Facades\DB;

/** Dipanggil lewat approval 'batalkan_kas_kecil' (lihat ApprovalController). */
class KasKecilService
{
    /**
     * Pastikan saldo Kas Kecil (akun 1005 menurut jurnal) cukup untuk mengeluarkan $nominal.
     * Wajib dipanggil di dalam DB::transaction: baris akun 1005 dikunci sampai transaksi selesai, jadi dua
     * pengeluaran bersamaan tidak sama-sama lolos dengan membaca saldo yang sama. Dipakai oleh pengeluaran
     * manual, bayar hutang lewat Kas Kecil, komisi desainer, dan upah jahit.
     */
    public static function pastikanSaldoCukup(float $nominal): void
    {
        DB::table('bag_akun')->where('kode_akun', '1005')->lockForUpdate()->first();
        $saldo = PembayaranHutangService::saldoKasKecil();
        if (round($nominal, 2) > $saldo + 0.01) {
            throw new \RuntimeException('Saldo Kas Kecil (Rp ' . number_format(max(0, $saldo), 0, ',', '.') . ') tidak cukup untuk mengeluarkan Rp ' . number_format($nominal, 0, ',', '.') . '. Isi Kas Kecil dulu.');
        }
    }

    public static function batalkan(int $id): void
    {
        DB::transaction(function () use ($id) {
            $kasKecil = KasKecil::whereKey($id)->lockForUpdate()->first();

            if (!$kasKecil) {
                throw new \RuntimeException('Catatan kas kecil tidak ditemukan atau sudah dibatalkan.');
            }

            AccountingService::reverseByReferensi('KK-' . $kasKecil->id);
            // Setoran dari laci: baris cerminnya di Kas Tak Terduga ikut dibatalkan (laci kembali).
            DB::table('kas_manual')->where('keterangan', 'like', "%[KK-{$kasKecil->id}]%")->delete();
            // Pembayaran komisi desainer dibatalkan -> komisi kembali Belum dibayar.
            DB::table('transaksi_detail')->where('id_kas_kecil_komisi', $kasKecil->id)->update(['komisi_status' => 'Belum', 'komisi_dibayar_at' => null, 'id_kas_kecil_komisi' => null]);
            // Pembayaran upah jahit dibatalkan -> catatan jahitnya kembali belum dibayar.
            DB::table('jasa_jahit_catat')->where('id_kas_kecil', $kasKecil->id)->update(['id_kas_kecil' => null, 'dibayar_at' => null, 'updated_at' => now()]);
            $kasKecil->delete();
        });
    }
}
