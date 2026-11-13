<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sejak 2026-11-13 aplikasi mendukung metode pembayaran "QRIS BRI" (pilihan
 * ke-5 di kasir). pembayaran_riwayat.metode adalah ENUM lama
 * ('Tunai','Transfer','Debit','Deposit') - insert dengan nilai baru tanpa
 * migrasi ini gagal dengan "Data truncated for column 'metode'" (mode SQL
 * ketat), dan transaksi yang memakai QRIS BRI akan 500 saat disimpan.
 *
 * Catatan: transaksi.metode_pembayaran berupa varchar(50) sehingga aman
 * tanpa perubahan. Tidak ada nilai lama yang diubah; down() mengembalikan
 * enum seperti semula.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pembayaran_riwayat MODIFY metode ENUM('Tunai','Transfer','Debit','Deposit','QRIS BRI') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pembayaran_riwayat MODIFY metode ENUM('Tunai','Transfer','Debit','Deposit') NOT NULL");
    }
};
