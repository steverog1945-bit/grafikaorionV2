<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware autentikasi REST API publik.
 *
 * Memeriksa header `Authorization: Bearer <api_token>` atau query parameter
 * `?api_token=<api_token>`. Token harus cocok dengan kolom users.api_token.
 *
 * Middleware ini sengaja terpisah dari web session auth agar API dapat
 * diakses dari aplikasi eksternal (mobile app, e-commerce, integrasi) tanpa
 * perlu cookie/session.
 */
class ApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?? $request->query('api_token');

        if (!$token) {
            return response()->json([
                'error' => 'Token API diperlukan. Sertakan header Authorization: Bearer <token> atau parameter ?api_token=<token>.',
            ], 401);
        }

        $user = \App\Models\User::where('api_token', $token)->first();

        if (!$user) {
            return response()->json([
                'error' => 'Token API tidak valid.',
            ], 401);
        }

        // Pasang user ke request dan auth guard untuk controller yang
        // memakai auth()->user() atau $request->user().
        auth()->setUser($user);

        return $next($request);
    }
}
