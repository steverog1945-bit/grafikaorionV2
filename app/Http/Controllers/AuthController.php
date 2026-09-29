<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin() { return view('login'); }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Kunci sesi: URL "intended" adalah jejak SESI LAMA (dibulatkan
            // oleh redirect() saat user dipaksa ke /login, atau URL terakhir
            // sebelum logout di browser yang sama). Tidak boleh dipercaya
            // mentah-mentah: role BARU yang baru saja login bisa saja tidak
            // punya izin melihat halaman itu (mis. halaman khusus
            // Administrator) dan langsung menabrak 403. Validasikan dulu
            // dengan aturan yang SAMA dengan CheckMenuAccess; kalau gagal,
            // jatuh ke dashboard - halaman yang aman untuk semua role.
            $tujuan = 'dashboard';
            $intended = $request->session()->get('url.intended');

            if (is_string($intended) && $intended !== '') {
                $path = trim(parse_url($intended, PHP_URL_PATH) ?: '', '/');

                if ($path !== '' && \App\Support\Izin::bolehPath($request->user(), $path)) {
                    $tujuan = $intended;
                }

                // Selalu dihapus: sesi baru harus mulai bersih, jejak sesi
                // lama tidak boleh hidup lagi di dalamnya.
                $request->session()->remove('url.intended');
            }

            return redirect()->to($tujuan);
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
