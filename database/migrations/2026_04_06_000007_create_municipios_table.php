<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('municipios', function (Blueprint $table) {

            // 🧬 UUID
            $table->uuid('id')->primary();

            $table->string('nome', 150);

            // 🔥 CORREÇÃO AQUI
            $table->uuid('estado_id')->nullable();

            $table->timestamps();

            // índices
            $table->index('nome');
            $table->index('estado_id');

            // FK correta
            $table->foreign('estado_id')
                ->references('id')
                ->on('estados')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipios');
    }
};
