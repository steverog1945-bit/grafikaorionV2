<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'menu.access' => \App\Http\Middleware\CheckMenuAccess::class,
            'restrict.input-only' => \App\Http\Middleware\RestrictInputOnlyRoles::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\HeaderKeamanan::class);

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
