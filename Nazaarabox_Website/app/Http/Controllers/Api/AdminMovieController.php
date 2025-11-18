<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Genre;
use App\Models\Category;
use App\Models\Language;
use App\Models\MovieEmbed;
use Illuminate\Http\Request;

class AdminMovieController extends Controller
{
    /**
     * Get movies list with filters and pagination
     */
    public function index(Request $request)
    {
        try {
            $query = Movie::query();

            // Search
            if ($request->has('search') && $request->search) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('title', 'LIKE', "%{$searchTerm}%")
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
                case 'title':
                    $query->orderBy('title', $order);
                    break;
                case 'release_date':
                    $query->orderBy('release_date', $order);
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
            $movies = $query->with(['genres', 'category'])
                           ->offset($offset)
                           ->limit($limit)
                           ->get();

            $totalPages = (int) ceil($total / $limit);

            // Format movies
            $formattedMovies = $movies->map(function($movie) {
                $posterPath = $movie->poster_path;
                if ($posterPath && !str_starts_with($posterPath, 'http')) {
                    $posterPath = str_starts_with($posterPath, '/') ? $posterPath : '/' . $posterPath;
                }
                
                return [
                    'id' => $movie->id,
                    'title' => $movie->title,
                    'slug' => $movie->slug,
                    'poster_path' => $posterPath ?: '/images/placeholder.svg',
                    'release_date' => $movie->release_date?->format('Y-m-d'),
                    'runtime' => $movie->runtime,
                    'vote_average' => (float) $movie->vote_average,
                    'view_count' => $movie->view_count ?? 0,
                    'status' => $movie->status ?? 'pending',
                    'is_featured' => (bool) $movie->is_featured,
                    'created_at' => $movie->created_at?->toISOString(),
                ];
            });

            // Get stats
            $stats = [
                'total' => Movie::count(),
                'active' => Movie::where('status', 'active')->count(),
                'inactive' => Movie::where('status', '!=', 'active')->count(),
                'featured' => Movie::where('is_featured', true)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'movies' => $formattedMovies,
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
                'message' => 'Error fetching movies: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get movie stats
     */
    public function stats()
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'total' => Movie::count(),
                    'active' => Movie::where('status', 'active')->count(),
                    'inactive' => Movie::where('status', '!=', 'active')->count(),
                    'pending' => Movie::where('status', 'pending')->count(),
                    'featured' => Movie::where('is_featured', true)->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single movie
     */
    public function show($id)
    {
        try {
            $movie = Movie::with(['genres', 'category', 'dubbingLanguage'])->findOrFail($id);
            
            // Format poster path
            $posterPath = $movie->poster_path;
            if ($posterPath && !str_starts_with($posterPath, 'http')) {
                $posterPath = str_starts_with($posterPath, '/') ? $posterPath : '/' . $posterPath;
            }
            
            // Format the movie data
            $formattedMovie = [
                'id' => $movie->id,
                'title' => $movie->title,
                'slug' => $movie->slug,
                'overview' => $movie->overview,
                'poster_path' => $posterPath ?: '/images/placeholder.svg',
                'backdrop_path' => $movie->backdrop_path,
                'release_date' => $movie->release_date?->format('Y-m-d'),
                'runtime' => $movie->runtime,
                'vote_average' => (float) $movie->vote_average,
                'vote_count' => $movie->vote_count,
                'view_count' => $movie->view_count ?? 0,
                'popularity' => (float) $movie->popularity,
                'status' => $movie->status ?? 'pending',
                'is_featured' => (bool) $movie->is_featured,
                'original_language' => $movie->original_language,
                'tmdb_id' => $movie->tmdb_id,
                'genres' => $movie->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                    ];
                }),
                'category' => $movie->category ? [
                    'id' => $movie->category->id,
                    'name' => $movie->category->name,
                    'slug' => $movie->category->slug,
                ] : null,
                'dubbing_language' => $movie->dubbingLanguage ? [
                    'id' => $movie->dubbingLanguage->id,
                    'name' => $movie->dubbingLanguage->name,
                    'code' => $movie->dubbingLanguage->code,
                ] : null,
                'created_at' => $movie->created_at?->toISOString(),
                'updated_at' => $movie->updated_at?->toISOString(),
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'movie' => $formattedMovie
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Movie not found: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update movie
     */
    public function update(Request $request, $id)
    {
        try {
            $movie = Movie::findOrFail($id);

            $request->validate([
                'title' => 'sometimes|string|max:255',
                'slug' => 'sometimes|string|max:255|unique:movies,slug,' . $id,
                'overview' => 'nullable|string',
                'release_date' => 'nullable|date',
                'runtime' => 'nullable|integer|min:0',
                'status' => 'sometimes|string|in:active,inactive,pending',
                'is_featured' => 'nullable|boolean',
                'original_language' => 'nullable|string|max:5',
                'dubbing_language_id' => 'nullable|integer|exists:languages,id',
                'category_id' => 'nullable|integer|exists:categories,id',
                'genres' => 'nullable|array',
                'genres.*' => 'integer|exists:genres,id',
            ]);

            $updateData = [];
            
            if ($request->has('title')) {
                $updateData['title'] = $request->title;
            }
            if ($request->has('slug')) {
                $updateData['slug'] = $request->slug;
            }
            if ($request->has('overview')) {
                $updateData['overview'] = $request->overview;
            }
            if ($request->has('release_date')) {
                $updateData['release_date'] = $request->release_date ?: null;
            }
            if ($request->has('runtime')) {
                $updateData['runtime'] = $request->runtime ?: null;
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

            $movie->update($updateData);

            // Sync genres if provided
            if ($request->has('genres')) {
                $movie->genres()->sync($request->genres);
            }

            // Reload with relationships
            $movie->refresh();
            $movie->load(['genres', 'category', 'dubbingLanguage']);

            // Format the response
            $posterPath = $movie->poster_path;
            if ($posterPath && !str_starts_with($posterPath, 'http')) {
                $posterPath = str_starts_with($posterPath, '/') ? $posterPath : '/' . $posterPath;
            }

            $formattedMovie = [
                'id' => $movie->id,
                'title' => $movie->title,
                'slug' => $movie->slug,
                'overview' => $movie->overview,
                'poster_path' => $posterPath ?: '/images/placeholder.svg',
                'backdrop_path' => $movie->backdrop_path,
                'release_date' => $movie->release_date?->format('Y-m-d'),
                'runtime' => $movie->runtime,
                'vote_average' => (float) $movie->vote_average,
                'vote_count' => $movie->vote_count,
                'view_count' => $movie->view_count ?? 0,
                'popularity' => (float) $movie->popularity,
                'status' => $movie->status ?? 'pending',
                'is_featured' => (bool) $movie->is_featured,
                'original_language' => $movie->original_language,
                'tmdb_id' => $movie->tmdb_id,
                'genres' => $movie->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                    ];
                }),
                'category' => $movie->category ? [
                    'id' => $movie->category->id,
                    'name' => $movie->category->name,
                    'slug' => $movie->category->slug,
                ] : null,
                'dubbing_language' => $movie->dubbingLanguage ? [
                    'id' => $movie->dubbingLanguage->id,
                    'name' => $movie->dubbingLanguage->name,
                    'code' => $movie->dubbingLanguage->code,
                ] : null,
                'created_at' => $movie->created_at?->toISOString(),
                'updated_at' => $movie->updated_at?->toISOString(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Movie updated successfully',
                'data' => [
                    'movie' => $formattedMovie
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
                'message' => 'Error updating movie: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new movie
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:movies,slug',
                'overview' => 'nullable|string',
                'release_date' => 'nullable|date',
                'runtime' => 'nullable|integer|min:0',
                'status' => 'sometimes|string|in:active,inactive,pending',
                'is_featured' => 'nullable|boolean',
                'original_language' => 'nullable|string|max:5',
                'dubbing_language_id' => 'nullable|integer|exists:languages,id',
                'category_id' => 'nullable|integer|exists:categories,id',
                'tmdb_id' => 'nullable|integer|unique:movies,tmdb_id',
                'imdb_id' => 'nullable|string|max:255',
                'poster_path' => 'nullable|string',
                'backdrop_path' => 'nullable|string',
                'vote_average' => 'nullable|numeric|min:0|max:10',
                'vote_count' => 'nullable|integer|min:0',
                'popularity' => 'nullable|numeric|min:0',
                'genres' => 'nullable|array',
                'genres.*' => 'integer|exists:genres,id',
            ]);

            $movieData = [
                'title' => $request->title,
                'slug' => $request->slug ?: \Illuminate\Support\Str::slug($request->title),
                'overview' => $request->overview,
                'release_date' => $request->release_date ?: null,
                'runtime' => $request->runtime ?: null,
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
            ];

            $movie = Movie::create($movieData);

            // Sync genres if provided
            if ($request->has('genres') && is_array($request->genres)) {
                $movie->genres()->sync($request->genres);
            }

            // Auto-add default embeds if requested
            if ($request->has('auto_add_embeds') && is_array($request->auto_add_embeds) && !empty($request->auto_add_embeds)) {
                // Use TMDB ID if available, otherwise fallback to movie ID
                $embedId = $movie->tmdb_id ?: $movie->id;
                
                $embedConfigs = [
                    'vidlink' => [
                        'server_name' => 'VidLink',
                        'embed_url' => 'https://vidlink.pro/movie/' . $embedId,
                        'priority' => 1,
                    ],
                    'vidsrc' => [
                        'server_name' => 'VidSrc',
                        'embed_url' => 'https://vidsrc.icu/embed/movie/' . $embedId,
                        'priority' => 2,
                    ],
                    'vidfast' => [
                        'server_name' => 'VidFast',
                        'embed_url' => 'https://vidfast.pro/movie/' . $embedId,
                        'priority' => 3,
                    ],
                ];

                foreach ($request->auto_add_embeds as $embedType) {
                    if (isset($embedConfigs[$embedType])) {
                        $embedData = $embedConfigs[$embedType];
                        MovieEmbed::create([
                            'movie_id' => $movie->id,
                            'server_name' => $embedData['server_name'],
                            'embed_url' => $embedData['embed_url'],
                            'language_id' => null,
                            'priority' => $embedData['priority'],
                            'is_active' => true,
                        ]);
                    }
                }
            }

            // Reload with relationships
            $movie->refresh();
            $movie->load(['genres', 'category', 'dubbingLanguage']);

            // Format the response
            $posterPath = $movie->poster_path;
            if ($posterPath && !str_starts_with($posterPath, 'http')) {
                $posterPath = str_starts_with($posterPath, '/') ? $posterPath : '/' . $posterPath;
            }

            $formattedMovie = [
                'id' => $movie->id,
                'title' => $movie->title,
                'slug' => $movie->slug,
                'overview' => $movie->overview,
                'poster_path' => $posterPath ?: '/images/placeholder.svg',
                'backdrop_path' => $movie->backdrop_path,
                'release_date' => $movie->release_date?->format('Y-m-d'),
                'runtime' => $movie->runtime,
                'vote_average' => (float) $movie->vote_average,
                'vote_count' => $movie->vote_count,
                'view_count' => $movie->view_count ?? 0,
                'popularity' => (float) $movie->popularity,
                'status' => $movie->status ?? 'pending',
                'is_featured' => (bool) $movie->is_featured,
                'original_language' => $movie->original_language,
                'tmdb_id' => $movie->tmdb_id,
                'genres' => $movie->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                    ];
                }),
                'category' => $movie->category ? [
                    'id' => $movie->category->id,
                    'name' => $movie->category->name,
                    'slug' => $movie->category->slug,
                ] : null,
                'dubbing_language' => $movie->dubbingLanguage ? [
                    'id' => $movie->dubbingLanguage->id,
                    'name' => $movie->dubbingLanguage->name,
                    'code' => $movie->dubbingLanguage->code,
                ] : null,
                'created_at' => $movie->created_at?->toISOString(),
                'updated_at' => $movie->updated_at?->toISOString(),
            ];

            $message = 'Movie created successfully';
            if ($request->has('auto_add_embeds') && is_array($request->auto_add_embeds) && !empty($request->auto_add_embeds)) {
                $embedCount = count($request->auto_add_embeds);
                $message .= ' with ' . $embedCount . ' default embed(s)';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'movie' => $formattedMovie
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
                'message' => 'Error creating movie: ' . $e->getMessage()
            ], 500);
        }
    }
}

