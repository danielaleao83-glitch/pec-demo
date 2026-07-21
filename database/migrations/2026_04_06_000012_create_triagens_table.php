<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('triagens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('atendimento_id')->nullable();
            $table->string('pressao_arterial')->nullable();
            $table->float('temperatura')->nullable();
            $table->float('peso')->nullable();
            $table->float('altura')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('triagens');
    }
};
