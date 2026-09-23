<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya jalur penulisan ke audit_logs - dipakai baik oleh trait
 * Auditable (model Eloquent, otomatis) maupun dipanggil manual langsung
 * dari controller yang masih pakai DB::table() (mayoritas app ini).
 *
 * PENTING - keterbatasan yang harus dipahami: trait Auditable HANYA
 * menangkap perubahan lewat Eloquent (Model::create()/update()/delete()).
 * Controller yang menulis lewat DB::table()->insert()/update()/delete()
 * TIDAK otomatis tercatat - itu sebabnya method log() di sini bersifat
 * publik & sengaja dibuat mudah dipanggil manual. Baru menerapkan
 * Auditable + memanggil log() manual DI TITIK MUTASI YANG SPESIFIK
 * membuat baris itu benar-benar tercatat - menambah model/trait saja
 * TIDAK otomatis mengaudit apapun kalau controllernya masih DB::table().
 */
class AuditLogger
{
    public static function log(
        string $event,
        string $auditableType,
        ?int $auditableId,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $keterangan = null
    ): void {
        $user = Auth::user();

        AuditLog::create([
            'user_id' => $user?->id_user,
            'user_label' => $user?->username,
            'event' => $event,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'keterangan' => $keterangan,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'url' => request()?->fullUrl(),
            'ip_address' => request()?->ip(),
            'created_at' => now(),
        ]);
    }

    /**
     * Helper untuk jalur DB::table(): baca baris SEBELUM diubah, lakukan
     * $callback (yang melakukan UPDATE-nya), lalu catat diff old->new.
     * Dipakai supaya controller tidak perlu manual susun old_values.
     *
     * @param  \Closure(): void  $callback
     */
    public static function logUpdate(string $table, string $pk, int $id, \Closure $callback, ?string $keterangan = null): void
    {
        $before = (array) DB::table($table)->where($pk, $id)->first();
        $callback();
        $after = (array) DB::table($table)->where($pk, $id)->first();

        static::log('updated', $table, $id, $before ?: null, $after ?: null, $keterangan);
    }
}
