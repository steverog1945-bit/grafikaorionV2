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
    public static function getHargaEfektif(int $idProduk, int $idGolongan, float $qty, bool $siapCetak = false): array
    {
        // File Siap Cetak: harga tersendiri per produk (tier & golongan sendiri), tanpa promo.
        if ($siapCetak) {
            $tier = DB::table('harga_siap_cetak')
                ->where('id_produk', $idProduk)->where('id_golongan', $idGolongan)
                ->where('range_min', '<=', $qty)
                ->where(fn ($q) => $q->whereNull('range_max')->orWhere('range_max', '>=', $qty))
                ->orderBy('range_min', 'desc')->first();

            return ['harga_jual' => $tier ? (float) $tier->harga_jual : 0, 'is_promo' => false, 'promo_selesai' => null, 'siap_cetak' => true];
        }

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
            ->where(function ($q) use ($qty) {
                $q->whereNull('range_max')->orWhere('range_max', '>=', $qty);
            })
            ->orderBy('range_min', 'desc')
            ->first();

        return [
            'harga_jual' => $harga ? (float) $harga->harga_jual : 0,
            'is_promo' => false,
            'promo_selesai' => null,
        ];
    }

    /** Id produk yang punya harga File Siap Cetak (opsi ini hanya ditawarkan untuk produk tersebut). */
    public static function produkDenganHargaSiapCetak(): array
    {
        return DB::table('harga_siap_cetak')->distinct()->pluck('id_produk')->map(fn ($v) => (int) $v)->all();
    }

    /**
     * Harga Finishing bertingkat berdasarkan qty (finishing_harga) - TANPA
     * dimensi golongan (finishing dianggap ongkos kerja, satu rentang
     * berlaku untuk semua pelanggan). Kalau tidak ada tier yang cocok atau
     * belum diatur tiernya sama sekali, jatuh balik ke finishing.harga_default.
     * Ini murni ANGKA ACUAN/starting point untuk POS - operator tetap bisa
     * menimpa manual, sama seperti perilaku harga_default sebelumnya.
     */
    public static function getHargaFinishingEfektif(int $idFinishing, float $qty): float
    {
        $tier = DB::table('finishing_harga')
            ->where('id_finishing', $idFinishing)
            ->where('range_min', '<=', $qty)
            ->where(function ($q) use ($qty) {
                $q->whereNull('range_max')->orWhere('range_max', '>=', $qty);
            })
            ->orderBy('range_min', 'desc')
            ->first();

        if ($tier) {
            return (float) $tier->harga;
        }

        $default = DB::table('finishing')->where('id_finishing', $idFinishing)->value('harga_default');

        return (float) ($default ?? 0);
    }

    /**
     * Sama seperti getHargaFinishingEfektif tapi untuk Jasa (jasa_harga +
     * jasa.harga_default sebagai fallback). Jasa sebelumnya tidak punya
     * harga tersimpan sama sekali - ini angka acuan baru, kasir tetap bisa
     * override manual di POS.
     */
    public static function getHargaJasaEfektif(int $idJasa, float $qty): float
    {
        $tier = DB::table('jasa_harga')
            ->where('id_jasa', $idJasa)
            ->where('range_min', '<=', $qty)
            ->where(function ($q) use ($qty) {
                $q->whereNull('range_max')->orWhere('range_max', '>=', $qty);
            })
            ->orderBy('range_min', 'desc')
            ->first();

        if ($tier) {
            return (float) $tier->harga;
        }

        $default = DB::table('jasa')->where('id_jasa', $idJasa)->value('harga_default');

        return (float) ($default ?? 0);
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
