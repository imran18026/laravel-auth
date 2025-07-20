<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedAccessTest extends TestCase
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
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'superadmin@example.com',
            'password' => 'superadmin123',
        ]);
        $token = $response->json('access_token');

        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/v1/admin/users')
            ->assertStatus(200);

        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/v1/admin/dashboard')
            ->assertStatus(200);

        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/v1/admin/admin-only')
            ->assertStatus(200);

        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/v1/admin/super-admin-only')
            ->assertStatus(200);
    }

    public function test_admin_cannot_access_super_admin_routes()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'admin123',
        ]);
        $token = $response->json('access_token');

        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/v1/admin/super-admin-only')
            ->assertStatus(403);
    }

    public function test_regular_user_cannot_access_admin_routes()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);
        $token = $response->json('access_token');

        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/v1/admin/dashboard')
            ->assertStatus(403);
    }

    public function test_user_list_shows_correct_users_based_on_role()
    {
        // Super admin sees everyone
        $superAdmin = $this->postJson('/api/v1/auth/login', [
            'email' => 'superadmin@example.com',
            'password' => 'superadmin123',
        ])->json('access_token');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $superAdmin])
            ->getJson('/api/v1/admin/users');

        $response->assertStatus(200);
        $this->assertCount(4, $response->json('users'));

        // Admin doesn't see super admin
        $admin = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'admin123',
        ])->json('access_token');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $admin])
            ->getJson('/api/v1/admin/users');

        $response->assertStatus(200);
        $users = $response->json('users');
        $this->assertCount(3, $users);
        $this->assertEmpty(
            array_filter($users, fn($u) => in_array('Super Admin', $u['roles']))
        );
    }
}
