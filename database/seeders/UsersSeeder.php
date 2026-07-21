<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        if (! $adminRole) {
            $this->command->error('Role admin não encontrada!');

            return;
        }

        User::create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
        ]);

        $this->command->info('Usuário admin criado com sucesso!');
    }
}
