<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Backup & Recovery (roadmap LEVEL 1 - WAJIB). Butuh `php artisan
// schedule:work` (atau Task Scheduler Windows tiap menit memanggil
// `php artisan schedule:run`) benar-benar berjalan di server produksi -
// jadwal ini sendiri tidak aktif tanpa proses scheduler tersebut.
Schedule::command('backup:run daily')->dailyAt('01:00')->withoutOverlapping();
Schedule::command('backup:run weekly')->weeklyOn(1, '01:30')->withoutOverlapping();
// Perawatan data: cache/sesi kedaluwarsa, log audit lama ke arsip. Setelah backup harian.
Schedule::command('data:rapikan')->dailyAt('03:00')->withoutOverlapping();
// Pemeriksaan pembukuan (jurnal seimbang, Kas Kecil vs jurnal, piutang vs sisa tagihan, stok, collation).
// Temuan muncul di lonceng notifikasi dan halaman Audit Bulanan.
Schedule::command('audit:integritas')->dailyAt('04:00')->withoutOverlapping();
Schedule::command('backup:run monthly')->monthlyOn(1, '02:00')->withoutOverlapping();
// Verifikasi integritas arsip backup harian (jalankan setelah backup:run 01:00).
Schedule::command('backup:verify daily')->dailyAt('01:45')->withoutOverlapping();
// Audit AI teks bebas berkas vs fakta nota (advisory, Ollama lokal, ~35-77
// detik per nota): default memindai nota 7 hari terakhir. Jam 02:30 - di
// luar jam sibuk dan tidak menabrak backup monthly tanggal 1 jam 02:00.
Schedule::command('nota:audit-ai')->dailyAt('02:30')->withoutOverlapping();
// Pemantauan laravel.log (audit run-1, GO audit-log): pindai error tiap 15 menit,
// tulis ringkasan kecil ke storage/logs/monitor-log-periksa.log. exit 1 di atas ambang;
// pantau berkas ini secara manual / terhubung ke WA/Notifikasi nanti.
Schedule::command('log:periksa --ambang=20 --contoh=3')->everyFifteenMinutes()->withoutOverlapping()->appendOutputTo(storage_path('logs/monitor-log-periksa.log'));
