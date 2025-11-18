<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MovieEmbed;
use App\Models\EpisodeEmbed;
use App\Models\Language;
use Illuminate\Http\Request;

class EmbedApiController extends Controller
{
    /**
     * Get embeds for a movie
     */
    public function getMovieEmbeds($movieId)
    {
        try {
            // For admin, show all embeds (active and inactive)
            // For public API, filter by is_active
            $embeds = MovieEmbed::where('movie_id', $movieId)
                ->with('language')
                ->orderBy('priority', 'desc')
                ->orderBy('id')
                ->get(['id', 'server_name', 'embed_url', 'language_id', 'priority', 'is_active', 'requires_ad']);

            return response()->json([
                'success' => true,
                'data' => $embeds->map(function($embed) {
                    // Decode HTML entities in embed URL (handle double encoding)
                    $embedUrl = $embed->embed_url;
                    $embedUrl = html_entity_decode($embedUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $embedUrl = html_entity_decode($embedUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    
                    return [
                        'id' => $embed->id,
                        'server_name' => $embed->server_name,
                        'embed_url' => $embedUrl,
                        'language_id' => $embed->language_id,
                        'language' => $embed->language ? [
                            'id' => $embed->language->id,
                            'name' => $embed->language->name,
                            'code' => $embed->language->code,
                        ] : null,
                        'priority' => $embed->priority,
                        'is_active' => $embed->is_active,
                        'requires_ad' => $embed->requires_ad ?? false,
                    ];
                })
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching embeds: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get embeds for an episode
     */
    public function getEpisodeEmbeds($episodeId)
    {
        try {
            // For admin, show all embeds (active and inactive)
            // For public API, filter by is_active
            $embeds = EpisodeEmbed::where('episode_id', $episodeId)
                ->orderBy('priority', 'desc')
                ->orderBy('id')
                ->get(['id', 'server_name', 'embed_url', 'priority', 'is_active', 'requires_ad']);

            return response()->json([
                'success' => true,
                'data' => $embeds->map(function($embed) {
                    // Decode HTML entities in embed URL (handle double encoding)
                    $embedUrl = $embed->embed_url;
                    $embedUrl = html_entity_decode($embedUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $embedUrl = html_entity_decode($embedUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    
                    return [
                        'id' => $embed->id,
                        'server_name' => $embed->server_name,
                        'embed_url' => $embedUrl,
                        'priority' => $embed->priority,
                        'is_active' => $embed->is_active,
                        'requires_ad' => $embed->requires_ad ?? false,
                    ];
                })
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching embeds: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Create episode embed
     */
    public function createEpisodeEmbed(Request $request, $episodeId)
    {
        try {
            $request->validate([
                'server_name' => 'required|string|max:255',
                'embed_url' => 'required|string',
                'priority' => 'nullable|integer|min:0|max:999',
                'is_active' => 'nullable|boolean',
                'requires_ad' => 'nullable|boolean',
            ]);

            $embedUrl = $this->processEmbedInput($request->embed_url);

            $embed = EpisodeEmbed::create([
                'episode_id' => $episodeId,
                'server_name' => $request->server_name,
                'embed_url' => $embedUrl,
                'priority' => $request->priority ?? 0,
                'is_active' => $request->is_active ?? true,
                'requires_ad' => $request->requires_ad ?? false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Embed created successfully',
                'data' => $embed
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating embed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update episode embed
     */
    public function updateEpisodeEmbed(Request $request, $episodeId, $embedId)
    {
        try {
            $embed = EpisodeEmbed::where('episode_id', $episodeId)->findOrFail($embedId);

            $request->validate([
                'server_name' => 'sometimes|string|max:255',
                'embed_url' => 'sometimes|string',
                'priority' => 'nullable|integer|min:0|max:999',
                'is_active' => 'nullable|boolean',
                'requires_ad' => 'nullable|boolean',
            ]);

            $updateData = [];
            if ($request->has('server_name')) {
                $updateData['server_name'] = $request->server_name;
            }
            if ($request->has('embed_url')) {
                $updateData['embed_url'] = $this->processEmbedInput($request->embed_url);
            }
            if ($request->has('priority')) {
                $updateData['priority'] = $request->priority;
            }
            if ($request->has('is_active')) {
                $updateData['is_active'] = $request->is_active;
            }
            if ($request->has('requires_ad')) {
                $updateData['requires_ad'] = $request->requires_ad;
            }

            $embed->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Embed updated successfully',
                'data' => $embed->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating embed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete episode embed
     */
    public function deleteEpisodeEmbed($episodeId, $embedId)
    {
        try {
            $embed = EpisodeEmbed::where('episode_id', $episodeId)->findOrFail($embedId);
            $embed->delete();

            return response()->json([
                'success' => true,
                'message' => 'Embed deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting embed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process embed input (URL, ID, or iframe HTML) to extract embed URL
     * Returns both the processed URL and the original format
     */
    private function processEmbedInput($input)
    {
        $input = trim($input);
        $original = $input;
        
        // Check if it's an iframe HTML - if so, preserve it
        if (preg_match('/<iframe/i', $input)) {
            // Store the full iframe HTML as-is
            return $input;
        }
        
        // If it's already an embed URL, return it
        if (filter_var($input, FILTER_VALIDATE_URL) && 
            (str_contains($input, 'embed') || str_contains($input, 'iframe'))) {
            return $input;
        }
        
        // YouTube ID extraction
        if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $input, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
        if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $input, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
        if (preg_match('/^([a-zA-Z0-9_-]{11})$/', $input)) {
            // Just YouTube ID
            return 'https://www.youtube.com/embed/' . $input;
        }
        
        // Dailymotion ID extraction
        if (preg_match('/dailymotion\.com\/video\/([a-zA-Z0-9]+)/', $input, $matches)) {
            return 'https://www.dailymotion.com/embed/video/' . $matches[1];
        }
        if (preg_match('/^([a-zA-Z0-9]+)$/', $input) && strlen($input) >= 5 && strlen($input) <= 10) {
            // Could be Dailymotion ID (usually 5-10 chars)
            return 'https://www.dailymotion.com/embed/video/' . $input;
        }
        
        // If it's a valid URL, return it as is
        if (filter_var($input, FILTER_VALIDATE_URL)) {
            return $input;
        }
        
        return $input; // Return as is if we can't process it
    }

    /**
     * Create movie embed
     */
    public function createMovieEmbed(Request $request, $movieId)
    {
        try {
            $request->validate([
                'server_name' => 'required|string|max:255',
                'embed_url' => 'required|string',
                'language_id' => 'nullable|integer|exists:languages,id',
                'priority' => 'nullable|integer|min:0|max:999',
                'is_active' => 'nullable|boolean',
                'requires_ad' => 'nullable|boolean',
            ]);

            $embedUrl = $this->processEmbedInput($request->embed_url);

            $embed = MovieEmbed::create([
                'movie_id' => $movieId,
                'server_name' => $request->server_name,
                'embed_url' => $embedUrl,
                'language_id' => $request->language_id ?: null,
                'priority' => $request->priority ?? 0,
                'is_active' => $request->is_active ?? true,
                'requires_ad' => $request->requires_ad ?? false,
            ]);
            
            $embed->load('language');

            return response()->json([
                'success' => true,
                'message' => 'Embed created successfully',
                'data' => $embed
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating embed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update movie embed
     */
    public function updateMovieEmbed(Request $request, $movieId, $embedId)
    {
        try {
            $embed = MovieEmbed::where('movie_id', $movieId)->findOrFail($embedId);

            $request->validate([
                'server_name' => 'sometimes|string|max:255',
                'embed_url' => 'sometimes|string',
                'language_id' => 'nullable|integer|exists:languages,id',
                'priority' => 'nullable|integer|min:0|max:999',
                'is_active' => 'nullable|boolean',
                'requires_ad' => 'nullable|boolean',
            ]);

            $updateData = [];
            if ($request->has('server_name')) {
                $updateData['server_name'] = $request->server_name;
            }
            if ($request->has('embed_url')) {
                $updateData['embed_url'] = $this->processEmbedInput($request->embed_url);
            }
            if ($request->has('language_id')) {
                $updateData['language_id'] = $request->language_id ?: null;
            }
            if ($request->has('priority')) {
                $updateData['priority'] = $request->priority;
            }
            if ($request->has('is_active')) {
                $updateData['is_active'] = $request->is_active;
            }
            if ($request->has('requires_ad')) {
                $updateData['requires_ad'] = $request->requires_ad;
            }

            $embed->update($updateData);
            $embed->load('language');

            return response()->json([
                'success' => true,
                'message' => 'Embed updated successfully',
                'data' => $embed->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating embed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete movie embed
     */
    public function deleteMovieEmbed($movieId, $embedId)
    {
        try {
            $embed = MovieEmbed::where('movie_id', $movieId)->findOrFail($embedId);
            $embed->delete();

            return response()->json([
                'success' => true,
                'message' => 'Embed deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting embed: ' . $e->getMessage()
            ], 500);
        }
    }
}

