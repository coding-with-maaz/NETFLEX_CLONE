<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TVShow;
use App\Traits\FormatsImageUrls;
use Illuminate\Http\Request;

class TVShowApiController extends Controller
{
    use FormatsImageUrls;
    public function index(Request $request)
    {
        $query = TVShow::query();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'active');
        }

        // Filter by featured
        if ($request->has('is_featured')) {
            $isFeatured = filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN);
            $query->where('is_featured', $isFeatured);
        }

        // Filter by category
        if ($request->has('category') && $request->category && $request->category !== 'All') {
            $categoryValue = $request->category;
            $query->whereHas('category', function($q) use ($categoryValue) {
                $q->where(function($subQ) use ($categoryValue) {
                    $subQ->where('slug', $categoryValue)
                         ->orWhere('name', $categoryValue);
                });
            });
        }

        // Filter by genre
        if ($request->has('genre') && $request->genre && $request->genre !== 'All') {
            $genreValue = $request->genre;
            $genreId = is_numeric($genreValue) ? (int)$genreValue : null;
            
            if ($genreId) {
                // Filter by genre ID
                $query->whereHas('genres', function($q) use ($genreId) {
                    $q->where('genres.id', $genreId);
                });
            } else {
                // Filter by genre name or slug
                $query->whereHas('genres', function($q) use ($genreValue) {
                    $q->where('genres.slug', $genreValue)
                      ->orWhere('genres.name', $genreValue);
                });
            }
        }

        // Filter by year
        if ($request->has('year') && $request->year && $request->year !== 'Other') {
            $year = $request->year;
            
            // Handle decade ranges (e.g., "2010s")
            if (str_ends_with($year, 's')) {
                $decade = (int) substr($year, 0, -1);
                $startYear = $decade;
                $endYear = $decade + 9;
                $query->whereRaw('YEAR(first_air_date) >= ?', [$startYear])
                      ->whereRaw('YEAR(first_air_date) <= ?', [$endYear]);
            } elseif (is_numeric($year)) {
                // Single year
                $query->whereRaw('YEAR(first_air_date) = ?', [(int)$year]);
            }
        }

        // Filter by language
        if ($request->has('language') && $request->language && $request->language !== 'All') {
            $languageValue = $request->language;
            // Match by language code (original_language is typically a 2-letter code)
            // Extract code if it's a full name like "French dub" -> "fr"
            $code = $languageValue;
            if (strlen($languageValue) > 2) {
                // Try to extract 2-letter code or match by name
                $code = strtolower(substr($languageValue, 0, 2));
            }
            $query->where(function($q) use ($languageValue, $code) {
                $q->where('original_language', $code)
                  ->orWhere('original_language', 'like', "%{$code}%")
                  ->orWhereRaw("LOWER(original_language) LIKE ?", [strtolower("%{$languageValue}%")]);
            });
        }

        // Filter by rating
        if ($request->has('min_rating') && $request->min_rating) {
            $query->where('vote_average', '>=', (float)$request->min_rating);
        }

        if ($request->has('min_votes')) {
            $query->where('vote_count', '>=', (int)$request->min_votes);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'foryou');
        $order = $request->get('order', 'desc');

        switch ($sortBy) {
            case 'foryou':
                // For You: Mix of popularity, rating, and recent views - personalized recommendation
                $query->orderByRaw('(popularity * 0.4 + vote_average * 3.0 + view_count * 0.01 + (UNIX_TIMESTAMP(created_at) / 86400)) DESC');
                break;
            case 'hottest':
                // Hottest: Most views and recent activity
                $query->orderBy('view_count', 'desc')
                      ->orderBy('created_at', 'desc');
                break;
            case 'latest':
            case 'created_at':
                $query->orderBy('created_at', $order);
                break;
            case 'popularity':
                $query->orderBy('popularity', $order);
                break;
            case 'rating':
            case 'top-rated':
                $query->orderBy('vote_average', $order);
                break;
            case 'name':
            case 'a-z':
                $query->orderBy('name', 'asc');
                break;
            case 'views':
                $query->orderBy('view_count', $order);
                break;
            case 'first_air_date':
                $query->orderBy('first_air_date', $order);
                break;
            default:
                // Default to For You algorithm
                $query->orderByRaw('(popularity * 0.4 + vote_average * 3.0 + view_count * 0.01 + (UNIX_TIMESTAMP(created_at) / 86400)) DESC');
        }

        // Pagination
        $page = max(1, (int) $request->get('page', 1));
        $limit = min(100, max(1, (int) $request->get('limit', 20)));
        $offset = ($page - 1) * $limit;
        
        // Get total count before pagination
        $total = $query->count();
        
        // Get results with relationships
        $tvShows = $query->with(['genres', 'category'])
                       ->offset($offset)
                       ->limit($limit)
                       ->get();
        
        $totalPages = (int) ceil($total / $limit);

        // Format response
        $formattedTVShows = $tvShows->map(function($tvShow) {
            // Format poster and backdrop paths - prepend TMDB base URL if relative path
            $posterPath = $tvShow->poster_path;
            if ($posterPath) {
                if (str_starts_with($posterPath, 'http')) {
                    $posterPath = $posterPath;
                } elseif (str_starts_with($posterPath, '/')) {
                    $posterPath = 'https://image.tmdb.org/t/p/w500' . $posterPath;
                } else {
                    $posterPath = 'https://image.tmdb.org/t/p/w500/' . $posterPath;
                }
            } else {
                $posterPath = '/images/placeholder.svg';
            }
            
            $backdropPath = $tvShow->backdrop_path;
            if ($backdropPath) {
                if (str_starts_with($backdropPath, 'http')) {
                    $backdropPath = $backdropPath;
                } elseif (str_starts_with($backdropPath, '/')) {
                    $backdropPath = 'https://image.tmdb.org/t/p/w1280' . $backdropPath;
                } else {
                    $backdropPath = 'https://image.tmdb.org/t/p/w1280/' . $backdropPath;
                }
            } else {
                $backdropPath = ($posterPath !== '/images/placeholder.svg' ? $posterPath : '/images/placeholder.svg');
            }
            
            return [
                'id' => $tvShow->id,
                'name' => $tvShow->name,
                'slug' => $tvShow->slug,
                'overview' => $tvShow->overview,
                'poster_path' => $posterPath,
                'backdrop_path' => $backdropPath,
                'first_air_date' => $tvShow->first_air_date?->format('Y-m-d'),
                'last_air_date' => $tvShow->last_air_date?->format('Y-m-d'),
                'number_of_seasons' => $tvShow->number_of_seasons,
                'number_of_episodes' => $tvShow->number_of_episodes,
                'vote_average' => (float) $tvShow->vote_average,
                'vote_count' => $tvShow->vote_count,
                'view_count' => $tvShow->view_count,
                'status' => $tvShow->status,
                'is_featured' => $tvShow->is_featured,
                'popularity' => (float) $tvShow->popularity,
                'original_language' => $tvShow->original_language,
                'episode_run_time' => $tvShow->episode_run_time,
                'genres' => $tvShow->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                    ];
                }),
                'created_at' => $tvShow->created_at?->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'tvShows' => $formattedTVShows,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => $totalPages,
                    'has_next' => $page < $totalPages,
                    'has_prev' => $page > 1,
                ]
            ]
        ]);
    }

    public function show($id)
    {
        try {
            // Support lookup by ID (numeric), slug, name, or TMDB ID
            $query = TVShow::with(['genres', 'category', 'dubbingLanguage', 'seasons.episodes']);
            
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
            
            // Don't filter by status for show endpoint - allow viewing any TV show

            // Format the response to match frontend expectations
            $formatted = [
                'id' => $tvShow->id,
                'name' => $tvShow->name ?? 'Untitled',
                'slug' => $tvShow->slug ?? '',
                'overview' => $tvShow->overview ?? '',
                'poster_path' => $tvShow->poster_path,
                'backdrop_path' => $tvShow->backdrop_path,
                'first_air_date' => $tvShow->first_air_date?->format('Y-m-d'),
                'last_air_date' => $tvShow->last_air_date?->format('Y-m-d'),
                'number_of_seasons' => $tvShow->number_of_seasons,
                'number_of_episodes' => $tvShow->number_of_episodes,
                'vote_average' => (float) $tvShow->vote_average,
                'vote_count' => $tvShow->vote_count,
                'view_count' => $tvShow->view_count,
                'status' => $tvShow->status,
                'is_featured' => $tvShow->is_featured,
                'popularity' => (float) $tvShow->popularity,
                'original_language' => $tvShow->original_language,
                'original_name' => $tvShow->name, // Use name as original_name if field doesn't exist
                'episode_run_time' => $tvShow->episode_run_time,
                'category' => $tvShow->category ? [
                    'id' => $tvShow->category->id,
                    'name' => $tvShow->category->name,
                    'slug' => $tvShow->category->slug,
                ] : null,
                'genres' => $tvShow->genres ? $tvShow->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                    ];
                })->values() : [],
                'seasons' => $tvShow->seasons ? $tvShow->seasons->map(function($season) {
                    return [
                        'id' => $season->id,
                        'season_number' => $season->season_number,
                        'number' => $season->season_number, // Alias for frontend compatibility
                        'name' => $season->name,
                        'overview' => $season->overview,
                        'episode_count' => $season->episode_count ?? ($season->episodes ? $season->episodes->count() : 0),
                        'poster_path' => $season->poster_path,
                        'air_date' => $season->air_date?->format('Y-m-d'),
                        'episodes' => $season->episodes ? $season->episodes->map(function($episode) {
                            return [
                                'id' => $episode->id,
                                'episode_number' => $episode->episode_number,
                                'number' => $episode->episode_number,
                                'name' => $episode->name,
                                'overview' => $episode->overview,
                                'still_path' => $episode->still_path,
                                'air_date' => $episode->air_date?->format('Y-m-d'),
                                'runtime' => $episode->runtime,
                                'vote_average' => (float) $episode->vote_average,
                                'vote_count' => $episode->vote_count,
                                'view_count' => $episode->view_count ?? 0,
                            ];
                        })->values() : [],
                    ];
                })->sortBy('season_number')->values() : [],
            ];

            // Format poster and backdrop paths - ensure full URLs for TMDB images
            $posterPath = $tvShow->poster_path;
            if ($posterPath) {
                if (str_starts_with($posterPath, 'http')) {
                    $posterPath = $posterPath;
                } elseif (str_starts_with($posterPath, '/')) {
                    $posterPath = 'https://image.tmdb.org/t/p/w500' . $posterPath;
                } else {
                    $posterPath = 'https://image.tmdb.org/t/p/w500/' . $posterPath;
                }
            } else {
                $posterPath = '/images/placeholder.svg';
            }
            
            $backdropPath = $tvShow->backdrop_path;
            if ($backdropPath) {
                if (str_starts_with($backdropPath, 'http')) {
                    $backdropPath = $backdropPath;
                } elseif (str_starts_with($backdropPath, '/')) {
                    $backdropPath = 'https://image.tmdb.org/t/p/w1280' . $backdropPath;
                } else {
                    $backdropPath = 'https://image.tmdb.org/t/p/w1280/' . $backdropPath;
                }
            } else {
                $backdropPath = ($posterPath !== '/images/placeholder.svg' ? $posterPath : '/images/placeholder.svg');
            }

            // Update formatted with proper image URLs
            $formatted['poster_path'] = $posterPath;
            $formatted['backdrop_path'] = $backdropPath;
            $formatted['created_at'] = $tvShow->created_at?->toISOString();

            return response()->json([
                'success' => true,
                'data' => $formatted
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'TV show not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error fetching TV show details: ' . $e->getMessage(), [
                'tvshow_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching TV show details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top rated TV shows
     */
    public function topRated(Request $request)
    {
        $query = TVShow::query();
        
        // Only active TV shows
        $query->where('status', 'active');
        
        // Filter by minimum rating and votes
        $minRating = $request->get('min_rating', 7.5);
        $minVotes = $request->get('min_votes', 100);
        
        $query->where('vote_average', '>=', (float)$minRating)
              ->where('vote_count', '>=', (int)$minVotes);
        
        // Sort by rating
        $query->orderBy('vote_average', 'desc')
              ->orderBy('vote_count', 'desc');
        
        // Pagination
        $page = max(1, (int) $request->get('page', 1));
        $limit = min(100, max(1, (int) $request->get('limit', 20)));
        $offset = ($page - 1) * $limit;
        
        $total = $query->count();
        
        $tvShows = $query->with(['genres', 'category'])
                       ->offset($offset)
                       ->limit($limit)
                       ->get();
        
        $totalPages = (int) ceil($total / $limit);
        
        // Format response
        $formattedTVShows = $tvShows->map(function($tvShow) {
            $posterPath = $tvShow->poster_path;
            if ($posterPath) {
                if (str_starts_with($posterPath, 'http')) {
                    $posterPath = $posterPath;
                } elseif (str_starts_with($posterPath, '/')) {
                    $posterPath = 'https://image.tmdb.org/t/p/w500' . $posterPath;
                } else {
                    $posterPath = 'https://image.tmdb.org/t/p/w500/' . $posterPath;
                }
            } else {
                $posterPath = '/images/placeholder.svg';
            }
            
            $backdropPath = $tvShow->backdrop_path;
            if ($backdropPath) {
                if (str_starts_with($backdropPath, 'http')) {
                    $backdropPath = $backdropPath;
                } elseif (str_starts_with($backdropPath, '/')) {
                    $backdropPath = 'https://image.tmdb.org/t/p/w1280' . $backdropPath;
                } else {
                    $backdropPath = 'https://image.tmdb.org/t/p/w1280/' . $backdropPath;
                }
            } else {
                $backdropPath = ($posterPath !== '/images/placeholder.svg' ? $posterPath : '/images/placeholder.svg');
            }
            
            return [
                'id' => $tvShow->id,
                'name' => $tvShow->name,
                'slug' => $tvShow->slug,
                'overview' => $tvShow->overview,
                'poster_path' => $posterPath,
                'backdrop_path' => $backdropPath,
                'first_air_date' => $tvShow->first_air_date?->format('Y-m-d'),
                'vote_average' => (float) $tvShow->vote_average,
                'vote_count' => $tvShow->vote_count,
                'view_count' => $tvShow->view_count,
                'status' => $tvShow->status,
                'is_featured' => $tvShow->is_featured,
                'popularity' => (float) $tvShow->popularity,
                'original_language' => $tvShow->original_language,
                'genres' => $tvShow->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                    ];
                })->values(),
                'created_at' => $tvShow->created_at?->toISOString(),
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'tvShows' => $formattedTVShows,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => $totalPages,
                    'has_next' => $page < $totalPages,
                    'has_prev' => $page > 1,
                ]
            ]
        ]);
    }

    /**
     * Get popular TV shows
     */
    public function popular(Request $request)
    {
        $query = TVShow::query();
        
        // Only active TV shows
        $query->where('status', 'active');
        
        // Sort by popularity
        $query->orderBy('popularity', 'desc')
              ->orderBy('view_count', 'desc');
        
        // Pagination
        $page = max(1, (int) $request->get('page', 1));
        $limit = min(100, max(1, (int) $request->get('limit', 20)));
        $offset = ($page - 1) * $limit;
        
        $total = $query->count();
        
        $tvShows = $query->with(['genres', 'category'])
                       ->offset($offset)
                       ->limit($limit)
                       ->get();
        
        $totalPages = (int) ceil($total / $limit);
        
        // Format response
        $formattedTVShows = $tvShows->map(function($tvShow) {
            $posterPath = $tvShow->poster_path;
            if ($posterPath) {
                if (str_starts_with($posterPath, 'http')) {
                    $posterPath = $posterPath;
                } elseif (str_starts_with($posterPath, '/')) {
                    $posterPath = 'https://image.tmdb.org/t/p/w500' . $posterPath;
                } else {
                    $posterPath = 'https://image.tmdb.org/t/p/w500/' . $posterPath;
                }
            } else {
                $posterPath = '/images/placeholder.svg';
            }
            
            $backdropPath = $tvShow->backdrop_path;
            if ($backdropPath) {
                if (str_starts_with($backdropPath, 'http')) {
                    $backdropPath = $backdropPath;
                } elseif (str_starts_with($backdropPath, '/')) {
                    $backdropPath = 'https://image.tmdb.org/t/p/w1280' . $backdropPath;
                } else {
                    $backdropPath = 'https://image.tmdb.org/t/p/w1280/' . $backdropPath;
                }
            } else {
                $backdropPath = ($posterPath !== '/images/placeholder.svg' ? $posterPath : '/images/placeholder.svg');
            }
            
            return [
                'id' => $tvShow->id,
                'name' => $tvShow->name,
                'slug' => $tvShow->slug,
                'overview' => $tvShow->overview,
                'poster_path' => $posterPath,
                'backdrop_path' => $backdropPath,
                'first_air_date' => $tvShow->first_air_date?->format('Y-m-d'),
                'vote_average' => (float) $tvShow->vote_average,
                'vote_count' => $tvShow->vote_count,
                'view_count' => $tvShow->view_count,
                'status' => $tvShow->status,
                'is_featured' => $tvShow->is_featured,
                'popularity' => (float) $tvShow->popularity,
                'original_language' => $tvShow->original_language,
                'genres' => $tvShow->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                    ];
                })->values(),
                'created_at' => $tvShow->created_at?->toISOString(),
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'tvShows' => $formattedTVShows,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => $totalPages,
                    'has_next' => $page < $totalPages,
                    'has_prev' => $page > 1,
                ]
            ]
        ]);
    }

    public function search(Request $request)
    {
        $query = TVShow::query();

        // Filter by status
        $query->where('status', 'active');

        // Search by name, ID, slug, or TMDB ID - case insensitive for overview, also search by genre name
        if ($request->has('q') && $request->q) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('slug', 'LIKE', "%{$searchTerm}%")
                  ->orWhereRaw('LOWER(overview) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                  ->orWhereHas('genres', function($subQ) use ($searchTerm) {
                      // Also search by genre name or slug
                      $subQ->where('genres.name', 'LIKE', "%{$searchTerm}%")
                           ->orWhere('genres.slug', 'LIKE', "%{$searchTerm}%");
                  });
                
                // If search term is numeric, also search by ID or TMDB ID
                if (is_numeric($searchTerm)) {
                    $q->orWhere('id', $searchTerm)
                      ->orWhere('tmdb_id', $searchTerm);
                }
            });
        }

        // Filter by genre - support ID, slug, or name
        if ($request->has('genre') && $request->genre) {
            $genreValue = $request->genre;
            $genreId = is_numeric($genreValue) ? (int)$genreValue : null;
            
            if ($genreId) {
                // Filter by genre ID
                $query->whereHas('genres', function($q) use ($genreId) {
                    $q->where('genres.id', $genreId);
                });
            } else {
                // Filter by genre name or slug
                $query->whereHas('genres', function($q) use ($genreValue) {
                    $q->where('genres.slug', $genreValue)
                      ->orWhere('genres.name', $genreValue);
                });
            }
        }

        // Filter by year - handle decade ranges and NULL dates
        if ($request->has('year') && $request->year && $request->year !== 'All' && $request->year !== 'Other') {
            $year = $request->year;
            
            // Handle decade ranges (e.g., "2010s")
            if (str_ends_with($year, 's')) {
                $decade = (int) substr($year, 0, -1);
                $startYear = $decade;
                $endYear = $decade + 9;
                $query->whereNotNull('first_air_date')
                      ->whereRaw('YEAR(first_air_date) >= ?', [$startYear])
                      ->whereRaw('YEAR(first_air_date) <= ?', [$endYear]);
            } elseif (is_numeric($year)) {
                // Single year
                $query->whereNotNull('first_air_date')
                      ->whereRaw('YEAR(first_air_date) = ?', [(int)$year]);
            }
        }

        // Filter by language
        if ($request->has('language') && $request->language) {
            $query->where('original_language', $request->language);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'popularity');
        $order = $request->get('order', 'desc');

        switch ($sortBy) {
            case 'title':
                $query->orderBy('name', $order);
                break;
            case 'release_date':
            case 'first_air_date':
                $query->orderBy('first_air_date', $order);
                break;
            case 'vote_average':
            case 'rating':
                $query->orderBy('vote_average', $order);
                break;
            case 'popularity':
            default:
                $query->orderBy('popularity', $order);
                break;
        }

        // Pagination
        $page = max(1, (int) $request->get('page', 1));
        $limit = max(1, min(100, (int) $request->get('limit', 20)));
        $offset = ($page - 1) * $limit;

        // Get total count
        $totalItems = $query->count();

        // Get results with relationships
        $tvShows = $query->with(['genres', 'category'])
            ->skip($offset)
            ->take($limit)
            ->get();

        // Format response
        $formattedTVShows = $tvShows->map(function($tvShow) {
            // Format poster and backdrop paths - prepend TMDB base URL if relative path
            $posterPath = $tvShow->poster_path;
            if ($posterPath) {
                if (str_starts_with($posterPath, 'http')) {
                    $posterPath = $posterPath;
                } elseif (str_starts_with($posterPath, '/')) {
                    $posterPath = 'https://image.tmdb.org/t/p/w500' . $posterPath;
                } else {
                    $posterPath = 'https://image.tmdb.org/t/p/w500/' . $posterPath;
                }
            } else {
                $posterPath = '/images/placeholder.svg';
            }
            
            $backdropPath = $tvShow->backdrop_path;
            if ($backdropPath) {
                if (str_starts_with($backdropPath, 'http')) {
                    $backdropPath = $backdropPath;
                } elseif (str_starts_with($backdropPath, '/')) {
                    $backdropPath = 'https://image.tmdb.org/t/p/w1280' . $backdropPath;
                } else {
                    $backdropPath = 'https://image.tmdb.org/t/p/w1280/' . $backdropPath;
                }
            } else {
                $backdropPath = ($posterPath !== '/images/placeholder.svg' ? $posterPath : '/images/placeholder.svg');
            }
            
            return [
                'id' => $tvShow->id,
                'name' => $tvShow->name,
                'slug' => $tvShow->slug,
                'overview' => $tvShow->overview,
                'poster_path' => $posterPath,
                'backdrop_path' => $backdropPath,
                'first_air_date' => $tvShow->first_air_date?->format('Y-m-d'),
                'last_air_date' => $tvShow->last_air_date?->format('Y-m-d'),
                'number_of_seasons' => $tvShow->number_of_seasons,
                'number_of_episodes' => $tvShow->number_of_episodes,
                'vote_average' => (float) $tvShow->vote_average,
                'vote_count' => $tvShow->vote_count,
                'view_count' => $tvShow->view_count,
                'status' => $tvShow->status,
                'is_featured' => $tvShow->is_featured,
                'popularity' => (float) $tvShow->popularity,
                'original_language' => $tvShow->original_language,
                'episode_run_time' => $tvShow->episode_run_time,
                'genres' => $tvShow->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                    ];
                }),
                'created_at' => $tvShow->created_at?->toISOString(),
            ];
        });

        $totalPages = ceil($totalItems / $limit);

        return response()->json([
            'success' => true,
            'data' => [
                'tvShows' => $formattedTVShows,
                'pagination' => [
                    'currentPage' => $page,
                    'totalPages' => $totalPages,
                    'totalItems' => $totalItems,
                    'itemsPerPage' => $limit,
                    'hasNextPage' => $page < $totalPages,
                    'hasPrevPage' => $page > 1,
                ]
            ]
        ]);
    }
}

