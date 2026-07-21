<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seeders do SUS...');

        $this->call([
            RolesSeeder::class,
            CnesSeeder::class,
            UnidadesSeeder::class,
            AdminUserSeeder::class,
            PacienteSeeder::class,
        ]);

        if (App::environment('local')) {
            $this->command->warn('⚠️ Seeders avançados não executados automaticamente:');
            foreach (['AtendimentoHistoricoSeeder', 'HistoricoUsuariosSeeder'] as $seeder) {
                $this->command->line("→ {$seeder}");
            }
        }

        $this->command->info('🏁 Processo finalizado.');
    }
}
