<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Season;
use App\Models\Episode;
use Illuminate\Http\Request;

class SeasonApiController extends Controller
{
    /**
     * Get seasons for a TV show
     */
    public function getTVShowSeasons($tvshowId)
    {
        try {
            // Eager load episodes to get accurate episode count
            $seasons = Season::where('tv_show_id', $tvshowId)
                ->with('episodes')
                ->orderBy('season_number')
                ->get();

            $formatted = $seasons->map(function($season) {
                // Use actual episode count from relationship, fallback to stored count
                $episodeCount = $season->episodes ? $season->episodes->count() : ($season->episode_count ?? 0);
                
                return [
                    'id' => $season->id,
                    'season_number' => $season->season_number,
                    'number' => $season->season_number, // Alias for frontend compatibility
                    'name' => $season->name,
                    'overview' => $season->overview,
                    'poster_path' => $season->poster_path,
                    'air_date' => $season->air_date?->format('Y-m-d'),
                    'episode_count' => $episodeCount,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formatted
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching seasons: ' . $e->getMessage(), [
                'tvshow_id' => $tvshowId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching seasons: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get episodes for a specific season
     */
    public function getSeasonEpisodes($tvshowId, $seasonId)
    {
        try {
            $episodes = Episode::where('season_id', $seasonId)
                ->whereHas('season', function($q) use ($tvshowId) {
                    $q->where('tv_show_id', $tvshowId);
                })
                ->orderBy('episode_number')
                ->with(['season.tvShow'])
                ->get();

            $formatted = $episodes->map(function($episode) {
                return [
                    'id' => $episode->id,
                    'episode_number' => $episode->episode_number,
                    'number' => $episode->episode_number, // Alias for frontend compatibility
                    'name' => $episode->name,
                    'title' => $episode->name, // Alias for frontend compatibility
                    'overview' => $episode->overview,
                    'still_path' => $episode->still_path,
                    'air_date' => $episode->air_date?->format('Y-m-d'),
                    'runtime' => $episode->runtime,
                    'vote_average' => (float) $episode->vote_average,
                    'vote_count' => $episode->vote_count,
                    'season' => $episode->season ? [
                        'id' => $episode->season->id,
                        'season_number' => $episode->season->season_number,
                    ] : null,
                    'tvShow' => $episode->season && $episode->season->tvShow ? [
                        'id' => $episode->season->tvShow->id,
                        'name' => $episode->season->tvShow->name,
                        'poster_path' => $episode->season->tvShow->poster_path,
                        'backdrop_path' => $episode->season->tvShow->backdrop_path,
                    ] : null,
                    'tvshow' => $episode->season && $episode->season->tvShow ? [
                        'id' => $episode->season->tvShow->id,
                        'name' => $episode->season->tvShow->name,
                        'poster_path' => $episode->season->tvShow->poster_path,
                        'backdrop_path' => $episode->season->tvShow->backdrop_path,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formatted
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching episodes: ' . $e->getMessage(), [
                'tvshow_id' => $tvshowId,
                'season_id' => $seasonId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching episodes: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}

