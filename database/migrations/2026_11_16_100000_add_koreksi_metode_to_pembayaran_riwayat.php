<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Koreksi metode pembayaran nota (lihat KoreksiPembayaranService): void +
// input ulang dengan metode yang benar dalam SATU transaksi, jadi status nota
// tidak pernah mundur. Kolomnya mengikuti pola void yang sudah ada di
// pembayaran_hutang (voided_at/void_alasan), ditambah:
//   - no_referensi_jurnal : referensi jurnal "nota-BYR-n" dicatat DI SAAT
//     insert, supaya pembalikan jurnal (AccountingService::reverseByReferensi)
//     menunjuk persis kelompok jurnal milik baris pembayaran yang di-void -
//     tidak menebak dari keterangan/nominal lagi.
//   - id_kas : sesi kas pemilik pembayaran, supaya koreksi bisa memindahkan
//     nominal antara saldo_akhir (Tunai) dan total_transfer (Transfer/QRIS BRI)
//     di sesi yang benar.
return new class extends Migration {
    public function up()
    {
        Schema::table('pembayaran_riwayat', function (Blueprint $table) {
            $table->string('no_referensi_jurnal', 100)->nullable()->after('keterangan');
            $table->unsignedBigInteger('id_kas')->nullable()->after('no_referensi_jurnal');
            $table->timestamp('voided_at')->nullable()->after('id_kas');
            $table->unsignedBigInteger('voided_by')->nullable()->after('voided_at');
            $table->string('void_alasan', 255)->nullable()->after('voided_by');
            $table->index(['id_transaksi', 'voided_at'], 'pembayaran_riwayat_nota_aktif_idx');
        });
    }

    public function down()
    {
        Schema::table('pembayaran_riwayat', function (Blueprint $table) {
            $table->dropIndex('pembayaran_riwayat_nota_aktif_idx');
            $table->dropColumn(['no_referensi_jurnal', 'id_kas', 'voided_at', 'voided_by', 'void_alasan']);
        });
    }
};
