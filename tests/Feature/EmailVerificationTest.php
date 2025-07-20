<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_verification_works()
    {
        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->postJson($verificationUrl);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Email verified successfully']);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    /** @test */
    public function email_verification_fails_with_invalid_hash()
    {
        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => 'invalid-hash']
        );

        $response = $this->postJson($verificationUrl);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid verification link']);
    }
}
