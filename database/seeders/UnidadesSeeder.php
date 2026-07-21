<?php

namespace Database\Seeders;

use App\Models\Estabelecimentos\Unidade;
use Illuminate\Database\Seeder;

class UnidadesSeeder extends Seeder
{
    public function run(): void
    {
        $unidades = [
            [
                'nome' => 'UBS Central',
                'cnes' => '1234567',
                'tipo' => 'UBS',
                'municipio' => 'Belém',
                'estado' => 'PA',
            ],
            [
                'nome' => 'UBS Norte',
                'cnes' => '2345678',
                'tipo' => 'UBS',
                'municipio' => 'Belém',
                'estado' => 'PA',
            ],
            [
                'nome' => 'UBS Sul',
                'cnes' => '3456789',
                'tipo' => 'UBS',
                'municipio' => 'Belém',
                'estado' => 'PA',
            ],
        ];

        foreach ($unidades as $unidade) {

            Unidade::updateOrCreate(
                ['cnes' => $unidade['cnes']],
                $unidade
            );
        }

        $this->command->info('✅ UnidadesSeeder executado com sucesso.');
    }
}
