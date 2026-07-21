<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos_sistema', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | 🆔 ID (UUID pode manter)
            |--------------------------------------------------------------------------
            */
            $table->uuid('id')->primary();

            /*
            |--------------------------------------------------------------------------
            | 👤 USER (BIGINT - PADRÃO LARAVEL)
            |--------------------------------------------------------------------------
            */
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | 📊 EVENTO
            |--------------------------------------------------------------------------
            */
            $table->string('tipo')->nullable();
            $table->json('descricao')->nullable();

            /*
            |--------------------------------------------------------------------------
            | 🌐 RASTREIO
            |--------------------------------------------------------------------------
            */
            $table->ipAddress('ip')->nullable();
            $table->string('user_agent')->nullable();

            /*
            |--------------------------------------------------------------------------
            | 🔗 HASH LGPD
            |--------------------------------------------------------------------------
            */
            $table->string('hash_anterior', 64)->nullable();
            $table->string('hash_atual', 64)->nullable();

            /*
            |--------------------------------------------------------------------------
            | ⏱ DATA
            |--------------------------------------------------------------------------
            */
            $table->timestamp('created_at')->useCurrent();

            /*
            |--------------------------------------------------------------------------
            | ⚡ INDEX
            |--------------------------------------------------------------------------
            */
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos_sistema');
    }
};
