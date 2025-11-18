<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EpisodeApiController extends Controller
{
    public function latestAll(Request $request)
    {
        $limit = $request->get('limit', 20);
        
        // Get latest added episodes (prioritize created_at for recently added content)
        $episodes = Episode::with(['season.tvShow'])
            ->whereHas('season.tvShow', function($q) {
                $q->where('status', 'active');
            })
            ->orderBy('created_at', 'desc')
            ->orderBy('air_date', 'desc')
            ->limit($limit)
            ->get();

        $formatted = $episodes->map(function($episode) {
            $tvShow = $episode->season->tvShow;
            
            // Format still_path
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

            // Format TV show poster and backdrop
            $tvPosterPath = $tvShow->poster_path;
            if ($tvPosterPath) {
                if (str_starts_with($tvPosterPath, 'http')) {
                    $tvPosterPath = $tvPosterPath;
                } elseif (str_starts_with($tvPosterPath, '/')) {
                    $tvPosterPath = 'https://image.tmdb.org/t/p/w500' . $tvPosterPath;
                } else {
                    $tvPosterPath = 'https://image.tmdb.org/t/p/w500/' . $tvPosterPath;
                }
            } else {
                $tvPosterPath = '/images/placeholder.svg';
            }

            $tvBackdropPath = $tvShow->backdrop_path;
            if ($tvBackdropPath) {
                if (str_starts_with($tvBackdropPath, 'http')) {
                    $tvBackdropPath = $tvBackdropPath;
                } elseif (str_starts_with($tvBackdropPath, '/')) {
                    $tvBackdropPath = 'https://image.tmdb.org/t/p/w1280' . $tvBackdropPath;
                } else {
                    $tvBackdropPath = 'https://image.tmdb.org/t/p/w1280/' . $tvBackdropPath;
                }
            } else {
                $tvBackdropPath = ($tvPosterPath !== '/images/placeholder.svg' ? $tvPosterPath : '/images/placeholder.svg');
            }

            return [
                'id' => $episode->id,
                'name' => $episode->name,
                'episode_number' => $episode->episode_number,
                'season_number' => $episode->season->season_number,
                'overview' => $episode->overview,
                'still_path' => $stillPath,
                'air_date' => $episode->air_date?->format('Y-m-d'),
                'runtime' => $episode->runtime,
                'vote_average' => (float) $episode->vote_average,
                'view_count' => $episode->view_count,
                'tv_show' => [
                    'id' => $tvShow->id,
                    'name' => $tvShow->name,
                    'poster_path' => $tvPosterPath,
                    'backdrop_path' => $tvBackdropPath,
                ],
                // Also include alternative formats for compatibility
                'tvShow' => [
                    'id' => $tvShow->id,
                    'name' => $tvShow->name,
                    'poster_path' => $tvPosterPath,
                    'backdrop_path' => $tvBackdropPath,
                ],
                'tvshow' => [
                    'id' => $tvShow->id,
                    'name' => $tvShow->name,
                    'poster_path' => $tvPosterPath,
                    'backdrop_path' => $tvBackdropPath,
                ],
                'tvshow_id' => $tvShow->id,
                'created_at' => $episode->created_at?->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted
        ]);
    }

    public function today(Request $request)
    {
        $limit = $request->get('limit', 20);
        $today = now()->format('Y-m-d');
        
        $episodes = Episode::with(['season.tvShow'])
            ->where('air_date', $today)
            ->whereHas('season.tvShow', function($q) {
                $q->where('status', 'active');
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        $formatted = $episodes->map(function($episode) {
            $tvShow = $episode->season->tvShow;
            
            // Format still_path
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

            // Format TV show poster and backdrop
            $tvPosterPath = $tvShow->poster_path;
            if ($tvPosterPath) {
                if (str_starts_with($tvPosterPath, 'http')) {
                    $tvPosterPath = $tvPosterPath;
                } elseif (str_starts_with($tvPosterPath, '/')) {
                    $tvPosterPath = 'https://image.tmdb.org/t/p/w500' . $tvPosterPath;
                } else {
                    $tvPosterPath = 'https://image.tmdb.org/t/p/w500/' . $tvPosterPath;
                }
            } else {
                $tvPosterPath = '/images/placeholder.svg';
            }

            $tvBackdropPath = $tvShow->backdrop_path;
            if ($tvBackdropPath) {
                if (str_starts_with($tvBackdropPath, 'http')) {
                    $tvBackdropPath = $tvBackdropPath;
                } elseif (str_starts_with($tvBackdropPath, '/')) {
                    $tvBackdropPath = 'https://image.tmdb.org/t/p/w1280' . $tvBackdropPath;
                } else {
                    $tvBackdropPath = 'https://image.tmdb.org/t/p/w1280/' . $tvBackdropPath;
                }
            } else {
                $tvBackdropPath = ($tvPosterPath !== '/images/placeholder.svg' ? $tvPosterPath : '/images/placeholder.svg');
            }

            return [
                'id' => $episode->id,
                'name' => $episode->name,
                'episode_number' => $episode->episode_number,
                'season_number' => $episode->season->season_number,
                'overview' => $episode->overview,
                'still_path' => $stillPath,
                'air_date' => $episode->air_date?->format('Y-m-d'),
                'runtime' => $episode->runtime,
                'vote_average' => (float) $episode->vote_average,
                'view_count' => $episode->view_count,
                'tv_show' => [
                    'id' => $tvShow->id,
                    'name' => $tvShow->name,
                    'poster_path' => $tvPosterPath,
                    'backdrop_path' => $tvBackdropPath,
                ],
                // Also include alternative formats for compatibility
                'tvShow' => [
                    'id' => $tvShow->id,
                    'name' => $tvShow->name,
                    'poster_path' => $tvPosterPath,
                    'backdrop_path' => $tvBackdropPath,
                ],
                'tvshow' => [
                    'id' => $tvShow->id,
                    'name' => $tvShow->name,
                    'poster_path' => $tvPosterPath,
                    'backdrop_path' => $tvBackdropPath,
                ],
                'tvshow_id' => $tvShow->id,
                'created_at' => $episode->created_at?->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted
        ]);
    }

    public function todayAll(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        
        try {
            // Parse the date and get start and end of day
            $startOfDay = \Carbon\Carbon::parse($date)->startOfDay();
            $endOfDay = \Carbon\Carbon::parse($date)->endOfDay();
            
            // Fetch episodes created on this date (uploaded on this date)
            $episodes = Episode::with(['season.tvShow'])
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->whereHas('season.tvShow', function($q) {
                    $q->where('status', 'active');
                })
                ->orderBy('created_at', 'desc')
                ->get();
            
            $formatted = $episodes->map(function($episode) {
                $tvShow = $episode->season->tvShow;
                
                // Format still_path
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

                // Format TV show poster and backdrop
                $tvPosterPath = $tvShow->poster_path;
                if ($tvPosterPath) {
                    if (str_starts_with($tvPosterPath, 'http')) {
                        $tvPosterPath = $tvPosterPath;
                    } elseif (str_starts_with($tvPosterPath, '/')) {
                        $tvPosterPath = 'https://image.tmdb.org/t/p/w500' . $tvPosterPath;
                    } else {
                        $tvPosterPath = 'https://image.tmdb.org/t/p/w500/' . $tvPosterPath;
                    }
                } else {
                    $tvPosterPath = '/images/placeholder.svg';
                }

                $tvBackdropPath = $tvShow->backdrop_path;
                if ($tvBackdropPath) {
                    if (str_starts_with($tvBackdropPath, 'http')) {
                        $tvBackdropPath = $tvBackdropPath;
                    } elseif (str_starts_with($tvBackdropPath, '/')) {
                        $tvBackdropPath = 'https://image.tmdb.org/t/p/w1280' . $tvBackdropPath;
                    } else {
                        $tvBackdropPath = 'https://image.tmdb.org/t/p/w1280/' . $tvBackdropPath;
                    }
                } else {
                    $tvBackdropPath = ($tvPosterPath !== '/images/placeholder.svg' ? $tvPosterPath : '/images/placeholder.svg');
                }

                return [
                    'id' => $episode->id,
                    'name' => $episode->name,
                    'episode_number' => $episode->episode_number,
                    'season_number' => $episode->season->season_number,
                    'overview' => $episode->overview,
                    'still_path' => $stillPath,
                    'air_date' => $episode->air_date?->format('Y-m-d'),
                    'runtime' => $episode->runtime,
                    'vote_average' => (float) $episode->vote_average,
                    'view_count' => $episode->view_count,
                    'tv_show' => [
                        'id' => $tvShow->id,
                        'name' => $tvShow->name,
                        'poster_path' => $tvPosterPath,
                        'backdrop_path' => $tvBackdropPath,
                    ],
                    'tvShow' => [
                        'id' => $tvShow->id,
                        'name' => $tvShow->name,
                        'poster_path' => $tvPosterPath,
                        'backdrop_path' => $tvBackdropPath,
                    ],
                    'tvshow' => [
                        'id' => $tvShow->id,
                        'name' => $tvShow->name,
                        'poster_path' => $tvPosterPath,
                        'backdrop_path' => $tvBackdropPath,
                    ],
                    'tvshow_id' => $tvShow->id,
                    'created_at' => $episode->created_at?->toISOString(),
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $formatted
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching episodes: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get single episode details
     */
    public function show($id)
    {
        try {
            // Allow viewing any episode, even if TV show is inactive (for public API)
            // This is useful for viewing episode details directly
            $episode = Episode::with(['season.tvShow', 'embeds', 'downloads'])
                ->findOrFail($id);

            // Check if episode has required relationships
            if (!$episode->season) {
                return response()->json([
                    'success' => false,
                    'message' => 'Episode season not found'
                ], 404);
            }

            if (!$episode->season->tvShow) {
                return response()->json([
                    'success' => false,
                    'message' => 'Episode TV show not found'
                ], 404);
            }

            $tvShow = $episode->season->tvShow;

            // Format still_path
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

            // Format TV show poster and backdrop
            $tvPosterPath = $tvShow->poster_path;
            if ($tvPosterPath) {
                if (str_starts_with($tvPosterPath, 'http')) {
                    $tvPosterPath = $tvPosterPath;
                } elseif (str_starts_with($tvPosterPath, '/')) {
                    $tvPosterPath = 'https://image.tmdb.org/t/p/w500' . $tvPosterPath;
                } else {
                    $tvPosterPath = 'https://image.tmdb.org/t/p/w500/' . $tvPosterPath;
                }
            } else {
                $tvPosterPath = '/images/placeholder.svg';
            }

            $tvBackdropPath = $tvShow->backdrop_path;
            if ($tvBackdropPath) {
                if (str_starts_with($tvBackdropPath, 'http')) {
                    $tvBackdropPath = $tvBackdropPath;
                } elseif (str_starts_with($tvBackdropPath, '/')) {
                    $tvBackdropPath = 'https://image.tmdb.org/t/p/w1280' . $tvBackdropPath;
                } else {
                    $tvBackdropPath = 'https://image.tmdb.org/t/p/w1280/' . $tvBackdropPath;
                }
            } else {
                $tvBackdropPath = ($tvPosterPath !== '/images/placeholder.svg' ? $tvPosterPath : '/images/placeholder.svg');
            }

            $formatted = [
                'id' => $episode->id,
                'name' => $episode->name,
                'episode_number' => $episode->episode_number,
                'season_number' => $episode->season->season_number,
                'overview' => $episode->overview,
                'still_path' => $stillPath,
                'air_date' => $episode->air_date?->format('Y-m-d'),
                'runtime' => $episode->runtime,
                'vote_average' => (float) $episode->vote_average,
                'vote_count' => $episode->vote_count,
                'view_count' => $episode->view_count ?? 0,
                'season' => [
                    'id' => $episode->season->id,
                    'season_number' => $episode->season->season_number,
                    'name' => $episode->season->name,
                ],
                'tv_show' => [
                    'id' => $tvShow->id,
                    'name' => $tvShow->name,
                    'slug' => $tvShow->slug,
                    'poster_path' => $tvPosterPath,
                    'backdrop_path' => $tvBackdropPath,
                ],
                'embeds' => $episode->embeds->where('is_active', true)
                    ->sortByDesc('priority')
                    ->values()
                    ->map(function($embed) {
                        // Decode HTML entities in embed URL
                        $embedUrl = $embed->embed_url;
                        $embedUrl = html_entity_decode($embedUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $embedUrl = html_entity_decode($embedUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        
                        return [
                            'id' => $embed->id,
                            'server_name' => $embed->server_name,
                            'embed_url' => $embedUrl,
                            'priority' => $embed->priority,
                        ];
                    }),
                'downloads' => $episode->downloads->where('is_active', true)
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
                'created_at' => $episode->created_at?->toISOString(),
            ];

            return response()->json([
                'success' => true,
                'data' => $formatted
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Episode not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching episode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search and filter episodes
     */
    public function search(Request $request)
    {
        $query = Episode::query();

        // Filter by active TV shows only
        $query->whereHas('season.tvShow', function($q) {
            $q->where('status', 'active');
        });

        // Search by episode name or TV show name
        if ($request->has('q') && $request->q) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('overview', 'LIKE', "%{$searchTerm}%")
                  ->orWhereHas('season.tvShow', function($subQ) use ($searchTerm) {
                      $subQ->where('name', 'LIKE', "%{$searchTerm}%");
                  });
            });
        }

        // Filter by TV show
        if ($request->has('tvshow_id') && $request->tvshow_id) {
            $query->whereHas('season', function($q) use ($request) {
                $q->where('tv_show_id', $request->tvshow_id);
            });
        }

        // Filter by season
        if ($request->has('season_id') && $request->season_id) {
            $query->where('season_id', $request->season_id);
        }

        // Filter by genre (via TV show)
        if ($request->has('genre') && $request->genre) {
            $genreValue = $request->genre;
            $genreId = is_numeric($genreValue) ? (int)$genreValue : null;
            
            $query->whereHas('season.tvShow.genres', function($q) use ($genreId, $genreValue) {
                if ($genreId) {
                    $q->where('genres.id', $genreId);
                } else {
                    $q->where('genres.slug', $genreValue)
                      ->orWhere('genres.name', $genreValue);
                }
            });
        }

        // Filter by air date range
        if ($request->has('air_date_from')) {
            $query->where('air_date', '>=', $request->air_date_from);
        }
        if ($request->has('air_date_to')) {
            $query->where('air_date', '<=', $request->air_date_to);
        }

        // Filter by rating
        if ($request->has('min_rating')) {
            $query->where('vote_average', '>=', (float)$request->min_rating);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $order = $request->get('order', 'desc');

        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', $order);
                break;
            case 'air_date':
                $query->orderBy('air_date', $order);
                break;
            case 'rating':
            case 'vote_average':
                $query->orderBy('vote_average', $order);
                break;
            case 'episode_number':
                $query->orderBy('episode_number', $order);
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

        $total = $query->count();

        $episodes = $query->with(['season.tvShow', 'embeds'])
            ->offset($offset)
            ->limit($limit)
            ->get();

        $totalPages = (int) ceil($total / $limit);

        $formatted = $episodes->map(function($episode) {
            $tvShow = $episode->season->tvShow ?? null;

            // Format still_path
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

            // Format TV show poster
            $tvPosterPath = $tvShow->poster_path ?? null;
            if ($tvPosterPath) {
                if (str_starts_with($tvPosterPath, 'http')) {
                    $tvPosterPath = $tvPosterPath;
                } elseif (str_starts_with($tvPosterPath, '/')) {
                    $tvPosterPath = 'https://image.tmdb.org/t/p/w500' . $tvPosterPath;
                } else {
                    $tvPosterPath = 'https://image.tmdb.org/t/p/w500/' . $tvPosterPath;
                }
            } else {
                $tvPosterPath = '/images/placeholder.svg';
            }

            return [
                'id' => $episode->id,
                'name' => $episode->name,
                'episode_number' => $episode->episode_number,
                'season_number' => $episode->season->season_number ?? 0,
                'overview' => $episode->overview,
                'still_path' => $stillPath,
                'air_date' => $episode->air_date?->format('Y-m-d'),
                'runtime' => $episode->runtime,
                'vote_average' => (float) $episode->vote_average,
                'view_count' => $episode->view_count ?? 0,
                'tv_show' => $tvShow ? [
                    'id' => $tvShow->id,
                    'name' => $tvShow->name,
                    'slug' => $tvShow->slug,
                    'poster_path' => $tvPosterPath,
                ] : null,
                'season' => $episode->season ? [
                    'id' => $episode->season->id,
                    'season_number' => $episode->season->season_number,
                ] : null,
                'created_at' => $episode->created_at?->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'episodes' => $formatted,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => $totalPages,
                    'has_next' => $page < $totalPages,
                    'has_prev' => $page > 1,
                ]
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Update episode
     */
    public function update(Request $request, $id)
    {
        try {
            $episode = Episode::findOrFail($id);

            $request->validate([
                'name' => 'sometimes|string|max:255',
                'overview' => 'nullable|string',
                'still_path' => 'nullable|string',
                'air_date' => 'nullable|date',
                'runtime' => 'nullable|integer|min:0',
                'vote_average' => 'nullable|numeric|min:0|max:10',
                'vote_count' => 'nullable|integer|min:0',
            ]);

            $updateData = [];
            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }
            if ($request->has('overview')) {
                $updateData['overview'] = $request->overview;
            }
            if ($request->has('still_path')) {
                $updateData['still_path'] = $request->still_path;
            }
            if ($request->has('air_date')) {
                $updateData['air_date'] = $request->air_date ?: null;
            }
            if ($request->has('runtime')) {
                $updateData['runtime'] = $request->runtime ?: null;
            }
            if ($request->has('vote_average')) {
                $updateData['vote_average'] = $request->vote_average;
            }
            if ($request->has('vote_count')) {
                $updateData['vote_count'] = $request->vote_count;
            }

            $episode->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Episode updated successfully',
                'data' => [
                    'episode' => [
                        'id' => $episode->id,
                        'name' => $episode->name,
                        'episode_number' => $episode->episode_number,
                        'overview' => $episode->overview,
                        'still_path' => $episode->still_path,
                        'air_date' => $episode->air_date?->format('Y-m-d'),
                        'runtime' => $episode->runtime,
                        'vote_average' => (float) $episode->vote_average,
                        'vote_count' => $episode->vote_count,
                        'view_count' => $episode->view_count ?? 0,
                    ]
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
                'message' => 'Error updating episode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete episode
     */
    public function destroy($id)
    {
        try {
            $episode = Episode::findOrFail($id);
            $episode->delete();

            return response()->json([
                'success' => true,
                'message' => 'Episode deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting episode: ' . $e->getMessage()
            ], 500);
        }
    }
}

