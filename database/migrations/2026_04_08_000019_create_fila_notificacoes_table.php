<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fila_notificacoes', function (Blueprint $table) {

            // 🔥 UUID com geração automática (PostgreSQL)
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));

            // Relacionamento com paciente
            $table->unsignedBigInteger('paciente_id')->nullable();

            // Canal (whatsapp, sms, email)
            $table->string('canal')->default('whatsapp');

            // Destino (telefone)
            $table->string('destino');

            // Conteúdo
            $table->text('mensagem');

            // Controle
            $table->string('status')->default('pendente'); // pendente, enviado, erro
            $table->integer('tentativas')->default(0);
            $table->text('ultimo_erro')->nullable();

            // Auditoria
            $table->timestamps();
            $table->softDeletes();

            // FK
            $table->foreign('paciente_id')
                ->references('id')
                ->on('pacientes')
                ->nullOnDelete();

            // 🔥 ÍNDICES IMPORTANTES
            $table->index('paciente_id');
            $table->index('status');
            $table->index('canal');

            // 🔥 ÍNDICE DE FILA (ALTA PERFORMANCE)
            $table->index(['status', 'tentativas']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fila_notificacoes');
    }
};
