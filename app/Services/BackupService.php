<?php

namespace App\Services;

use App\Models\BackupLog;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * Backup & Recovery (roadmap LEVEL 1 - WAJIB, "wajib sebelum production").
 * Menjalankan mysqldump ke storage/app/backups/{tipe}/ (di luar public/,
 * tidak pernah bisa diakses lewat web - lihat catatan security di audit)
 * lalu menerapkan retensi (hapus file lama di luar batas config('backup.
 * retention')). Setiap eksekusi dicatat ke backup_logs baik sukses maupun
 * gagal, supaya halaman /backup selalu punya riwayat yang bisa ditelusuri.
 */
class BackupService
{
    public static function run(string $tipe): BackupLog
    {
        $startedAt = now();
        // P1: status awal 'Berjalan' - sebelumnya langsung 'Sukses' padahal
        // dump belum jalan; proses mati mendadak meninggalkan baris
        // "Sukses" kosong (false positive di halaman /backup). Kolom enum
        // diperluas 'Berjalan' lewat migrasi 2026_10_18_000001.
        $log = BackupLog::create(['tipe' => $tipe, 'status' => 'Berjalan', 'started_at' => $startedAt]);

        try {
            $dir = config('backup.disk_path') . DIRECTORY_SEPARATOR . $tipe;
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Dua cadangan di detik yang sama tidak boleh saling menimpa: beri akhiran _2, _3, ...
            $kunci = self::kunciEnkripsi();
            $ext = $kunci ? '.sql.enc' : '.sql';
            $dasar = "backup_{$tipe}_" . $startedAt->format('Ymd_His');
            $filename = $dasar . $ext;
            for ($n = 2; file_exists($dir . DIRECTORY_SEPARATOR . $filename); $n++) {
                $filename = $dasar . '_' . $n . $ext;
            }
            $filePath = $dir . DIRECTORY_SEPARATOR . $filename;

            $dump = self::dump();
            if ($kunci) {
                $dump = self::enkripsiDump($dump, $kunci);
            }
            file_put_contents($filePath, $dump);
            $size = filesize($filePath);

            self::terapkanRetensi($dir, $tipe);

            [$offsiteStatus, $offsiteError] = self::salinKeLokasiKedua($dump, $tipe, $filename);

            $log->update([
                'file_path' => $filePath,
                'ukuran_bytes' => $size,
                'status' => 'Sukses',
                'offsite_status' => $offsiteStatus,
                'offsite_error' => $offsiteError,
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $log->update([
                'status' => 'Gagal',
                'pesan_error' => $e->getMessage(),
                'finished_at' => now(),
            ]);
            throw $e;
        }

        return $log->fresh();
    }

    /**
     * Mencoba mengaktifkan enkripsi at-rest; mengembalikan kunci 32-byte
     * (base64-decoded dari BACKUP_ENCRYPTION_KEY) atau null bila tidak diaktifkan.
     * Gagal keras (die) bila BACKUP_ENCRYPTION_ENABLED=true tetapi kunci absen/invalid —
     * tidak boleh diam-diam menulis dump plaintext.
     */
    private static function kunciEnkripsi(): ?string
    {
        if (!(bool) config('backup.encryption')) {
            return null;
        }
        $kunci = base64_decode((string) config('backup.encryption_key'), true);
        if ($kunci === false || strlen($kunci) !== 32) {
            throw new \RuntimeException('BACKUP_ENCRYPTION_KEY harus base64 dari 32-byte acak (php: base64_encode(random_bytes(32)))');
        }
        return $kunci;
    }

    /** Magic "JLA1" || IV(12) || TAG(16) || ciphertext (AES-256-GCM). */
    private static function enkripsiDump(string $plaintext, string $key): string
    {
        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
        if ($ciphertext === false) {
            throw new \RuntimeException('Enkripsi dump gagal');
        }
        return 'JLA1' . $iv . $tag . $ciphertext;
    }

    /** Untuk restore manual (lihat docs/RESTORE-BACKUP.md). */
    public static function dekripsiDump(string $ciphertext, string $key): string
    {
        if (substr($ciphertext, 0, 4) !== 'JLA1') {
            throw new \RuntimeException('Bukan format backup JLA1');
        }
        $iv = substr($ciphertext, 4, 12);
        $tag = substr($ciphertext, 16, 16);
        $body = substr($ciphertext, 32);
        $out = openssl_decrypt($body, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '');
        if ($out === false) {
            throw new \RuntimeException('Dekripsi gagal (kunci salah atau berkas rusak)');
        }
        return $out;
    }

    private static function dump(): string
    {
        $db = config('database.connections.mysql');

        $command = [
            config('backup.mysqldump_path'),
            '--host=' . $db['host'],
            '--port=' . $db['port'],
            '--user=' . $db['username'],
            '--single-transaction',
            '--routines',
            '--triggers',
            $db['database'],
        ];

        // Password lewat env var MYSQL_PWD (bukan --password= di argumen
        // command) supaya tidak muncul di daftar proses OS - hanya
        // ditambahkan kalau memang ada password (XAMPP root lokal umumnya
        // tanpa password).
        $env = $db['password'] !== '' ? ['MYSQL_PWD' => $db['password']] : null;

        $process = new Process($command, null, $env);
        $process->setTimeout(300);
        $process->mustRun();

        return $process->getOutput();
    }

    /**
     * Salin dump ke disk KEDUA yang terpisah dari server ini (lihat catatan
     * di config/backup.php). Kegagalan di sini SENGAJA tidak melempar
     * exception - backup lokal yang sudah berhasil tidak boleh ikut
     * ditandai gagal gara-gara lokasi kedua bermasalah (mis. koneksi
     * jaringan ke S3 sedang putus); statusnya dicatat terpisah supaya tetap
     * terlihat & bisa memicu notifikasi (lihat NotifikasiService).
     *
     * @return array{0: ?string, 1: ?string} [offsite_status, offsite_error]
     */
    private static function salinKeLokasiKedua(string $dump, string $tipe, string $filename): array
    {
        $disk = config('backup.secondary_disk');
        if (!$disk) {
            return [null, null];
        }

        try {
            Storage::disk($disk)->put($tipe . '/' . $filename, $dump);

            // Retensi yang sama dengan salinan lokal, supaya lokasi kedua tidak tumbuh tanpa batas.
            $maks = (int) config("backup.retention.{$tipe}", 7);
            $berkas = collect(Storage::disk($disk)->files($tipe))
                ->filter(fn ($f) => preg_match('/\.sql(\.enc)?$/', $f) === 1)
                ->sortByDesc(fn ($f) => Storage::disk($disk)->lastModified($f))
                ->values();
            foreach ($berkas->slice($maks) as $lama) {
                Storage::disk($disk)->delete($lama);
            }

            return ['Sukses', null];
        } catch (\Throwable $e) {
            return ['Gagal', $e->getMessage()];
        }
    }

    private static function terapkanRetensi(string $dir, string $tipe): void
    {
        $maxSimpan = config("backup.retention.{$tipe}", 7);

        $files = collect(array_merge(
            glob($dir . DIRECTORY_SEPARATOR . '*.sql'),
            glob($dir . DIRECTORY_SEPARATOR . '*.sql.enc')
        ))
            // Urut waktu ubah lalu nama (berisi cap waktu), supaya berkas di detik yang sama tetap urut benar.
            ->sortByDesc(fn (string $f) => sprintf('%012d', filemtime($f)) . basename($f))
            ->values();

        foreach ($files->slice($maxSimpan) as $fileLama) {
            @unlink($fileLama);
        }
    }
}
