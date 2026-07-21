<?php

namespace Database\Seeders;

use App\Models\Permissoes\Role;
use App\Models\Permissoes\User;
use Illuminate\Database\Seeder;

class TestDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Criar roles básicas
        $adminRole = Role::firstOrCreate(['nome' => 'admin']);
        $profRole = Role::firstOrCreate(['nome' => 'profissional']);

        // Criar usuário admin
        $admin = User::factory()->create([
            'name' => 'Admin Teste',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole($adminRole->id);

        // Criar 10 profissionais SUS
        User::factory(10)->create()->each(function ($user) use ($profRole) {
            $user->assignRole($profRole->id);
        });
    }
}
