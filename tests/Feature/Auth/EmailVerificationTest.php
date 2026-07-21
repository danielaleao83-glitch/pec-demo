<?php

namespace Tests\Feature\Auth;

use App\Models\Permissoes\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();
        $response = $this->actingAs($user)->get('/verify-email');
        $response->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        Event::fake();
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);
        $response->assertRedirect('/dashboard');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('invalid@sus.gov.br')]
        );

        $response = $this->actingAs($user)->get($verificationUrl);
        $response->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
