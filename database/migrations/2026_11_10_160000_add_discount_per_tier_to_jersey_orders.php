<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Diskon per tier untuk modul Jersey - potongan Rp PER PCS pada harga tier
 * yang sedang aktif (mis. tier 80.000/pcs dikurangi 20.000 -> 60.000/pcs).
 * Harga satuan tier lain tetap berlaku; Diskon keseluruhan (Rp) tetap ada
 * dan DITAMBAH deraskan subtotal kedua (diskon per-tier dulu, lalu diskon
 * keseluruhan - lihat JerseyOrderController::hitungHargaBaris).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jersey_orders', function (Blueprint $table) {
            $table->decimal('discount_per_tier', 15, 2)->default(0)->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('jersey_orders', function (Blueprint $table) {
            $table->dropColumn('discount_per_tier');
        });
    }
};
