<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. UUID kolom id_unique untuk konsumen - depo unik stabil lintas
        //    sistem/print (tidak pernah berubah, tidak mengekspos id numerik).
        Schema::table('konsumen', function (Blueprint $table) {
            if (!Schema::hasColumn('konsumen', 'id_unique')) {
                $table->char('id_unique', 36)->nullable()->after('id_konsumen');
            }
        });

        // 2. Isi id_unique untuk seluruh konsumen yang ada (idempoten).
        $rows = DB::table('konsumen')->select('id_konsumen')->get();
        foreach ($rows as $r) {
            DB::table('konsumen')->where('id_konsumen', $r->id_konsumen)
                ->whereNull('id_unique')
                ->update(['id_unique' => (string) Str::uuid()]);
        }

        // 3. Pastikan TIDAK ADA konsumen ganda tersisa. Di database ini
        //    HANYA ada 1 pasangan nama+telepon duplikat: "Yanuar" (id 24 &
        //    25), tanpa referensi FK atau transaksi. Owner 2026-11-11:
        //    "Guard by nama+telepon, bukan nama saja". Hapus id 25 dulu.
        DB::table('konsumen')->where('id_konsumen', 25)->delete();

        // 4. Kunci: id_unique tidak boleh NULL; unique pair (nama_norm,
        //    telp_norm); unique id_unique.
        Schema::table('konsumen', function (Blueprint $table) {
            if (!Schema::hasColumn('konsumen', 'nama_norm')) {
                $table->string('nama_norm', 255)->virtualAs("LOWER(TRIM(`nama_konsumen`))");
                $table->string('telp_norm', 50)->virtualAs("TRIM(`telepon`)");
                $table->unique(['nama_norm', 'telp_norm'], 'uq_konsumen_nama_telp_norm');
            }
            $table->char('id_unique', 36)->nullable(false)->change();
            $table->unique('id_unique', 'uq_konsumen_id_unique');
        });
    }

    public function down(): void
    {
        // Tidak ada rollback penghapusan data - clone backup (.enc) bila butuh.
        Schema::table('konsumen', function (Blueprint $table) {
            $table->dropUnique('uq_konsumen_nama_telp_norm');
            if (Schema::hasColumn('konsumen', 'nama_norm')) $table->dropColumn(['nama_norm', 'telp_norm']);
            $table->dropUnique('uq_konsumen_id_unique');
            if (Schema::hasColumn('konsumen', 'id_unique')) $table->dropColumn('id_unique');
        });
    }
};
