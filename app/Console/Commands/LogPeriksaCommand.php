<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Pemeriksaan log aplikasi — HANYA MEMBACA. Mencocokkan laravel-*.log
 * (daily driver) dan laravel.log (legacy) untuk baris .ERROR dalam
 * rentang waktu, lalu melaporkan jumlahnya. Kode keluar 1 bila jumlah
 * melewati ambang, 0 bila aman — supaya bisa dipanggil dari penjaga
 * pemantauan (Task Scheduler, skrip monitoring, atau Nagios/Zabbix).
 *
 *   php artisan log:periksa
 *   php artisan log:periksa --menit=1440 --ambang=10
 */
class LogPeriksaCommand extends Command
{
    protected $signature = 'log:periksa
        {--menit=1440 : Rentang waktu mundur dalam menit (default 1440 = 24 jam)}
        {--ambang=20 : Batas jumlah error sebelum keluar non-zero}
        {--contoh=5 : Tampilkan maksimal N contoh baris error terbaru}';

    protected $description = 'Pindai log produksi untuk baris ERROR; exit 1 bila melewati ambang';

    public function handle(): int
    {
        $menit = max(1, (int) $this->option('menit'));
        $ambang = max(1, (int) $this->option('ambang'));
        $mulai = now()->subMinutes($menit);

        $files = glob(storage_path('logs/laravel*.log')) ?: [];

        $terhitung = 0;
        $contoh = [];
        foreach ($files as $file) {
            $fh = @fopen($file, 'r');
            if (!$fh) {
                $this->error("Tidak bisa membaca {$file}");
                continue;
            }
            while (($line = fgets($fh)) !== false) {
                if (strpos($line, '[') !== 0) {
                    continue; // baris stacktrace/continuation dihitung lewat baris induknya
                }
                $ts = \DateTime::createFromFormat('[Y-m-d H:i:s]', substr($line, 0, 21));
                if (!$ts || $ts < $mulai) {
                    continue;
                }
                if (stripos($line, '.ERROR') !== false || stripos($line, 'ERROR:') !== false) {
                    $terhitung++;
                    if (count($contoh) < (int) $this->option('contoh')) {
                        $contoh[] = trim(substr($line, 0, 260));
                    }
                }
            }
            fclose($fh);
        }

        $status = $terhitung >= $ambang ? 'HAMBAT' : 'AMAN';
        $this->line(sprintf('Log error %d menit terakhir: %d baris (ambang %d) — %s', $menit, $terhitung, $ambang, $status));
        foreach ($contoh as $c) {
            $this->line('  - ' . $c);
        }

        return $terhitung >= $ambang ? self::FAILURE : self::SUCCESS;
    }
}
