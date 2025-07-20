<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class OAuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_login_via_oauth_provider()
    {
        $this->mockSocialite('test@example.com');

        $response = $this->getJson('/api/v1/auth/oauth/google/callback');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'user' => ['id', 'email']
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'email_verified_at' => now()
        ]);
    }

    protected function mockSocialite($email)
    {
        $user = new SocialiteUser();
        $user->map([
            'id' => '12345',
            'name' => 'Test User',
            'email' => $email,
            'avatar' => 'https://example.com/avatar.jpg',
        ]);

        Socialite::shouldReceive('driver->stateless->user')
            ->andReturn($user);
    }
}
