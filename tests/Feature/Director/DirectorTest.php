<?php

namespace Tests\Feature\Director;

use App\Models\Director;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectorTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate()
    {
        $user = User::factory()->create();

        return auth('api')->login($user);
    }

    /**
     * Listar directores
     */
    public function test_authenticated_user_can_list_directors(): void
    {
        Director::factory()->count(3)->create();

        $token = $this->authenticate();

        $response = $this->withHeader(
            'Authorization',
            'Bearer '.$token
        )->getJson('/api/auth/directors');

        $response->assertStatus(200);
    }

    /**
     * Crear director
     */
    public function test_authenticated_user_can_create_director(): void
    {
        $token = $this->authenticate();

        $data = [
            'name' => 'Christopher',
            'surname' => 'Nolan',
            'birthdate' => '1970-07-30',
        ];

        $response = $this->withHeader(
            'Authorization',
            'Bearer '.$token
        )->postJson('/api/auth/directors', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('directors', [
            'name' => 'Christopher',
        ]);
    }

    /**
     * Crear director inválido
     */
    public function test_create_director_requires_valid_data(): void
    {
        $token = $this->authenticate();

        $response = $this->withHeader(
            'Authorization',
            'Bearer '.$token
        )->postJson('/api/auth/directors', []);

        $response->assertStatus(422);
    }

    /**
     * Ver director
     */
    public function test_authenticated_user_can_view_director(): void
    {
        $director = Director::factory()->create();

        $token = $this->authenticate();

        $response = $this->withHeader(
            'Authorization',
            'Bearer '.$token
        )->getJson('/api/auth/directors/'.$director->id);

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $director->id,
            ]);
    }

    /**
     * Actualizar director
     */
    public function test_authenticated_user_can_update_director(): void
    {
        $director = Director::factory()->create();

        $token = $this->authenticate();

        $response = $this->withHeader(
            'Authorization',
            'Bearer '.$token
        )->putJson('/api/auth/directors/'.$director->id, [
            'name' => 'Updated',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('directors', [
            'id' => $director->id,
            'name' => 'Updated',
        ]);
    }

    /**
     * Borrar director
     */
    public function test_authenticated_user_can_delete_director(): void
    {
        $director = Director::factory()->create();

        $token = $this->authenticate();

        $response = $this->withHeader(
            'Authorization',
            'Bearer '.$token
        )->deleteJson('/api/auth/directors/'.$director->id);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('directors', [
            'id' => $director->id,
        ]);
    }

    /**
     * Acceso sin token
     */
    public function test_directors_endpoints_require_authentication(): void
    {
        $response = $this->getJson('/api/auth/directors');

        $response->assertStatus(401);
    }
}
