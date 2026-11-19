<?php

namespace App\Http\Middleware;

use App\Models\MonitoringAgent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Auth agent spooler di PC produksi: header X-Agent-Token, dicocokkan
 * lewat SHA-256 dengan token yang tersimpan (token plaintext tidak pernah
 * disimpan). Endpoint juga fail-closed sampai monitoring_cetak.enabled
 * dinyalakan di environment (GO operator + owner).
 */
class MonitoringCetakAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('monitoring_cetak.enabled', false)) {
            return response()->json([
                'message' => 'Monitoring Cetak agent API belum diaktifkan.',
            ], 503)->header('Cache-Control', 'no-store');
        }

        $token = $request->header('X-Agent-Token');
        $token = is_string($token) ? trim($token) : '';

        if ($token === '') {
            return response()->json(['message' => 'Token agent wajib dikirim di header X-Agent-Token.'], 401);
        }

        $agent = MonitoringAgent::where('token_hash', hash('sha256', $token))
            ->where('aktif', true)
            ->first();

        if (! $agent) {
            return response()->json(['message' => 'Token agent tidak dikenal/nonaktif.'], 401);
        }

        $agent->forceFill([
            'last_seen_at' => now(),
            'last_ip' => $request->ip(),
        ])->save();

        $request->attributes->set('agent', $agent);

        return $next($request);
    }
}
