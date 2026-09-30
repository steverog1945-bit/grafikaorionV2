<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Fungsi format angka global (angka, rupiah, angka_polos, angka_input) - pengaman bila autoload composer belum diperbarui.
        require_once app_path('helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        // Hardening P0-4: batasi percobaan login (per kombinasi username+IP,
        // BUKAN per IP saja - supaya satu IP kantor/warnet dengan banyak user
        // tidak saling mengunci) - sebelumnya endpoint /login sama sekali
        // tidak dibatasi, terbuka untuk brute-force password tanpa batas
        // begitu aplikasi online.
        RateLimiter::for('login', function (Request $request) {
            $kunci = \Illuminate\Support\Str::lower((string) $request->input('username')) . '|' . $request->ip();

            return Limit::perMinute(5)->by($kunci)->response(function (Request $request, array $headers) {
                return back()->withInput($request->only('username'))
                    ->withErrors(['username' => 'Terlalu banyak percobaan login. Coba lagi dalam beberapa saat.']);
            });
        });

        // Batasi endpoint yang menulis data.
        //
        // Sebelumnya HANYA /login yang dibatasi. Semua operasi tulis
        // lain - 158 route POST/PUT/PATCH/DELETE termasuk simpan nota,
        // approve, dan Ajax polling kasir - bisa dipanggil tanpa batas.
        // Untuk sistem keuangan dampaknya bukan hanya penyalahgunaan:
        // satu script yang salah mengulang POST bisa menulis ratusan
        // baris jurnal sebelum ada yang menyadari.
        //
        // Batasnya longgar dengan sengaja. Ini aplikasi internal dengan
        // user yang sah; kalau modul ini terlalu sempit, pekerjaan sah
        // justru ikut terblokir. Yang dicegah adalah pengulangan tak
        // terbatas, bukan pemakaian wajar.
        //
        // Kunci memakai user, bukan IP, supaya beberapa orang yang
        // berbagi satu IP kantor tidak saling mengunci.
        RateLimiter::for('tulis', function (Request $request) {
            $id = $request->user()?->getAuthIdentifier();
            $kunci = $id ? 'u' . $id : 'ip' . $request->ip();

            return Limit::perMinute(180)->by($kunci)->response(
                fn (Request $r, array $headers) => $r->expectsJson()
                    ? response()->json(['message' => 'Terlalu banyak permintaan. Coba lagi sebentar.'], 429)
                    : back()->withInput($r->except('_token'))->with('error', 'Terlalu banyak permintaan. Coba lagi sebentar.')
            );
        });

        // Endpoint AJAX yang dipanggil halaman kasir terus-menerus
        // (sinyal notifikasi, pengecekan transaksi baru). Halaman ini
        // terbuka berjam-jam di layar kasir, jadi JANGAN ikut limiter
        // 'tulis' di atas: 180 permintaan per menit akan mengunci kasir
        // yang sedang menghitung. Batasnya longgar tapi tetap ada,
        // supaya satu tab yang rusak tidak bisa membanjiri server.
        RateLimiter::for('polling', function (Request $request) {
            $id = $request->user()?->getAuthIdentifier();
            return Limit::perMinute(120)->by('poll' . ($id ?: $request->ip()));
        });

        // @bolehHarga ... @else ... @endbolehHarga : tampilkan hanya untuk role yang berhak melihat harga/nilai uang.
        \Illuminate\Support\Facades\Blade::if('bolehHarga', fn () => \App\Support\AksesHarga::bolehLihat());

        // @versi($row) : kolom tersembunyi versi baris untuk optimistic locking (lihat App\Support\VersiBaris).
        \Illuminate\Support\Facades\Blade::directive('versi', fn ($e) => "<?php echo \App\Support\VersiBaris::field({$e}); ?>");

        // Berbagi status kas ke semua View
    view()->composer('*', function ($view) {
        if (auth()->check()) {
            // Mengambil kas harian yang dibuka oleh user manapun hari ini yang masih 'open'
            $kas = \App\Services\KasSesiService::aktif();
            $view->with('kasAktif', $kas);
            $view->with('kasBasi', \App\Services\KasSesiService::basi($kas));
        }
    });
    }
}
