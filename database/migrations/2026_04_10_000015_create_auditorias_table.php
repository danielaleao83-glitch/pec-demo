<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {

            // 🔑 ID (UUID manual controlado pela aplicação)
            $table->uuid('id')->primary();

            // 👤 usuário
            $table->uuid('user_id')->nullable()->index();

            // 🧠 contexto
            $table->string('acao', 50)->index();
            $table->string('modulo', 100)->index();
            $table->uuid('registro_id')->nullable()->index();

            // 📊 dados (compatível com todos bancos)
            $table->json('dados_antes')->nullable();
            $table->json('dados_depois')->nullable();

            // 🌐 request
            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('url')->nullable();
            $table->string('metodo_http', 10)->nullable();

            // ⏱️ evento clínico real (NÃO ORM)
            $table->timestamp('executado_em')->useCurrent()->index();

            // 🔗 rastreio
            $table->uuid('correlation_id')->nullable()->index();

            // 🔐 integridade forense
            $table->text('hash_integridade');
            $table->text('hash_anterior')->nullable();
            $table->text('assinatura')->nullable();
        });

        /*
        |--------------------------------------------------
        | 🔐 IMUTABILIDADE REAL (POSTGRES)
        |--------------------------------------------------
        */

        DB::statement("
            CREATE OR REPLACE FUNCTION bloquear_auditoria()
            RETURNS trigger AS $$
            BEGIN
                RAISE EXCEPTION 'AUDITORIA IMUTÁVEL - OPERAÇÃO NÃO PERMITIDA';
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::statement("
            CREATE TRIGGER trg_auditoria_no_update
            BEFORE UPDATE ON auditorias
            FOR EACH ROW
            EXECUTE FUNCTION bloquear_auditoria();
        ");

        DB::statement("
            CREATE TRIGGER trg_auditoria_no_delete
            BEFORE DELETE ON auditorias
            FOR EACH ROW
            EXECUTE FUNCTION bloquear_auditoria();
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trg_auditoria_no_update ON auditorias;');
        DB::statement('DROP TRIGGER IF EXISTS trg_auditoria_no_delete ON auditorias;');
        DB::statement('DROP FUNCTION IF EXISTS bloquear_auditoria;');

        Schema::dropIfExists('auditorias');
    }
};