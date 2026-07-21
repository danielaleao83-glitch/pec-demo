<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HistoricoUsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // ⚠️ Limpar registros DEV/teste (não truncar em produção)
        DB::table('historico_usuarios')->delete();

        $usuarios = [
            [
                'id' => Str::uuid(),
                'nome' => 'João Silva',
                'email' => 'joao@example.com',
                'telefone' => '11999999999',
                'cpf' => '12345678901',
                'cargo' => 'Analista',
                'data_nascimento' => '1990-05-20',
                'endereco' => 'Rua A, 123',
                'acao' => 'criado',
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => Str::uuid(),
                'nome' => 'Maria Souza',
                'email' => 'maria@example.com',
                'telefone' => '11988888888',
                'cpf' => '98765432100',
                'cargo' => 'Coordenadora',
                'data_nascimento' => '1985-12-10',
                'endereco' => 'Rua B, 456',
                'acao' => 'criado',
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Adicione mais usuários ou importe do Excel/CSV
        ];

        // Inserção com updateOrInsert para evitar duplicidade
        foreach ($usuarios as $usuario) {
            DB::table('historico_usuarios')->updateOrInsert(
                ['cpf' => $usuario['cpf']],
                $usuario
            );
        }

        $this->command->info('✅ HistoricoUsuariosSeeder executado com sucesso.');
    }
}
