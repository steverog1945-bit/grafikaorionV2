<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Sumber tunggal untuk menentukan harga jual efektif sebuah produk, supaya
 * harga yang DITAMPILKAN ke operator (live saat mengetik qty di POS) dan
 * harga yang benar-benar DISIMPAN saat checkout (PenjualanController::store)
 * selalu dihitung dengan cara yang identik. Sebelumnya kedua tempat itu
 * masing-masing punya query sendiri yang berpotensi ngedrift.
 */
class HargaService
{
    /**
     * Harga promo (kalau sedang aktif) selalu menang di atas price list biasa,
     * dan berlaku untuk SEMUA golongan konsumen (tabel harga_promo memang
     * tidak punya kolom id_golongan - flat price, bukan per range/golongan).
     * Begitu promo berakhir (lewat tgl_selesai atau status diubah manual),
     * harga otomatis kembali ke setting_harga tanpa perlu ubah apa pun di
     * sana - harga normal tidak pernah disentuh oleh promo.
     */
    public static function getHargaEfektif(int $idProduk, int $idGolongan, float $qty): array
    {
        $promo = self::promoAktif($idProduk);

        if ($promo) {
            return [
                'harga_jual' => (float) $promo->harga_promo,
                'is_promo' => true,
                'promo_selesai' => $promo->tgl_selesai,
            ];
        }

        $harga = DB::table('setting_harga')
            ->where('id_produk', $idProduk)
            ->where('id_golongan', $idGolongan)
            ->where('range_min', '<=', $qty)
            ->orderBy('range_min', 'desc')
            ->first();

        return [
            'harga_jual' => $harga ? (float) $harga->harga_jual : 0,
            'is_promo' => false,
            'promo_selesai' => null,
        ];
    }

    public static function promoAktif(int $idProduk): ?object
    {
        $today = now()->format('Y-m-d');

        return DB::table('harga_promo')
            ->where('id_produk', $idProduk)
            ->where('status_promo', 'aktif')
            ->whereDate('tgl_mulai', '<=', $today)
            ->whereDate('tgl_selesai', '>=', $today)
            ->orderByDesc('id_promo')
            ->first();
    }
}
