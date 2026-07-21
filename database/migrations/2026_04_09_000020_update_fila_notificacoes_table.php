<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('fila_notificacoes')) {
            return;
        }

        Schema::table('fila_notificacoes', function (Blueprint $table) {

            if (! Schema::hasColumn('fila_notificacoes', 'paciente_id')) {
                $table->unsignedBigInteger('paciente_id')->nullable();
            }

            if (! Schema::hasColumn('fila_notificacoes', 'canal')) {
                $table->string('canal')->default('whatsapp');
            }

            if (! Schema::hasColumn('fila_notificacoes', 'status')) {
                $table->string('status')->default('pendente');
            }

            if (! Schema::hasColumn('fila_notificacoes', 'tentativas')) {
                $table->integer('tentativas')->default(0);
            }

            if (! Schema::hasColumn('fila_notificacoes', 'ultimo_erro')) {
                $table->text('ultimo_erro')->nullable();
            }

            // ❌ NÃO CRIAR FK AQUI (já existe)
            // $table->foreign('paciente_id')->references('id')->on('pacientes')->nullOnDelete();

        });
    }

    public function down(): void
    {
        // ⚠️ NÃO remover nada (produção segura)
    }
};
