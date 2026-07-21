<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescricoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('atendimento_id')->nullable();
            $table->text('medicamento')->nullable();
            $table->text('dosagem')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescricoes');
    }
};
