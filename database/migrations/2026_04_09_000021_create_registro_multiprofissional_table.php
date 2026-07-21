<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 🔥 GARANTE EXTENSÕES (POSTGRES)
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');

        Schema::create('registro_multiprofissional', function (Blueprint $table) {

            // 🔑 ID
            $table->bigIncrements('id');

            // 🔗 RELACIONAMENTOS PRINCIPAIS
            $table->unsignedBigInteger('atendimento_id');
            $table->unsignedBigInteger('paciente_id')->nullable();
            $table->unsignedBigInteger('profissional_id');

            // 🧠 CONTROLE CLÍNICO
            $table->string('tipo_registro', 50);
            $table->string('tipo_atendimento', 50)->nullable();

            // 📊 PADRÃO SUS (CBO)
            $table->string('cbo', 10)->nullable();

            // 📝 CONTEÚDO CLÍNICO
            $table->text('descricao')->nullable();
            $table->text('conduta')->nullable();
            $table->text('observacoes')->nullable();

            // 🔄 POLIMORFISMO (E-SUS PADRÃO)
            $table->unsignedBigInteger('registravel_id');
            $table->string('registravel_type', 150);

            // 🔐 AUDITORIA (OBRIGATÓRIO EM SISTEMA SUS)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // ⏱️ CONTROLE
            $table->timestamps();
            $table->softDeletes();

            // 🔗 FOREIGN KEYS (COM NOMES EXPLÍCITOS - EVITA ERRO POSTGRES)
            $table->foreign('atendimento_id', 'fk_registro_atendimento')
                ->references('id')
                ->on('atendimentos')
                ->cascadeOnDelete();

            $table->foreign('paciente_id', 'fk_registro_paciente')
                ->references('id')
                ->on('pacientes')
                ->nullOnDelete();

            $table->foreign('profissional_id', 'fk_registro_profissional')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            // ⚡ ÍNDICES DE PERFORMANCE (ALTO VOLUME CAPS)
            $table->index('atendimento_id', 'idx_registro_atendimento');
            $table->index('paciente_id', 'idx_registro_paciente');
            $table->index('profissional_id', 'idx_registro_profissional');
            $table->index('tipo_registro', 'idx_registro_tipo');
            $table->index('tipo_atendimento', 'idx_registro_tipo_atendimento');

            // 🔍 INDEX COMPOSTO (CONSULTAS REAIS SUS)
            $table->index(
                ['paciente_id', 'tipo_atendimento'],
                'idx_registro_paciente_tipo'
            );

            // 🔍 BUSCA RÁPIDA EM HISTÓRICO
            $table->index(
                ['atendimento_id', 'created_at'],
                'idx_registro_timeline'
            );
        });
    }

    public function down(): void
    {
        Schema::table('registro_multiprofissional', function (Blueprint $table) {

            // 🔥 REMOVE FKs ANTES (OBRIGATÓRIO NO POSTGRES)
            $table->dropForeign('fk_registro_atendimento');
            $table->dropForeign('fk_registro_paciente');
            $table->dropForeign('fk_registro_profissional');

            // 🔥 REMOVE ÍNDICES
            $table->dropIndex('idx_registro_atendimento');
            $table->dropIndex('idx_registro_paciente');
            $table->dropIndex('idx_registro_profissional');
            $table->dropIndex('idx_registro_tipo');
            $table->dropIndex('idx_registro_tipo_atendimento');
            $table->dropIndex('idx_registro_paciente_tipo');
            $table->dropIndex('idx_registro_timeline');
        });

        Schema::dropIfExists('registro_multiprofissional');
    }
};
