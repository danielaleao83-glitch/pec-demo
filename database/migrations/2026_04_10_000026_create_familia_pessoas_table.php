<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('familia_pessoas', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('familia_id')->constrained();
            $table->foreignUuid('paciente_id')->nullable();

            $table->string('parentesco'); // mãe, filho, etc
            $table->boolean('responsavel')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('familia_pessoas');
    }
};
