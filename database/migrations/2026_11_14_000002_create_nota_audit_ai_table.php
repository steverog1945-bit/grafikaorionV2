<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hasil audit AI satu nota (driver Ollama/kompatibel, dipanggil lewat
 * command `nota:audit-ai` - TIDAK pernah dijalankan di request halaman).
 * Baris dihapus lalu ditulis ulang tiap nota diaudit ulang, jadi tabel ini
 * adalah "hasil pemeriksaan terakhir", bukan riwayat permanen.
 *
 * KATEGORI temuan (string terkontrol dari service):
 *   finishing_tidak_tertagih | deskripsi_tak_cocok | ukuran_lain | lainnya
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('nota_audit_ai')) {
            return;
        }

        Schema::create('nota_audit_ai', function (Blueprint $t) {
            $t->id('id_nota_audit_ai');
            $t->unsignedBigInteger('id_transaksi')->index('idx_nota_audit_ai_transaksi');
            $t->string('sumber', 255);              // dari mana kutipan itu (nama berkas/catatan)
            $t->string('kutipan', 255)->nullable(); // potongan teks yang menyinggung
            $t->string('temuan', 255);              // kalimat masalah versi AI
            $t->string('kategori', 40)->nullable();
            $t->decimal('keyakinian', 4, 3)->default(0); // 0..1, advisory saja
            $t->string('model', 60)->nullable();    // model AI yang memeriksa
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_audit_ai');
    }
};
