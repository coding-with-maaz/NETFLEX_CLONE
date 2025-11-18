<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TVShow;
use App\Models\Season;
use App\Models\Episode;
use App\Models\EpisodeEmbed;
use App\Models\EpisodeDownload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminTVShowController extends Controller
{
    /**
     * Resolve TV show by ID, slug, name, or TMDB ID
     */
    private function resolveTVShow($identifier)
    {
        if (is_numeric($identifier)) {
            return TVShow::where(function($q) use ($identifier) {
                $q->where('id', $identifier)
                  ->orWhere('tmdb_id', $identifier);
            })->first();
        } else {
            return TVShow::where(function($q) use ($identifier) {
                $q->where('slug', $identifier)
                  ->orWhereRaw('LOWER(name) = ?', [strtolower($identifier)])
                  ->orWhere('name', 'LIKE', "%{$identifier}%");
            })->first();
        }
    }

    /**
     * Get TV shows list with filters and pagination
     */
    public function index(Request $request)
    {
        try {
            $query = TVShow::query();

            // Search
            if ($request->has('search') && $request->search) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('slug', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('overview', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Filter by status
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Filter by featured
            if ($request->has('featured') && $request->featured !== '') {
                $query->where('is_featured', (bool) $request->featured);
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $order = $request->get('order', 'desc');

            switch ($sortBy) {
                case 'name':
                case 'title':
                    $query->orderBy('name', $order);
                    break;
                case 'first_air_date':
                    $query->orderBy('first_air_date', $order);
                    break;
                case 'vote_average':
                case 'rating':
                    $query->orderBy('vote_average', $order);
                    break;
                case 'view_count':
                    $query->orderBy('view_count', $order);
                    break;
                case 'created_at':
                default:
                    $query->orderBy('created_at', $order);
                    break;
            }

            // Pagination
            $page = max(1, (int) $request->get('page', 1));
            $limit = min(100, max(1, (int) $request->get('limit', 20)));
            $offset = ($page - 1) * $limit;

            // Get total count
            $total = $query->count();

            // Get results
            $tvShows = $query->with(['genres', 'category'])
                           ->offset($offset)
                           ->limit($limit)
                           ->get();

            $totalPages = (int) ceil($total / $limit);

            // Format TV shows
            $formattedTVShows = $tvShows->map(function($tvShow) {
                $posterPath = $tvShow->poster_path;
                if ($posterPath && !str_starts_with($posterPath, 'http')) {
                    $posterPath = str_starts_with($posterPath, '/') ? $posterPath : '/' . $posterPath;
                }
                
                return [
                    'id' => $tvShow->id,
                    'name' => $tvShow->name,
                    'title' => $tvShow->name, // Alias for compatibility
                    'slug' => $tvShow->slug,
                    'poster_path' => $posterPath ?: '/images/placeholder.svg',
                    'first_air_date' => $tvShow->first_air_date?->format('Y-m-d'),
                    'vote_average' => (float) $tvShow->vote_average,
                    'view_count' => $tvShow->view_count ?? 0,
                    'status' => $tvShow->status ?? 'pending',
                    'is_featured' => (bool) $tvShow->is_featured,
                    'created_at' => $tvShow->created_at?->toISOString(),
                ];
            });

            // Get stats
            $stats = [
                'total' => TVShow::count(),
                'active' => TVShow::where('status', 'active')->count(),
                'inactive' => TVShow::where('status', '!=', 'active')->count(),
                'featured' => TVShow::where('is_featured', true)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'tvShows' => $formattedTVShows,
                    'tvshows' => $formattedTVShows, // Alias for compatibility
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $limit,
                        'total' => $total,
                        'total_pages' => $totalPages,
                        'has_next' => $page < $totalPages,
                        'has_prev' => $page > 1,
                    ],
                    'stats' => $stats,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching TV shows: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get TV show stats
     */
    public function stats()
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'total' => TVShow::count(),
                    'active' => TVShow::where('status', 'active')->count(),
                    'inactive' => TVShow::where('status', '!=', 'active')->count(),
                    'pending' => TVShow::where('status', 'pending')->count(),
                    'featured' => TVShow::where('is_featured', true)->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching TV show stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single TV show
     */
    public function show(Request $request, $id)
    {
        try {
            // Only load seasons.episodes if explicitly requested (for performance)
            $loadEpisodes = $request->boolean('load_episodes', false);
            
            // Support lookup by ID (numeric), slug, name, or TMDB ID
            $query = $loadEpisodes 
                ? TVShow::with(['genres', 'category', 'dubbingLanguage', 'seasons.episodes'])
                : TVShow::with(['genres', 'category', 'dubbingLanguage', 'seasons']);
            
            // Check if $id is numeric - if so, try primary key or tmdb_id first
            if (is_numeric($id)) {
                $tvShow = $query->where(function($q) use ($id) {
                    $q->where('id', $id)
                      ->orWhere('tmdb_id', $id);
                })->first();
            } else {
                // Non-numeric: search by slug or name (case-insensitive)
                $tvShow = $query->where(function($q) use ($id) {
                    $q->where('slug', $id)
                      ->orWhereRaw('LOWER(name) = ?', [strtolower($id)])
                      ->orWhere('name', 'LIKE', "%{$id}%");
                })->first();
            }
            
            if (!$tvShow) {
                return response()->json([
                    'success' => false,
                    'message' => 'TV show not found'
                ], 404);
            }

            $posterPath = $tvShow->poster_path;
            if ($posterPath && !str_starts_with($posterPath, 'http')) {
                $posterPath = str_starts_with($posterPath, '/') ? $posterPath : '/' . $posterPath;
            }

            $backdropPath = $tvShow->backdrop_path;
            if ($backdropPath && !str_starts_with($backdropPath, 'http')) {
                $backdropPath = str_starts_with($backdropPath, '/') ? $backdropPath : '/' . $backdropPath;
            }

            $formattedTVShow = [
                'id' => $tvShow->id,
                'name' => $tvShow->name,
                'title' => $tvShow->name, // Alias for compatibility
                'slug' => $tvShow->slug,
                'overview' => $tvShow->overview,
                'poster_path' => $posterPath ?: '/images/placeholder.svg',
                'backdrop_path' => $backdropPath,
                'first_air_date' => $tvShow->first_air_date?->format('Y-m-d'),
                'vote_average' => (float) $tvShow->vote_average,
                'vote_count' => $tvShow->vote_count,
                'view_count' => $tvShow->view_count ?? 0,
                'popularity' => (float) $tvShow->popularity,
                'status' => $tvShow->status ?? 'pending',
                'is_featured' => (bool) $tvShow->is_featured,
                'original_language' => $tvShow->original_language,
                'dubbing_language' => $tvShow->dubbingLanguage ? [
                    'id' => $tvShow->dubbingLanguage->id,
                    'name' => $tvShow->dubbingLanguage->name,
                    'code' => $tvShow->dubbingLanguage->code,
                ] : null,
                'genres' => $tvShow->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                    ];
                }),
                'category' => $tvShow->category ? [
                    'id' => $tvShow->category->id,
                    'name' => $tvShow->category->name,
                    'slug' => $tvShow->category->slug,
                ] : null,
                'tmdb_id' => $tvShow->tmdb_id,
                'seasons' => $tvShow->seasons->map(function($season) use ($loadEpisodes) {
                    $episodesData = [];
                    if ($loadEpisodes && isset($season->episodes)) {
                        $episodesData = $season->episodes->map(function($episode) {
                            return [
                                'id' => $episode->id,
                                'episode_number' => $episode->episode_number,
                                'name' => $episode->name,
                                'overview' => $episode->overview,
                                'still_path' => $episode->still_path,
                                'air_date' => $episode->air_date?->format('Y-m-d'),
                                'runtime' => $episode->runtime,
                                'vote_average' => (float) $episode->vote_average,
                                'vote_count' => $episode->vote_count,
                                'view_count' => $episode->view_count ?? 0,
                            ];
                        })->values();
                    }
                    
                    return [
                        'id' => $season->id,
                        'season_number' => $season->season_number,
                        'name' => $season->name,
                        'overview' => $season->overview,
                        'poster_path' => $season->poster_path,
                        'air_date' => $season->air_date?->format('Y-m-d'),
                        'episode_count' => $season->episode_count,
                        'episodes' => $episodesData,
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'tvShow' => $formattedTVShow,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching TV show: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new TV show with seasons and episodes
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:tv_shows,slug',
                'overview' => 'nullable|string',
                'first_air_date' => 'nullable|date',
                'status' => 'sometimes|string|in:active,inactive,pending',
                'is_featured' => 'nullable|boolean',
                'original_language' => 'nullable|string|max:5',
                'dubbing_language_id' => 'nullable|integer|exists:languages,id',
                'category_id' => 'nullable|integer|exists:categories,id',
                'tmdb_id' => 'nullable|integer|unique:tv_shows,tmdb_id',
                'imdb_id' => 'nullable|string|max:20|unique:tv_shows,imdb_id',
                'poster_path' => 'nullable|string',
                'backdrop_path' => 'nullable|string',
                'vote_average' => 'nullable|numeric|min:0|max:10',
                'vote_count' => 'nullable|integer|min:0',
                'popularity' => 'nullable|numeric|min:0',
                'genres' => 'nullable|array',
                'genres.*' => 'integer|exists:genres,id',
                'seasons' => 'nullable|array',
                'seasons.*.season_number' => 'required|integer|min:0',
                'seasons.*.episodes' => 'nullable|array',
                'seasons.*.episodes.*.episode_number' => 'required|integer|min:1',
                'seasons.*.episodes.*.embeds' => 'nullable|array',
                'seasons.*.episodes.*.downloads' => 'nullable|array',
            ]);

            DB::beginTransaction();

            try {
                // Calculate number of seasons and episodes
                $numberOfSeasons = $request->has('seasons') && is_array($request->seasons) ? count($request->seasons) : 0;
                $numberOfEpisodes = 0;
                if ($request->has('seasons') && is_array($request->seasons)) {
                    foreach ($request->seasons as $season) {
                        if (isset($season['episodes']) && is_array($season['episodes'])) {
                            $numberOfEpisodes += count($season['episodes']);
                        }
                    }
                }

                $tvShowData = [
                    'name' => $request->name,
                    'slug' => $request->slug ?: Str::slug($request->name),
                    'overview' => $request->overview,
                    'first_air_date' => $request->first_air_date ?: null,
                    'status' => $request->status ?? 'pending',
                    'is_featured' => (bool) ($request->is_featured ?? false),
                    'original_language' => $request->original_language ?: null,
                    'dubbing_language_id' => $request->dubbing_language_id ?: null,
                    'category_id' => $request->category_id ?: null,
                    'tmdb_id' => $request->tmdb_id ?: null,
                    'imdb_id' => $request->imdb_id ?: null,
                    'poster_path' => $request->poster_path ?: null,
                    'backdrop_path' => $request->backdrop_path ?: null,
                    'vote_average' => $request->vote_average ?? 0,
                    'vote_count' => $request->vote_count ?? 0,
                    'popularity' => $request->popularity ?? 0,
                    'number_of_seasons' => $numberOfSeasons,
                    'number_of_episodes' => $numberOfEpisodes,
                ];

                $tvShow = TVShow::create($tvShowData);

                // Sync genres if provided
                if ($request->has('genres') && is_array($request->genres)) {
                    $tvShow->genres()->sync($request->genres);
                }

                // Create seasons and episodes if provided
                if ($request->has('seasons') && is_array($request->seasons)) {
                    foreach ($request->seasons as $seasonData) {
                        $season = Season::create([
                            'tv_show_id' => $tvShow->id,
                            'season_number' => $seasonData['season_number'],
                            'name' => $seasonData['name'] ?? "Season {$seasonData['season_number']}",
                            'overview' => $seasonData['overview'] ?? null,
                            'poster_path' => $seasonData['poster_path'] ?? null,
                            'air_date' => $seasonData['air_date'] ?? null,
                            'episode_count' => isset($seasonData['episodes']) && is_array($seasonData['episodes']) ? count($seasonData['episodes']) : 0,
                        ]);

                        // Create episodes for this season
                        if (isset($seasonData['episodes']) && is_array($seasonData['episodes'])) {
                            foreach ($seasonData['episodes'] as $episodeData) {
                                $episode = Episode::create([
                                    'season_id' => $season->id,
                                    'episode_number' => $episodeData['episode_number'],
                                    'name' => $episodeData['name'] ?? null,
                                    'overview' => $episodeData['overview'] ?? null,
                                    'still_path' => $episodeData['still_path'] ?? null,
                                    'air_date' => $episodeData['air_date'] ?? null,
                                    'runtime' => $episodeData['runtime'] ?? null,
                                    'vote_average' => $episodeData['vote_average'] ?? 0,
                                    'vote_count' => $episodeData['vote_count'] ?? 0,
                                ]);

                                // Create embeds for this episode
                                if (isset($episodeData['embeds']) && is_array($episodeData['embeds'])) {
                                    foreach ($episodeData['embeds'] as $embedData) {
                                        EpisodeEmbed::create([
                                            'episode_id' => $episode->id,
                                            'server_name' => $embedData['server_name'] ?? 'Unknown',
                                            'embed_url' => $embedData['embed_url'],
                                            'priority' => $embedData['priority'] ?? 0,
                                            'is_active' => $embedData['is_active'] ?? true,
                                        ]);
                                    }
                                }

                                // Create downloads for this episode
                                if (isset($episodeData['downloads']) && is_array($episodeData['downloads'])) {
                                    foreach ($episodeData['downloads'] as $downloadData) {
                                        EpisodeDownload::create([
                                            'episode_id' => $episode->id,
                                            'server_name' => $downloadData['server_name'] ?? 'Unknown',
                                            'download_url' => $downloadData['download_url'],
                                            'quality' => $downloadData['quality'] ?? null,
                                            'size' => $downloadData['size'] ?? null,
                                            'priority' => $downloadData['priority'] ?? 0,
                                            'is_active' => $downloadData['is_active'] ?? true,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }

                DB::commit();

                // Reload with relationships
                $tvShow->refresh();
                $tvShow->load(['genres', 'category', 'dubbingLanguage']);

                $posterPath = $tvShow->poster_path;
                if ($posterPath && !str_starts_with($posterPath, 'http')) {
                    $posterPath = str_starts_with($posterPath, '/') ? $posterPath : '/' . $posterPath;
                }

                $formattedTVShow = [
                    'id' => $tvShow->id,
                    'name' => $tvShow->name,
                    'slug' => $tvShow->slug,
                    'overview' => $tvShow->overview,
                    'poster_path' => $posterPath ?: '/images/placeholder.svg',
                    'first_air_date' => $tvShow->first_air_date?->format('Y-m-d'),
                    'vote_average' => (float) $tvShow->vote_average,
                    'view_count' => $tvShow->view_count ?? 0,
                    'status' => $tvShow->status ?? 'pending',
                    'is_featured' => (bool) $tvShow->is_featured,
                    'original_language' => $tvShow->original_language,
                    'dubbing_language' => $tvShow->dubbingLanguage ? [
                        'id' => $tvShow->dubbingLanguage->id,
                        'name' => $tvShow->dubbingLanguage->name,
                        'code' => $tvShow->dubbingLanguage->code,
                    ] : null,
                    'tmdb_id' => $tvShow->tmdb_id,
                    'genres' => $tvShow->genres->map(function($genre) {
                        return [
                            'id' => $genre->id,
                            'name' => $genre->name,
                            'slug' => $genre->slug,
                        ];
                    }),
                    'category' => $tvShow->category ? [
                        'id' => $tvShow->category->id,
                        'name' => $tvShow->category->name,
                        'slug' => $tvShow->category->slug,
                    ] : null,
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'TV show created successfully',
                    'data' => [
                        'tvShow' => $formattedTVShow
                    ]
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating TV show: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update TV show
     */
    public function update(Request $request, $id)
    {
        try {
            $tvShow = $this->resolveTVShow($id);
            
            if (!$tvShow) {
                return response()->json([
                    'success' => false,
                    'message' => 'TV show not found'
                ], 404);
            }

            $request->validate([
                'name' => 'sometimes|string|max:255',
                'slug' => 'sometimes|string|max:255|unique:tv_shows,slug,' . $tvShow->id,
                'overview' => 'nullable|string',
                'first_air_date' => 'nullable|date',
                'status' => 'sometimes|string|in:active,inactive,pending',
                'is_featured' => 'nullable|boolean',
                'original_language' => 'nullable|string|max:5',
                'dubbing_language_id' => 'nullable|integer|exists:languages,id',
                'category_id' => 'nullable|integer|exists:categories,id',
                'tmdb_id' => 'nullable|integer|unique:tv_shows,tmdb_id,' . $tvShow->id,
                'imdb_id' => 'nullable|string|max:20|unique:tv_shows,imdb_id,' . $tvShow->id,
                'genres' => 'nullable|array',
                'genres.*' => 'integer|exists:genres,id',
            ]);

            $updateData = [];
            
            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }
            if ($request->has('slug')) {
                $updateData['slug'] = $request->slug;
            }
            if ($request->has('overview')) {
                $updateData['overview'] = $request->overview;
            }
            if ($request->has('first_air_date')) {
                $updateData['first_air_date'] = $request->first_air_date ?: null;
            }
            if ($request->has('status')) {
                $updateData['status'] = $request->status;
            }
            if ($request->has('is_featured')) {
                $updateData['is_featured'] = (bool) $request->is_featured;
            }
            if ($request->has('original_language')) {
                $updateData['original_language'] = $request->original_language ?: null;
            }
            if ($request->has('dubbing_language_id')) {
                $updateData['dubbing_language_id'] = $request->dubbing_language_id ?: null;
            }
            if ($request->has('category_id')) {
                $updateData['category_id'] = $request->category_id ?: null;
            }
            if ($request->has('tmdb_id')) {
                $updateData['tmdb_id'] = $request->tmdb_id ?: null;
            }
            if ($request->has('imdb_id')) {
                $updateData['imdb_id'] = $request->imdb_id ?: null;
            }

            $tvShow->update($updateData);

            // Sync genres if provided
            if ($request->has('genres')) {
                $tvShow->genres()->sync($request->genres);
            }

            // Reload with relationships
            $tvShow->refresh();
            $tvShow->load(['genres', 'category', 'dubbingLanguage']);

            $posterPath = $tvShow->poster_path;
            if ($posterPath && !str_starts_with($posterPath, 'http')) {
                $posterPath = str_starts_with($posterPath, '/') ? $posterPath : '/' . $posterPath;
            }

            $formattedTVShow = [
                'id' => $tvShow->id,
                'name' => $tvShow->name,
                'title' => $tvShow->name,
                'slug' => $tvShow->slug,
                'overview' => $tvShow->overview,
                'poster_path' => $posterPath ?: '/images/placeholder.svg',
                'first_air_date' => $tvShow->first_air_date?->format('Y-m-d'),
                'vote_average' => (float) $tvShow->vote_average,
                'vote_count' => $tvShow->vote_count,
                'view_count' => $tvShow->view_count ?? 0,
                'popularity' => (float) $tvShow->popularity,
                'status' => $tvShow->status ?? 'pending',
                'is_featured' => (bool) $tvShow->is_featured,
                'original_language' => $tvShow->original_language,
                'dubbing_language' => $tvShow->dubbingLanguage ? [
                    'id' => $tvShow->dubbingLanguage->id,
                    'name' => $tvShow->dubbingLanguage->name,
                    'code' => $tvShow->dubbingLanguage->code,
                ] : null,
                'genres' => $tvShow->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                    ];
                }),
                'category' => $tvShow->category ? [
                    'id' => $tvShow->category->id,
                    'name' => $tvShow->category->name,
                    'slug' => $tvShow->category->slug,
                ] : null,
                'tmdb_id' => $tvShow->tmdb_id,
            ];

            return response()->json([
                'success' => true,
                'message' => 'TV show updated successfully',
                'data' => [
                    'tvShow' => $formattedTVShow
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating TV show: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add season(s) and episodes to existing TV show
     */
    public function addSeasons(Request $request, $id)
    {
        try {
            $tvShow = $this->resolveTVShow($id);
            
            if (!$tvShow) {
                return response()->json([
                    'success' => false,
                    'message' => 'TV show not found'
                ], 404);
            }

            $request->validate([
                'seasons' => 'required|array',
                'seasons.*.season_number' => 'required|integer|min:0',
                'seasons.*.name' => 'nullable|string|max:255',
                'seasons.*.overview' => 'nullable|string',
                'seasons.*.poster_path' => 'nullable|string',
                'seasons.*.air_date' => 'nullable|date',
                'seasons.*.episodes' => 'required|array',
                'seasons.*.episodes.*.episode_number' => 'required|integer|min:1',
                'seasons.*.episodes.*.name' => 'nullable|string|max:255',
                'seasons.*.episodes.*.overview' => 'nullable|string',
                'seasons.*.episodes.*.still_path' => 'nullable|string',
                'seasons.*.episodes.*.air_date' => 'nullable|date',
                'seasons.*.episodes.*.runtime' => 'nullable|integer',
                'seasons.*.episodes.*.vote_average' => 'nullable|numeric|min:0|max:10',
                'seasons.*.episodes.*.vote_count' => 'nullable|integer|min:0',
                'seasons.*.episodes.*.embeds' => 'nullable|array',
                'seasons.*.episodes.*.downloads' => 'nullable|array',
            ]);

            DB::beginTransaction();

            try {
                $numberOfSeasons = count($request->seasons);
                $numberOfEpisodes = 0;

                foreach ($request->seasons as $seasonData) {
                    // Check if season already exists
                    $season = Season::firstOrCreate(
                        [
                            'tv_show_id' => $tvShow->id,
                            'season_number' => $seasonData['season_number'],
                        ],
                        [
                            'name' => $seasonData['name'] ?? "Season {$seasonData['season_number']}",
                            'overview' => $seasonData['overview'] ?? null,
                            'poster_path' => $seasonData['poster_path'] ?? null,
                            'air_date' => $seasonData['air_date'] ?? null,
                            'episode_count' => isset($seasonData['episodes']) && is_array($seasonData['episodes']) ? count($seasonData['episodes']) : 0,
                        ]
                    );

                    // Update season if it already exists
                    if (!$season->wasRecentlyCreated) {
                        $season->update([
                            'name' => $seasonData['name'] ?? $season->name ?? "Season {$seasonData['season_number']}",
                            'overview' => $seasonData['overview'] ?? $season->overview,
                            'poster_path' => $seasonData['poster_path'] ?? $season->poster_path,
                            'air_date' => $seasonData['air_date'] ?? $season->air_date,
                            'episode_count' => isset($seasonData['episodes']) && is_array($seasonData['episodes']) ? count($seasonData['episodes']) : $season->episode_count,
                        ]);
                    }

                    // Create episodes for this season
                    if (isset($seasonData['episodes']) && is_array($seasonData['episodes'])) {
                        foreach ($seasonData['episodes'] as $episodeData) {
                            // Check if episode already exists
                            $episode = Episode::firstOrCreate(
                                [
                                    'season_id' => $season->id,
                                    'episode_number' => $episodeData['episode_number'],
                                ],
                                [
                                    'name' => $episodeData['name'] ?? "Episode {$episodeData['episode_number']}",
                                    'overview' => $episodeData['overview'] ?? null,
                                    'still_path' => $episodeData['still_path'] ?? null,
                                    'air_date' => $episodeData['air_date'] ?? null,
                                    'runtime' => $episodeData['runtime'] ?? null,
                                    'vote_average' => $episodeData['vote_average'] ?? 0,
                                    'vote_count' => $episodeData['vote_count'] ?? 0,
                                ]
                            );

                            // Update episode if it already exists
                            if (!$episode->wasRecentlyCreated) {
                                $episode->update([
                                    'name' => $episodeData['name'] ?? $episode->name,
                                    'overview' => $episodeData['overview'] ?? $episode->overview,
                                    'still_path' => $episodeData['still_path'] ?? $episode->still_path,
                                    'air_date' => $episodeData['air_date'] ?? $episode->air_date,
                                    'runtime' => $episodeData['runtime'] ?? $episode->runtime,
                                    'vote_average' => $episodeData['vote_average'] ?? $episode->vote_average,
                                    'vote_count' => $episodeData['vote_count'] ?? $episode->vote_count,
                                ]);
                            }

                            $numberOfEpisodes++;

                            // Create embeds if provided
                            if (isset($episodeData['embeds']) && is_array($episodeData['embeds'])) {
                                foreach ($episodeData['embeds'] as $embedData) {
                                    EpisodeEmbed::updateOrCreate(
                                        [
                                            'episode_id' => $episode->id,
                                            'server_name' => $embedData['server_name'] ?? 'Server 1',
                                        ],
                                        [
                                            'embed_url' => $embedData['embed_url'] ?? '',
                                            'priority' => $embedData['priority'] ?? 1,
                                            'is_active' => $embedData['is_active'] ?? true,
                                            'language_id' => $embedData['language_id'] ?? null,
                                        ]
                                    );
                                }
                            }

                            // Create downloads if provided
                            if (isset($episodeData['downloads']) && is_array($episodeData['downloads'])) {
                                foreach ($episodeData['downloads'] as $downloadData) {
                                    EpisodeDownload::updateOrCreate(
                                        [
                                            'episode_id' => $episode->id,
                                            'server_name' => $downloadData['server_name'] ?? 'Server 1',
                                        ],
                                        [
                                            'download_url' => $downloadData['download_url'] ?? '',
                                            'quality' => $downloadData['quality'] ?? null,
                                            'size' => $downloadData['size'] ?? null,
                                            'priority' => $downloadData['priority'] ?? 1,
                                            'is_active' => $downloadData['is_active'] ?? true,
                                        ]
                                    );
                                }
                            }
                        }
                    }

                    // Update season episode count
                    $actualEpisodeCount = Episode::where('season_id', $season->id)->count();
                    $season->update(['episode_count' => $actualEpisodeCount]);
                }

                // Update TV show counts
                $actualSeasonCount = Season::where('tv_show_id', $tvShow->id)->count();
                $actualEpisodeCount = Episode::whereHas('season', function($q) use ($tvShow) {
                    $q->where('tv_show_id', $tvShow->id);
                })->count();

                $tvShow->update([
                    'number_of_seasons' => $actualSeasonCount,
                    'number_of_episodes' => $actualEpisodeCount,
                ]);

                DB::commit();

                // Reload TV show with relationships
                $tvShow->load(['genres', 'category', 'dubbingLanguage', 'seasons.episodes']);

                $formattedTVShow = [
                    'id' => $tvShow->id,
                    'name' => $tvShow->name,
                    'slug' => $tvShow->slug,
                    'tmdb_id' => $tvShow->tmdb_id,
                    'seasons' => $tvShow->seasons->map(function($season) {
                        return [
                            'id' => $season->id,
                            'season_number' => $season->season_number,
                            'name' => $season->name,
                            'overview' => $season->overview,
                            'poster_path' => $season->poster_path,
                            'air_date' => $season->air_date?->format('Y-m-d'),
                            'episode_count' => $season->episode_count,
                            'episodes' => $season->episodes->map(function($episode) {
                                return [
                                    'id' => $episode->id,
                                    'episode_number' => $episode->episode_number,
                                    'name' => $episode->name,
                                    'overview' => $episode->overview,
                                    'still_path' => $episode->still_path,
                                    'air_date' => $episode->air_date?->format('Y-m-d'),
                                    'runtime' => $episode->runtime,
                                    'vote_average' => (float) $episode->vote_average,
                                    'vote_count' => $episode->vote_count,
                                    'view_count' => $episode->view_count ?? 0,
                                ];
                            }),
                        ];
                    }),
                ];

                return response()->json([
                    'success' => true,
                    'message' => "Added {$numberOfSeasons} season(s) with {$numberOfEpisodes} episode(s)",
                    'data' => [
                        'tvShow' => $formattedTVShow,
                    ]
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding seasons: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete TV show
     */
    public function destroy($id)
    {
        try {
            $tvShow = $this->resolveTVShow($id);
            
            if (!$tvShow) {
                return response()->json([
                    'success' => false,
                    'message' => 'TV show not found'
                ], 404);
            }

            DB::beginTransaction();

            try {
                // Delete all related data
                // Episodes are automatically deleted via cascade, but let's be explicit
                $seasons = $tvShow->seasons;
                foreach ($seasons as $season) {
                    // Delete episode embeds
                    foreach ($season->episodes as $episode) {
                        EpisodeEmbed::where('episode_id', $episode->id)->delete();
                        EpisodeDownload::where('episode_id', $episode->id)->delete();
                    }
                    // Delete episodes
                    Episode::where('season_id', $season->id)->delete();
                }
                // Delete seasons
                Season::where('tv_show_id', $tvShow->id)->delete();
                
                // Delete views
                \App\Models\View::where('viewable_type', TVShow::class)
                    ->where('viewable_id', $tvShow->id)
                    ->delete();

                // Detach genres (many-to-many relationship)
                $tvShow->genres()->detach();

                // Delete the TV show
                $tvShow->delete();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'TV show deleted successfully'
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'TV show not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting TV show: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new season for a TV show
     */
    public function createSeason(Request $request, $tvshowId)
    {
        try {
            $tvShow = $this->resolveTVShow($tvshowId);
            
            if (!$tvShow) {
                return response()->json([
                    'success' => false,
                    'message' => 'TV show not found'
                ], 404);
            }

            $request->validate([
                'season_number' => 'required|integer|min:0',
                'name' => 'nullable|string|max:255',
                'overview' => 'nullable|string',
                'poster_path' => 'nullable|string',
                'air_date' => 'nullable|date',
            ]);

            // Check if season with this number already exists
            $existingSeason = Season::where('tv_show_id', $tvShow->id)
                ->where('season_number', $request->season_number)
                ->first();

            if ($existingSeason) {
                return response()->json([
                    'success' => false,
                    'message' => 'A season with this number already exists for this TV show.'
                ], 422);
            }

            $season = Season::create([
                'tv_show_id' => $tvShow->id,
                'season_number' => $request->season_number,
                'name' => $request->name ?? "Season {$request->season_number}",
                'overview' => $request->overview,
                'poster_path' => $request->poster_path,
                'air_date' => $request->air_date,
                'episode_count' => 0,
            ]);

            // Update TV show season count
            $actualSeasonCount = Season::where('tv_show_id', $tvShow->id)->count();
            $tvShow->update(['number_of_seasons' => $actualSeasonCount]);

            return response()->json([
                'success' => true,
                'message' => 'Season created successfully',
                'data' => [
                    'season' => [
                        'id' => $season->id,
                        'season_number' => $season->season_number,
                        'name' => $season->name,
                        'overview' => $season->overview,
                        'poster_path' => $season->poster_path,
                        'air_date' => $season->air_date?->format('Y-m-d'),
                        'episode_count' => $season->episode_count,
                    ]
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating season: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a season
     */
    public function updateSeason(Request $request, $tvshowId, $seasonId)
    {
        try {
            $tvShow = $this->resolveTVShow($tvshowId);
            
            if (!$tvShow) {
                return response()->json([
                    'success' => false,
                    'message' => 'TV show not found'
                ], 404);
            }
            $season = Season::where('tv_show_id', $tvShow->id)
                ->where('id', $seasonId)
                ->firstOrFail();

            $request->validate([
                'season_number' => 'sometimes|integer|min:0',
                'name' => 'nullable|string|max:255',
                'overview' => 'nullable|string',
                'poster_path' => 'nullable|string',
                'air_date' => 'nullable|date',
            ]);

            // Check if season number is being changed and conflicts with existing season
            if ($request->has('season_number') && $request->season_number != $season->season_number) {
                $existingSeason = Season::where('tv_show_id', $tvShow->id)
                    ->where('season_number', $request->season_number)
                    ->where('id', '!=', $season->id)
                    ->first();

                if ($existingSeason) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A season with this number already exists for this TV show.'
                    ], 422);
                }
            }

            $season->update([
                'season_number' => $request->has('season_number') ? $request->season_number : $season->season_number,
                'name' => $request->has('name') ? $request->name : $season->name,
                'overview' => $request->has('overview') ? $request->overview : $season->overview,
                'poster_path' => $request->has('poster_path') ? $request->poster_path : $season->poster_path,
                'air_date' => $request->has('air_date') ? $request->air_date : $season->air_date,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Season updated successfully',
                'data' => [
                    'season' => [
                        'id' => $season->id,
                        'season_number' => $season->season_number,
                        'name' => $season->name,
                        'overview' => $season->overview,
                        'poster_path' => $season->poster_path,
                        'air_date' => $season->air_date?->format('Y-m-d'),
                        'episode_count' => $season->episode_count,
                    ]
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Season not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating season: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a season
     */
    public function deleteSeason($tvshowId, $seasonId)
    {
        try {
            $tvShow = $this->resolveTVShow($tvshowId);
            
            if (!$tvShow) {
                return response()->json([
                    'success' => false,
                    'message' => 'TV show not found'
                ], 404);
            }
            $season = Season::where('tv_show_id', $tvShow->id)
                ->where('id', $seasonId)
                ->firstOrFail();

            DB::beginTransaction();

            try {
                // Delete all episode embeds and downloads
                foreach ($season->episodes as $episode) {
                    EpisodeEmbed::where('episode_id', $episode->id)->delete();
                    EpisodeDownload::where('episode_id', $episode->id)->delete();
                }

                // Delete all episodes
                Episode::where('season_id', $season->id)->delete();

                // Delete the season
                $season->delete();

                // Update TV show counts
                $actualSeasonCount = Season::where('tv_show_id', $tvShow->id)->count();
                $actualEpisodeCount = Episode::whereHas('season', function($q) use ($tvShow) {
                    $q->where('tv_show_id', $tvShow->id);
                })->count();

                $tvShow->update([
                    'number_of_seasons' => $actualSeasonCount,
                    'number_of_episodes' => $actualEpisodeCount,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Season deleted successfully'
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Season not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting season: ' . $e->getMessage()
            ], 500);
        }
    }
}

