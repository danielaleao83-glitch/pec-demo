<?php

namespace Tests\Feature;

use App\Models\Permissoes\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se a página inicial ("/") retorna sucesso.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Cria um usuário para autenticação, caso necessário
        $user = User::factory()->create();

        // Acessa a rota principal autenticado
        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }

    /**
     * Testa acesso público sem autenticação.
     */
    public function test_the_homepage_is_accessible_without_auth(): void
    {
        $response = $this->get('/');

        // Dependendo do middleware, pode ser 200 ou redirecionamento para login
        $response->assertStatus(200);
    }
}
