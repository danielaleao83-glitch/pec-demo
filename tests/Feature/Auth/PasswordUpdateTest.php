<?php

namespace Tests\Feature\Auth;

use App\Models\Permissoes\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $response = $this->actingAs($user)->patch('/user/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect();
        $this->assertTrue(auth()->check());
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $response = $this->actingAs($user)->patch('/user/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrors('current_password');
    }
}
