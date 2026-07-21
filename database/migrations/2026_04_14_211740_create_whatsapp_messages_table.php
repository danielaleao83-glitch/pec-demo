<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {

            // 🆔 UUID (padrão distribuído SUS)
            $table->uuid('id')->primary();

            // 📞 destino (E.164)
            $table->string('phone', 20)->index();

            // 💬 mensagem
            $table->longText('message');

            // 👤 vínculos hospitalares
            $table->unsignedBigInteger('paciente_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();

            // 🏥 contexto clínico opcional
            $table->string('context', 50)->nullable()->index();
            // ex: consulta, triagem, vacina, lembrete

            // 📊 status da fila (pipeline robusto)
            $table->enum('status', [
                'pending',
                'queued',
                'processing',
                'sent',
                'failed',
                'cancelled',
            ])->default('pending')->index();

            // 🔁 controle de retry (SUS-grade reliability)
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('max_attempts')->default(5);
            $table->timestamp('next_retry_at')->nullable()->index();

            // 📡 resposta do provider (Meta / Twilio / gateway)
            $table->json('response')->nullable();

            // ❌ erro estruturado
            $table->text('error')->nullable();

            // 🔐 idempotência forte (evita duplicação em fila)
            $table->string('idempotency_key', 120)->nullable()->unique();

            // ⏱ controle de envio
            $table->timestamp('sent_at')->nullable()->index();

            // 🧑‍⚕️ rastreabilidade hospitalar
            $table->string('channel')->default('whatsapp')->index();
            $table->string('provider')->nullable()->index();

            $table->timestamps();

            // 🚀 índices compostos para fila (performance real)
            $table->index(['status', 'next_retry_at']);
            $table->index(['status', 'attempts']);
            $table->index(['paciente_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
