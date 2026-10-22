<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_token(): void
    {
        $authenticatedUser = User::factory()->create();

        $token = $authenticatedUser
            ->createToken('test-token')
            ->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/register', [
            'name' => 'Mahasiswa',
            'email' => 'register@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Registrasi berhasil')
            ->assertJsonStructure([
                'user' => ['name', 'email'],
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'register@example.com',
        ]);
    }

    public function test_user_can_login(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Login berhasil')
            ->assertJsonStructure([
                'user' => ['id', 'email'],
                'token',
            ]);
    }

    public function test_books_require_authentication(): void
    {
        $this->getJson('/api/books')->assertUnauthorized();
    }

    public function test_register_requires_authentication(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Mahasiswa',
            'email' => 'register-no-token@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertUnauthorized();
    }
    public function test_login_with_wrong_password_returns_401(): void
    {
        User::factory()->create([
            'email' => 'wrong-password@example.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/login', [
            'email' => 'wrong-password@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    }

    public function test_register_validation_failure_returns_422(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $this->withToken($token)->postJson('/api/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => '123',
            'password_confirmation' => 'different',
        ])->assertUnprocessable();
    }
}
