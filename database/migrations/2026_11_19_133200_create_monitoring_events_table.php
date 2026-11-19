<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_agent_id')->nullable()->constrained('monitoring_agents')->nullOnDelete();
            $table->foreignId('monitoring_mesin_id')->constrained('monitoring_mesins')->cascadeOnDelete();
            // started | progress | completed | error
            $table->string('event', 16);
            $table->string('printer', 100)->nullable();
            $table->string('doc_name', 255)->nullable();
            $table->unsignedInteger('pages_total')->nullable();
            $table->unsignedInteger('pages_done')->nullable();
            $table->unsignedBigInteger('bytes_total')->nullable();
            $table->unsignedBigInteger('bytes_done')->nullable();
            $table->json('detail')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['monitoring_mesin_id', 'created_at']);
            $table->index(['event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_events');
    }
};
