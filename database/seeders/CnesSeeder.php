<?php

namespace Database\Seeders;

use App\Models\CnesUnidade;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CnesSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/cnes.csv');

        // ⚠️ Validação do arquivo
        if (! file_exists($path)) {
            $this->command->warn('⚠️ Arquivo cnes.csv não encontrado. Seeder ignorado.');

            return;
        }

        if (($file = fopen($path, 'r')) === false) {
            $this->command->error('❌ Erro ao abrir o arquivo cnes.csv.');

            return;
        }

        // 📌 Remove header
        fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {

            // ⚠️ Linha inválida
            if (empty($row[0])) {
                continue;
            }

            CnesUnidade::updateOrCreate(

                ['cnes' => $row[0]],

                [
                    // ⚠️ remover se UUID já for automático no model
                    'id' => Str::uuid(),

                    'nome_fantasia' => $row[1] ?? null,
                    'razao_social' => $row[2] ?? null,
                    'municipio' => $row[3] ?? null,
                    'uf' => $row[4] ?? null,
                    'tipo_unidade' => $row[5] ?? null,
                    'natureza_juridica' => $row[6] ?? null,
                    'telefone' => $row[7] ?? null,
                    'email' => $row[8] ?? null,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        fclose($file);

        $this->command->info('✅ CNES importado com sucesso.');
    }
}
