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
Schedule::command('backup:run monthly')->monthlyOn(1, '02:00')->withoutOverlapping();
