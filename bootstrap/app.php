<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Header keamanan dipasang global, bukan per route. Alasannya
        // ada di docblock HeaderKeamanan: header ini harus ikut pada
        // respons 403/404/422 dan redirect juga, bukan cuma halaman sukses.
        $middleware->append(\App\Http\Middleware\HeaderKeamanan::class);

        // Satu submit_id = satu transaksi untuk SEMUA permintaan tulis (klik ganda, coba ulang, dua tab).
        // Tidak melakukan apa pun bila permintaan tidak membawa submit_id. Lihat docblock SubmitSekaliJalan.
        $middleware->appendToGroup('web', \App\Http\Middleware\SubmitSekaliJalan::class);

        // Akun yang dinonaktifkan di tengah sesi langsung dikeluarkan di
        // request berikutnya (sebelumnya: sesi tetap sah sampai kedaluwarsa).
        $middleware->appendToGroup('web', \App\Http\Middleware\PastikanUserAktif::class);

        $middleware->alias([
            'menu.access' => \App\Http\Middleware\CheckMenuAccess::class,
            'restrict.input-only' => \App\Http\Middleware\RestrictInputOnlyRoles::class,
            'throttle.write' => \App\Http\Middleware\ThrottleWriteRequests::class,
            'hanya.admin' => \App\Http\Middleware\HanyaAdministrator::class,
            'sekali.jalan' => \App\Http\Middleware\SubmitSekaliJalan::class,
            'api.auth' => \App\Http\Middleware\ApiAuth::class,
            'api.rbac' => \App\Http\Middleware\ApiRbac::class,
            'monitoring-cetak-agent' => \App\Http\Middleware\MonitoringCetakAgent::class,
        ]);

        // API eksternal masih tahap persiapan. Gate fail-closed hanya untuk
        // routes/api.php; semua route ERP web tetap utuh.
        // Aktifkan hanya setelah RBAC, limiter dan checkout API diperbaiki.
        $middleware->group('api', [
            \App\Http\Middleware\ApiPreparationGate::class,
        ]);

        // Otorisasi menu WAJIB berjalan sebelum route-model binding. Bawaan
        // Laravel menaruh SubstituteBindings lebih dulu, sehingga pengguna
        // tanpa izin mendapat 404 untuk id yang tidak ada dan 403 untuk id
        // yang ada (keberadaan data bisa ditebak), dan query pencarian model
        // berjalan sebelum izin diperiksa.
        $middleware->prependToPriorityList(
            before: \Illuminate\Routing\Middleware\SubstituteBindings::class,
            prepend: \App\Http\Middleware\CheckMenuAccess::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Periode akuntansi ditutup: tampilkan pesan jelas, bukan halaman 500.
        // Transaksi database yang membungkus sudah di-rollback oleh Laravel
        // sebelum exception sampai di sini.
        $exceptions->render(function (\App\Exceptions\PeriodeAkuntansiTertutupException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        });
    })->create();
