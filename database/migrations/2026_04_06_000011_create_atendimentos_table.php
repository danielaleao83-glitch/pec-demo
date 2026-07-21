<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atendimentos', function (Blueprint $table) {

            // =========================================================
            // 🔑 IDENTIDADE
            // =========================================================
            $table->id();

            // =========================================================
            // 👤 PACIENTE
            // =========================================================
            $table->foreignId('paciente_id')
                ->constrained('pacientes')
                ->cascadeOnDelete();

            // =========================================================
            // 🧑‍⚕️ PROFISSIONAL
            // =========================================================
            $table->foreignId('profissional_id')
                ->nullable()
                ->constrained('profissionais')
                ->nullOnDelete();

            // =========================================================
            // 🏥 UNIDADE (UUID CORRETO)
            // =========================================================
            $table->foreignUuid('unidade_id')
                ->nullable()
                ->constrained('unidades')
                ->nullOnDelete();

            // =========================================================
            // 🧠 CONTEXTO CLÍNICO
            // =========================================================
            $table->string('tipo_atendimento')->default('consulta');
            $table->string('classificacao_risco')->nullable();
            $table->text('observacao')->nullable();

            // =========================================================
            // ⏱️ DATA REAL
            // =========================================================
            $table->timestamp('data_atendimento')->useCurrent();

            // =========================================================
            // 🔐 AUDITORIA
            // =========================================================
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // =========================================================
            // 🔒 INTEGRIDADE
            // =========================================================
            $table->text('hash_integridade')->nullable();

            // =========================================================
            // 🧾 CONTROLE
            // =========================================================
            $table->timestamps();
            $table->softDeletes();

            // =========================================================
            // 🔍 ÍNDICES
            // =========================================================
            $table->index('data_atendimento');
            $table->index('tipo_atendimento');
        });

        /*
        |----------------------------------------------------------
        | 🔒 VALIDAÇÕES
        |----------------------------------------------------------
        */

        DB::statement("
            ALTER TABLE atendimentos
            ADD CONSTRAINT check_tipo_atendimento
            CHECK (tipo_atendimento IN ('consulta','urgencia','domiciliar','telemedicina'))
        ");

        DB::statement("
            ALTER TABLE atendimentos
            ADD CONSTRAINT check_classificacao_risco
            CHECK (
                classificacao_risco IS NULL OR
                classificacao_risco IN ('vermelho','amarelo','verde','azul')
            )
        ");

        DB::statement('
            ALTER TABLE atendimentos
            ADD CONSTRAINT check_observacao_tamanho
            CHECK (
                observacao IS NULL OR
                length(observacao) <= 5000
            )
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('atendimentos');
    }
};
