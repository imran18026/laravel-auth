<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmsVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_request_sms_verification()
    {
        $user = User::factory()->create([
            'phone' => '+1234567890',
            'phone_verified' => false
        ]);

        $token = auth()->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/auth/send-sms-code', [
            'phone' => '+1234567890'
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'SMS verification code sent']);
    }

    /** @test */
    public function user_can_verify_sms_code()
    {
        $user = User::factory()->create([
            'phone' => '+1234567890',
            'phone_verified' => false
        ]);

        $token = auth()->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/auth/verify-sms', [
            'phone' => '+1234567890',
            'code' => '123456'
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Phone number verified']);

        $this->assertTrue($user->fresh()->phone_verified);
    }
}
