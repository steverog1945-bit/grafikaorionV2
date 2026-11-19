<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_mesin_id')->constrained('monitoring_mesins')->cascadeOnDelete();
            $table->string('nama', 100);
            // SHA-256 token; token plaintext hanya ditampilkan sekali saat dibuat.
            $table->string('token_hash', 64)->unique();
            $table->timestamp('last_seen_at')->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_agents');
    }
};
