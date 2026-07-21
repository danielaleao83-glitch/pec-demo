<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            $table->bigIncrements('id');

            // UUID REAL (PostgreSQL compatible)
            $table->uuid('uuid')->unique()->index();

            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');

            $table->string('cns')->nullable();
            $table->string('cbo')->nullable();
            $table->string('tipo_profissional')->nullable();

            $table->boolean('ativo')->default(true);
            $table->timestamp('ultimo_login_em')->nullable();
            $table->string('ultimo_ip', 45)->nullable();

            $table->unsignedBigInteger('role_id')->nullable();

            $table->text('hash_integridade')->nullable();
            $table->string('origem_registro')->default('sistema');

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('ativo');
            $table->index('ultimo_login_em');
        });

        // FK segura
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->nullOnDelete();
        });

        /**
         * UUID automático CORRETO no PostgreSQL
         */
        DB::statement("CREATE EXTENSION IF NOT EXISTS pgcrypto");

        DB::statement("
            ALTER TABLE users
            ALTER COLUMN uuid SET DEFAULT gen_random_uuid();
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};