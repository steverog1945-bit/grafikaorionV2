<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bahan baku yang benar-benar dipotong oleh sebuah finishing.
 *
 * Sebelum ini, finishing hanya punya TARIF (harga_default/harga_beli) dan
 * tidak pernah menyebut bahan apa yang dikonsumsi. Akibatnya order dengan
 * finishing Styrofoam tidak pernah memotong stok Styrofoam dan HPP-nya tidak
 * memuat bahan itu sama sekali - bahan 150 (STYROFOAM) punya 0 baris
 * kartu_stok walau sudah dipakai beberapa order.
 *
 * Kolom yang ditambahkan:
 *  - id_bahan               : bahan baku yang dipotong (opsional - finishing
 *                             jasa seperti jahit/laminasi tidak memotong bahan
 *                             spesifik dan boleh dibiarkan kosong).
 *  - qty_bahan_per_satuan   : berapa satuan STOK bahan yang terpakai untuk
 *                             1 satuan HITUNG finishing (m2 untuk metode luas,
 *                             meter untuk keliling, pcs untuk quantity).
 *                             Contoh: 1 lembar styrofoam 100x200 cm = 0,5
 *                             lembar per m2 -> isi 0,5.
 *
 * angka ini karena ukuran lembar adalah fakta fisik yang hanya diketahui
 * pemilik/operator. Setelah owner mengonfirmasi 1 lembar = 100x200 cm,
 * faktor 0,5 lembar/m2 diisi oleh migrasi lanjutan
 * 2026_11_21_100100_finishing_styrofoam_faktor.php. Migrasi ini hanya
 * MENAUTKAN finishing bernama "styrofoam" ke bahan baku STYROFOAM.
 *
 * Aditif murni: tidak ada kolom lama yang diubah/dihapus. Selama faktor masih
 * NULL, alur order berjalan persis seperti sebelumnya (tidak ada pemotongan
 * stok baru yang tak terduga).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finishing', function (Blueprint $t) {
            if (!Schema::hasColumn('finishing', 'id_bahan')) {
                $t->unsignedBigInteger('id_bahan')->nullable()->after('id_mesin')->index();
            }
            if (!Schema::hasColumn('finishing', 'qty_bahan_per_satuan')) {
                $t->decimal('qty_bahan_per_satuan', 15, 4)->nullable()->after('id_bahan');
            }
        });

        // Tautkan finishing styrofoam ke bahan baku STYROFOAM bila kodenya ada
        // di database ini. Hanya yang id_bahan-nya masih kosong - konfigurasi
        // manual admin tidak pernah ditimpa.
        $idBahan = DB::table('bahan_baku')->where('kode_bahan', 'STYROFOAM')->value('id_bahan');
        if ($idBahan) {
            DB::table('finishing')
                ->whereNull('id_bahan')
                ->where('nama_finishing', 'like', '%styrofoam%')
                ->update(['id_bahan' => $idBahan]);
        }
    }

    public function down(): void
    {
        Schema::table('finishing', function (Blueprint $t) {
            if (Schema::hasColumn('finishing', 'qty_bahan_per_satuan')) {
                $t->dropColumn('qty_bahan_per_satuan');
            }
            if (Schema::hasColumn('finishing', 'id_bahan')) {
                $t->dropColumn('id_bahan');
            }
        });
    }
};
