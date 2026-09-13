<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed a default role
        DB::table('roles')->insert([
            'role_id'    => 1,
            'role_name'  => 'Customer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_user_can_register_successfully()
    {
        $response = $this->postJson('/api/register', [
            'full_name'             => 'Jane Doe',
            'email'                 => 'jane@example.com',
            'phone'                 => '0123456789',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'access_token',
                'token_type',
                'data' => [
                    'id',
                    'role_id',
                    'role_name',
                    'full_name',
                    'email',
                    'phone',
                    'created_at',
                    'updated_at',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email'     => 'jane@example.com',
            'full_name' => 'Jane Doe',
        ]);
    }

    public function test_registration_validation_fails()
    {
        $response = $this->postJson('/api/register', [
            'full_name' => '',
            'email'     => 'invalid-email',
            'password'  => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status'  => false,
                'message' => 'Validation error',
            ])
            ->assertJsonStructure(['errors' => ['full_name', 'email', 'password']]);
    }

    public function test_user_can_login_successfully()
    {
        DB::table('users')->insert([
            'role_id'    => 1,
            'full_name'  => 'John Smith',
            'email'      => 'john@example.com',
            'phone'      => '0987654321',
            'password'   => Hash::make('secret123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'john@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'access_token',
                'token_type',
                'data' => [
                    'id',
                    'role_id',
                    'role_name',
                    'full_name',
                    'email',
                    'phone',
                ]
            ]);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        DB::table('users')->insert([
            'role_id'    => 1,
            'full_name'  => 'John Smith',
            'email'      => 'john@example.com',
            'password'   => Hash::make('secret123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status'  => false,
                'message' => 'Invalid login credentials',
            ]);
    }

    public function test_authenticated_user_can_get_profile_and_logout()
    {
        $reg = $this->postJson('/api/register', [
            'full_name'             => 'Alice User',
            'email'                 => 'alice@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $token = $reg->json('access_token');

        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/me');

        $meResponse->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data'   => [
                    'email' => 'alice@example.com',
                ]
            ]);

        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson([
                'status'  => true,
                'message' => 'Logged out successfully',
            ]);
    }
}
