<?php

namespace App\Support;

/**
 * Deteksi "tabel Monitoring Cetak belum ada di database ini".
 *
 * Tiga tabel monitoring_* dibuat oleh migration, tapi halaman ini bisa saja
 * dibuka sebelum migration dijalankan di environment itu - di sana halaman
 * menampilkan instruksi, bukan error 500.
 */
class MonitoringCetakSchemaCheck
{
    private const TABLES = ['monitoring_mesins', 'monitoring_agents', 'monitoring_events'];

    /** Apakah exception ini "table doesn't exist" untuk tabel monitoring kita? */
    public static function isMissingMonitoringTable(\Throwable $e): bool
    {
        $pesan = strtolower($e->getMessage());
        foreach (self::TABLES as $table) {
            if (str_contains($pesan, $table)) {
                return true;
            }
        }

        return false;
    }
}
