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
            $kasKecil->delete();
        });
    }
}
