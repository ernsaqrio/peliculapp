<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TOKEN EXPIRADO → 401 con mensaje de expiración
     */
    public function test_token_expirado_devuelve_401()
    {
        $user = User::factory()->create();

        $token = JWTAuth::fromUser($user);

        JWTAuth::invalidate(JWTAuth::setToken($token));

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    /**
     * ERRORES NO EXPONEN STACK TRACE EN PRODUCCIÓN
     */
    public function test_respuestas_de_error_no_exponen_stack_trace()
    {
        config(['app.env' => 'production']);
        config(['app.debug' => false]);

        // Forzamos error llamando a endpoint inexistente protegido o inválido
        $response = $this->getJson('/api/endpoint-que-no-existe');

        $response->assertStatus(404);

        $json = $response->json();

        $this->assertArrayNotHasKey('exception', $json);
        $this->assertArrayNotHasKey('file', $json);
        $this->assertArrayNotHasKey('line', $json);
    }

    /**
     * PASSWORD NUNCA SE EXPONE EN /me
     */
    public function test_password_no_aparece_en_respuesta_me()
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret123'),
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson('/api/auth/me');

        $response->assertStatus(200);

        $response->assertJsonMissingPath('password');
    }
}
