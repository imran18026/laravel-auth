<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesSeeder::class);
        $this->seed(\Database\Seeders\TestUsersSeeder::class);
    }

    public function test_super_admin_can_access_all_routes()
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'superadmin@example.com',
            'password' => 'superadmin123',
        ])->json('access_token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/dashboard')
            ->assertStatus(200);

        $this->withToken($token)
            ->getJson('/api/v1/admin/super-admin-only')
            ->assertStatus(200);
    }

    public function test_admin_cannot_access_super_admin_routes()
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'admin123',
        ])->json('access_token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/super-admin-only')
            ->assertStatus(403);
    }

    public function test_regular_user_cannot_access_admin_routes()
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ])->json('access_token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/dashboard')
            ->assertStatus(403);
    }
}
