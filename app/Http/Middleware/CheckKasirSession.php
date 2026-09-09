<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckKasirSession
{
    public function handle(Request $request, Closure $next)
{
    // 1. Cek sesi yang sedang OPEN
    $sesi = DB::table('kas_harian')
        ->where('status_lock', 'open')
        ->where('id_user', auth()->id())
        ->first();

    // 2. LOGIKA PERBAIKAN:
    // Jika tidak ada sesi OPEN DAN user TIDAK sedang mengakses halaman kelola kas atau simpan kas
    if (!$sesi) {
        // Cek apakah URL saat ini adalah halaman untuk buka kas
        // Pastikan route name atau URL ini sesuai dengan web.php Anda
        if (!$request->is('kasir/kelola-kas*') && !$request->is('kasir/store-kas*')) {
            return redirect()->route('kasir.kelola-kas')->with('error', 'Wajib melakukan Open Saldo!');
        }
    }

    return $next($request);
}
}