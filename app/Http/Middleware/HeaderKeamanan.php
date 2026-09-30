<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header pengaman dasar untuk semua respons web (audit Fase 4).
 *
 * Sengaja TIDAK memasang Content-Security-Policy dan HSTS:
 * - CSP akan memutus halaman yang memuat skrip/gaya dari CDN dan skrip inline
 *   yang dipakai hampir semua modul; perlu pekerjaan tersendiri, bukan tempelan.
 * - HSTS hanya bermakna di HTTPS, sedangkan aplikasi ini dipakai lewat LAN (HTTP).
 */
class HeaderKeamanan
{
    public function handle(Request $request, Closure $next): Response
    {
        $respons = $next($request);

        // Browser tidak boleh menebak tipe berkas (mencegah unggahan dieksekusi sebagai skrip).
        $respons->headers->set('X-Content-Type-Options', 'nosniff');
        // Halaman tidak boleh dibingkai situs lain (clickjacking); cetak/pratinjau satu asal tetap jalan.
        $respons->headers->set('X-Frame-Options', 'SAMEORIGIN');
        // Alamat lengkap (yang memuat nomor nota, id, dst) tidak dikirim ke situs luar.
        $respons->headers->set('Referrer-Policy', 'same-origin');
        // Kamera hanya untuk halaman aplikasi ini sendiri (kasir memindai barcode nota); mikrofon & lokasi tidak dipakai.
        $respons->headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');

        return $respons;
    }
}
