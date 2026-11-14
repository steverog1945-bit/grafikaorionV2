<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kamus alias finishing untuk RekonsiliasiNotaService: nama file berkas /
 * catatan yang menulis "laminating", "lam glossy", "mata ayam", typo dan
 * sebagainya tetap bisa dicocokkan ke baris finishing yang benar.
 *
 * Nama `finishing.nama_finishing` sendiri SELALU jadi alias implisit
 * (dibaca langsung dari tabelnya), jadi tabel ini cuma berisi TAMBAHAN
 * di luar nama resmi - boleh kosong dan rekonsiliasi tetap jalan.
 *
 * alias TIDAK unique dengan sengaja: satu alias boleh menunjuk beberapa
 * finishing ("laminasi doff" = Hot Laminasi Doff/Glossy ATAU Laminasi
 * Dingin Doff/Glossy). Rekonsiliasi menganggapnya tercukupi bila SALAH
 * SATU kandidatnya tertagih, dan menampilkan semua nama kandidat bila
 * tidak ada yang tertagih.
 * Migrasi hanya membuat tabel - php artisan migrate tetap kewenangan owner.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('finishing_alias')) {
            return;
        }

        Schema::create('finishing_alias', function (Blueprint $t) {
            $t->id('id_finishing_alias');
            $t->unsignedBigInteger('id_finishing')->index('fk_finishing_alias_finishing');
            $t->string('alias', 100);
            $t->index('alias', 'idx_finishing_alias_alias');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finishing_alias');
    }
};
