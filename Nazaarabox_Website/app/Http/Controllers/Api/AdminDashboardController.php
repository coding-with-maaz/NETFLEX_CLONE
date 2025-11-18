<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Movie;
use App\Models\TVShow;
use App\Models\Season;
use App\Models\Episode;
use App\Models\View;
use App\Models\MovieEmbed;
use App\Models\MovieDownload;
use App\Models\EpisodeEmbed;
use App\Models\EpisodeDownload;
use App\Models\ContentRequest;
use App\Models\EmbedReport;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(Request $request)
    {
        try {
            // Admin stats
            $adminStats = [
                'total' => Admin::count(),
                'active' => Admin::where('is_active', true)->count(),
            ];

            // Movie stats
            $movieStats = [
                'total' => Movie::count(),
                'active' => Movie::where('status', 'active')->count(),
                'inactive' => Movie::where('status', '!=', 'active')->count(),
                'pending' => Movie::where('status', 'pending')->count(),
                'featured' => Movie::where('is_featured', true)->count(),
                'embeds' => MovieEmbed::where('is_active', true)->count(),
                'downloads' => MovieDownload::where('is_active', true)->count(),
            ];

            // TV Show stats
            $tvShowStats = [
                'total' => TVShow::count(),
                'active' => TVShow::where('status', 'active')->count(),
                'inactive' => TVShow::where('status', '!=', 'active')->count(),
                'pending' => TVShow::where('status', 'pending')->count(),
                'featured' => TVShow::where('is_featured', true)->count(),
                'embeds' => EpisodeEmbed::where('is_active', true)->count(), // TV shows use episode embeds
                'downloads' => EpisodeDownload::where('is_active', true)->count(), // TV shows use episode downloads
                'seasons' => Season::count(),
                'episodes' => Episode::count(),
            ];

            // Content Requests stats
            $requestStats = [
                'total' => ContentRequest::count(),
                'pending' => ContentRequest::where('status', 'pending')->count(),
                'approved' => ContentRequest::where('status', 'approved')->count(),
                'rejected' => ContentRequest::where('status', 'rejected')->count(),
                'completed' => ContentRequest::where('status', 'completed')->count(),
                'movies' => ContentRequest::where('type', 'movie')->count(),
                'tvshows' => ContentRequest::where('type', 'tvshow')->count(),
            ];

            // Embed Reports stats
            $reportStats = [
                'total' => EmbedReport::count(),
                'pending' => EmbedReport::where('status', 'pending')->count(),
                'reviewed' => EmbedReport::where('status', 'reviewed')->count(),
                'fixed' => EmbedReport::where('status', 'fixed')->count(),
                'dismissed' => EmbedReport::where('status', 'dismissed')->count(),
                'movies' => EmbedReport::where('content_type', 'movie')->count(),
                'episodes' => EmbedReport::where('content_type', 'episode')->count(),
            ];

            // Recent activity
            $recentMovies = Movie::latest('created_at')
                ->limit(5)
                ->get()
                ->map(function($movie) {
                    return [
                        'id' => $movie->id,
                        'title' => $movie->title,
                        'status' => $movie->status,
                        'release_date' => $movie->release_date?->format('Y-m-d'),
                    ];
                });

            $recentTVShows = TVShow::latest('created_at')
                ->limit(5)
                ->get()
                ->map(function($tvShow) {
                    return [
                        'id' => $tvShow->id,
                        'name' => $tvShow->name,
                        'status' => $tvShow->status,
                        'first_air_date' => $tvShow->first_air_date?->format('Y-m-d'),
                    ];
                });

            // Recent requests
            $recentRequests = ContentRequest::latest('requested_at')
                ->limit(5)
                ->get()
                ->map(function($request) {
                    return [
                        'id' => $request->id,
                        'type' => $request->type,
                        'title' => $request->title,
                        'status' => $request->status,
                        'request_count' => $request->request_count,
                        'requested_at' => $request->requested_at?->format('Y-m-d H:i'),
                    ];
                });

            // Recent reports
            $recentReports = EmbedReport::latest('reported_at')
                ->limit(5)
                ->get()
                ->map(function($report) {
                    return [
                        'id' => $report->id,
                        'content_type' => $report->content_type,
                        'content_id' => $report->content_id,
                        'report_type' => $report->report_type,
                        'status' => $report->status,
                        'report_count' => $report->report_count,
                        'reported_at' => $report->reported_at?->format('Y-m-d H:i'),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'admin' => $adminStats,
                    'movies' => $movieStats,
                    'tvShows' => $tvShowStats,
                    'requests' => $requestStats,
                    'reports' => $reportStats,
                    'recentActivity' => [
                        'movies' => $recentMovies,
                        'tvShows' => $recentTVShows,
                        'requests' => $recentRequests,
                        'reports' => $recentReports,
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get view analytics
     */
    public function viewAnalytics(Request $request)
    {
        try {
            // Total views (all time)
            $totalMovieViews = View::where('viewable_type', Movie::class)->count();
            $totalTVShowViews = View::where('viewable_type', TVShow::class)->count();

            // Today's views
            $todayMovieViews = View::where('viewable_type', Movie::class)
                ->whereDate('viewed_at', today())
                ->count();
            $todayTVShowViews = View::where('viewable_type', TVShow::class)
                ->whereDate('viewed_at', today())
                ->count();

            // This week's views
            $weekMovieViews = View::where('viewable_type', Movie::class)
                ->where('viewed_at', '>=', now()->startOfWeek())
                ->count();
            $weekTVShowViews = View::where('viewable_type', TVShow::class)
                ->where('viewed_at', '>=', now()->startOfWeek())
                ->count();

            // This month's views
            $monthMovieViews = View::where('viewable_type', Movie::class)
                ->where('viewed_at', '>=', now()->startOfMonth())
                ->count();
            $monthTVShowViews = View::where('viewable_type', TVShow::class)
                ->where('viewed_at', '>=', now()->startOfMonth())
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => [
                        'combined' => $totalMovieViews + $totalTVShowViews,
                        'movies' => $totalMovieViews,
                        'tvShows' => $totalTVShowViews,
                    ],
                    'today' => [
                        'combined' => $todayMovieViews + $todayTVShowViews,
                        'movies' => $todayMovieViews,
                        'tvShows' => $todayTVShowViews,
                    ],
                    'week' => [
                        'combined' => $weekMovieViews + $weekTVShowViews,
                        'movies' => $weekMovieViews,
                        'tvShows' => $weekTVShowViews,
                    ],
                    'month' => [
                        'combined' => $monthMovieViews + $monthTVShowViews,
                        'movies' => $monthMovieViews,
                        'tvShows' => $monthTVShowViews,
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching view analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get leaderboard overview
     */
    public function leaderboardOverview(Request $request)
    {
        try {
            $limit = $request->get('limit', 5);
            
            // Get top movies for different periods
            $moviePeriods = ['today', 'week', 'month', 'overall'];
            $movies = [];
            
            foreach ($moviePeriods as $period) {
                $query = Movie::where('status', 'active');
                
                $dateFilter = match($period) {
                    'today' => now()->startOfDay(),
                    'week' => now()->startOfWeek(),
                    'month' => now()->startOfMonth(),
                    'overall' => null,
                    default => null,
                };
                
                if ($dateFilter) {
                    $query->where('updated_at', '>=', $dateFilter);
                }
                
                $movies[$period] = $query->orderBy('view_count', 'desc')
                    ->orderBy('popularity', 'desc')
                    ->limit($limit)
                    ->get()
                    ->map(function($movie) {
                        $posterPath = $movie->poster_path;
                        if ($posterPath && !str_starts_with($posterPath, 'http')) {
                            $posterPath = str_starts_with($posterPath, '/') ? $posterPath : '/' . $posterPath;
                        }
                        
                        return [
                            'id' => $movie->id,
                            'title' => $movie->title,
                            'poster_path' => $posterPath,
                            'vote_average' => (float) $movie->vote_average,
                            'view_count' => $movie->view_count,
                        ];
                    });
            }

            // Get top TV shows for different periods
            $tvShowPeriods = ['today', 'week', 'month', 'overall'];
            $tvShows = [];
            
            foreach ($tvShowPeriods as $period) {
                $query = TVShow::where('status', 'active');
                
                $dateFilter = match($period) {
                    'today' => now()->startOfDay(),
                    'week' => now()->startOfWeek(),
                    'month' => now()->startOfMonth(),
                    'overall' => null,
                    default => null,
                };
                
                if ($dateFilter) {
                    $query->where('updated_at', '>=', $dateFilter);
                }
                
                $tvShows[$period] = $query->orderBy('view_count', 'desc')
                    ->orderBy('popularity', 'desc')
                    ->limit($limit)
                    ->get()
                    ->map(function($tvShow) {
                        $posterPath = $tvShow->poster_path;
                        if ($posterPath && !str_starts_with($posterPath, 'http')) {
                            $posterPath = str_starts_with($posterPath, '/') ? $posterPath : '/' . $posterPath;
                        }
                        
                        return [
                            'id' => $tvShow->id,
                            'name' => $tvShow->name,
                            'poster_path' => $posterPath,
                            'vote_average' => (float) $tvShow->vote_average,
                            'view_count' => $tvShow->view_count,
                        ];
                    });
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'movies' => $movies,
                    'tvShows' => $tvShows,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching leaderboard: ' . $e->getMessage()
            ], 500);
        }
    }
}

