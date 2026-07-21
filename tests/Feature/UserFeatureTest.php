<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_returns_200_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        // pode ser 200 OU redirect dependendo da rota
        $response->assertStatus(200);
    }

    public function test_user_registration(): void
    {
        $userData = [
            'name' => 'Teste SUS',
            'email' => 'teste@sus.gov.br',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $userData);

        // ⚠️ Laravel normalmente redireciona para dashboard
        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
        ]);
    }

    public function test_user_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }
}
