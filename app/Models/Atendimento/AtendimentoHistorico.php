<?php

namespace App\Models\Atendimento;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class AtendimentoHistorico Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('atendimento_historicos', function (Blueprint $table) {
            $table->id();

            // ------------------------------------------------------------------
            // RELACIONAMENTOS
            // ------------------------------------------------------------------
            $table->foreignId('atendimento_id')
                ->constrained('atendimentos')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('paciente_id')
                ->constrained('pacientes')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('profissional_id')
                ->nullable()
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            // ------------------------------------------------------------------
            // CAMPOS DE HISTÓRICO
            // ------------------------------------------------------------------
            $table->string('acao');       // Ex: 'criado', 'atualizado', 'excluido'
            $table->text('descricao')->nullable(); // Detalhes da ação

            // ------------------------------------------------------------------
            // AUDITORIA GOVERNAMENTAL
            // ------------------------------------------------------------------
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            // ------------------------------------------------------------------
            // TIMESTAMPS E SOFT DELETE
            // ------------------------------------------------------------------
            $table->timestamps();
            $table->softDeletes();

            // ------------------------------------------------------------------
            // ÍNDICES PARA PERFORMANCE
            // ------------------------------------------------------------------
            $table->index(['atendimento_id', 'paciente_id'], 'idx_atendimento_paciente');
            $table->index('profissional_id', 'idx_profissional');
            $table->index('acao', 'idx_acao');

            // ------------------------------------------------------------------
            // INTEGRIDADE: Evita duplicidade de ação para o mesmo atendimento na mesma data
            // ------------------------------------------------------------------
            $table->unique(['atendimento_id', 'acao', 'created_at'], 'uniq_atendimento_acao_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atendimento_historicos');
    }
};
