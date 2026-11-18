<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Tab modul baris kedua Topbar Tema Premium.
 *
 * Kontrak: aktif() SELALU mengembalikan array tersedia (bawaan bila tabel
 * kosong/belum dimigrasi), supaya topbar tidak pernah kosong dan halaman
 * tidak pernah 500 karena kolom/tabel belum ada di DB runtime yang belum
 * dimigrasi (pelajaran dari insiden app_settings.qris_path).
 */
class TopbarTab
{
    public const CACHE_KEY = 'topbar_tabs_aktif';

    /** Default identik seed migrasi (tanpa DB sama sekali). */
    public static function bawaan(): array
    {
        return [
            ['label' => 'Dashboard',  'url' => '/dashboard',          'icon' => 'fa-gauge'],
            ['label' => 'Penjualan',  'url' => '/penjualan/create',   'icon' => 'fa-cart-shopping'],
            ['label' => 'Produksi',   'url' => '/produksi',           'icon' => 'fa-industry'],
            ['label' => 'Master',     'url' => '/bahan-baku',         'icon' => 'fa-cubes'],
            ['label' => 'Keuangan',   'url' => '/kasir/monitoring',   'icon' => 'fa-money-bill-wave'],
            ['label' => 'Laporan',    'url' => '/laporan/order',      'icon' => 'fa-chart-line'],
            ['label' => 'Jersey',     'url' => '/jersey',             'icon' => 'fa-shirt'],
            ['label' => 'Pengaturan', 'url' => '/pengaturan-aplikasi','icon' => 'fa-sliders'],
        ];
    }

    /** Tab aktif utk topbar (fallback penuh bila DB belum dimigrasi). */
    public static function aktif(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(5), function () {
            if (! Schema::hasTable('topbar_tabs')) {
                return self::bawaan();
            }

            $rows = \Illuminate\Support\Facades\DB::table('topbar_tabs')
                ->where('aktif', true)
                ->orderBy('urutan')
                ->orderBy('id')
                ->get(['label', 'url', 'icon']);

            return $rows->map(fn ($r) => [
                'label' => (string) $r->label,
                'url'   => (string) $r->url,
                'icon'  => (string) $r->icon,
            ])->all() ?: self::bawaan();
        });
    }

    /** Selamat tinggal cache (dipanggil setelah simpan). */
    public static function bersihkanCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
