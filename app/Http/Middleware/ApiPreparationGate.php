<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tutup API eksternal secara default selama fase persiapan.
 * Tidak terpasang pada web, dashboard, PWA manifest, atau checkout Kasir.
 */
class ApiPreparationGate
{
    public function handle(Request $request, Closure $next): Response
    {
        // Agent produksi punya gerbang fail-closed sendiri
        // (monitoring_cetak.enabled) dan token sendiri, jadi tidak ikut
        // tertahan oleh gate persiapan API eksternal.
        if ($request->is('api/monitoring-cetak/agent', 'api/monitoring-cetak/agent/*')) {
            return $next($request);
        }

        if (!config('orion_api.enabled', false)) {
            return response()->json([
                'message' => 'API eksternal masih dalam tahap persiapan.',
            ], 503)->header('Cache-Control', 'no-store');
        }

        return $next($request);
    }
}
