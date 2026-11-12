<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Menerima laporan error dari klien (JS) agar error yang "diam" di browser
 * tercatat di server. Tanpa DB, tanpa migration. Batas 4 KB, throttle 30/menit.
 *
 * Diport dari erpV3 (2026-11-12) bersama penanganan window.onerror /
 * unhandledrejection di resources/js/app.js - tanpa itu, endpoint ini tidak
 * pernah dipanggil. Channel log 'client' didefinisikan di config/logging.php
 * dan menulis ke storage/logs/client.log (terpisah dari laravel.log supaya
 * error klien berisik bisa disaring tanpa mengubur log server lainnya).
 */
class ClientLogController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:32'],
            'pesan' => ['required', 'string', 'max:1000'],
            'url' => ['nullable', 'string', 'max:500'],
            'baris' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'kolom' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'stack' => ['nullable', 'string', 'max:2000'],
        ]);

        // Sanitasi: buang query string & fragment dari url (privasi).
        $url = $data['url'] ?? '';
        $url = preg_replace('/[?#].*$/', '', $url);

        Log::channel('client')->warning('client-error', [
            'kode' => $data['kode'],
            'pesan' => $data['pesan'],
            'url' => $url,
            'baris' => $data['baris'] ?? null,
            'kolom' => $data['kolom'] ?? null,
            'stack' => $data['stack'] ?? null,
            'user_id' => optional($request->user())->id_user ?? optional($request->user())->id ?? null,
            'ip' => $request->ip(),
        ]);

        return response()->noContent(); // 204
    }
}
