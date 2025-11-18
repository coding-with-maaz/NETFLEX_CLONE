<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Movie::query();

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

        // Filter by year
        if ($request->has('year') && $request->year && $request->year !== 'Other') {
            $year = $request->year;
            
            // Handle decade ranges (e.g., "2010s")
            if (str_ends_with($year, 's')) {
                $decade = (int) substr($year, 0, -1);
                $startYear = $decade;
                $endYear = $decade + 9;
                $query->whereRaw('YEAR(release_date) >= ?', [$startYear])
                      ->whereRaw('YEAR(release_date) <= ?', [$endYear]);
            } elseif (is_numeric($year)) {
                // Single year
                $query->whereRaw('YEAR(release_date) = ?', [(int)$year]);
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
            case 'title':
            case 'a-z':
                $query->orderBy('title', 'asc');
                break;
            case 'views':
                $query->orderBy('view_count', $order);
                break;
            case 'release_date':
                $query->orderBy('release_date', $order);
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
        $movies = $query->with(['genres', 'category'])
                       ->offset($offset)
                       ->limit($limit)
                       ->get();
        
        $totalPages = (int) ceil($total / $limit);

        // Format response
        $formattedMovies = $movies->map(function($movie) {
            // Format poster and backdrop paths - ensure full URLs for TMDB images
            $posterPath = $movie->poster_path;
            if ($posterPath) {
                if (str_starts_with($posterPath, 'http')) {
                    // Already a full URL
                    $posterPath = $posterPath;
                } elseif (str_starts_with($posterPath, '/')) {
                    // TMDB path like /abc123.jpg - prepend base URL
                    $posterPath = 'https://image.tmdb.org/t/p/w500' . $posterPath;
                } else {
                    // Relative path without leading slash
                    $posterPath = 'https://image.tmdb.org/t/p/w500/' . $posterPath;
                }
            } else {
                $posterPath = '/images/placeholder.svg';
            }
            
            $backdropPath = $movie->backdrop_path;
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
                'id' => $movie->id,
                'title' => $movie->title,
                'slug' => $movie->slug,
                'overview' => $movie->overview,
                'poster_path' => $posterPath,
                'backdrop_path' => $backdropPath,
                'release_date' => $movie->release_date?->format('Y-m-d'),
                'runtime' => $movie->runtime,
                'vote_average' => (float) $movie->vote_average,
                'vote_count' => $movie->vote_count,
                'view_count' => $movie->view_count,
                'status' => $movie->status,
                'is_featured' => $movie->is_featured,
                'popularity' => (float) $movie->popularity,
                'original_language' => $movie->original_language,
                'genres' => $movie->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                    ];
                }),
                'created_at' => $movie->created_at?->toISOString(),
            ];
        });

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
                ]
            ]
        ]);
    }

    public function show($id)
    {
        try {
            $movie = Movie::with(['genres', 'category', 'embeds', 'downloads'])
                ->where('status', 'active')
                ->findOrFail($id);

            // Format poster and backdrop paths - ensure full URLs for TMDB images
            $posterPath = $movie->poster_path;
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
            
            $backdropPath = $movie->backdrop_path;
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

            // Format the response to match frontend expectations
            $formatted = [
                'id' => $movie->id,
                'title' => $movie->title,
                'slug' => $movie->slug,
                'overview' => $movie->overview,
                'poster_path' => $posterPath,
                'backdrop_path' => $backdropPath,
                'release_date' => $movie->release_date?->format('Y-m-d'),
                'runtime' => $movie->runtime,
                'vote_average' => (float) $movie->vote_average,
                'vote_count' => $movie->vote_count,
                'view_count' => $movie->view_count,
                'status' => $movie->status,
                'is_featured' => $movie->is_featured,
                'imdb_id' => $movie->imdb_id,
                'original_language' => $movie->original_language,
                'tagline' => $movie->tagline,
                'popularity' => (float) $movie->popularity,
                'revenue' => $movie->revenue,
                'budget' => $movie->budget,
                'category' => $movie->category ? [
                    'id' => $movie->category->id,
                    'name' => $movie->category->name,
                    'slug' => $movie->category->slug,
                ] : null,
                'genres' => $movie->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                    ];
                })->values(),
                'embeds' => $movie->embeds->where('is_active', true)
                    ->sortByDesc('priority')
                    ->values()
                    ->map(function($embed) {
                        // Decode HTML entities in embed URL (handle double encoding)
                        $embedUrl = $embed->embed_url;
                        // Decode multiple times in case of double encoding
                        $embedUrl = html_entity_decode($embedUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $embedUrl = html_entity_decode($embedUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        
                        return [
                            'id' => $embed->id,
                            'server_name' => $embed->server_name,
                            'embed_url' => $embedUrl,
                            'priority' => $embed->priority,
                        ];
                    }),
                'downloads' => $movie->downloads->where('is_active', true)
                    ->sortByDesc('priority')
                    ->values()
                    ->map(function($download) {
                        return [
                            'id' => $download->id,
                            'quality' => $download->quality,
                            'server_name' => $download->server_name,
                            'download_url' => $download->download_url,
                            'size' => $download->size,
                            'priority' => $download->priority,
                        ];
                    }),
                'created_at' => $movie->created_at?->toISOString(),
            ];

            // Use JSON_UNESCAPED_UNICODE to preserve actual characters (for embed HTML)
            return response()->json([
                'success' => true,
                'data' => $formatted
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Movie not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching movie: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top rated movies
     */
    public function topRated(Request $request)
    {
        $query = Movie::query();
        
        // Only active movies
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
        
        $movies = $query->with(['genres', 'category'])
                       ->offset($offset)
                       ->limit($limit)
                       ->get();
        
        $totalPages = (int) ceil($total / $limit);
        
        // Format response (same as index)
        $formattedMovies = $movies->map(function($movie) {
            $posterPath = $movie->poster_path;
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
            
            $backdropPath = $movie->backdrop_path;
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
                'id' => $movie->id,
                'title' => $movie->title,
                'slug' => $movie->slug,
                'overview' => $movie->overview,
                'poster_path' => $posterPath,
                'backdrop_path' => $backdropPath,
                'release_date' => $movie->release_date?->format('Y-m-d'),
                'runtime' => $movie->runtime,
                'vote_average' => (float) $movie->vote_average,
                'vote_count' => $movie->vote_count,
                'view_count' => $movie->view_count,
                'status' => $movie->status,
                'is_featured' => $movie->is_featured,
                'popularity' => (float) $movie->popularity,
                'original_language' => $movie->original_language,
                'genres' => $movie->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                    ];
                })->values(),
                'created_at' => $movie->created_at?->toISOString(),
            ];
        });
        
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
                ]
            ]
        ]);
    }

    /**
     * Get trending movies
     */
    public function trending(Request $request)
    {
        $query = Movie::query();
        
        // Only active movies
        $query->where('status', 'active');
        
        // Period: day, week, month (default: week)
        $period = $request->get('period', 'week');
        
        // Filter by date range
        $dateFilter = match($period) {
            'day' => now()->subDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            default => now()->subWeek(),
        };
        
        $query->where('created_at', '>=', $dateFilter);
        
        // Sort by trending algorithm (popularity + views + recency)
        $query->orderByRaw('(popularity * 0.4 + vote_average * 3.0 + view_count * 0.01 + (UNIX_TIMESTAMP(created_at) / 86400)) DESC');
        
        // Pagination
        $page = max(1, (int) $request->get('page', 1));
        $limit = min(100, max(1, (int) $request->get('limit', 20)));
        $offset = ($page - 1) * $limit;
        
        $total = $query->count();
        
        $movies = $query->with(['genres', 'category'])
                       ->offset($offset)
                       ->limit($limit)
                       ->get();
        
        $totalPages = (int) ceil($total / $limit);
        
        // Format response
        $formattedMovies = $movies->map(function($movie) {
            $posterPath = $movie->poster_path;
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
            
            $backdropPath = $movie->backdrop_path;
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
                'id' => $movie->id,
                'title' => $movie->title,
                'slug' => $movie->slug,
                'overview' => $movie->overview,
                'poster_path' => $posterPath,
                'backdrop_path' => $backdropPath,
                'release_date' => $movie->release_date?->format('Y-m-d'),
                'runtime' => $movie->runtime,
                'vote_average' => (float) $movie->vote_average,
                'vote_count' => $movie->vote_count,
                'view_count' => $movie->view_count,
                'status' => $movie->status,
                'is_featured' => $movie->is_featured,
                'popularity' => (float) $movie->popularity,
                'original_language' => $movie->original_language,
                'genres' => $movie->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                    ];
                })->values(),
                'created_at' => $movie->created_at?->toISOString(),
            ];
        });
        
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
                'period' => $period
            ]
        ]);
    }

    public function todayAll(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        
        try {
            // Parse the date and get start and end of day
            $startOfDay = \Carbon\Carbon::parse($date)->startOfDay();
            $endOfDay = \Carbon\Carbon::parse($date)->endOfDay();
            
            // Fetch movies created on this date
            $movies = Movie::where('status', 'active')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->with(['genres', 'category'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Format response
            $formattedMovies = $movies->map(function($movie) {
                // Format poster and backdrop paths - ensure full URLs for TMDB images
                $posterPath = $movie->poster_path;
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
                
                $backdropPath = $movie->backdrop_path;
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
                    'id' => $movie->id,
                    'title' => $movie->title,
                    'slug' => $movie->slug,
                    'overview' => $movie->overview,
                    'poster_path' => $posterPath,
                    'backdrop_path' => $backdropPath,
                    'release_date' => $movie->release_date?->format('Y-m-d'),
                    'runtime' => $movie->runtime,
                    'vote_average' => (float) $movie->vote_average,
                    'vote_count' => $movie->vote_count,
                    'view_count' => $movie->view_count ?? 0,
                    'status' => $movie->status,
                    'is_featured' => $movie->is_featured,
                    'popularity' => (float) $movie->popularity,
                    'original_language' => $movie->original_language,
                    'genres' => $movie->genres->map(function($genre) {
                        return [
                            'id' => $genre->id,
                            'name' => $genre->name,
                            'slug' => $genre->slug,
                        ];
                    })->values(),
                    'created_at' => $movie->created_at?->toISOString(),
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $formattedMovies
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching movies: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $query = Movie::query();

        // Filter by status
        $query->where('status', 'active');

        // Search by title - case insensitive for overview, also search by genre name
        if ($request->has('q') && $request->q) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('slug', 'LIKE', "%{$searchTerm}%")
                  ->orWhereRaw('LOWER(overview) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                  ->orWhereHas('genres', function($subQ) use ($searchTerm) {
                      // Also search by genre name or slug
                      $subQ->where('genres.name', 'LIKE', "%{$searchTerm}%")
                           ->orWhere('genres.slug', 'LIKE', "%{$searchTerm}%");
                  });
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
                $query->whereNotNull('release_date')
                      ->whereRaw('YEAR(release_date) >= ?', [$startYear])
                      ->whereRaw('YEAR(release_date) <= ?', [$endYear]);
            } elseif (is_numeric($year)) {
                // Single year
                $query->whereNotNull('release_date')
                      ->whereRaw('YEAR(release_date) = ?', [(int)$year]);
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
                $query->orderBy('title', $order);
                break;
            case 'release_date':
                $query->orderBy('release_date', $order);
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
        $movies = $query->with(['genres', 'category'])
            ->skip($offset)
            ->take($limit)
            ->get();

        // Format response
        $formattedMovies = $movies->map(function($movie) {
            // Format poster and backdrop paths
            $posterPath = $movie->poster_path;
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
            
            $backdropPath = $movie->backdrop_path;
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
                'id' => $movie->id,
                'title' => $movie->title,
                'slug' => $movie->slug,
                'overview' => $movie->overview,
                'poster_path' => $posterPath,
                'backdrop_path' => $backdropPath,
                'release_date' => $movie->release_date?->format('Y-m-d'),
                'runtime' => $movie->runtime,
                'vote_average' => (float) $movie->vote_average,
                'vote_count' => $movie->vote_count,
                'view_count' => $movie->view_count,
                'status' => $movie->status,
                'is_featured' => $movie->is_featured,
                'popularity' => (float) $movie->popularity,
                'original_language' => $movie->original_language,
                'genres' => $movie->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                    ];
                }),
                'created_at' => $movie->created_at?->toISOString(),
            ];
        });

        $totalPages = ceil($totalItems / $limit);

        return response()->json([
            'success' => true,
            'data' => [
                'movies' => $formattedMovies,
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

