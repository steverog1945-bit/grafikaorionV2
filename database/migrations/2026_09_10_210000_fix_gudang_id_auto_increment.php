<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * gudang.id_gudang tidak pernah punya AUTO_INCREMENT sejak tabel ini dibuat
 * (kemungkinan baris "Gudang Utama" awalnya di-insert manual dengan id
 * eksplisit). Ditemukan saat testing halaman Master Gudang baru - insert
 * baru gagal dengan "Field 'id_gudang' doesn't have a default value".
 * MODIFY murni menambah AUTO_INCREMENT, tidak mengubah data yang sudah ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE kedatangan_bahan DROP FOREIGN KEY kedatangan_bahan_id_gudang_foreign');
        DB::statement('ALTER TABLE gudang MODIFY id_gudang BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE kedatangan_bahan ADD CONSTRAINT kedatangan_bahan_id_gudang_foreign FOREIGN KEY (id_gudang) REFERENCES gudang (id_gudang) ON DELETE SET NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE kedatangan_bahan DROP FOREIGN KEY kedatangan_bahan_id_gudang_foreign');
        DB::statement('ALTER TABLE gudang MODIFY id_gudang BIGINT(20) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE kedatangan_bahan ADD CONSTRAINT kedatangan_bahan_id_gudang_foreign FOREIGN KEY (id_gudang) REFERENCES gudang (id_gudang) ON DELETE SET NULL');
    }
};
