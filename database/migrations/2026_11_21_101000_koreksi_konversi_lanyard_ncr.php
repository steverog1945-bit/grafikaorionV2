<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Koreksi dua nilai konversi bahan baku yang salah satuan.
 *
 * 1) LANYARD 20mm (id 56, kode LNY)
 *    - panjang_roll = 100 m, lebar_roll = 0,02 m.
 *    - nilai_konversi lama = 2 = m2 per roll (0,02 x 100), padahal
 *      satuan hitung BOM-nya Q*1,1 adalah METER, bukan m2.
 *    - akibatnya 25 lanyard -> 27,5 / 2 = 13,75 roll (55 m per item).
 *    - koreksi: id_satuan_hitung = m (id 14), nilai_konversi = 100
 *      (1 roll = 100 meter), sehingga 25 item -> 27,5 / 100 = 0,275 roll.
 *    - rumus BOM Q*1,1 sengaja TIDAK diubah.
 *
 * 2) NCR paper (id 149, kode NCR)
 *    - satuan stok rim, nilai_konversi lama = 1, sehingga 25 nota
 *      mengurangi 25 rim penuh.
 *    - semua bahan berkode rim lain di database ini memakai 500 lembar/rim
 *      (AC210, AC260, BONTAX, AP120/150, HVS A3/A4, dll).
 *    - koreksi: nilai_konversi = 500, id_satuan_hitung = lembar (id 18).
 *    - harga_rata_rata masih 0 dan sengaja tidak diisi (data supplier
 *      tidak tersedia); BOM rangkap 2/3 juga sengaja tidak disentuh.
 *
 * Hanya dua baris bahan baku yang diubah. Tidak ada BOM, finishing, harga,
 * jurnal, route, atau kode aplikasi yang tersentuh.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('bahan_baku')
            ->where('id_bahan', 56)
            ->where('kode_bahan', 'LNY')
            ->where('nilai_konversi', 2)
            ->update([
                'id_satuan_hitung' => 14, // m
                'nilai_konversi'   => 100, // 1 roll = 100 meter
            ]);

        DB::table('bahan_baku')
            ->where('id_bahan', 149)
            ->where('kode_bahan', 'NCR')
            ->where('nilai_konversi', 1)
            ->update([
                'id_satuan_hitung' => 18, // lembar
                'nilai_konversi'   => 500, // 1 rim = 500 lembar
            ]);
    }

    public function down(): void
    {
        DB::table('bahan_baku')
            ->where('id_bahan', 56)
            ->where('kode_bahan', 'LNY')
            ->where('nilai_konversi', 100)
            ->update([
                'id_satuan_hitung' => 11, // pcs
                'nilai_konversi'   => 2,
            ]);

        DB::table('bahan_baku')
            ->where('id_bahan', 149)
            ->where('kode_bahan', 'NCR')
            ->where('nilai_konversi', 500)
            ->update([
                'id_satuan_hitung' => 11, // pcs
                'nilai_konversi'   => 1,
            ]);
    }
};
