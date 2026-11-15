<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Komponen biaya MINIMUM ORDER yang digulung ke baris item (mode HYBRID
 * T4-A, keputusan owner 2026-11-15).
 *
 * Selama ini selisih minimum hanya "baked in" ke biaya_cetak + catatan
 * "Termasuk Minimum Order ...: selisih X m2" - tidak ada angka tersimpan.
 * Akibatnya baris lama tidak bisa direkonsiliasi saat kelompok minimum
 * berubah status di simpan berikutnya (susulan menambah item yang
 * melengkapi kelompok, atau edit nota): surcharge lama tetap tertagih
 * (double charge) karena tidak ada cara tahu berapa nominalnya.
 *
 * biaya_minimum = KOMPONEN DI DALAM biaya_cetak (bukan tambahan di luar):
 * biaya_cetak = luas aktual + biaya_minimum. subtotal tetap
 * biaya_cetak + biaya_desain + biaya_finishing, jadi tampilan nota,
 * POS, SPK, dan jurnal tidak berubah sama sekali.
 *
 * Backfill: baris carrier lama (is_minimum_order=1) diisi dari selisih
 * yang tercatat di catatan_produksi x tarif price list saat ini. Feature
 * ini baru berumur satu hari, jadi populasinya kecil; kalau parsing
 * gagal, kolom dibiarkan 0 (rekonsiliasi berikutnya menormalkan dari 0).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_detail', function (Blueprint $table) {
            if (!Schema::hasColumn('transaksi_detail', 'biaya_minimum')) {
                $table->decimal('biaya_minimum', 14, 2)->default(0)->after('biaya_cetak');
            }
        });

        // Backfill baris carrier lama. Advisory: kegagalan satu baris tidak
        // boleh menggagalkan migrasi - baris itu tinggal tak terekonsiliasi.
        $carrier = DB::table('transaksi_detail as td')
            ->join('transaksi as t', 'td.id_transaksi', '=', 't.id_transaksi')
            ->join('konsumen as k', 't.id_konsumen', '=', 'k.id_konsumen')
            ->where('td.is_minimum_order', 1)
            ->where('td.biaya_minimum', 0)
            ->whereNotNull('td.id_produk')
            ->get([
                'td.id_detail', 'td.id_produk', 'td.catatan_produksi',
                'k.id_golongan',
            ]);

        foreach ($carrier as $baris) {
            try {
                $selisih = \App\Support\MinimumLuas::selisihDariCatatan((string) $baris->catatan_produksi);
                if ($selisih === null || $selisih <= 0) {
                    continue;
                }
                $tarif = (float) \App\Services\HargaService::getHargaEfektif(
                    (int) $baris->id_produk, (int) $baris->id_golongan, $selisih, false
                )['harga_jual'];
                if ($tarif <= 0) {
                    continue;
                }
                DB::table('transaksi_detail')
                    ->where('id_detail', $baris->id_detail)
                    ->update(['biaya_minimum' => round($tarif * $selisih, 2)]);
            } catch (\Throwable) {
                continue;
            }
        }
    }

    public function down(): void
    {
        Schema::table('transaksi_detail', function (Blueprint $table) {
            if (Schema::hasColumn('transaksi_detail', 'biaya_minimum')) {
                $table->dropColumn('biaya_minimum');
            }
        });
    }
};
