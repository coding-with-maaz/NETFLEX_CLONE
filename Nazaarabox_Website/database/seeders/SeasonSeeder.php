<?php

namespace Database\Seeders;

use App\Models\Season;
use App\Models\TVShow;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tvShows = TVShow::all();

        foreach ($tvShows as $tvShow) {
            $numberOfSeasons = $tvShow->number_of_seasons > 0 ? $tvShow->number_of_seasons : 1;
            $episodesPerSeason = $numberOfSeasons > 0 ? (int)($tvShow->number_of_episodes / $numberOfSeasons) : 10;
            
            for ($i = 1; $i <= $numberOfSeasons; $i++) {
                $airDate = $tvShow->first_air_date 
                    ? $tvShow->first_air_date->copy()->addMonths(($i - 1) * 12)
                    : now()->addMonths(($i - 1) * 12);
                
                $seasonEpisodes = $i === $numberOfSeasons 
                    ? ($tvShow->number_of_episodes - (($i - 1) * $episodesPerSeason))
                    : $episodesPerSeason;
                
                Season::create([
                    'tv_show_id' => $tvShow->id,
                    'season_number' => $i,
                    'name' => "Season {$i}",
                    'overview' => "Season {$i} of {$tvShow->name}",
                    'air_date' => $airDate,
                    'episode_count' => max(1, $seasonEpisodes),
                ]);
            }
        }
    }
}

