<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('territorializacoes', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('microarea');
            $table->string('equipe_esf');
            $table->string('profissional_responsavel')->nullable();

            $table->json('area_geo')->nullable(); // mapa futuro

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('territorializacoes');
    }
};
