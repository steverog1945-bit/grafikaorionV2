<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Sudah login -> langsung ke dashboard, bukan menampilkan form login lagi.
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return response()->view('login')->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Selalu mendarat di Dashboard. URL "intended" (jejak halaman
            // terakhir sesi lama / halaman yang memaksa login) sengaja
            // DIBUANG: sesi baru harus mulai bersih dari dashboard, dan role
            // baru tidak boleh terbawa ke halaman yang tidak berhak dilihat.
            $request->session()->forget('url.intended');

            return redirect()->route('dashboard');
        }

        return back()->withErrors(['username' => 'User tidak ditemukan atau password salah.']);
    }

    public function logout(Request $request)
    {
        // Lupakan URL terakhir milik sesi ini SEBELUM sesi dihancurkan.
        // Tanpa ini, redirect()->intended() di login() bisa membawa user
        // berikutnya (role lain, browser yang sama) ke halaman terakhir
        // sesi lama yang tidak berhak dia lihat - inilah akar bug
        // "logout lalu login akun produksi = error 403".
        $request->session()->remove('url.intended');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
