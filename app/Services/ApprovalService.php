<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Approval Workflow generik (roadmap LEVEL 1 - WAJIB). Prinsipnya:
 * aksi yang butuh persetujuan TIDAK dieksekusi langsung oleh pengaju -
 * cuma DIAJUKAN (ajukan()), tersimpan di approval_requests dengan status
 * Menunggu. Eksekusi SUNGGUHAN baru terjadi saat approver memanggil
 * setujui() dengan callback yang menjalankan logic bisnis yang
 * sebenarnya - service ini SENGAJA tidak tahu apa-apa soal logic tiap
 * jenis aksi (batalkan nota, dst), cuma mengurus siklus hidup
 * pengajuan/keputusannya. Pemetaan tipe_aksi -> callback eksekusi ada di
 * controller pemanggil (lihat ApprovalController::approve()).
 */
class ApprovalService
{
    public static function ajukan(
        string $tipeAksi,
        ?string $referensiType,
        ?int $referensiId,
        array $payload,
        string $ringkasan,
        ?string $alasanPengajuan = null
    ): ApprovalRequest {
        return ApprovalRequest::create([
            'tipe_aksi' => $tipeAksi,
            'referensi_type' => $referensiType,
            'referensi_id' => $referensiId,
            'payload' => $payload,
            'ringkasan' => $ringkasan,
            'requested_by' => Auth::id(),
            'alasan_pengajuan' => $alasanPengajuan,
            'status' => 'Menunggu',
        ]);
    }

    /**
     * @param  \Closure(array $payload): void  $eksekusi  Dijalankan HANYA kalau pengajuan masih Menunggu, di dalam transaction yang sama dengan perubahan status approval-nya
     */
    public static function setujui(ApprovalRequest $req, \Closure $eksekusi, ?string $catatan = null): void
    {
        DB::transaction(function () use ($req, $eksekusi, $catatan) {
            // lockForUpdate() - dua approver mengklik "Setujui" nyaris
            // bersamaan pada pengajuan yang sama tidak boleh sama-sama
            // lolos & mengeksekusi aksi bisnisnya dua kali.
            $fresh = ApprovalRequest::whereKey($req->id)->lockForUpdate()->firstOrFail();

            if ($fresh->status !== 'Menunggu') {
                throw new \RuntimeException('Pengajuan ini sudah diputuskan sebelumnya (status: ' . $fresh->status . ').');
            }

            $eksekusi($fresh->payload ?? []);

            $fresh->update([
                'status' => 'Disetujui',
                'decided_by' => Auth::id(),
                'catatan_keputusan' => $catatan,
                'decided_at' => now(),
            ]);
        });
    }

    public static function tolak(ApprovalRequest $req, ?string $catatan = null): void
    {
        DB::transaction(function () use ($req, $catatan) {
            $fresh = ApprovalRequest::whereKey($req->id)->lockForUpdate()->firstOrFail();

            if ($fresh->status !== 'Menunggu') {
                throw new \RuntimeException('Pengajuan ini sudah diputuskan sebelumnya (status: ' . $fresh->status . ').');
            }

            $fresh->update([
                'status' => 'Ditolak',
                'decided_by' => Auth::id(),
                'catatan_keputusan' => $catatan,
                'decided_at' => now(),
            ]);
        });
    }
}
