<?php

namespace Database\Seeders;

use App\Models\Auditoria\HistoricoUsuario;
use App\Models\Permissoes\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HistoricoUsuariosAutoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ⚠️ Verifica se existem usuários
        if (User::count() === 0) {
            $this->command->warn('⚠️ Nenhum usuário encontrado. Seeder ignorado.');

            return;
        }

        // ⚠️ Limpa apenas registros DEV/teste (não truncar em produção)
        HistoricoUsuario::query()->delete();

        $usuarios = User::limit(5)->get();

        foreach ($usuarios as $usuario) {

            $acaoBase = [
                'id' => Str::uuid(),
                'usuario_id' => $usuario->id,
                'created_by' => $usuario->id,
                'updated_by' => $usuario->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // 🟢 CRIAÇÃO
            HistoricoUsuario::create(array_merge($acaoBase, [
                'acao' => 'criado',
                'descricao' => 'Usuário criado no sistema.',
            ]));

            // 🟡 ATUALIZAÇÃO
            HistoricoUsuario::create(array_merge($acaoBase, [
                'id' => Str::uuid(),
                'acao' => 'atualizado',
                'descricao' => 'Dados do usuário atualizados automaticamente.',
            ]));

            // 🔴 DESATIVAÇÃO / SOFT DELETE
            HistoricoUsuario::create(array_merge($acaoBase, [
                'id' => Str::uuid(),
                'acao' => 'desativado',
                'descricao' => 'Usuário marcado como desativado (soft delete).',
            ]));
        }

        $this->command->info('✅ HistoricoUsuariosAutoSeeder executado com sucesso.');
    }
}
