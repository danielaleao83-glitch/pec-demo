<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // 🔍 Busca role admin
        $roleId = DB::table('roles')
            ->where('nome', 'admin')
            ->value('id');

        // ⚠️ Segurança: garante que a role existe
        if (! $roleId) {
            throw new \Exception('Role admin não encontrada. Rode o RolesSeeder primeiro.');
        }

        // 👤 Cria ou atualiza usuário admin
        $user = User::updateOrCreate(

            ['email' => 'admin@sistema.com'],

            [
                // ⚠️ Remover se UUID já for automático no Model
                'id' => Str::uuid(),

                'name' => 'Administrador do Sistema',
                'password' => Hash::make('123456'), // 🔥 DEV
                'email_verified_at' => now(),

                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 🔗 Vincula usuário à role
        DB::table('user_roles')->updateOrInsert(

            [
                'user_id' => $user->id,
                'role_id' => $roleId,
            ],

            [
                'id' => Str::uuid(),
                'user_id' => $user->id,
                'role_id' => $roleId,
            ]
        );
    }
}
