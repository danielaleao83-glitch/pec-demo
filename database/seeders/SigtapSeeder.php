<?php

namespace Database\Seeders;

use App\Models\SigtapProcedimento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SigtapSeeder extends Seeder
{
    public function run(): void
    {
        $procedimentos = [
            [
                'codigo' => '0301010013',
                'nome' => 'Consulta médica em atenção básica',
                'complexidade' => 'Atenção Básica',
                'tipo_financiamento' => 'PAB',
                'ativo' => true,
            ],
            // Adicione mais procedimentos do SIGTAP conforme necessidade
        ];

        foreach ($procedimentos as $proc) {
            SigtapProcedimento::updateOrCreate(
                ['codigo' => $proc['codigo']],
                array_merge($proc, [
                    'id' => Str::uuid(), // garante UUID
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('✅ SigtapSeeder executado com sucesso.');
    }
}
