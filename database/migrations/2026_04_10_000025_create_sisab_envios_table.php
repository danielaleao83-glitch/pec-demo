<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sisab_envios', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->timestamp('data_envio')->nullable();
            $table->string('tipo'); // atendimento, cadastro, etc

            $table->json('payload'); // dados enviados
            $table->text('xml_gerado');

            $table->string('status')->default('pendente');
            // pendente | validado | enviado | erro

            $table->text('erro')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sisab_envios');
    }
};
