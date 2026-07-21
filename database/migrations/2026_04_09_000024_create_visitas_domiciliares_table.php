<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitas_domiciliares', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | 🆔 ID
            |--------------------------------------------------------------------------
            */
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | 🏠 DOMICÍLIO (UUID)
            |--------------------------------------------------------------------------
            */
            $table->uuid('domicilio_id');

            $table->foreign('domicilio_id', 'fk_visitas_domicilio')
                ->references('id')
                ->on('domicilios')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | 👨‍👩‍👧 FAMÍLIA (UUID)
            |--------------------------------------------------------------------------
            */
            $table->uuid('familia_id')->nullable();

            $table->foreign('familia_id', 'fk_visitas_familia')
                ->references('id')
                ->on('familias')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | 🧑‍⚕️ PROFISSIONAL (BIGINT)
            |--------------------------------------------------------------------------
            */
            $table->foreignId('profissional_id')
                ->constrained('profissionais')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | 👤 PACIENTE (BIGINT)
            |--------------------------------------------------------------------------
            */
            $table->foreignId('paciente_id')
                ->nullable()
                ->constrained('pacientes')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | 📊 DADOS DA VISITA
            |--------------------------------------------------------------------------
            */
            $table->date('data_visita');

            $table->string('tipo_visita')->nullable();
            $table->text('observacoes')->nullable();

            /*
            |--------------------------------------------------------------------------
            | 📍 GEOLOCALIZAÇÃO
            |--------------------------------------------------------------------------
            */
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            /*
            |--------------------------------------------------------------------------
            | 🔐 AUDITORIA
            |--------------------------------------------------------------------------
            */
            $table->ipAddress('ip')->nullable();
            $table->string('user_agent')->nullable();

            /*
            |--------------------------------------------------------------------------
            | ⏱ TIMESTAMPS
            |--------------------------------------------------------------------------
            */
            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | ⚡ INDEXES
            |--------------------------------------------------------------------------
            */
            $table->index('domicilio_id');
            $table->index('familia_id');
            $table->index('profissional_id');
            $table->index('paciente_id');
            $table->index('data_visita');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitas_domiciliares');
    }
};
