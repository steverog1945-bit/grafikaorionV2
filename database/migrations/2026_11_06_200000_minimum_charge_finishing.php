<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Minimum charge finishing (lantai biaya) jadi data master, bukan angka mati.
 *
 * Latar belakang: minimum charge Potong Manual (LINEAR_GRID) sebelumnya
 * ditulis langsung sebagai angka 5.000 di DUA tempat -
 *
 *   - app/Services/HargaService.php (server, yang menentukan angka tersimpan)
 *   - resources/views/penjualan/create.blade.php (layar, yang menampilkan angka)
 *
 * Dua salinan angka yang sama di dua tempat adalah recipe klasik untuk
 * layar dan server berbeda: begitu satu sisi diubah, sisi lain diam-diam
 * jadi basi. Layar menampilkan angka yang berbeda dari yang sebenarnya
 * disimpan, dan tidak ada satu pun tempat yang bisa mengubahnya.
 *
 * Solusi: kolom minimum_charge di tabel finishing, satu baris per jenis
 * finishing. Alasannya per finishing, bukan satu angka global:
 *
 *   - tarif per meternya sendiri sudah per finishing (harga_default), jadi
 *     lantai biayanya juga sebaiknya per finishing;
 *   - "Potong Manual" (INDOOR / EPSON) dan "Potong Manual A3" (KONICA
 *     MINOLTA) bisa punya lantai yang berbeda;
 *   - nilainya bisa diubah operator dari Master Finishing, tanpa SQL.
 *
 * Nilai 0 = tidak ada minimum charge (biaya = hasil rumus apa adanya).
 * Finished yang tidak memakai rumus grid (luas/keliling/quantity) tidak
 * pernah membaca kolom ini, jadi biayanya tidak terpengaruh.
 *
 * Backfill: finishing LINEAR_GRID yang sudah ada diberi 5.000 - nilai yang
 * sebelumnya dipakai di kode - supaya biaya nota yang sudah tercatat dan
 * perhitungan yang sedang berjalan tidak berubah(nilainya sama).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finishing', function (Blueprint $t) {
            if (!Schema::hasColumn('finishing', 'minimum_charge')) {
                $t->decimal('minimum_charge', 15, 2)->default(0)->after('harga_default');
            }
        });

        // Backfill: finishing Potong Manual yang sudah ada ikutkan angka 5.000
        // yang sebelumnya hardcode di kode (nilainya tidak berubah).
        DB::table('finishing')
            ->where('metode_hitung', 'LINEAR_GRID')
            ->where('minimum_charge', 0)
            ->update(['minimum_charge' => 5000]);
    }

    public function down(): void
    {
        Schema::table('finishing', function (Blueprint $t) {
            if (Schema::hasColumn('finishing', 'minimum_charge')) {
                $t->dropColumn('minimum_charge');
            }
        });
    }
};
