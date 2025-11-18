<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\TVShow;
use App\Models\Episode;
use Illuminate\Http\Request;

class SearchApiController extends Controller
{
    /**
     * Unified search across movies, TV shows, and episodes
     */
    public function search(Request $request)
    {
        $query = trim($request->get('q', ''));
        $type = $request->get('type', 'all'); // 'movies', 'tvshows', 'episodes', 'all'
        $page = max(1, (int) $request->get('page', 1));
        $limit = min(100, max(1, (int) $request->get('limit', 20)));
        $offset = ($page - 1) * $limit;

        // If no query provided, return empty results
        if (empty($query)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'query' => '',
                    'type' => $type,
                    'results' => [
                        'movies' => [],
                        'tvShows' => [],
                        'episodes' => [],
                    ],
                    'totals' => [
                        'movies' => 0,
                        'tvShows' => 0,
                        'episodes' => 0,
                    ],
                    'total' => 0,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $limit,
                        'total' => 0,
                        'total_pages' => 0,
                        'has_next' => false,
                        'has_prev' => false,
                    ]
                ]
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $results = [
            'movies' => [],
            'tvShows' => [],
            'episodes' => [],
        ];

        $totals = [
            'movies' => 0,
            'tvShows' => 0,
            'episodes' => 0,
        ];

        // Search movies
        if ($type === 'all' || $type === 'movies') {
            $moviesQuery = Movie::where('status', 'active');
            
            if ($query) {
                $moviesQuery->where(function($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('slug', 'LIKE', "%{$query}%")
                      ->orWhereRaw('LOWER(overview) LIKE ?', ['%' . strtolower($query) . '%']);
                });
            }

            $totals['movies'] = $moviesQuery->count();
            $movies = $moviesQuery->with(['genres', 'category'])
                ->offset($offset)
                ->limit($limit)
                ->orderBy('popularity', 'desc')
                ->get();

            $results['movies'] = $movies->map(function($movie) {
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

                return [
                    'id' => $movie->id,
                    'title' => $movie->title,
                    'slug' => $movie->slug,
                    'poster_path' => $posterPath,
                    'release_date' => $movie->release_date?->format('Y-m-d'),
                    'vote_average' => (float) $movie->vote_average,
                    'type' => 'movie',
                ];
            });
        }

        // Search TV shows
        if ($type === 'all' || $type === 'tvshows') {
            $tvShowsQuery = TVShow::where('status', 'active');
            
            if ($query) {
                $tvShowsQuery->where(function($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('slug', 'LIKE', "%{$query}%")
                      ->orWhereRaw('LOWER(overview) LIKE ?', ['%' . strtolower($query) . '%']);
                    
                    // If query is numeric, also search by ID or TMDB ID
                    if (is_numeric($query)) {
                        $q->orWhere('id', $query)
                          ->orWhere('tmdb_id', $query);
                    }
                });
            }

            $totals['tvShows'] = $tvShowsQuery->count();
            $tvShows = $tvShowsQuery->with(['genres', 'category'])
                ->offset($offset)
                ->limit($limit)
                ->orderBy('popularity', 'desc')
                ->get();

            $results['tvShows'] = $tvShows->map(function($tvShow) {
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

                return [
                    'id' => $tvShow->id,
                    'name' => $tvShow->name,
                    'slug' => $tvShow->slug,
                    'poster_path' => $posterPath,
                    'first_air_date' => $tvShow->first_air_date?->format('Y-m-d'),
                    'vote_average' => (float) $tvShow->vote_average,
                    'type' => 'tvshow',
                ];
            });
        }

