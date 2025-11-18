<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\TVShow;
use Illuminate\Http\Request;

class GenreApiController extends Controller
{
    /**
     * Get genre details with movies and TV shows
     */
    public function show(Request $request, $id)
    {
        try {
            // Get genre by ID or slug
            $genre = Genre::where('id', $id)
                ->orWhere('slug', $id)
                ->where('is_active', true)
                ->firstOrFail();

            // Get movies for this genre
            $moviesQuery = Movie::where('status', 'active')
                ->whereHas('genres', function($q) use ($genre) {
                    $q->where('genres.id', $genre->id);
                });

            // Get TV shows for this genre
            $tvShowsQuery = TVShow::where('status', 'active')
                ->whereHas('genres', function($q) use ($genre) {
                    $q->where('genres.id', $genre->id);
                });

            // Apply filters
            $sortBy = $request->get('sort_by', 'popularity');
            $minRating = $request->get('min_rating');
            $year = $request->get('year');
            $language = $request->get('language');
            $type = $request->get('type', 'all'); // 'movies', 'tvshows', 'all'

            // Filter by rating
            if ($minRating) {
                $moviesQuery->where('vote_average', '>=', (float)$minRating);
                $tvShowsQuery->where('vote_average', '>=', (float)$minRating);
            }

            // Filter by year
            if ($year && $year !== 'All' && $year !== 'Other') {
                if (str_ends_with($year, 's')) {
                    $decade = (int) substr($year, 0, -1);
                    $startYear = $decade;
                    $endYear = $decade + 9;
                    $moviesQuery->whereRaw('YEAR(release_date) >= ?', [$startYear])
                              ->whereRaw('YEAR(release_date) <= ?', [$endYear]);
                    $tvShowsQuery->whereRaw('YEAR(first_air_date) >= ?', [$startYear])
                               ->whereRaw('YEAR(first_air_date) <= ?', [$endYear]);
                } elseif (is_numeric($year)) {
                    $moviesQuery->whereRaw('YEAR(release_date) = ?', [(int)$year]);
                    $tvShowsQuery->whereRaw('YEAR(first_air_date) = ?', [(int)$year]);
                }
            }

            // Filter by language
            if ($language && $language !== 'All') {
                $code = strlen($language) > 2 ? strtolower(substr($language, 0, 2)) : $language;
                $moviesQuery->where('original_language', $code);
                $tvShowsQuery->where('original_language', $code);
            }

            // Apply sorting
            switch ($sortBy) {
                case 'rating':
                case 'top-rated':
                    $moviesQuery->orderBy('vote_average', 'desc')->orderBy('vote_count', 'desc');
                    $tvShowsQuery->orderBy('vote_average', 'desc')->orderBy('vote_count', 'desc');
                    break;
                case 'latest':
                    $moviesQuery->orderBy('release_date', 'desc');
                    $tvShowsQuery->orderBy('first_air_date', 'desc');
                    break;
                case 'popularity':
                default:
                    $moviesQuery->orderBy('popularity', 'desc');
                    $tvShowsQuery->orderBy('popularity', 'desc');
                    break;
            }

            // Pagination
            $page = max(1, (int) $request->get('page', 1));
            $limit = min(100, max(1, (int) $request->get('limit', 24)));
            $offset = ($page - 1) * $limit;

            $movies = collect();
            $tvShows = collect();
            $moviesTotal = 0;
            $tvShowsTotal = 0;

            // Fetch based on type
            if ($type === 'movies' || $type === 'all') {
                $moviesTotal = $moviesQuery->count();
                if ($type === 'all') {
                    // For 'all', fetch all movies and TV shows, then combine and paginate
                    $movies = $moviesQuery->with(['genres', 'category'])->get();
                } else {
                    // For 'movies' only, paginate normally
                    $movies = $moviesQuery->with(['genres', 'category'])
                        ->offset($offset)
                        ->limit($limit)
                        ->get();
                }
            }

            if ($type === 'tvshows' || $type === 'all') {
                $tvShowsTotal = $tvShowsQuery->count();
                if ($type === 'all') {
                    // For 'all', fetch all TV shows, then combine and paginate
                    $tvShows = $tvShowsQuery->with(['genres', 'category'])->get();
                } else {
                    // For 'tvshows' only, paginate normally
                    $tvShows = $tvShowsQuery->with(['genres', 'category'])
                        ->offset($offset)
                        ->limit($limit)
                        ->get();
                }
            }

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

                return [
                    'id' => $movie->id,
                    'title' => $movie->title,
                    'slug' => $movie->slug,
                    'poster_path' => $posterPath,
                    'release_date' => $movie->release_date?->format('Y-m-d'),
                    'vote_average' => (float) $movie->vote_average,
                    'view_count' => $movie->view_count,
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

                return [
                    'id' => $tvShow->id,
                    'name' => $tvShow->name,
                    'slug' => $tvShow->slug,
                    'poster_path' => $posterPath,
                    'first_air_date' => $tvShow->first_air_date?->format('Y-m-d'),
                    'vote_average' => (float) $tvShow->vote_average,
                    'view_count' => $tvShow->view_count,
                    'type' => 'tvshow',
                ];
            });

            // Combine and sort if type is 'all'
            $allContent = collect();
            if ($type === 'all') {
                $allContent = $formattedMovies->merge($formattedTVShows)
                    ->sortByDesc(function($item) {
                        return ($item['vote_average'] ?? 0) * 100 + ($item['view_count'] ?? 0);
                    })
                    ->values();
            }

            $totalItems = match($type) {
                'movies' => $moviesTotal,
                'tvshows' => $tvShowsTotal,
                default => $moviesTotal + $tvShowsTotal,
            };

            $totalPages = (int) ceil($totalItems / $limit);

            // Get paginated content
            $paginatedContent = match($type) {
                'movies' => $formattedMovies,
                'tvshows' => $formattedTVShows,
                default => $allContent->slice($offset, $limit)->values(),
            };

            return response()->json([
                'success' => true,
                'data' => [
                    'genre' => [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'slug' => $genre->slug,
                        'description' => $genre->description,
                    ],
                    'content' => $paginatedContent,
                    'movies' => $type !== 'tvshows' ? $formattedMovies : [],
                    'tvShows' => $type !== 'movies' ? $formattedTVShows : [],
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $limit,
                        'total' => $totalItems,
                        'total_pages' => $totalPages,
                        'has_next' => $page < $totalPages,
                        'has_prev' => $page > 1,
                    ],
                    'filters' => [
                        'type' => $type,
                        'sort_by' => $sortBy,
                        'min_rating' => $minRating,
                        'year' => $year,
                        'language' => $language,
                    ]
                ]
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Genre not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching genre: ' . $e->getMessage()
            ], 500);
        }
    }
}

