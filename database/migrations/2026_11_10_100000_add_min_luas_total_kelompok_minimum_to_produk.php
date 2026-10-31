<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->decimal('min_luas_total', 10, 4)->default(0)->after('min_lebar');
            $table->string('kelompok_minimum', 100)->nullable()->after('min_luas_total');
        });
    }

    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn(['min_luas_total', 'kelompok_minimum']);
        });
    }
};
