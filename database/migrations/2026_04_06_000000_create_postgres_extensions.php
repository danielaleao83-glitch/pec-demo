<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |----------------------------------------------------------------------
        | 🧠 EXECUTAR SOMENTE EM POSTGRESQL
        |----------------------------------------------------------------------
        */
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        /*
        |----------------------------------------------------------------------
        | 🔒 LISTA DE EXTENSÕES
        |----------------------------------------------------------------------
        */
        $extensoes = [
            'pgcrypto',
            'uuid-ossp',
            'pg_trgm',
            'btree_gin',
            'citext',
        ];

        /*
        |----------------------------------------------------------------------
        | 🚀 CRIA EXTENSÕES COM IF NOT EXISTS (MAIS SEGURO)
        |----------------------------------------------------------------------
        */
        foreach ($extensoes as $extensao) {
            try {
                DB::statement("CREATE EXTENSION IF NOT EXISTS \"{$extensao}\";");
            } catch (\Throwable $e) {

                /*
                |--------------------------------------------------------------
                | ⚠️ NÃO QUEBRA MIGRATION SE EXTENSÃO NÃO EXISTIR NO SERVIDOR
                |--------------------------------------------------------------
                | Ex: hospedagem que não permite pg_trgm
                */
                logger()->warning("Extensão PostgreSQL não criada: {$extensao}", [
                    'erro' => $e->getMessage(),
                ]);
            }
        }
    }

    public function down(): void
    {
        /*
        |----------------------------------------------------------------------
        | ⚠️ NÃO REMOVER EXTENSÕES EM PRODUÇÃO
        |----------------------------------------------------------------------
        */
    }
};
