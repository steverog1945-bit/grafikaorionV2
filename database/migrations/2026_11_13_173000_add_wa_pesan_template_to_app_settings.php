<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * Template pesan WhatsApp untuk fitur "Kirim WA Tagihan" di Laporan Piutang
 * (tautan wa.me, tanpa API/gateway). Placeholder: {nama} {nota} {tanggal}
 * {total} {status} {sisa} {wa}. NULL = pakai bawaan aplikasi. Diisi operator
 * sendiri dari halaman Laporan Piutang (role dengan hak lihat harga).
 * Diport dari erpV3 (2026-11-11) supaya tagihan ke pelanggan lebih mudah
 * ditindaklanjuti kasir.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('app_settings', 'wa_pesan_template')) {
                $table->text('wa_pesan_template')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            if (Schema::hasColumn('app_settings', 'wa_pesan_template')) {
                $table->dropColumn('wa_pesan_template');
            }
        });
    }
};
