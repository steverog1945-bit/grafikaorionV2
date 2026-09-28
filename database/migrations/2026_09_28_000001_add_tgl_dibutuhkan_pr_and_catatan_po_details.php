<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Melengkapi field yang dibutuhkan siklus purchasing tetapi tidak tersedia:
// 1. purchase_requests.tgl_dibutuhkan - kapan barang dibutuhkan (estimasi
//    kebutuhan pengaju), dipakai pembeli sebagai prioritas saat membuat PO.
// 2. purchase_order_details.catatan - catatan per item PO (mis. "merk harus
//    sama dengan PR", "harga max 50rb/m"), terbawa dari catatan item PR saat
//    Buat PO. Header PO sudah punya catatan sendiri sejak awal.
return new class extends Migration {
    public function up()
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->date('tgl_dibutuhkan')->nullable()->after('tanggal');
        });

        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->string('catatan', 255)->nullable()->after('subtotal');
        });
    }

    public function down()
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn('tgl_dibutuhkan');
        });

        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->dropColumn('catatan');
        });
    }
};
