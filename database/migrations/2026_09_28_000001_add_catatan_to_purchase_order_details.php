<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Melengkapi field yang dibutuhkan siklus purchasing tetapi tidak tersedia:
// purchase_order_details.catatan - catatan per item PO (mis. "merk harus
// sama dengan PR", "harga max 50rb/m"), terbawa dari catatan item PR saat
// Buat PO. Header PO sudah punya catatan sendiri sejak awal.
return new class extends Migration {
    public function up()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->string('catatan', 255)->nullable()->after('subtotal');
        });
    }

    public function down()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->dropColumn('catatan');
        });
    }
};
