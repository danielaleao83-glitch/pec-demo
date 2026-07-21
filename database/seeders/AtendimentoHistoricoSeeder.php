<?php

namespace Database\Seeders;

use App\Models\Atendimento\Atendimento;
use App\Models\Atendimento\AtendimentoHistorico;
use App\Models\Paciente\Paciente;
use App\Models\Permissoes\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AtendimentoHistoricoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ⚠️ Só roda se existir base mínima
        if (
            Atendimento::count() === 0 ||
            Paciente::count() === 0 ||
            User::count() === 0
        ) {
            $this->command->warn('⚠️ AtendimentoHistoricoSeeder ignorado (dados base insuficientes)');

            return;
        }

        // ⚠️ Evita truncate em produção (mais seguro)
        AtendimentoHistorico::query()->delete();

        $atendimentos = Atendimento::limit(5)->get();
        $pacientes = Paciente::limit(5)->get();
        $profissionais = User::limit(5)->get();

        foreach ($atendimentos as $index => $atendimento) {

            $paciente = $pacientes[$index % $pacientes->count()];
            $profissional = $profissionais[$index % $profissionais->count()];

            $dadosBase = [
                'id' => Str::uuid(),

                'atendimento_id' => $atendimento->id,
                'paciente_id' => $paciente->id,
                'profissional_id' => $profissional->id,

                'created_by' => $profissional->id,
                'updated_by' => $profissional->id,

                'created_at' => now(),
                'updated_at' => now(),
            ];

            // 🟢 CRIADO
            AtendimentoHistorico::create(array_merge($dadosBase, [
                'acao' => 'criado',
                'descricao' => 'Registro inicial do atendimento.',
            ]));

            // 🟡 ATUALIZADO
            AtendimentoHistorico::create(array_merge($dadosBase, [
                'id' => Str::uuid(),
                'acao' => 'atualizado',
                'descricao' => 'Alteração de dados do atendimento.',
            ]));

            // 🔴 EXCLUÍDO (soft)
            AtendimentoHistorico::create(array_merge($dadosBase, [
                'id' => Str::uuid(),
                'acao' => 'excluido',
                'descricao' => 'Atendimento marcado como excluído.',
            ]));
        }

        $this->command->info('✅ AtendimentoHistoricoSeeder executado.');
    }
}
