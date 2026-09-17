<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard Gudang (role id 5) menautkan langsung ke Laporan Stok Bahan
 * Baku (id_menu 46, sebelumnya cuma Administrator) - wajar diberi akses
 * karena laporan itu memang tentang tanggung jawab mereka (evaluasi
 * pengadaan bahan).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('role_permissions')->where('id_role', 5)->where('id_menu', 46)->exists()) {
            DB::table('role_permissions')->insert([
                'id_role' => 5,
                'id_menu' => 46,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('role_permissions')->where('id_role', 5)->where('id_menu', 46)->delete();
    }
};
