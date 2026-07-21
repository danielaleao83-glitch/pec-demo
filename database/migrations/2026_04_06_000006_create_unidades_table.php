<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {

            // =========================================================
            // 🧬 IDENTIDADE
            // =========================================================
            $table->uuid('id')->primary();

            // =========================================================
            // 🏥 DADOS PRINCIPAIS
            // =========================================================
            $table->string('nome');

            $table->char('cnes', 7)->unique();
            $table->string('tipo')->nullable();

            $table->string('municipio');
            $table->char('estado', 2);

            $table->boolean('ativo')->default(true);

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
            // 🧾 CICLO DE VIDA
            // =========================================================
            $table->timestamps();
            $table->softDeletes();

            // =========================================================
            // 🔒 CONTROLE
            // =========================================================
            $table->text('hash_integridade')->nullable();
            $table->string('origem_registro')->default('manual');

            // =========================================================
            // 🔍 PERFORMANCE
            // =========================================================
            $table->index('cnes');
            $table->index(['municipio', 'estado']);
            $table->index('tipo');
            $table->index('ativo');
            $table->index('created_by');
        });

        /*
        |==========================================================
        | 🔥 REGRAS AVANÇADAS (APENAS POSTGRESQL)
        |==========================================================
        */
        if (DB::getDriverName() === 'pgsql') {

            // CHECKS
            DB::statement("
                ALTER TABLE unidades
                ADD CONSTRAINT check_cnes_numeric
                CHECK (cnes ~ '^[0-9]{7}$')
            ");

            DB::statement("
                ALTER TABLE unidades
                ADD CONSTRAINT check_estado_format
                CHECK (estado ~ '^[A-Z]{2}$')
            ");

            DB::statement("
                ALTER TABLE unidades
                ADD CONSTRAINT check_origem_registro
                CHECK (origem_registro IN ('manual','sisab','import','integracao'))
            ");

            // FUNÇÃO HASH
            DB::statement("
                CREATE OR REPLACE FUNCTION fn_unidades_hash()
                RETURNS trigger AS $$
                BEGIN
                    NEW.hash_integridade :=
                        encode(
                            digest(
                                coalesce(NEW.id::text,'') ||
                                coalesce(NEW.nome,'') ||
                                coalesce(NEW.cnes,'') ||
                                coalesce(NEW.updated_at::text,''),
                                'sha256'
                            ),
                            'hex'
                        );
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
            ");

            // TRIGGER
            DB::statement('DROP TRIGGER IF EXISTS trg_unidades_hash ON unidades');

            DB::statement('
                CREATE TRIGGER trg_unidades_hash
                BEFORE INSERT OR UPDATE ON unidades
                FOR EACH ROW
                EXECUTE FUNCTION fn_unidades_hash()
            ');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP TRIGGER IF EXISTS trg_unidades_hash ON unidades');
            DB::statement('DROP FUNCTION IF EXISTS fn_unidades_hash');
        }

        Schema::dropIfExists('unidades');
    }
};