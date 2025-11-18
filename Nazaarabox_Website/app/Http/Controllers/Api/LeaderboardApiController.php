<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\TVShow;
use App\Models\View;
use Illuminate\Http\Request;

class LeaderboardApiController extends Controller
{
    /**
     * Get trending content (movies and TV shows combined)
     */
    public function trending(Request $request)
    {
        $period = $request->get('period', 'week');
        $limit = min(100, max(1, (int) $request->get('limit', 50)));
        
        // Get trending movies
        $moviesQuery = Movie::where('status', 'active');
        $tvShowsQuery = TVShow::where('status', 'active');
        
        // Filter by period based on View records
        $dateFilter = match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            'overall' => null, // No date filter for overall/all time
            default => now()->subWeek(),
        };
        
        if ($dateFilter) {
            // Filter by views in the period
            $movieIds = View::where('viewable_type', Movie::class)
                ->where('viewed_at', '>=', $dateFilter)
                ->groupBy('viewable_id')
                ->pluck('viewable_id')
                ->toArray();
            
            $tvShowIds = View::where('viewable_type', TVShow::class)
                ->where('viewed_at', '>=', $dateFilter)
                ->groupBy('viewable_id')
                ->pluck('viewable_id')
                ->toArray();
            
            if (!empty($movieIds)) {
                $moviesQuery->whereIn('id', $movieIds);
            } else {
                $moviesQuery->whereRaw('1 = 0'); // No results if no views
            }
            
            if (!empty($tvShowIds)) {
                $tvShowsQuery->whereIn('id', $tvShowIds);
            } else {
                $tvShowsQuery->whereRaw('1 = 0'); // No results if no views
            }
        }
        
        // Get movies with view counts from Views table
        $movies = $moviesQuery->with(['genres'])
            ->withCount(['views as recent_views' => function($query) use ($dateFilter) {
                if ($dateFilter) {
                    $query->where('viewed_at', '>=', $dateFilter);
                }
            }])
            ->orderBy('recent_views', 'desc')
            ->orderBy('view_count', 'desc')
            ->orderBy('popularity', 'desc')
            ->limit($limit)
            ->get();
        
        $tvShows = $tvShowsQuery->with(['genres'])
            ->withCount(['views as recent_views' => function($query) use ($dateFilter) {
                if ($dateFilter) {
                    $query->where('viewed_at', '>=', $dateFilter);
                }
            }])
            ->orderBy('recent_views', 'desc')
            ->orderBy('view_count', 'desc')
            ->orderBy('popularity', 'desc')
            ->limit($limit)
            ->get();
        
        // Format movies
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
                'poster_path' => $posterPath,
                'backdrop_path' => $backdropPath,
                'vote_average' => (float) $movie->vote_average,
                'view_count' => $movie->view_count ?? 0,
                'viewCount' => $movie->view_count ?? 0, // Alias for frontend compatibility
                'popularity' => (float) $movie->popularity,
                'release_date' => $movie->release_date?->format('Y-m-d'),
                'genres' => $movie->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                    ];
                })->values(),
                'type' => 'movie',
            ];
        });
        
        // Format TV shows
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
                'poster_path' => $posterPath,
                'backdrop_path' => $backdropPath,
                'vote_average' => (float) $tvShow->vote_average,
                'view_count' => $tvShow->view_count ?? 0,
                'viewCount' => $tvShow->view_count ?? 0, // Alias for frontend compatibility
                'popularity' => (float) $tvShow->popularity,
                'first_air_date' => $tvShow->first_air_date?->format('Y-m-d'),
                'genres' => $tvShow->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                    ];
                })->values(),
                'type' => 'tvshow',
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'movies' => $formattedMovies,
                'tvShows' => $formattedTVShows,
                'period' => $period,
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    
    /**
     * Get movies leaderboard
     */
    public function moviesLeaderboard(Request $request)
    {
        $period = $request->get('period', 'week');
        $limit = min(100, max(1, (int) $request->get('limit', 20)));
        
        $query = Movie::where('status', 'active');
        
        // Filter by period based on View records
        $dateFilter = match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            'overall' => null,
            default => now()->subWeek(),
        };
        
        if ($dateFilter) {
            $movieIds = View::where('viewable_type', Movie::class)
                ->where('viewed_at', '>=', $dateFilter)
                ->groupBy('viewable_id')
                ->pluck('viewable_id')
                ->toArray();
            
            if (!empty($movieIds)) {
                $query->whereIn('id', $movieIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        
        $movies = $query->with(['genres'])
            ->withCount(['views as recent_views' => function($q) use ($dateFilter) {
                if ($dateFilter) {
                    $q->where('viewed_at', '>=', $dateFilter);
                }
            }])
            ->orderBy('recent_views', 'desc')
            ->orderBy('view_count', 'desc')
            ->orderBy('popularity', 'desc')
            ->limit($limit)
            ->get();
        
        $formatted = $movies->map(function($movie) {
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
                'poster_path' => $posterPath,
                'backdrop_path' => $backdropPath,
                'vote_average' => (float) $movie->vote_average,
                'view_count' => $movie->view_count ?? 0,
                'viewCount' => $movie->view_count ?? 0,
                'popularity' => (float) $movie->popularity,
                'release_date' => $movie->release_date?->format('Y-m-d'),
                'genres' => $movie->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                    ];
                })->values(),
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'movies' => $formatted,
                'period' => $period,
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    
    /**
     * Get TV shows leaderboard
     */
    public function tvShowsLeaderboard(Request $request)
    {
        $period = $request->get('period', 'week');
        $limit = min(100, max(1, (int) $request->get('limit', 20)));
        
        $query = TVShow::where('status', 'active');
        
        // Filter by period based on View records
        $dateFilter = match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            'overall' => null,
            default => now()->subWeek(),
        };
        
        if ($dateFilter) {
            $tvShowIds = View::where('viewable_type', TVShow::class)
                ->where('viewed_at', '>=', $dateFilter)
                ->groupBy('viewable_id')
                ->pluck('viewable_id')
                ->toArray();
            
            if (!empty($tvShowIds)) {
                $query->whereIn('id', $tvShowIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        
        $tvShows = $query->with(['genres'])
            ->withCount(['views as recent_views' => function($q) use ($dateFilter) {
                if ($dateFilter) {
                    $q->where('viewed_at', '>=', $dateFilter);
                }
            }])
            ->orderBy('recent_views', 'desc')
            ->orderBy('view_count', 'desc')
            ->orderBy('popularity', 'desc')
            ->limit($limit)
            ->get();
        
        $formatted = $tvShows->map(function($tvShow) {
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
                'poster_path' => $posterPath,
                'backdrop_path' => $backdropPath,
                'vote_average' => (float) $tvShow->vote_average,
                'view_count' => $tvShow->view_count ?? 0,
                'viewCount' => $tvShow->view_count ?? 0,
                'popularity' => (float) $tvShow->popularity,
                'first_air_date' => $tvShow->first_air_date?->format('Y-m-d'),
                'genres' => $tvShow->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                    ];
                })->values(),
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'tvShows' => $formatted,
                'period' => $period,
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

