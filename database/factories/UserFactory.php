<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = \App\Models\User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Senha padrão
            'cns' => $this->faker->unique()->numerify('###########'),
            'cbo' => $this->faker->numerify('#####'),
            'tipo_profissional' => $this->faker->word(),
            'role_id' => null, // pode ser setado manualmente nos testes
            'ativo' => true,
            'remember_token' => Str::random(10),
        ];
    }

    // Usuário desativado (exemplo de estado)
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'ativo' => false,
        ]);
    }
}
