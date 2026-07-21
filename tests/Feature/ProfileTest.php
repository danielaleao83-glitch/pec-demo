<?php

namespace Tests\Feature;

use App\Models\Permissoes\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/profile');
        $response->assertStatus(200);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Novo Nome',
            'email' => $user->email,
        ]);

        $response->assertRedirect();
        $this->assertEquals('Novo Nome', $user->fresh()->name);
    }

    public function test_email_verification_status_is_unchanged_when_email_is_unchanged(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)->patch('/profile', ['email' => $user->email]);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $response = $this->actingAs($user)->delete('/profile', [
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertDeleted($user);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $response = $this->actingAs($user)->delete('/profile', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
