<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados', function (Blueprint $table) {

            // 🧬 UUID (padrão governo)
            $table->uuid('id')->primary();

            // 📌 Dados
            $table->string('nome', 100);
            $table->char('uf', 2);

            // 🔐 controle
            $table->timestamps();

            // 🔍 índices
            $table->index('nome');
            $table->unique('uf');
        });

        /*
        |----------------------------------------------------------
        | 🔒 VALIDAÇÃO UF (APENAS POSTGRESQL)
        |----------------------------------------------------------
        */
        if (DB::getDriverName() === 'pgsql') {

            DB::statement("
                ALTER TABLE estados
                ADD CONSTRAINT check_uf_format
                CHECK (uf ~ '^[A-Z]{2}$')
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('estados');
    }
};