<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Genre;
use App\Models\TVShow;
use Illuminate\Database\Seeder;

class TVShowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dramaGenre = Genre::where('slug', 'drama')->first();
        $romanceGenre = Genre::where('slug', 'romance')->first();
        $kDramaGenre = Genre::where('slug', 'k-drama')->first();
        $actionGenre = Genre::where('slug', 'action')->first();
        $comedyGenre = Genre::where('slug', 'comedy')->first();
        $thrillerGenre = Genre::where('slug', 'thriller')->first();
        
        $kDramaCategory = Category::where('slug', 'k-drama')->first();

        $tvShows = [
            [
                'tmdb_id' => 1396,
                'name' => 'Breaking Bad',
                'overview' => 'A high school chemistry teacher turned methamphetamine manufacturer partners with a former student.',
                'first_air_date' => '2008-01-20',
                'last_air_date' => '2013-09-29',
                'number_of_seasons' => 5,
                'number_of_episodes' => 62,
                'vote_average' => 9.5,
                'vote_count' => 4500,
                'view_count' => 5000000,
                'status' => 'ended',
                'is_featured' => true,
                'original_language' => 'en',
                'popularity' => 95.8,
                'episode_run_time' => 47,
                'type' => 'Scripted',
            ],
            [
                'tmdb_id' => 1399,
                'name' => 'Game of Thrones',
                'overview' => 'Nine noble families fight for control over the lands of Westeros.',
                'first_air_date' => '2011-04-17',
                'last_air_date' => '2019-05-19',
                'number_of_seasons' => 8,
                'number_of_episodes' => 73,
                'vote_average' => 8.5,
                'vote_count' => 21000,
                'view_count' => 8000000,
                'status' => 'ended',
                'is_featured' => true,
                'original_language' => 'en',
                'popularity' => 92.5,
                'episode_run_time' => 57,
                'type' => 'Scripted',
            ],
            [
                'tmdb_id' => 66732,
                'name' => 'Stranger Things',
                'overview' => 'When a young boy vanishes, a small town uncovers a mystery involving secret experiments.',
                'first_air_date' => '2016-07-15',
                'last_air_date' => null,
                'number_of_seasons' => 4,
                'number_of_episodes' => 34,
                'vote_average' => 8.7,
                'vote_count' => 12000,
                'view_count' => 6000000,
                'status' => 'active',
                'is_featured' => true,
                'original_language' => 'en',
                'popularity' => 88.3,
                'episode_run_time' => 51,
                'type' => 'Scripted',
            ],
            [
                'tmdb_id' => 1398,
                'name' => 'The Office',
                'overview' => 'A mockumentary on a group of typical office workers, where the workday consists of ego clashes.',
                'first_air_date' => '2005-03-24',
                'last_air_date' => '2013-05-16',
                'number_of_seasons' => 9,
                'number_of_episodes' => 201,
                'vote_average' => 8.9,
                'vote_count' => 5500,
                'view_count' => 4500000,
                'status' => 'ended',
                'is_featured' => false,
                'original_language' => 'en',
                'popularity' => 85.2,
                'episode_run_time' => 22,
                'type' => 'Scripted',
            ],
            [
                'tmdb_id' => 82856,
                'name' => 'The Mandalorian',
                'overview' => 'The travels of a lone bounty hunter in the outer reaches of the galaxy.',
                'first_air_date' => '2019-11-12',
                'last_air_date' => null,
                'number_of_seasons' => 3,
                'number_of_episodes' => 24,
                'vote_average' => 8.7,
                'vote_count' => 8500,
                'view_count' => 3500000,
                'status' => 'active',
                'is_featured' => true,
                'original_language' => 'en',
                'popularity' => 82.7,
                'episode_run_time' => 40,
                'type' => 'Scripted',
            ],
            // K-Dramas
            [
                'tmdb_id' => 104178,
                'name' => 'Crash Landing on You',
                'overview' => 'A South Korean heiress accidentally crash lands in North Korea and falls in love with an army officer.',
                'first_air_date' => '2019-12-14',
                'last_air_date' => '2020-02-16',
                'number_of_seasons' => 1,
                'number_of_episodes' => 16,
                'vote_average' => 8.7,
                'vote_count' => 2300,
                'view_count' => 2500000,
                'status' => 'ended',
                'is_featured' => true,
                'category_id' => $kDramaCategory?->id,
                'original_language' => 'ko',
                'popularity' => 78.5,
                'episode_run_time' => 70,
                'type' => 'Scripted',
            ],
            [
                'tmdb_id' => 94997,
                'name' => 'Squid Game',
                'overview' => 'Hundreds of cash-strapped players accept a strange invitation to compete in children\'s games.',
                'first_air_date' => '2021-09-17',
                'last_air_date' => null,
                'number_of_seasons' => 1,
                'number_of_episodes' => 9,
                'vote_average' => 8.2,
                'vote_count' => 18000,
                'view_count' => 12000000,
                'status' => 'active',
                'is_featured' => true,
                'category_id' => $kDramaCategory?->id,
                'original_language' => 'ko',
                'popularity' => 96.8,
                'episode_run_time' => 60,
                'type' => 'Scripted',
            ],
            [
                'tmdb_id' => 104815,
                'name' => 'Itaewon Class',
                'overview' => 'An ex-con and his friends fight to make their ambitious dreams for their street bar a reality.',
                'first_air_date' => '2020-01-31',
                'last_air_date' => '2020-03-21',
                'number_of_seasons' => 1,
                'number_of_episodes' => 16,
                'vote_average' => 8.5,
                'vote_count' => 1900,
                'view_count' => 1800000,
                'status' => 'ended',
                'is_featured' => false,
                'category_id' => $kDramaCategory?->id,
                'original_language' => 'ko',
                'popularity' => 72.3,
                'episode_run_time' => 70,
                'type' => 'Scripted',
            ],
        ];

        foreach ($tvShows as $tvShowData) {
            $tvShow = TVShow::create($tvShowData);
            
            // Attach genres
            if (isset($tvShowData['category_id']) && $tvShowData['category_id']) {
                // K-Drama
                if ($kDramaGenre) $tvShow->genres()->attach($kDramaGenre->id);
                if ($romanceGenre && rand(0, 1)) $tvShow->genres()->attach($romanceGenre->id);
                if ($dramaGenre) $tvShow->genres()->attach($dramaGenre->id);
            } else {
                // Regular TV shows
                if ($dramaGenre && rand(0, 1)) $tvShow->genres()->attach($dramaGenre->id);
                if ($thrillerGenre && rand(0, 1)) $tvShow->genres()->attach($thrillerGenre->id);
                if ($actionGenre && rand(0, 1)) $tvShow->genres()->attach($actionGenre->id);
                if ($comedyGenre && rand(0, 1)) $tvShow->genres()->attach($comedyGenre->id);
            }
        }
    }
}

