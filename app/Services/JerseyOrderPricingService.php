<?php

namespace App\Services;

use App\Models\JerseyPriceSetting;

/**
 * Cari tier harga jersey_price_settings yang cocok untuk kombinasi tipe
 * produk + kategori + qty - product_type_id/category_id di tabel tier
 * boleh NULL (berarti "berlaku untuk semua"), jadi pencarian dilakukan
 * dari yang PALING SPESIFIK (kedua dimensi cocok persis) ke yang paling
 * umum (keduanya NULL, tier generik). Tidak pernah melempar error kalau
 * tidak ada tier cocok - kasir/admin tetap bisa isi harga manual di form,
 * sama seperti pola HargaService di modul percetakan (harga tidak pernah
 * memblokir pembuatan order).
 */
class JerseyOrderPricingService
{
    public static function cariTierHarga(?int $productTypeId, ?int $categoryId, int $qty): ?JerseyPriceSetting
    {
        $kombinasi = [
            [$productTypeId, $categoryId],
            [null, $categoryId],
            [$productTypeId, null],
            [null, null],
        ];

        foreach ($kombinasi as [$pt, $cat]) {
            $tier = JerseyPriceSetting::query()
                ->where('is_active', true)
                ->where('jersey_product_type_id', $pt)
                ->where('jersey_category_id', $cat)
                ->where('qty_min', '<=', $qty)
                ->where(function ($q) use ($qty) {
                    $q->whereNull('qty_max')->orWhere('qty_max', '>=', $qty);
                })
                ->orderByDesc('qty_min')
                ->first();

            if ($tier) {
                return $tier;
            }
        }

        return null;
    }

    public static function hitungHargaSatuan(?int $productTypeId, ?int $categoryId, int $qty): float
    {
        return (float) (self::cariTierHarga($productTypeId, $categoryId, $qty)?->price ?? 0);
    }
}
