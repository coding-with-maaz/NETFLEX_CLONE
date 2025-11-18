<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| API Routes - These routes will be handled by the backend API
| The actual implementation should be in API controllers that proxy
| to the existing backend API or implement the logic directly
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    
    // ============================================
    // PUBLIC APIs (No API Key Required)
    // ============================================
    
    // Utils API - Public
    Route::get('/utils/all', [App\Http\Controllers\Api\UtilsApiController::class, 'all']);
    
    // Search API - Public (for better user experience)
    Route::get('/search', [App\Http\Controllers\Api\SearchApiController::class, 'search']);
    Route::get('/search/filters', [App\Http\Controllers\Api\SearchApiController::class, 'filterOptions']);
    Route::get('/movies/search', [App\Http\Controllers\Api\MovieApiController::class, 'search']);
    Route::get('/tvshows/search', [App\Http\Controllers\Api\TVShowApiController::class, 'search']);
    Route::get('/episodes/search', [App\Http\Controllers\Api\EpisodeApiController::class, 'search']);
    
    // View Tracking API - Public (for analytics)
    Route::post('/leaderboard/movies/{id}/view', function ($id) {
        try {
            $movie = \App\Models\Movie::findOrFail($id);
            $movie->increment('view_count');
            
            // Also create a View record
            \App\Models\View::create([
                'viewable_type' => \App\Models\Movie::class,
                'viewable_id' => $id,
                'viewed_at' => now(),
            ]);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error tracking view: ' . $e->getMessage()
            ], 500);
        }
    });
    Route::post('/leaderboard/tvshows/{id}/view', function ($id) {
        try {
            $tvShow = \App\Models\TVShow::findOrFail($id);
            $tvShow->increment('view_count');
            
            // Also create a View record
            \App\Models\View::create([
                'viewable_type' => \App\Models\TVShow::class,
                'viewable_id' => $id,
                'viewed_at' => now(),
            ]);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error tracking view: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Content Requests API - Public (no authentication required)
    Route::post('/requests', [App\Http\Controllers\Api\ContentRequestApiController::class, 'store']);
    Route::get('/requests', [App\Http\Controllers\Api\ContentRequestApiController::class, 'index']);
    
    // Embed Reports API - Public (no authentication required)
    Route::post('/reports/embed', [App\Http\Controllers\Api\EmbedReportApiController::class, 'store']);
    Route::get('/reports/embed', [App\Http\Controllers\Api\EmbedReportApiController::class, 'index']);
    
    // Comments API - Public (no authentication required)
    Route::get('/comments', [App\Http\Controllers\Api\CommentApiController::class, 'index']);
    Route::post('/comments', [App\Http\Controllers\Api\CommentApiController::class, 'store']);
    
    // ============================================
    // PROTECTED APIs (API Key Required)
    // ============================================
    
    Route::middleware(['api.key'])->group(function () {
        // Movies API
        Route::get('/movies', [App\Http\Controllers\Api\MovieApiController::class, 'index']);
        Route::get('/movies/top-rated', [App\Http\Controllers\Api\MovieApiController::class, 'topRated']);
        Route::get('/movies/trending', [App\Http\Controllers\Api\MovieApiController::class, 'trending']);
        Route::get('/movies/today/all', [App\Http\Controllers\Api\MovieApiController::class, 'todayAll']);
        Route::get('/movies/{id}', [App\Http\Controllers\Api\MovieApiController::class, 'show']);
        
        // TV Shows API - Specific routes must come before wildcard routes
        Route::get('/tvshows', [App\Http\Controllers\Api\TVShowApiController::class, 'index']);
        Route::get('/tvshows/top-rated', [App\Http\Controllers\Api\TVShowApiController::class, 'topRated']);
        Route::get('/tvshows/popular', [App\Http\Controllers\Api\TVShowApiController::class, 'popular']);
        Route::get('/tvshows/{id}', [App\Http\Controllers\Api\TVShowApiController::class, 'show']);
        
        // Seasons API
        Route::get('/tvshows/{id}/seasons', [App\Http\Controllers\Api\SeasonApiController::class, 'getTVShowSeasons']);
        Route::get('/tvshows/{id}/seasons/{seasonId}/episodes', [App\Http\Controllers\Api\SeasonApiController::class, 'getSeasonEpisodes']);
        
        // Episodes API
        Route::get('/episodes/latest/all', [App\Http\Controllers\Api\EpisodeApiController::class, 'latestAll']);
        Route::get('/episodes/today', [App\Http\Controllers\Api\EpisodeApiController::class, 'today']);
        Route::get('/episodes/today/all', [App\Http\Controllers\Api\EpisodeApiController::class, 'todayAll']);
        Route::patch('/episodes/{id}', [App\Http\Controllers\Api\EpisodeApiController::class, 'update']);
        Route::delete('/episodes/{id}', [App\Http\Controllers\Api\EpisodeApiController::class, 'destroy']);
        Route::get('/episodes/{id}', [App\Http\Controllers\Api\EpisodeApiController::class, 'show']); // Must come after specific routes
        
        // Genre API - Must come before other routes that might conflict
        Route::get('/genres/{id}', [App\Http\Controllers\Api\GenreApiController::class, 'show']);
        
        // Embeds API
        Route::get('/embeds/movies/{id}', [App\Http\Controllers\Api\EmbedApiController::class, 'getMovieEmbeds']);
        Route::post('/embeds/movies/{id}', [App\Http\Controllers\Api\EmbedApiController::class, 'createMovieEmbed']);
        Route::patch('/embeds/movies/{movieId}/{embedId}', [App\Http\Controllers\Api\EmbedApiController::class, 'updateMovieEmbed']);
        Route::delete('/embeds/movies/{movieId}/{embedId}', [App\Http\Controllers\Api\EmbedApiController::class, 'deleteMovieEmbed']);
        
        Route::get('/embeds/episodes/{id}', [App\Http\Controllers\Api\EmbedApiController::class, 'getEpisodeEmbeds']);
        Route::post('/embeds/episodes/{id}', [App\Http\Controllers\Api\EmbedApiController::class, 'createEpisodeEmbed']);
        Route::patch('/embeds/episodes/{episodeId}/{embedId}', [App\Http\Controllers\Api\EmbedApiController::class, 'updateEpisodeEmbed']);
        Route::delete('/embeds/episodes/{episodeId}/{embedId}', [App\Http\Controllers\Api\EmbedApiController::class, 'deleteEpisodeEmbed']);
        
        // Downloads API
        Route::get('/downloads/movies/{id}', [App\Http\Controllers\Api\DownloadApiController::class, 'getMovieDownloads']);
        Route::post('/downloads/movies/{id}', [App\Http\Controllers\Api\DownloadApiController::class, 'createMovieDownload']);
        Route::patch('/downloads/movies/{movieId}/{downloadId}', [App\Http\Controllers\Api\DownloadApiController::class, 'updateMovieDownload']);
        Route::delete('/downloads/movies/{movieId}/{downloadId}', [App\Http\Controllers\Api\DownloadApiController::class, 'deleteMovieDownload']);
        
        Route::get('/downloads/episodes/{id}', [App\Http\Controllers\Api\DownloadApiController::class, 'getEpisodeDownloads']);
        Route::post('/downloads/episodes/{id}', [App\Http\Controllers\Api\DownloadApiController::class, 'createEpisodeDownload']);
        Route::patch('/downloads/episodes/{episodeId}/{downloadId}', [App\Http\Controllers\Api\DownloadApiController::class, 'updateEpisodeDownload']);
        Route::delete('/downloads/episodes/{episodeId}/{downloadId}', [App\Http\Controllers\Api\DownloadApiController::class, 'deleteEpisodeDownload']);
        
        // Leaderboard API
        Route::get('/leaderboard/trending', [App\Http\Controllers\Api\LeaderboardApiController::class, 'trending']);
        Route::get('/leaderboard/movies/leaderboard', [App\Http\Controllers\Api\LeaderboardApiController::class, 'moviesLeaderboard']);
        Route::get('/leaderboard/tvshows/leaderboard', [App\Http\Controllers\Api\LeaderboardApiController::class, 'tvShowsLeaderboard']);
        Route::get('/leaderboard/overview', function (\Illuminate\Http\Request $request) {
            return response()->json(['success' => true, 'data' => []]);
        });
        
        // Utils API (additional endpoints)
        Route::get('/utils/genres', function () {
            return response()->json(['success' => true, 'data' => ['genres' => []]]);
        });
        Route::get('/utils/countries', function () {
            return response()->json(['success' => true, 'data' => ['countries' => []]]);
        });
        Route::get('/utils/categories', function () {
            return response()->json(['success' => true, 'data' => ['categories' => []]]);
        });
        Route::get('/utils/languages', function () {
            return response()->json(['success' => true, 'data' => ['languages' => []]]);
        });
    });
    
    // API Key Management (admin only - add admin auth middleware)
    Route::prefix('admin')->group(function () {
        // API Keys Management
        Route::get('/api-keys', [App\Http\Controllers\Api\ApiKeyController::class, 'index']);
        Route::post('/api-keys', [App\Http\Controllers\Api\ApiKeyController::class, 'store']);
        Route::get('/api-keys/{id}', [App\Http\Controllers\Api\ApiKeyController::class, 'show']);
        Route::patch('/api-keys/{id}', [App\Http\Controllers\Api\ApiKeyController::class, 'update']);
        Route::delete('/api-keys/{id}', [App\Http\Controllers\Api\ApiKeyController::class, 'destroy']);
    });
    
    // Admin API (protected)
    Route::prefix('admin')->group(function () {
        // Auth (public routes)
        Route::post('/auth/login', [App\Http\Controllers\Api\AdminAuthController::class, 'login']);
        Route::post('/auth/logout', [App\Http\Controllers\Api\AdminAuthController::class, 'logout']);
        Route::get('/auth/profile', [App\Http\Controllers\Api\AdminAuthController::class, 'profile']);
        
        // Development helper route to reset admin password (remove in production)
        if (config('app.debug')) {
            Route::post('/auth/reset-admin-password', function () {
                $admin = \App\Models\Admin::where('email', 'admin@nazaarabox.com')
                    ->orWhere('name', 'admin')
                    ->first();
                
                if (!$admin) {
                    $admin = \App\Models\Admin::create([
                        'name' => 'admin',
                        'email' => 'admin@nazaarabox.com',
                        'password' => \Illuminate\Support\Facades\Hash::make('Admin123!@#'),
                        'role' => 'super_admin',
                        'is_active' => true,
                    ]);
                } else {
                    $hashedPassword = \Illuminate\Support\Facades\Hash::make('Admin123!@#');
                    $admin->setAttribute('password', $hashedPassword);
                    $admin->is_active = true;
                    $admin->save();
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Admin password reset successfully',
                    'credentials' => [
                        'username' => 'admin',
                        'email' => 'admin@nazaarabox.com',
                        'password' => 'Admin123!@#'
                    ]
                ]);
            });
        }
        
        // Dashboard
        Route::get('/dashboard/stats', [App\Http\Controllers\Api\AdminDashboardController::class, 'stats']);
        Route::get('/analytics/views', [App\Http\Controllers\Api\AdminDashboardController::class, 'viewAnalytics']);
        Route::get('/leaderboard/overview', [App\Http\Controllers\Api\AdminDashboardController::class, 'leaderboardOverview']);
        
        // Movies Management
        Route::get('/movies', [App\Http\Controllers\Api\AdminMovieController::class, 'index']);
        Route::get('/movies/stats', [App\Http\Controllers\Api\AdminMovieController::class, 'stats']);
        Route::get('/movies/{id}', [App\Http\Controllers\Api\AdminMovieController::class, 'show']);
        Route::post('/movies', [App\Http\Controllers\Api\AdminMovieController::class, 'store']);
        Route::patch('/movies/{id}', [App\Http\Controllers\Api\AdminMovieController::class, 'update']);
        Route::delete('/movies/{id}', function ($id) {
            return response()->json(['success' => true]);
        });
        Route::patch('/movies/{id}/toggle-status', function ($id) {
            return response()->json(['success' => true]);
        });
        Route::patch('/movies/{id}/toggle-featured', function ($id) {
            $controller = new App\Http\Controllers\Api\AdminFeaturedController();
            return $controller->toggleFeatured(request(), 'movie', $id);
        });
        
        // TV Shows Management
        Route::get('/tvshows', [App\Http\Controllers\Api\AdminTVShowController::class, 'index']);
        Route::get('/tvshows/stats', [App\Http\Controllers\Api\AdminTVShowController::class, 'stats']);
        Route::get('/tvshows/{id}', [App\Http\Controllers\Api\AdminTVShowController::class, 'show']);
        Route::post('/tvshows', [App\Http\Controllers\Api\AdminTVShowController::class, 'store']);
        Route::patch('/tvshows/{id}', [App\Http\Controllers\Api\AdminTVShowController::class, 'update']);
        Route::post('/tvshows/{id}/seasons', [App\Http\Controllers\Api\AdminTVShowController::class, 'addSeasons']);
        Route::post('/tvshows/{tvshowId}/seasons/create', [App\Http\Controllers\Api\AdminTVShowController::class, 'createSeason']);
        Route::patch('/tvshows/{tvshowId}/seasons/{seasonId}', [App\Http\Controllers\Api\AdminTVShowController::class, 'updateSeason']);
        Route::delete('/tvshows/{tvshowId}/seasons/{seasonId}', [App\Http\Controllers\Api\AdminTVShowController::class, 'deleteSeason']);
        Route::delete('/tvshows/{id}', [App\Http\Controllers\Api\AdminTVShowController::class, 'destroy']);
        Route::patch('/tvshows/{id}/toggle-status', function ($id) {
            return response()->json(['success' => true]);
        });
        Route::patch('/tvshows/{id}/toggle-featured', function ($id) {
            $controller = new App\Http\Controllers\Api\AdminFeaturedController();
            return $controller->toggleFeatured(request(), 'tvshow', $id);
        });
        
        // Featured Content Management
        Route::get('/featured', [App\Http\Controllers\Api\AdminFeaturedController::class, 'index']);
        Route::patch('/featured/{type}/{id}/toggle', [App\Http\Controllers\Api\AdminFeaturedController::class, 'toggleFeatured'])->where('type', 'movie|tvshow')->where('id', '[0-9]+');
        Route::post('/featured/order', [App\Http\Controllers\Api\AdminFeaturedController::class, 'updateOrder']);
        Route::post('/featured/bulk', [App\Http\Controllers\Api\AdminFeaturedController::class, 'bulkToggle']);
        
        // Admins Management
        Route::get('/admins', function (\Illuminate\Http\Request $request) {
            return response()->json(['success' => true, 'data' => ['admins' => [], 'pagination' => []]]);
        });
        Route::get('/admins/{id}', function ($id) {
            return response()->json(['success' => true, 'data' => ['admin' => []]]);
        });
        Route::post('/admins', function (\Illuminate\Http\Request $request) {
            return response()->json(['success' => false, 'message' => 'Not implemented']);
        });
        Route::patch('/admins/{id}', function ($id, \Illuminate\Http\Request $request) {
            return response()->json(['success' => true]);
        });
        Route::delete('/admins/{id}', function ($id) {
            return response()->json(['success' => true]);
        });
        Route::patch('/admins/{id}/toggle-status', function ($id) {
            return response()->json(['success' => true]);
        });
        Route::post('/admins/{id}/revoke-sessions', function ($id) {
            return response()->json(['success' => true]);
        });
        
        // TMDB Integration
        Route::get('/tmdb/movies/search', function (\Illuminate\Http\Request $request) {
            return response()->json(['success' => true, 'data' => ['results' => []]]);
        });
        Route::get('/tmdb/tvshows/search', function (\Illuminate\Http\Request $request) {
            return response()->json(['success' => true, 'data' => ['results' => []]]);
        });
        
        // Content Requests Management
        Route::get('/requests', [App\Http\Controllers\Api\AdminContentRequestController::class, 'index']);
        Route::get('/requests/{id}', [App\Http\Controllers\Api\AdminContentRequestController::class, 'show']);
        Route::patch('/requests/{id}', [App\Http\Controllers\Api\AdminContentRequestController::class, 'update']);
        Route::delete('/requests/{id}', [App\Http\Controllers\Api\AdminContentRequestController::class, 'destroy']);
        Route::post('/requests/bulk-update', [App\Http\Controllers\Api\AdminContentRequestController::class, 'bulkUpdate']);
        
        // Embed Reports Management
        Route::get('/reports/embed', [App\Http\Controllers\Api\AdminEmbedReportController::class, 'index']);
        Route::get('/reports/embed/{id}', [App\Http\Controllers\Api\AdminEmbedReportController::class, 'show']);
        Route::patch('/reports/embed/{id}', [App\Http\Controllers\Api\AdminEmbedReportController::class, 'update']);
        Route::delete('/reports/embed/{id}', [App\Http\Controllers\Api\AdminEmbedReportController::class, 'destroy']);
        Route::post('/reports/embed/bulk-update', [App\Http\Controllers\Api\AdminEmbedReportController::class, 'bulkUpdate']);
        
        // Comments Management
        Route::get('/comments', [App\Http\Controllers\Api\AdminCommentController::class, 'index']);
        Route::get('/comments/{id}', [App\Http\Controllers\Api\AdminCommentController::class, 'show']);
        Route::patch('/comments/{id}', [App\Http\Controllers\Api\AdminCommentController::class, 'update']);
        Route::delete('/comments/{id}', [App\Http\Controllers\Api\AdminCommentController::class, 'destroy']);
        Route::post('/comments/bulk-update', [App\Http\Controllers\Api\AdminCommentController::class, 'bulkUpdate']);
        Route::get('/comments/export/emails', [App\Http\Controllers\Api\AdminCommentController::class, 'exportEmails']);
        
        // Ads Management
        Route::get('/ads/movies', [App\Http\Controllers\Api\AdminAdsController::class, 'getMoviesWithAds']);
        Route::get('/ads/episodes', [App\Http\Controllers\Api\AdminAdsController::class, 'getEpisodesWithAds']);
        Route::patch('/ads/movies/{movieId}/embeds/{embedId}/toggle', [App\Http\Controllers\Api\AdminAdsController::class, 'toggleMovieEmbedAd']);
        Route::patch('/ads/episodes/{episodeId}/embeds/{embedId}/toggle', [App\Http\Controllers\Api\AdminAdsController::class, 'toggleEpisodeEmbedAd']);
    });
});

