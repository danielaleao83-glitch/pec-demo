<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acessos_emergenciais', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | 🆔 ID
            |--------------------------------------------------------------------------
            */
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | 🔗 RELACIONAMENTOS
            |--------------------------------------------------------------------------
            */
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('paciente_id')
                ->constrained('pacientes')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | 🚨 JUSTIFICATIVA (OBRIGATÓRIO LGPD)
            |--------------------------------------------------------------------------
            */
            $table->text('motivo');

            /*
            |--------------------------------------------------------------------------
            | 🌐 RASTREABILIDADE
            |--------------------------------------------------------------------------
            */
            $table->ipAddress('ip');
            $table->string('user_agent')->nullable();

            /*
            |--------------------------------------------------------------------------
            | ⏱ DATA DO ACESSO
            |--------------------------------------------------------------------------
            */
            $table->timestamp('created_at')->useCurrent();

            /*
            |--------------------------------------------------------------------------
            | ⚡ INDEXES (PERFORMANCE / AUDITORIA)
            |--------------------------------------------------------------------------
            */
            $table->index('user_id');
            $table->index('paciente_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acessos_emergenciais');
    }
};
