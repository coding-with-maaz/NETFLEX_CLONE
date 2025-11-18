<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            GenreSeeder::class,
            CountrySeeder::class,
            LanguageSeeder::class,
            CategorySeeder::class,
            MovieSeeder::class,
            TVShowSeeder::class,
            SeasonSeeder::class,
            EpisodeSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
