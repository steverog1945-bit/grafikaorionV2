<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogger;
use App\Services\LandingPageHomepageResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Homepage publik: '/'.
     *
     * Flag OFF -> homepage existing (login.blade.php).
     * Flag ON  -> builder homepage, KALAU published revision valid.
     *            Kalau tidak valid -> fallback homepage existing.
     *
     * Route ini TIDAK lagi bernama 'login' (lihat catatan routes/web.php):
     * nama 'login' dipindah ke endpoint /masuk yang selalu memuat form login.
     */
    public function showLogin(Request $request, LandingPageHomepageResolver $resolver)
    {
        return $this->halamanBeranda($request, $resolver, false);
    }

    /**
     * Endpoint login khusus staf (Checkpoint 4E.1): /masuk (route name 'login').
     *
     * Sifat penting: method ini MELOMPATI resolver sama sekali. Jadi berapa pun
     * nilai feature flag - ON, OFF, atau database tidak terbaca - halaman ini
     * pasti memakai view 'login' yang memuat dialog masuk. Inilah jaminan bahwa
     * route('login') selalu mengarah ke sesuatu yang menyediakan akses login.
     */
    public function formLogin(Request $request)
    {
        return $this->halamanBeranda($request, null, true);
    }

    /**
     * Satu jalur render untuk '/' dan '/masuk'.
     */
    private function halamanBeranda(Request $request, ?LandingPageHomepageResolver $resolver, bool $paksaDialog)
    {
        // Jika sudah login DAN mengakses endpoint login '/masuk' ($paksaDialog=true),
        // SELALU redirect ke dashboard - ini adalah kontrak login endpoint.
        // Bedanya dengan '/': saat Gate B ON, '/' menampilkan Landing Page
        // untuk SEMUA peran, tapi '/masuk' tetap adalah login endpoint.
        if (Auth::check() && $paksaDialog) {
            return redirect()->route('dashboard');
        }

        // Sudah login tapi mengakses ROOT '/' tanpa paksaDialog:
        // - Gate B OFF: redirect ke dashboard (perilaku lama)
        // - Gate B ON : tampilkan Landing Page Published (kontrak operator)
        if (Auth::check() && ! $paksaDialog && ! config('landing_page_builder.enabled', false)) {
            return redirect()->route('dashboard');
        }

        // Jalur "/masuk": halaman LOGIN ERP KHUSUS KARYAWAN.
        //
        // View 'masuk' + layout 'publik.layout-masuk' (terpisah dari
        // template Landing Page lama). Form tidak lagi diletakkan di atas
        // hero, daftar layanan, atau section Landing Page lama.
        //
        // Yang TIDAK berubah: jalur data, pemanggilan renderer builder,
        // penanganan AJAX, session, dan seluruh logika autentikasi.
        if ($paksaDialog) {
            $data = PublikController::dataBeranda($request);

            // CAPTCHA keamanan: tampil setelah 3 percobaan gagal dari IP ini.
            // Jawaban disimpan di session agar cocok dengan pertanyaan yang
            // dirender, dan counter gagal disimpan di cache (database) agar
            // tetap berlaku walau sesi direset.
            $ip = (string) $request->ip();
            $failCount = (int) Cache::get('login_fail_ip:' . $ip, 0);
            $showCaptcha = $failCount >= 3;
            $captchaQuestion = null;
            if ($showCaptcha) {
                $a = random_int(1, 9);
                $b = random_int(1, 9);
                $request->session()->put('login_captcha_answer', $a + $b);
                $captchaQuestion = "Berapa hasil {$a} + {$b}?";
            } else {
                $request->session()->forget('login_captcha_answer');
            }
            $data['showCaptcha'] = $showCaptcha;
            $data['captchaQuestion'] = $captchaQuestion;

            if ($request->ajax()) {
                return PublikController::jawabanDaftar($data);
            }

            return response()->view('masuk', $data)->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            ]);
        }

        // Landing Page Builder public renderer (Checkpoint 4D.1).
        // Resolver mengembalikan mode 'existing' kalau feature flag OFF atau
        // published revision tidak valid -> perilaku homepage lama tetap utuh.
        $homepage = $resolver->resolve($request);

        if ($homepage['mode'] === 'builder') {
            return response()->view($homepage['view'], [
                'sections' => $homepage['data']['sections'] ?? [],
                'theme' => $homepage['data']['theme'] ?? [],
                'seo' => $homepage['data']['seo'] ?? [],
                'set' => \App\Models\AppSetting::current(),
            ])->withHeaders([
                'Cache-Control' => 'public, max-age=60',
            ]);
        }

        // Halaman depan = daftar tulisan publik + dialog masuk.
        $data = PublikController::dataBeranda($request);
        if ($request->ajax()) {
            return PublikController::jawabanDaftar($data);
        }

        // Baru saja ditolak dari halaman dalam: dialog masuk dibuka sendiri, sekali.
        $data['bukaMasuk'] = (bool) $request->session()->pull('url.intended');

        return response()->view('login', $data)->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|max:255',
            'password' => 'required|max:255',
        ]);

        $ip = (string) $request->ip();
        $failKey = 'login_fail_ip:' . $ip;
        $fails = (int) Cache::get($failKey, 0);

        // HARD LOCKOUT (penguatan login): CAPTCHA saja tidak menghentikan
        // brute-force - percobaan boleh terus berjalan selamanya selama
        // captcha terjawab (atau dijawab otomatis). Kini:
        //   - 10 kegagalan dalam 15 menit dari satu IP  -> IP dikunci 15 menit.
        //   - 10 kegagalan dalam 15 menit untuk satu username -> username itu
        //     dikunci 15 menit (menyebarnya serangan ke banyak IP tetap
        //     berhenti; serangan ke BANYAK username lewat 1 IP ditahan lock IP).
        // Password BENAR pun ditolak selama terkunci - pesannya tidak membocorkan
        // apakah password benar. Pelindung terakhir tetap throttle:login (5/mnt).
        $userKey = 'login_fail_user:' . strtolower($credentials['username']);
        $lockIp = 'login_lock_ip:' . $ip;
        $lockUser = 'login_lock_user:' . strtolower($credentials['username']);

        if (Cache::get($lockIp) || Cache::get($lockUser)) {
            $userDikenal = User::where('username', $credentials['username'])->value('id_user');
            AuditLogger::log('login_gagal', 'users', $userDikenal ? (int) $userDikenal : null, null, null,
                'Percobaan masuk saat terkunci (brute-force lockout) - IP ' . $ip);

            return redirect()->route('login')->withErrors([
                'username' => 'Terlalu banyak percobaan gagal. Coba lagi dalam 15 menit.',
            ])->withInput($request->only('username'));
        }

        // CAPTCHA keamanan: setelah 3 percobaan gagal dari IP ini, jawaban
        // wajib benar sebelum percobaan login diproses.
        if ($fails >= 3) {
            $expected = $request->session()->get('login_captcha_answer');
            $given = trim((string) $request->input('captcha', ''));
            if ($expected === null || (int) $given !== (int) $expected) {
                $failsBaru = $fails + 1;
                Cache::put($failKey, $failsBaru, now()->addMinutes(15));
                $this->naikkanLockout($userKey, $lockIp, $lockUser, $failsBaru);
                // Siapkan pertanyaan baru untuk render berikutnya.
                $a = random_int(1, 9);
                $b = random_int(1, 9);
                $request->session()->put('login_captcha_answer', $a + $b);
                return redirect()->route('login')->withErrors([
                    'captcha' => 'Jawaban keamanan salah. Coba lagi.',
                ])->withInput($request->only('username'));
            }
        }

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password'], 'is_active' => 1])) {
            Cache::forget($failKey);
            Cache::forget($userKey);
            $request->session()->forget('login_captcha_answer');
            $request->session()->regenerate();

            // Selalu mendarat di Dashboard. URL "intended" (jejak halaman
            // terakhir sesi lama / halaman yang memaksa login) sengaja
            // DIBUANG: sesi baru harus mulai bersih dari dashboard, dan role
            // baru tidak boleh terbawa ke halaman yang tidak berhak dilihat.
            $request->session()->forget('url.intended');

            AuditLogger::log('login', 'users', (int) $request->user()->id_user, null, null, 'Masuk ke sistem');

            return redirect()->route('dashboard');
        }

        // Akun dikenal & password benar tapi DINON-AKTIFKAN: pesannya harus
        // jelas (bukan berdiam di "User tidak ditemukan atau password salah"
        // sehingga karyawan resign mengira credentialnya salah).
        $akunTidakAktif = User::where('username', $credentials['username'])
            ->where('is_active', 0)
            ->first();
        if ($akunTidakAktif && Hash::check($credentials['password'], $akunTidakAktif->password)) {
            AuditLogger::log('login_gagal', 'users', (int) $akunTidakAktif->id_user, null, null, 'Percobaan masuk dengan akun yang sudah dinon-aktifkan');

            return redirect()->route('login')->withErrors([
                'username' => 'Akun ini sudah dinon-aktifkan (karyawan resign / akun tidak lagi dipakai). Hubungi Administrator bila seharusnya aktif.',
            ])->withInput($request->only('username'));
        }

        // Username yang diketik HANYA dicatat bila memang akun yang ada: orang
        // sering salah mengetik password di kolom username, dan itu tidak boleh
        // berakhir di log. Password tidak pernah dicatat.
        $dikenal = User::where('username', $credentials['username'])->value('id_user');
        AuditLogger::log(
            'login_gagal',
            'users',
            $dikenal ? (int) $dikenal : null,
            null,
            null,
            $dikenal ? 'Percobaan masuk gagal untuk akun ' . $credentials['username'] : 'Percobaan masuk gagal (username tidak dikenal)'
        );

        // Naikkan counter gagal (TTL 15 menit) agar CAPTCHA aktif setelah 3x,
        // dan lockout hard setelah 10x (lihat catatan di atas method).
        $failsBaru = $fails + 1;
        Cache::put($failKey, $failsBaru, now()->addMinutes(15));
        $this->naikkanLockout($userKey, $lockIp, $lockUser, $failsBaru);
        if ($failsBaru >= 3) {
            $a = random_int(1, 9);
            $b = random_int(1, 9);
            $request->session()->put('login_captcha_answer', $a + $b);
        } else {
            $request->session()->forget('login_captcha_answer');
        }

        // Login gagal harus kembali ke endpoint yang PASTI punya form login.
        // Sebelumnya `back()`: kalau form dibuka dari homepage '/' dan Landing
        // Page Builder aktif, '/' menyajikan halaman builder tanpa dialog -
        // pesan error jadi tak terlihat dan staf tidak bisa mencoba lagi.
        // route('login') kini '/masuk', yang form-nya selalu ada.
        return redirect()->route('login')->withErrors([
            'username' => 'User tidak ditemukan atau password salah.',
        ])->withInput($request->only('username'));
    }

    /**
     * Counter kegagalan per username + pemasangan lockout hard (penguatan
     * login). Dipanggil di setiap jalur kegagalan login; sukses login justru
     * MENGHAPUS counter (lihat login()). Lock TTL 15 menit.
     */
    private function naikkanLockout(string $userKey, string $lockIp, string $lockUser, int $failsIpBaru): void
    {
        $failsUserBaru = (int) Cache::get($userKey, 0) + 1;
        Cache::put($userKey, $failsUserBaru, now()->addMinutes(15));

        if ($failsIpBaru >= 10) {
            Cache::put($lockIp, now()->toIso8601String(), now()->addMinutes(15));
        }
        if ($failsUserBaru >= 10) {
            Cache::put($lockUser, now()->toIso8601String(), now()->addMinutes(15));
        }
    }

    public function logout(Request $request)
    {
        // Lupakan URL terakhir milik sesi ini SEBELUM sesi dihancurkan.
        // Tanpa ini, redirect()->intended() di login() bisa membawa user
        // berikutnya (role lain, browser yang sama) ke halaman terakhir
        // sesi lama yang tidak berhak dia lihat - inilah akar bug
        // "logout lalu login akun produksi = error 403".
        $request->session()->remove('url.intended');

        if (Auth::check()) {
            AuditLogger::log('logout', 'users', (int) Auth::id(), null, null, 'Keluar dari sistem');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // TARGET LOGOUT BERGANTUNG PADA GATE B.
        //
        // Sebelumnya selalu redirect('/'). Akibatnya:
        //  - Gate B OFF -> '/' me-render halaman depan LAMA (view 'login' /
        //    publik/layout) yang memakai header, hero, dan section lama.
        //    Staf lalu melihat "Landing Page lama" sesaat setelah keluar,
        //    DAN karakter '{' ikut tampil karena view lama itu punya
        //    kurung kurawal nyasar di dalam <body>.
        //
        //  Gate B ON  : '/' adalah Landing Page Published. Pengunjung
        //                (dan staf yang baru keluar) harus melihat beranda
        //                publik terbaru, BUKAN Dashboard.
        //  Gate B OFF : '/' adalah fallback beranda tanpa Builder, jadi
        //                tujuan keluar yang benar adalah endpoint login
        //                publik route('login') = /masuk, yang selalu punya
        //                form masuk dan tidak pernah menampilkan Dashboard.
        //
        // Route name dipakai, bukan path hardcode, supaya aman di root domain
        // maupun instalasi subdirektori (/grafikaorion/public).
        $gateB = (bool) config('landing_page_builder.enabled', false);

        return redirect($gateB ? route('beranda') : route('login'));
    }
}
