<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MovieDownload;
use App\Models\EpisodeDownload;
use Illuminate\Http\Request;

class DownloadApiController extends Controller
{
    /**
     * Get downloads for a movie
     */
    public function getMovieDownloads($movieId)
    {
        try {
            // For admin, show all downloads (active and inactive)
            // For public API, filter by is_active
            $downloads = MovieDownload::where('movie_id', $movieId)
                ->orderBy('priority', 'desc')
                ->orderBy('id')
                ->get(['id', 'quality', 'server_name', 'download_url', 'size', 'priority', 'is_active']);

            return response()->json([
                'success' => true,
                'data' => $downloads
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching downloads: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get downloads for an episode
     */
    public function getEpisodeDownloads($episodeId)
    {
        try {
            // For admin, show all downloads (active and inactive)
            // For public API, filter by is_active
            $downloads = EpisodeDownload::where('episode_id', $episodeId)
                ->orderBy('priority', 'desc')
                ->orderBy('id')
                ->get(['id', 'quality', 'server_name', 'download_url', 'size', 'priority', 'is_active']);

            return response()->json([
                'success' => true,
                'data' => $downloads
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching downloads: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Create episode download
     */
    public function createEpisodeDownload(Request $request, $episodeId)
    {
        try {
            $request->validate([
                'server_name' => 'required|string|max:255',
                'download_url' => 'required|string|url',
                'quality' => 'nullable|string|max:50',
                'size' => 'nullable|string|max:50',
                'priority' => 'nullable|integer|min:0|max:999',
                'is_active' => 'nullable|boolean',
            ]);

            $download = EpisodeDownload::create([
                'episode_id' => $episodeId,
                'server_name' => $request->server_name,
                'download_url' => $request->download_url,
                'quality' => $request->quality ?: null,
                'size' => $request->size ?: null,
                'priority' => $request->priority ?? 0,
                'is_active' => $request->is_active ?? true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Download created successfully',
                'data' => $download
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating download: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update episode download
     */
    public function updateEpisodeDownload(Request $request, $episodeId, $downloadId)
    {
        try {
            $download = EpisodeDownload::where('episode_id', $episodeId)->findOrFail($downloadId);

            $request->validate([
                'server_name' => 'sometimes|string|max:255',
                'download_url' => 'sometimes|string|url',
                'quality' => 'nullable|string|max:50',
                'size' => 'nullable|string|max:50',
                'priority' => 'nullable|integer|min:0|max:999',
                'is_active' => 'nullable|boolean',
            ]);

            $updateData = [];
            if ($request->has('server_name')) {
                $updateData['server_name'] = $request->server_name;
            }
            if ($request->has('download_url')) {
                $updateData['download_url'] = $request->download_url;
            }
            if ($request->has('quality')) {
                $updateData['quality'] = $request->quality ?: null;
            }
            if ($request->has('size')) {
                $updateData['size'] = $request->size ?: null;
            }
            if ($request->has('priority')) {
                $updateData['priority'] = $request->priority;
            }
            if ($request->has('is_active')) {
                $updateData['is_active'] = $request->is_active;
            }

            $download->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Download updated successfully',
                'data' => $download->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating download: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete episode download
     */
    public function deleteEpisodeDownload($episodeId, $downloadId)
    {
        try {
            $download = EpisodeDownload::where('episode_id', $episodeId)->findOrFail($downloadId);
            $download->delete();

            return response()->json([
                'success' => true,
                'message' => 'Download deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting download: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create movie download
     */
    public function createMovieDownload(Request $request, $movieId)
    {
        try {
            $request->validate([
                'server_name' => 'required|string|max:255',
                'download_url' => 'required|string|url',
                'quality' => 'nullable|string|max:50',
                'size' => 'nullable|string|max:50',
                'priority' => 'nullable|integer|min:0|max:999',
                'is_active' => 'nullable|boolean',
            ]);

            $download = MovieDownload::create([
                'movie_id' => $movieId,
                'server_name' => $request->server_name,
                'download_url' => $request->download_url,
                'quality' => $request->quality ?: null,
                'size' => $request->size ?: null,
                'priority' => $request->priority ?? 0,
                'is_active' => $request->is_active ?? true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Download created successfully',
                'data' => $download
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating download: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update movie download
     */
    public function updateMovieDownload(Request $request, $movieId, $downloadId)
    {
        try {
            $download = MovieDownload::where('movie_id', $movieId)->findOrFail($downloadId);

            $request->validate([
                'server_name' => 'sometimes|string|max:255',
                'download_url' => 'sometimes|string|url',
                'quality' => 'nullable|string|max:50',
                'size' => 'nullable|string|max:50',
                'priority' => 'nullable|integer|min:0|max:999',
                'is_active' => 'nullable|boolean',
            ]);

            $updateData = [];
            if ($request->has('server_name')) {
                $updateData['server_name'] = $request->server_name;
            }
            if ($request->has('download_url')) {
                $updateData['download_url'] = $request->download_url;
            }
            if ($request->has('quality')) {
                $updateData['quality'] = $request->quality;
            }
            if ($request->has('size')) {
                $updateData['size'] = $request->size;
            }
            if ($request->has('priority')) {
                $updateData['priority'] = $request->priority;
            }
            if ($request->has('is_active')) {
                $updateData['is_active'] = $request->is_active;
            }

            $download->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Download updated successfully',
                'data' => $download->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating download: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete movie download
     */
    public function deleteMovieDownload($movieId, $downloadId)
    {
        try {
            $download = MovieDownload::where('movie_id', $movieId)->findOrFail($downloadId);
            $download->delete();

            return response()->json([
                'success' => true,
                'message' => 'Download deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting download: ' . $e->getMessage()
            ], 500);
        }
    }
}

