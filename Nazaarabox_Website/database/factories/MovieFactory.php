<?php

namespace Database\Factories;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
class MovieFactory extends Factory
{
    protected $model = Movie::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tmdb_id' => $this->faker->unique()->numberBetween(1000, 99999),
            'title' => $this->faker->sentence(3),
            'slug' => $this->faker->unique()->slug(),
            'overview' => $this->faker->paragraph(3),
            'poster_path' => '/poster_' . $this->faker->word() . '.jpg',
            'backdrop_path' => '/backdrop_' . $this->faker->word() . '.jpg',
            'release_date' => $this->faker->date(),
            'runtime' => $this->faker->numberBetween(80, 180),
            'vote_average' => $this->faker->randomFloat(1, 5.0, 10.0),
            'vote_count' => $this->faker->numberBetween(10, 1000),
            'view_count' => $this->faker->numberBetween(0, 10000),
            'status' => 'active',
            'is_featured' => $this->faker->boolean(20),
            'imdb_id' => 'tt' . $this->faker->numerify('########'),
            'original_language' => $this->faker->randomElement(['en', 'es', 'fr', 'de']),
            'tagline' => $this->faker->sentence(),
            'popularity' => $this->faker->randomFloat(2, 1.0, 100.0),
            'revenue' => $this->faker->numberBetween(1000000, 1000000000),
            'budget' => $this->faker->numberBetween(1000000, 500000000),
        ];
    }

    /**
     * Indicate that the movie is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the movie is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}