        // Search episodes
        if ($type === 'all' || $type === 'episodes') {
            $episodesQuery = Episode::whereHas('season.tvShow', function($q) {
                $q->where('status', 'active');
            });

            if ($query) {
                $episodesQuery->where(function($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhereRaw('LOWER(overview) LIKE ?', ['%' . strtolower($query) . '%'])
                      ->orWhereHas('season.tvShow', function($subQ) use ($query) {
                          $subQ->where('name', 'LIKE', "%{$query}%");
                      });
                });
            }

            $totals['episodes'] = $episodesQuery->count();
            $episodes = $episodesQuery->with(['season.tvShow'])
                ->offset($offset)
                ->limit($limit)
                ->orderBy('created_at', 'desc')
                ->get();

            $results['episodes'] = $episodes->map(function($episode) {
                $tvShow = $episode->season->tvShow ?? null;
                
                $stillPath = $episode->still_path;
                if ($stillPath) {
                    if (str_starts_with($stillPath, 'http')) {
                        $stillPath = $stillPath;
                    } elseif (str_starts_with($stillPath, '/')) {
                        $stillPath = 'https://image.tmdb.org/t/p/w500' . $stillPath;
                    } else {
                        $stillPath = 'https://image.tmdb.org/t/p/w500/' . $stillPath;
                    }
                } else {
                    $stillPath = '/images/placeholder.svg';
                }

                return [
                    'id' => $episode->id,
                    'name' => $episode->name,
                    'episode_number' => $episode->episode_number,
                    'season_number' => $episode->season->season_number ?? 0,
                    'still_path' => $stillPath,
                    'tv_show' => $tvShow ? [
                        'id' => $tvShow->id,
                        'name' => $tvShow->name,
                    ] : null,
                    'type' => 'episode',
                ];
            });
        }

        $totalResults = array_sum($totals);

        return response()->json([
            'success' => true,
            'data' => [
                'query' => $query,
                'type' => $type,
                'results' => $results,
                'totals' => $totals,
                'total' => $totalResults,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $totalResults,
                    'total_pages' => (int) ceil($totalResults / $limit),
                    'has_next' => $page < ceil($totalResults / $limit),
                    'has_prev' => $page > 1,
                ]
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get available filter options
     */
    public function filterOptions(Request $request)
    {
        try {
            // Get available genres
            $genres = \App\Models\Genre::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);

            // Get available categories
            $categories = \App\Models\Category::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);

            // Get available languages
            $languages = \App\Models\Language::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'native_name']);

            // Get available years (from movies and TV shows)
            $movieYears = Movie::where('status', 'active')
                ->whereNotNull('release_date')
                ->selectRaw('YEAR(release_date) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->filter()
                ->values();

            $tvShowYears = TVShow::where('status', 'active')
                ->whereNotNull('first_air_date')
                ->selectRaw('YEAR(first_air_date) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->filter()
                ->values();

            $years = $movieYears->merge($tvShowYears)
                ->unique()
                ->sortDesc()
                ->values();

            // Get rating ranges
            $ratingRanges = [
                ['min' => 0, 'max' => 3, 'label' => '0-3'],
                ['min' => 3, 'max' => 5, 'label' => '3-5'],
                ['min' => 5, 'max' => 7, 'label' => '5-7'],
                ['min' => 7, 'max' => 8, 'label' => '7-8'],
                ['min' => 8, 'max' => 10, 'label' => '8-10'],
            ];

            // Get sort options
            $sortOptions = [
                ['value' => 'popularity', 'label' => 'Popularity'],
                ['value' => 'rating', 'label' => 'Rating'],
                ['value' => 'latest', 'label' => 'Latest'],
                ['value' => 'oldest', 'label' => 'Oldest'],
                ['value' => 'title', 'label' => 'Title (A-Z)'],
                ['value' => 'views', 'label' => 'Most Viewed'],
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'genres' => $genres,
                    'categories' => $categories,
                    'languages' => $languages,
                    'years' => $years,
                    'rating_ranges' => $ratingRanges,
                    'sort_options' => $sortOptions,
                ]
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching filter options: ' . $e->getMessage()
            ], 500);
        }
    }
}

