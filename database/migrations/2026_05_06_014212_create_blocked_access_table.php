<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {

            $table->uuid('id')->primary();

            $table->string('user_id')->nullable();
            $table->string('acao');
            $table->string('modulo');
            $table->string('registro_id')->nullable();

            $table->jsonb('dados_antes')->nullable();
            $table->jsonb('dados_depois')->nullable();

            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->nullable();

            // 🔐 FORENSE
            $table->text('hash_integridade');
            $table->text('hash_anterior')->nullable();
            $table->text('assinatura')->nullable();

            $table->timestamp('executado_em')->index();

            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};