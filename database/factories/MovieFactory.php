<?php

namespace Database\Factories;

use App\Models\Director;
use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Movie>
 */
class MovieFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'release_date' => fake()->date(),
            'sinopsis' => fake()->paragraph(),
            'duration' => fake()->numberBetween(80, 240),
            'gendre' => fake()->randomElement([
                'Action',
                'Drama',
                'Comedy',
                'Sci-Fi',
            ]),
            'director_id' => Director::factory(),
        ];
    }
}
