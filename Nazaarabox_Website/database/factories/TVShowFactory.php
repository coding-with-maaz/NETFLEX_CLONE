<?php

namespace Database\Factories;

use App\Models\TVShow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TVShow>
 */
class TVShowFactory extends Factory
{
    protected $model = TVShow::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tmdb_id' => $this->faker->unique()->numberBetween(1000, 99999),
            'name' => $this->faker->sentence(3),
            'slug' => $this->faker->unique()->slug(),
            'overview' => $this->faker->paragraph(3),
            'poster_path' => '/poster_' . $this->faker->word() . '.jpg',
            'backdrop_path' => '/backdrop_' . $this->faker->word() . '.jpg',
            'first_air_date' => $this->faker->date(),
            'last_air_date' => $this->faker->date(),
            'number_of_seasons' => $this->faker->numberBetween(1, 10),
            'number_of_episodes' => $this->faker->numberBetween(10, 200),
            'vote_average' => $this->faker->randomFloat(1, 5.0, 10.0),
            'vote_count' => $this->faker->numberBetween(10, 1000),
            'view_count' => $this->faker->numberBetween(0, 10000),
            'status' => 'active',
            'is_featured' => $this->faker->boolean(20),
            'imdb_id' => 'tt' . $this->faker->numerify('########'),
            'original_language' => $this->faker->randomElement(['en', 'es', 'fr', 'de']),
            'tagline' => $this->faker->sentence(),
            'popularity' => $this->faker->randomFloat(2, 1.0, 100.0),
            'episode_run_time' => $this->faker->numberBetween(20, 60),
        ];
    }

    /**
     * Indicate that the TV show is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the TV show is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}

