<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Database\Seeder;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $actionGenre = Genre::where('slug', 'action')->first();
        $dramaGenre = Genre::where('slug', 'drama')->first();
        $comedyGenre = Genre::where('slug', 'comedy')->first();
        $romanceGenre = Genre::where('slug', 'romance')->first();
        $thrillerGenre = Genre::where('slug', 'thriller')->first();

        $movies = [
            [
                'tmdb_id' => 550,
                'title' => 'Fight Club',
                'overview' => 'A ticking-time-bomb insomniac and a slippery soap salesman channel primal male aggression into a shocking new form of therapy.',
                'release_date' => '1999-10-15',
                'runtime' => 139,
                'vote_average' => 8.4,
                'vote_count' => 25000,
                'view_count' => 1500000,
                'status' => 'active',
                'is_featured' => true,
                'original_language' => 'en',
                'popularity' => 85.5,
            ],
            [
                'tmdb_id' => 13,
                'title' => 'Forrest Gump',
                'overview' => 'A man with a low IQ has accomplished great things in his life and been present during significant historic events.',
                'release_date' => '1994-07-06',
                'runtime' => 142,
                'vote_average' => 8.5,
                'vote_count' => 24000,
                'view_count' => 2000000,
                'status' => 'active',
                'is_featured' => true,
                'original_language' => 'en',
                'popularity' => 90.2,
            ],
            [
                'tmdb_id' => 278,
                'title' => 'The Shawshank Redemption',
                'overview' => 'Two imprisoned men bond over a number of years, finding solace and eventual redemption through acts of common decency.',
                'release_date' => '1994-09-23',
                'runtime' => 142,
                'vote_average' => 9.3,
                'vote_count' => 28000,
                'view_count' => 3000000,
                'status' => 'active',
                'is_featured' => true,
                'original_language' => 'en',
                'popularity' => 95.8,
            ],
            [
                'tmdb_id' => 238,
                'title' => 'The Godfather',
                'overview' => 'The aging patriarch of an organized crime dynasty transfers control of his clandestine empire to his reluctant son.',
                'release_date' => '1972-03-24',
                'runtime' => 175,
                'vote_average' => 9.2,
                'vote_count' => 17000,
                'view_count' => 2500000,
                'status' => 'active',
                'is_featured' => true,
                'original_language' => 'en',
                'popularity' => 88.7,
            ],
            [
                'tmdb_id' => 424,
                'title' => 'Schindler\'s List',
                'overview' => 'In German-occupied Poland during World War II, industrialist Oskar Schindler gradually becomes concerned for his Jewish workforce.',
                'release_date' => '1993-12-15',
                'runtime' => 195,
                'vote_average' => 8.9,
                'vote_count' => 13000,
                'view_count' => 1800000,
                'status' => 'active',
                'is_featured' => false,
                'original_language' => 'en',
                'popularity' => 75.3,
            ],
            [
                'tmdb_id' => 769,
                'title' => 'GoodFellas',
                'overview' => 'The story of Henry Hill and his life in the mob, covering his relationship with his wife Karen Hill and his mob partners.',
                'release_date' => '1990-09-21',
                'runtime' => 146,
                'vote_average' => 8.7,
                'vote_count' => 11000,
                'view_count' => 1200000,
                'status' => 'active',
                'is_featured' => false,
                'original_language' => 'en',
                'popularity' => 70.1,
            ],
            [
                'tmdb_id' => 389,
                'title' => '12 Angry Men',
                'overview' => 'A jury holdout attempts to prevent a miscarriage of justice by forcing his colleagues to reconsider the evidence.',
                'release_date' => '1957-04-10',
                'runtime' => 96,
                'vote_average' => 9.0,
                'vote_count' => 7000,
                'view_count' => 800000,
                'status' => 'active',
                'is_featured' => false,
                'original_language' => 'en',
                'popularity' => 65.4,
            ],
            [
                'tmdb_id' => 429,
                'title' => 'The Good, the Bad and the Ugly',
                'overview' => 'While the Civil War rages between the Union and the Confederacy, three men hunt for a fortune in gold.',
                'release_date' => '1966-12-23',
                'runtime' => 178,
                'vote_average' => 8.8,
                'vote_count' => 8000,
                'view_count' => 950000,
                'status' => 'active',
                'is_featured' => false,
                'original_language' => 'en',
                'popularity' => 68.9,
            ],
        ];

        foreach ($movies as $movieData) {
            $movie = Movie::create($movieData);
            
            // Attach genres
            if (rand(0, 1)) $movie->genres()->attach($actionGenre?->id);
            if (rand(0, 1)) $movie->genres()->attach($dramaGenre?->id);
            if (rand(0, 1)) $movie->genres()->attach($comedyGenre?->id);
            if (rand(0, 1)) $movie->genres()->attach($thrillerGenre?->id);
        }
    }
}

