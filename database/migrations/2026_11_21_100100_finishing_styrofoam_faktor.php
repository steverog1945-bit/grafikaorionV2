<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Faktor pemakaian Styrofoam - keputusan owner 21 November 2026.
 *
 * Migrasi 2026_11_21_100000 sengaja tidak menebak angka ini karena ukuran
 * lembar adalah data fisik. Setelah owner mengonfirmasi 1 lembar = 100x200 cm
 * (= 2 m2), faktornya menjadi:
 *
 *     0,5 lembar per 1 m2 finishing.
 *
 * Jadi order FLS280 80x40 cm (0,32 m2) memotong 0,16 lembar, bukan 0,32.
 * Tagihan finishing TIDAK berubah (tetap 0,32 x 35.000 = 11.200); yang
 * berubah hanya stok, snapshot pemakaian, dan HPP.
 *
 * Hanya mengisi baris yang masih kosong - konfigurasi manual admin tidak
 * pernah ditimpa.
 */
return new class extends Migration
{
    public function up(): void
    {
        $idBahan = DB::table('bahan_baku')->where('kode_bahan', 'STYROFOAM')->value('id_bahan');
        if (!$idBahan) {
            return;
        }

        DB::table('finishing')
            ->where('nama_finishing', 'like', '%styrofoam%')
            ->where('id_bahan', $idBahan)
            ->whereNull('qty_bahan_per_satuan')
            ->update(['qty_bahan_per_satuan' => 0.5]);
    }

    public function down(): void
    {
        $idBahan = DB::table('bahan_baku')->where('kode_bahan', 'STYROFOAM')->value('id_bahan');
        if (!$idBahan) {
            return;
        }

        DB::table('finishing')
            ->where('nama_finishing', 'like', '%styrofoam%')
            ->where('id_bahan', $idBahan)
            ->where('qty_bahan_per_satuan', 0.5)
            ->update(['qty_bahan_per_satuan' => null]);
    }
};
