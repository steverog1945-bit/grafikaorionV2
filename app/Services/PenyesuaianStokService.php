<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Eksekusi penyesuaian stok manual satu-bahan (jalur cepat di Kartu Stok).
 * Dipanggil HANYA dari ApprovalController::eksekusi() setelah pengajuan
 * bertipe 'penyesuaian_stok_manual' disetujui - KartuStokController::
 * storeAdjustment cuma mengajukan, tidak pernah mengubah stok sendiri.
 */
class PenyesuaianStokService
{
    public static function eksekusi(array $payload): void
    {
        DB::transaction(function () use ($payload) {
            $bahan = DB::table('bahan_baku')
                ->where('id_bahan', $payload['id_bahan'] ?? null)
                ->lockForUpdate()
                ->first();

            if (!$bahan) {
                throw new \RuntimeException('Bahan pada pengajuan ini sudah tidak ditemukan.');
            }

            // Stok TERKINI dibaca ulang di dalam lock - bisa saja sudah
            // berubah sejak pengajuan dibuat.
            $stokSebelum = (float) $bahan->stok;

            if ($payload['tipe'] === 'OUT') {
                $qty = (float) $payload['qty'];

                if ($qty > $stokSebelum) {
                    throw new \RuntimeException(
                        "Stok {$bahan->nama_bahan} tersisa " . number_format($stokSebelum, 2) .
                        ", tidak bisa dikeluarkan " . number_format($qty, 2) . '.'
                    );
                }

                $stokSesudah = $stokSebelum - $qty;
            } else {
                $stokSesudah = (float) $payload['stok_baru'];
                $qty = abs($stokSesudah - $stokSebelum);
            }

            DB::table('bahan_baku')
                ->where('id_bahan', $bahan->id_bahan)
                ->update(['stok' => $stokSesudah]);

            DB::table('kartu_stok')->insert([
                'id_bahan'     => $bahan->id_bahan,
                'tanggal'      => now(),
                'tipe'         => $payload['tipe'],
                'qty'          => $qty,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $stokSesudah,
                'referensi'    => $payload['tipe'] === 'OUT' ? 'MANUAL-KELUAR' : 'STOCK-OPNAME',
                'keterangan'   => $payload['keterangan'],
                'created_by'   => Auth::id(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        });
    }
}
