<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_a_token(): void
    {
        $response = $this->postJson('/api/register', [
            'full_name' => 'API Test User',
            'email' => 'api-test@example.com',
            'phone' => '0123456789',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Registration successful.')
            ->assertJsonStructure(['user', 'token']);

        $this->assertDatabaseHas('users', [
            'email' => 'api-test@example.com',
            'full_name' => 'API Test User',
        ]);
    }

    public function test_user_can_login_with_registered_credentials(): void
    {
        $this->postJson('/api/register', [
            'full_name' => 'API Test User',
            'email' => 'api-test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        $response = $this->postJson('/api/login', [
            'email' => 'api-test@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Login successful.')
            ->assertJsonStructure(['user', 'token']);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'missing@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized()
            ->assertJson(['message' => 'The provided credentials are incorrect.']);
    }
}
