<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\Implementations\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    public function test_register_creates_user_and_returns_token()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'client'
        ];

        $result = $this->authService->register($userData);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals($userData['name'], $result['user']->name);
        $this->assertEquals($userData['email'], $result['user']->email);
        $this->assertEquals($userData['role'], $result['user']->role);
        $this->assertDatabaseHas('users', ['email' => $userData['email']]);
    }

    public function test_login_returns_user_and_token_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $result = $this->authService->login($credentials);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals($user->id, $result['user']->id);
    }

    public function test_login_returns_null_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $credentials = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ];

        $result = $this->authService->login($credentials);

        $this->assertNull($result);
    }

    public function test_logout_deletes_user_tokens()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $user->createToken('test-token');

        $result = $this->authService->logout();

        $this->assertTrue($result);
        $this->assertEquals(0, $user->tokens()->count());
    }

    public function test_get_authenticated_user_returns_current_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $result = $this->authService->getAuthenticatedUser();

        $this->assertEquals($user->id, $result->id);
    }
}