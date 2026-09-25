<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
