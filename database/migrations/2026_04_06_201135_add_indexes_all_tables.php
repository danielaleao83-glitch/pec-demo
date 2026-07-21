<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_pacientes_cpf_hash ON pacientes(cpf_hash)');
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_pacientes_cns_hash ON pacientes(cns_hash)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_paciente_nome_trgm ON pacientes USING gin (nome gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_logs_auditoria_full ON logs_auditoria (acao, created_at)');
    }

    public function down(): void
    {
        // ⚠️ Não removemos índices críticos automaticamente
    }
};
