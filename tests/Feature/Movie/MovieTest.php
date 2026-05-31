<?php

namespace Tests\Feature\Movie;

use App\Models\Director;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Obtener token JWT
     */
    private function getToken(): string
    {
        $user = User::factory()->create();

        return auth('api')->login($user);
    }

    /**
     * Listar películas
     */
    public function test_authenticated_user_can_list_movies(): void
    {
        Movie::factory()->count(3)->create();

        $token = $this->getToken();

        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer '.$token
            )->getJson('/api/auth/auth/movies');

        $response->assertStatus(200);
    }

    /**
     * Crear película
     */
    public function test_authenticated_user_can_create_movie(): void
    {
        $token = $this->getToken();

        $director = Director::factory()->create();

        $data = [
            'title' => 'Interstellar',
            'release_date' => '2014-11-07',
            'sinopsis' => 'Viaje espacial épico',
            'duration' => 169,
            'gendre' => 'Sci-Fi',
            'director_id' => $director->id,
        ];

        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer '.$token
            )->postJson('/api/auth/auth/movies', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('movies', [
            'title' => 'Interstellar',
        ]);
    }

    /**
     * Validación al crear película
     */
    public function test_create_movie_requires_valid_data(): void
    {
        $token = $this->getToken();

        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer '.$token
            )->postJson('/api/auth/auth/movies', []);

        $response->assertStatus(422);
    }

    /**
     * Ver película
     */
    public function test_authenticated_user_can_view_movie(): void
    {
        $movie = Movie::factory()->create();

        $token = $this->getToken();

        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer '.$token
            )->getJson('/api/auth/auth/movies/'.$movie->id);

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $movie->id,
            ]);
    }

    /**
     * Actualizar película
     */
    public function test_authenticated_user_can_update_movie(): void
    {
        $movie = Movie::factory()->create();

        $token = $this->getToken();

        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer '.$token
            )->putJson('/api/auth/auth/movies/'.$movie->id, [
                'title' => 'Updated Movie',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('movies', [
            'id' => $movie->id,
            'title' => 'Updated Movie',
        ]);
    }

    /**
     * Eliminar película
     */
    public function test_authenticated_user_can_delete_movie(): void
    {
        $movie = Movie::factory()->create();

        $token = $this->getToken();

        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer '.$token
            )->deleteJson('/api/auth/auth/movies/'.$movie->id);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('movies', [
            'id' => $movie->id,
        ]);
    }

    /**
     * Rutas protegidas
     */
    public function test_movies_endpoints_require_authentication(): void
    {
        $response = $this->getJson('/api/auth/auth/movies');

        $response->assertStatus(401);
    }
}
