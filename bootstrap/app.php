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
