<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Monitoring Cetak (modul di dalam ERP, pengganti aplikasi monitor
 * berdiri sendiri setelah uji coba). Mesin dipantau agent spooler/RIP di
 * PC produksi; tabel ini hanya REFERENCIA ke `mesin` ERP -- tidak ada
 * mesin baru yang dibuat di ERP lewat sini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_mesins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_mesin')->nullable()->unique();
            $table->string('nama_mesin', 100);
            $table->string('tipe_mesin', 50)->nullable();
            // onyx | flexi | edgeprint | jobcentro | cutter | other
            $table->string('software', 30)->default('other');
            $table->string('pc_ip', 45)->nullable();
            // Nama printer Windows persis seperti Get-Printer.
            $table->string('printer_name', 100)->nullable();
            $table->boolean('aktif')->default(true);
            $table->string('catatan', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_mesins');
    }
};
