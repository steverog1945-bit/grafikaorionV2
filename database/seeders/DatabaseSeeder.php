<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Insert Roles
        DB::table('roles')->insert([
            ['id_role' => 1, 'nama_role' => 'Administrator'],
            ['id_role' => 2, 'nama_role' => 'Operator Desain'],
            ['id_role' => 3, 'nama_role' => 'Kasir'],
        ]);

        // 2. Insert Menus Utama
        DB::table('menus')->insert([
            ['id_menu' => 1, 'nama_menu' => 'Master Bahan', 'url_link' => 'bahan-baku'],
            ['id_menu' => 2, 'nama_menu' => 'BOM Produk', 'url_link' => 'bom'],
            ['id_menu' => 3, 'nama_menu' => 'Transaksi Baru', 'url_link' => 'transaksi'],
            ['id_menu' => 4, 'nama_menu' => 'Pengaturan Akses', 'url_link' => 'setting-akses'],
        ]);

        // 3. Insert Master Karyawan Awal
        DB::table('karyawan')->insert([
            'id_karyawan' => 1,
            'nama_karyawan' => 'Admin Utama',
            'bagian' => 'Admin',
            'telepon' => '08123456789',
            'status_aktif' => 1
        ]);

        // 4. Insert User Admin. JANGAN pernah hardcode password.
        // Password diambil dari SEED_ADMIN_PASSWORD (min 12 karakter) atau dibuat
        // acak satu-kali dan dicetak ke console (tidak tersimpan di file mana pun).
        $seedPassword = (string) env('SEED_ADMIN_PASSWORD', '');
        if (strlen($seedPassword) < 12) {
            $seedPassword = Str::random(28);
            $this->command->warn('SEED_ADMIN_PASSWORD kosong/<12 karakter: password admin acak: ' . $seedPassword);
        }
        DB::table('users')->updateOrInsert(
            ['username' => 'admin'],
            [
                'id_karyawan' => 1,
                'password' => Hash::make($seedPassword),
                'id_role' => 1,
                'updated_at' => now(),
            ]
        );
        DB::table('kategori')->insert([
    ['id_kategori' => 1, 'nama_kategori' => 'Sticker'],
    ['id_kategori' => 2, 'nama_kategori' => 'Banner/Flexi'],
]);

DB::table('satuan')->insert([
    ['id_satuan' => 1, 'nama_satuan' => 'cm', 'nilai_konversi' => 1],
    ['id_satuan' => 2, 'nama_satuan' => 'm', 'nilai_konversi' => 100],
]);
    }
}