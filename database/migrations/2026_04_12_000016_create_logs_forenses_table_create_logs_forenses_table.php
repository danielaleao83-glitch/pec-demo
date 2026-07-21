<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs_forenses', function (Blueprint $table) {

            // =====================================================
            // 🧬 IDENTIDADE
            // =====================================================
            $table->uuid('id')->primary();

            // =====================================================
            // 👤 RASTREABILIDADE
            // =====================================================
            $table->uuid('user_id')->nullable();
            $table->string('ip', 45)->nullable(); // IPv6 support
            $table->text('user_agent')->nullable();

            $table->string('correlation_id')->nullable();

            // =====================================================
            // 📡 CLASSIFICAÇÃO
            // =====================================================
            $table->string('canal');   // audit, rnds, security
            $table->string('nivel');   // info, warning, error, critical
            $table->string('modulo')->nullable();

            // =====================================================
            // 🧾 CONTEÚDO
            // =====================================================
            $table->text('mensagem');
            $table->jsonb('contexto')->nullable();

            // =====================================================
            // 🔗 CADEIA FORENSE (IMUTABILIDADE)
            // =====================================================
            $table->text('hash_anterior')->nullable();
            $table->text('hash_integridade');

            // =====================================================
            // 🔐 ASSINATURA DIGITAL
            // =====================================================
            $table->text('assinatura')->nullable();

            // =====================================================
            // ⏱️ TEMPO REAL (OFICIAL)
            // =====================================================
            $table->timestampTz('criado_em');

            // =====================================================
            // 🧾 LARAVEL
            // =====================================================
            $table->timestamps();

            // =====================================================
            // 🔍 ÍNDICES (FORENSE PERFORMANCE)
            // =====================================================
            $table->index(['canal', 'nivel']);
            $table->index(['user_id']);
            $table->index(['correlation_id']);
            $table->index(['criado_em']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs_forenses');
    }
};
