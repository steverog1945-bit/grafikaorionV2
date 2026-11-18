<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom monitoring `transaksi.total_terbayar`.
 *
 * Dulu jumlah terbayar selalu dihitung lewat subquery SUM(pembayaran_riwayat)
 * per tampilan (Monitoring Kasir, dashboard) - query agregat berulang di tiap
 * render halaman daftar. Sekarang nilainya TERSIMPAN dan dijaga sinkron pada
 * 4 titik tulis pembayaran (bayar kasir, bayar-deposit checkout, koreksi
 * metode void+insert, void) lewat TerbayarSinkron::sinkronkan().
 *
 * Sekaligus: index pada konsumen.nama_konsumen - pencarian AJAX konsumen
 * (search-ajax LIKE nama/telepon) tanpa index memindai seluruh tabel master.
 */
return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('transaksi', 'total_terbayar')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->decimal('total_terbayar', 15, 2)->default(0)->after('total_bayar');
            });
        }

        // Backfill satu kali dari sumber kebenaran (pembayaran sah, tanpa void).
        DB::statement(
            'UPDATE transaksi t SET t.total_terbayar = COALESCE((' .
            'SELECT SUM(p.nominal) FROM pembayaran_riwayat p ' .
            'WHERE p.id_transaksi = t.id_transaksi AND p.voided_at IS NULL), 0)'
        );

        if (Schema::hasColumn('konsumen', 'nama_konsumen')) {
            $adaIndex = DB::table('information_schema.statistics')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', 'konsumen')
                ->where('index_name', 'konsumen_nama_konsumen_idx')
                ->exists();
            if (! $adaIndex) {
                Schema::table('konsumen', function (Blueprint $table) {
                    $table->index('nama_konsumen', 'konsumen_nama_konsumen_idx');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn('total_terbayar');
        });
        Schema::table('konsumen', function (Blueprint $table) {
            $table->dropIndex('konsumen_nama_konsumen_idx');
        });
    }
};
