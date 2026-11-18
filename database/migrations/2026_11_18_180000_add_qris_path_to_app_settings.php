<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kode QRIS toko sebagai pengaturan aplikasi (bukan lagi mengandalkan berkas
 * "qris-bri.png" di storage/app/public yang harus disalin manual IT).
 *
 * Kasir bisa mengganti gambar langsung dari halaman Pengaturan Aplikasi
 * (menu 37, Administrator), sama seperti mengganti logo. Bila kolom kosong,
 * form pembayaran jatuh kembali ke berkas lama storage/qris-bri.png - perilaku
 * tidak berubah untuk instalasi yang sudah punya berkas tersebut.
 */
return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('app_settings', 'qris_path')) {
            Schema::table('app_settings', function (Blueprint $table) {
                $table->string('qris_path')->nullable()->after('nota_footer_text');
            });
        }
    }

    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn('qris_path');
        });
    }
};
