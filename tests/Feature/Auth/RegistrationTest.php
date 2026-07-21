<?php

namespace Tests\Feature\Auth;

use App\Models\Permissoes\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Teste SUS',
            'email' => 'teste@sus.gov.br',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');

        $user = User::first();
        $this->assertNotNull($user);
        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
