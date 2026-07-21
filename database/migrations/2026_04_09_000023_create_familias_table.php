<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('familias', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('domicilio_id')->constrained();

            $table->string('responsavel_nome')->nullable();
            $table->string('responsavel_cns')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('familias');
    }
};
