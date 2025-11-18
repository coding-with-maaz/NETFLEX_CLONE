<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\TVShow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminFeaturedController extends Controller
{
    /**
     * Get all featured content (movies and TV shows)
     */
    public function index(Request $request)
    {
        try {
            $type = $request->get('type', 'all'); // 'movies', 'tvshows', or 'all'
            $featuredFilter = $request->get('featured', 'all'); // 'all', 'featured', or 'unfeatured'
            
            $featuredMovies = [];
            $featuredTVShows = [];
            $unfeaturedMovies = [];
            $unfeaturedTVShows = [];
            
            $mapMovie = function ($movie, $isFeatured = true) {
                return [
                    'id' => $movie->id,
                    'type' => 'movie',
                    'title' => $movie->title,
                    'slug' => $movie->slug,
                    'poster_path' => $movie->poster_path,
                    'backdrop_path' => $movie->backdrop_path,
                    'release_date' => $movie->release_date?->format('Y-m-d'),
                    'vote_average' => (float) $movie->vote_average,
                    'view_count' => $movie->view_count,
                    'status' => $movie->status,
                    'is_featured' => $isFeatured,
                    'featured_order' => $movie->featured_order,
                    'tmdb_id' => $movie->tmdb_id,
                    'imdb_id' => $movie->imdb_id,
                    'created_at' => $movie->created_at?->format('Y-m-d H:i:s'),
                ];
            };
            
            $mapTVShow = function ($tvShow, $isFeatured = true) {
                return [
                    'id' => $tvShow->id,
                    'type' => 'tvshow',
                    'title' => $tvShow->name,
                    'slug' => $tvShow->slug,
                    'poster_path' => $tvShow->poster_path,
                    'backdrop_path' => $tvShow->backdrop_path,
                    'first_air_date' => $tvShow->first_air_date?->format('Y-m-d'),
                    'vote_average' => (float) $tvShow->vote_average,
                    'view_count' => $tvShow->view_count,
                    'status' => $tvShow->status,
                    'is_featured' => $isFeatured,
                    'featured_order' => $tvShow->featured_order,
                    'tmdb_id' => $tvShow->tmdb_id,
                    'imdb_id' => $tvShow->imdb_id,
                    'number_of_seasons' => $tvShow->number_of_seasons,
                    'created_at' => $tvShow->created_at?->format('Y-m-d H:i:s'),
                ];
            };
            
            if ($type === 'all' || $type === 'movies') {
                if ($featuredFilter === 'all' || $featuredFilter === 'featured') {
                    $featuredMovies = Movie::where('is_featured', true)
                        ->with(['genres', 'category', 'dubbingLanguage'])
                        ->orderBy('featured_order', 'asc')
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->map(fn($movie) => $mapMovie($movie, true));
                }
                
                if ($featuredFilter === 'all' || $featuredFilter === 'unfeatured') {
                    $unfeaturedMovies = Movie::where('is_featured', false)
                        ->with(['genres', 'category', 'dubbingLanguage'])
                        ->orderBy('created_at', 'desc')
                        ->limit(100) // Limit unfeatured to prevent loading too many
                        ->get()
                        ->map(fn($movie) => $mapMovie($movie, false));
                }
            }
            
            if ($type === 'all' || $type === 'tvshows') {
                if ($featuredFilter === 'all' || $featuredFilter === 'featured') {
                    $featuredTVShows = TVShow::where('is_featured', true)
                        ->with(['genres', 'category', 'dubbingLanguage'])
                        ->orderBy('featured_order', 'asc')
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->map(fn($tvShow) => $mapTVShow($tvShow, true));
                }
                
                if ($featuredFilter === 'all' || $featuredFilter === 'unfeatured') {
                    $unfeaturedTVShows = TVShow::where('is_featured', false)
                        ->with(['genres', 'category', 'dubbingLanguage'])
                        ->orderBy('created_at', 'desc')
                        ->limit(100) // Limit unfeatured to prevent loading too many
                        ->get()
                        ->map(fn($tvShow) => $mapTVShow($tvShow, false));
                }
            }
            
            // Combine featured and unfeatured
            $allMovies = collect($featuredMovies)->concat($unfeaturedMovies);
            $allTVShows = collect($featuredTVShows)->concat($unfeaturedTVShows);
            
            // If type is 'all', merge and sort
            if ($type === 'all') {
                $allItems = collect($allMovies)->concat($allTVShows);
                
                // Sort: featured items by order first, then by created_at
                $sortedItems = $allItems->sortBy([
                    ['is_featured', 'desc'], // Featured first
                    ['featured_order', 'asc'],
                    ['created_at', 'desc']
                ])->values()->all();
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'items' => $sortedItems,
                        'featured' => collect($sortedItems)->where('is_featured', true)->values()->all(),
                        'unfeatured' => collect($sortedItems)->where('is_featured', false)->values()->all(),
                        'movies_count' => count($featuredMovies),
                        'tvshows_count' => count($featuredTVShows),
                        'total_count' => count($sortedItems),
                    ]
                ]);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $type === 'movies' ? $allMovies->values()->all() : $allTVShows->values()->all(),
                    'movies' => $type === 'movies' ? $allMovies->values()->all() : [],
                    'tvshows' => $type === 'tvshows' ? $allTVShows->values()->all() : [],
                    'movies_count' => count($featuredMovies),
                    'tvshows_count' => count($featuredTVShows),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch content: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Toggle featured status for a movie or TV show
     */
    public function toggleFeatured(Request $request, $type, $id)
    {
        try {
            $model = $type === 'movie' ? Movie::find($id) : TVShow::find($id);
            
            if (!$model) {
                return response()->json([
                    'success' => false,
                    'message' => ucfirst($type) . ' not found'
                ], 404);
            }
            
            // Toggle featured status
            $wasFeatured = $model->is_featured;
            $newFeaturedStatus = !$wasFeatured;
            
            if ($newFeaturedStatus) {
                // If setting as featured, assign the next available order number
                // Get max order from currently featured items (excluding current item)
                $maxOrder = $type === 'movie' 
                    ? Movie::where('is_featured', true)->whereNotIn('id', [$id])->max('featured_order') ?? 0
                    : TVShow::where('is_featured', true)->whereNotIn('id', [$id])->max('featured_order') ?? 0;
                
                $model->is_featured = true;
                $model->featured_order = $maxOrder + 1;
                $model->save();
            } else {
                // If removing from featured, reorder remaining items first, then clear order
                $oldOrder = $model->featured_order;
                
                // Only reorder if oldOrder is not null and greater than 0
                if ($oldOrder !== null && $oldOrder > 0) {
                    // Reorder remaining featured items of the same type
                    if ($type === 'movie') {
                        Movie::where('is_featured', true)
                            ->whereNotIn('id', [$id])
                            ->whereNotNull('featured_order')
                            ->where('featured_order', '>', $oldOrder)
                            ->decrement('featured_order');
                    } else {
                        TVShow::where('is_featured', true)
                            ->whereNotIn('id', [$id])
                            ->whereNotNull('featured_order')
                            ->where('featured_order', '>', $oldOrder)
                            ->decrement('featured_order');
                    }
                }
                
                $model->is_featured = false;
                $model->featured_order = null;
                $model->save();
            }
            
            return response()->json([
                'success' => true,
                'message' => ucfirst($type) . ' featured status updated',
                'data' => [
                    'id' => $model->id,
                    'is_featured' => $model->is_featured,
                    'featured_order' => $model->featured_order,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update featured status: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update featured order for multiple items
     */
    public function updateOrder(Request $request)
    {
        try {
            $items = $request->input('items', []);
            
            if (empty($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items provided'
                ], 400);
            }
            
            DB::transaction(function () use ($items) {
                foreach ($items as $index => $item) {
                    $order = $index + 1;
                    $type = $item['type'] ?? null;
                    $id = $item['id'] ?? null;
                    
                    if (!$type || !$id) {
                        continue;
                    }
                    
                    if ($type === 'movie') {
                        Movie::where('id', $id)->update([
                            'featured_order' => $order,
                            'is_featured' => true
                        ]);
                    } elseif ($type === 'tvshow') {
                        TVShow::where('id', $id)->update([
                            'featured_order' => $order,
                            'is_featured' => true
                        ]);
                    }
                }
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Featured order updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update featured order: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk toggle featured status
     */
    public function bulkToggle(Request $request)
    {
        try {
            $items = $request->input('items', []);
            $action = $request->input('action', 'toggle'); // 'add', 'remove', or 'toggle'
            
            if (empty($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items provided'
                ], 400);
            }
            
            $updated = 0;
            
            DB::transaction(function () use ($items, $action, &$updated) {
                foreach ($items as $item) {
                    $type = $item['type'] ?? null;
                    $id = $item['id'] ?? null;
                    
                    if (!$type || !$id) {
                        continue;
                    }
                    
                    $model = $type === 'movie' ? Movie::find($id) : TVShow::find($id);
                    
                    if (!$model) {
                        continue;
                    }
                    
                    if ($action === 'add') {
                        if (!$model->is_featured) {
                            $model->is_featured = true;
                            $model->save();
                            
                            $maxOrder = $type === 'movie' 
                                ? Movie::where('is_featured', true)->whereNotIn('id', [$id])->max('featured_order') ?? 0
                                : TVShow::where('is_featured', true)->whereNotIn('id', [$id])->max('featured_order') ?? 0;
                            $model->featured_order = $maxOrder + 1;
                            $model->save();
                            $updated++;
                        }
                    } elseif ($action === 'remove') {
                        if ($model->is_featured) {
                            $oldOrder = $model->featured_order;
                            $model->is_featured = false;
                            $model->featured_order = null;
                            $model->save();
                            
                            // Reorder remaining items only if oldOrder was not null and greater than 0
                            if ($oldOrder !== null && $oldOrder > 0) {
                                if ($type === 'movie') {
                                    Movie::where('is_featured', true)
                                        ->whereNotIn('id', [$id])
                                        ->whereNotNull('featured_order')
                                        ->where('featured_order', '>', $oldOrder)
                                        ->decrement('featured_order');
                                } else {
                                    TVShow::where('is_featured', true)
                                        ->whereNotIn('id', [$id])
                                        ->whereNotNull('featured_order')
                                        ->where('featured_order', '>', $oldOrder)
                                        ->decrement('featured_order');
                                }
                            }
                            $updated++;
                        }
                    } else {
                        // Toggle
                        $wasFeatured = $model->is_featured;
                        $model->is_featured = !$model->is_featured;
                        
                        if ($model->is_featured) {
                            $model->save();
                            
                            $maxOrder = $type === 'movie' 
                                ? Movie::where('is_featured', true)->whereNotIn('id', [$id])->max('featured_order') ?? 0
                                : TVShow::where('is_featured', true)->whereNotIn('id', [$id])->max('featured_order') ?? 0;
                            $model->featured_order = $maxOrder + 1;
                            $model->save();
                            $updated++;
                        } else {
                            $oldOrder = $model->featured_order;
                            $model->featured_order = null;
                            $model->save();
                            
                            // Reorder remaining items only if oldOrder was not null and greater than 0
                            if ($oldOrder !== null && $oldOrder > 0) {
                                if ($type === 'movie') {
                                    Movie::where('is_featured', true)
                                        ->whereNotIn('id', [$id])
                                        ->whereNotNull('featured_order')
                                        ->where('featured_order', '>', $oldOrder)
                                        ->decrement('featured_order');
                                } else {
                                    TVShow::where('is_featured', true)
                                        ->whereNotIn('id', [$id])
                                        ->whereNotNull('featured_order')
                                        ->where('featured_order', '>', $oldOrder)
                                        ->decrement('featured_order');
                                }
                            }
                            $updated++;
                        }
                    }
                }
            });
            
            return response()->json([
                'success' => true,
                'message' => "Updated {$updated} item(s)",
                'data' => [
                    'updated_count' => $updated
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk update: ' . $e->getMessage()
            ], 500);
        }
    }
}
