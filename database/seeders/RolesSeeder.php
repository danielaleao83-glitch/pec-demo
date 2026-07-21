<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin',
            'medico',
            'enfermeiro',
            'atendente',
            'profissional_saude',
        ];

        foreach ($roles as $role) {
            if (! Role::where('name', $role)->exists()) {
                Role::create([
                    'name' => $role,
                    'description' => ucfirst(str_replace('_', ' ', $role)),
                ]);
            }
        }

        $this->command->info('Roles seed completed successfully!');
    }
}
