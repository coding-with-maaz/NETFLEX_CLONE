<?php

namespace Database\Seeders;

use App\Models\Episode;
use App\Models\Season;
use Illuminate\Database\Seeder;

class EpisodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seasons = Season::all();

        foreach ($seasons as $season) {
            $episodeCount = $season->episode_count > 0 ? $season->episode_count : 10;
            $baseAirDate = $season->air_date ?? now();
            
            for ($i = 1; $i <= $episodeCount; $i++) {
                Episode::create([
                    'season_id' => $season->id,
                    'episode_number' => $i,
                    'name' => "Episode {$i}",
                    'overview' => "Episode {$i} of {$season->name}",
                    'air_date' => $baseAirDate->copy()->addDays(($i - 1) * 7),
                    'runtime' => $season->tvShow->episode_run_time ?? 45,
                    'vote_average' => round(rand(70, 95) / 10, 1),
                    'vote_count' => rand(100, 500),
                    'view_count' => rand(50000, 500000),
                ]);
            }
        }
    }
}

