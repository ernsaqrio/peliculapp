<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Login correcto
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ]);
    }

    /**
     * Login incorrecto
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'incorrecta',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Login sin email
     */
    public function test_login_requires_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'password' => 'password',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    /**
     * Login sin password
     */
    public function test_login_requires_password(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    /**
     * /me con token válido
     */
    public function test_authenticated_user_can_access_me_endpoint(): void
    {
        $user = User::factory()->create();

        $token = auth('api')->login($user);

        $response = $this->withHeader(
            'Authorization',
            'Bearer '.$token
        )->getJson('/api/auth/me');

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'email' => $user->email,
            ]);
    }

    /**
     * /me sin token
     */
    public function test_me_endpoint_requires_token(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    /**
     * Refresh válido
     */
    public function test_user_can_refresh_token(): void
    {
        $user = User::factory()->create();

        $token = auth('api')->login($user);

        $response = $this->withHeader(
            'Authorization',
            'Bearer '.$token
        )->postJson('/api/auth/refresh');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ]);
    }

    /**
     * Logout invalida token
     */
    public function test_logout_invalidates_token(): void
    {
        $user = User::factory()->create();

        $token = auth('api')->login($user);

        // Logout
        $this->withHeader(
            'Authorization',
            'Bearer '.$token
        )->postJson('/api/auth/logout');

        // Reutilizar token
        $response = $this->withHeader(
            'Authorization',
            'Bearer '.$token
        )->getJson('/api/auth/me');

        $response->assertStatus(401);
    }
}
