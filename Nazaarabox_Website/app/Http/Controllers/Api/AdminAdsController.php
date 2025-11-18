<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\MovieEmbed;
use App\Models\Episode;
use App\Models\EpisodeEmbed;
use App\Models\TVShow;
use Illuminate\Http\Request;

class AdminAdsController extends Controller
{
    /**
     * Get all movies with embeds that require ads
     */
    public function getMoviesWithAds()
    {
        try {
            // Get movies that have at least one embed with requires_ad = true
            $movies = Movie::whereHas('embeds', function($query) {
                $query->where('requires_ad', true);
            })
            ->with(['embeds' => function($query) {
                $query->where('requires_ad', true)
                      ->orderBy('priority', 'desc')
                      ->orderBy('id');
            }])
            ->orderBy('title')
            ->get();

            $data = $movies->map(function($movie) {
                return [
                    'id' => $movie->id,
                    'title' => $movie->title,
                    'poster_path' => $movie->poster_path,
                    'release_date' => $movie->release_date,
                    'status' => $movie->status,
                    'embeds' => $movie->embeds->map(function($embed) {
                        return [
                            'id' => $embed->id,
                            'server_name' => $embed->server_name,
                            'priority' => $embed->priority,
                            'is_active' => $embed->is_active,
                            'requires_ad' => $embed->requires_ad,
                        ];
                    }),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching movies with ads: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get all episodes with embeds that require ads
     */
    public function getEpisodesWithAds()
    {
        try {
            // Get episodes that have at least one embed with requires_ad = true
            $episodes = Episode::whereHas('embeds', function($query) {
                $query->where('requires_ad', true);
            })
            ->with(['embeds' => function($query) {
                $query->where('requires_ad', true)
                      ->orderBy('priority', 'desc')
                      ->orderBy('id');
            }])
            ->with(['season' => function($query) {
                $query->with(['tvShow' => function($q) {
                    $q->select('id', 'name', 'poster_path');
                }]);
            }])
            ->orderBy('episode_number')
            ->get();

            $data = $episodes->map(function($episode) {
                return [
                    'id' => $episode->id,
                    'name' => $episode->name,
                    'episode_number' => $episode->episode_number,
                    'season_number' => $episode->season ? $episode->season->season_number : null,
                    'still_path' => $episode->still_path,
                    'air_date' => $episode->air_date,
                    'tv_show' => ($episode->season && $episode->season->tvShow) ? [
                        'id' => $episode->season->tvShow->id,
                        'name' => $episode->season->tvShow->name,
                        'poster_path' => $episode->season->tvShow->poster_path,
                    ] : null,
                    'embeds' => $episode->embeds->map(function($embed) {
                        return [
                            'id' => $embed->id,
                            'server_name' => $embed->server_name,
                            'priority' => $embed->priority,
                            'is_active' => $embed->is_active,
                            'requires_ad' => $embed->requires_ad,
                        ];
                    }),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching episodes with ads: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Toggle requires_ad for a movie embed
     */
    public function toggleMovieEmbedAd(Request $request, $movieId, $embedId)
    {
        try {
            $embed = MovieEmbed::where('movie_id', $movieId)
                ->findOrFail($embedId);

            $embed->requires_ad = !$embed->requires_ad;
            $embed->save();

            return response()->json([
                'success' => true,
                'message' => 'Ad requirement toggled successfully',
                'data' => [
                    'requires_ad' => $embed->requires_ad
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error toggling ad requirement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle requires_ad for an episode embed
     */
    public function toggleEpisodeEmbedAd(Request $request, $episodeId, $embedId)
    {
        try {
            $embed = EpisodeEmbed::where('episode_id', $episodeId)
                ->findOrFail($embedId);

            $embed->requires_ad = !$embed->requires_ad;
            $embed->save();

            return response()->json([
                'success' => true,
                'message' => 'Ad requirement toggled successfully',
                'data' => [
                    'requires_ad' => $embed->requires_ad
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error toggling ad requirement: ' . $e->getMessage()
            ], 500);
        }
    }
}

