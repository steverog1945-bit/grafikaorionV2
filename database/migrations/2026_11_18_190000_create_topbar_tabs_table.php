<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tab modul pada baris kedua Topbar Tema Premium ("Command Center").
 *
 * Isi diatur Administrator lewat Pengaturan → Topbar Premium (label, URL,
 * ikon, urutan, aktif/sembunyi). Bila tabel kosong, TopbarTab::aktif()
 * mengembalikan daftar bawaan — tampilan identik sebelum fitur ini ada.
 */
return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('topbar_tabs')) {
            Schema::create('topbar_tabs', function (Blueprint $table) {
                $table->id();
                $table->string('label', 40);
                $table->string('url', 191);
                $table->string('icon', 64);          // kelas Font Awesome, mis. fa-cart-shopping
                $table->unsignedInteger('urutan')->default(100);
                $table->boolean('aktif')->default(true);
                $table->timestamps();
                $table->index('urutan', 'topbar_tabs_urutan_idx');
            });

            // Bawaan = persis tab yang sekarang hardcoded di _premium-bar.
            DB::table('topbar_tabs')->insert([
                ['label' => 'Dashboard',  'url' => '/dashboard',          'icon' => 'fa-gauge',            'urutan' => 10, 'aktif' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['label' => 'Penjualan',  'url' => '/penjualan/create',   'icon' => 'fa-cart-shopping',    'urutan' => 20, 'aktif' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['label' => 'Produksi',   'url' => '/produksi',           'icon' => 'fa-industry',         'urutan' => 30, 'aktif' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['label' => 'Master',     'url' => '/bahan-baku',         'icon' => 'fa-cubes',            'urutan' => 40, 'aktif' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['label' => 'Keuangan',   'url' => '/kasir/monitoring',   'icon' => 'fa-money-bill-wave',  'urutan' => 50, 'aktif' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['label' => 'Laporan',    'url' => '/laporan/order',      'icon' => 'fa-chart-line',       'urutan' => 60, 'aktif' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['label' => 'Jersey',     'url' => '/jersey',             'icon' => 'fa-shirt',            'urutan' => 70, 'aktif' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['label' => 'Pengaturan', 'url' => '/pengaturan-aplikasi','icon' => 'fa-sliders',          'urutan' => 80, 'aktif' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('topbar_tabs');
    }
};
