<?php

namespace App\Services;

use App\Models\KasKecil;
use Illuminate\Support\Facades\DB;

/** Dipanggil lewat approval 'batalkan_kas_kecil' (lihat ApprovalController). */
class KasKecilService
{
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
            $kasKecil->delete();
        });
    }
}
