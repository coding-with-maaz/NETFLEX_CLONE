<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\TVShowController;
use App\Http\Controllers\TrendingController;
use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ContentRequestController;

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Movies
Route::prefix('movies')->name('movies.')->group(function () {
    Route::get('/', [MovieController::class, 'index'])->name('index');
    Route::get('/trending', [MovieController::class, 'trending'])->name('trending');
    Route::get('/top-rated', [MovieController::class, 'topRated'])->name('top-rated');
    Route::get('/today', [MovieController::class, 'today'])->name('today');
    Route::get('/{id}', [MovieController::class, 'show'])->name('show');
});

// TV Shows - Specific routes must come before wildcard routes
Route::prefix('tvshows')->name('tvshows.')->group(function () {
    Route::get('/', [TVShowController::class, 'index'])->name('index');
    Route::get('/popular', [TVShowController::class, 'popular'])->name('popular');
    Route::get('/top-rated', [TVShowController::class, 'topRated'])->name('top-rated');
    // Today's Episodes route must come before {id} route to avoid conflicts
    Route::get('/today', [EpisodeController::class, 'today'])->name('today');
    Route::get('/{id}', [TVShowController::class, 'show'])->name('show');
});

// Trending
Route::get('/trending', [TrendingController::class, 'index'])->name('trending');

// Episodes - Also support /episodes/today for compatibility
Route::prefix('episodes')->name('episodes.')->group(function () {
    Route::get('/today', [EpisodeController::class, 'today'])->name('today');
});

// Search
Route::get('/search', [SearchController::class, 'index'])->name('search');

// Genre Detail
Route::get('/genre/{id}/{name?}', [GenreController::class, 'show'])->name('genre.show');

// Content Request Page
Route::get('/request', [ContentRequestController::class, 'index'])->name('request.index');
Route::get('/request-content', [ContentRequestController::class, 'index'])->name('request-content'); // Alias

// Download Page
Route::get('/download', [App\Http\Controllers\DownloadController::class, 'index'])->name('download');
Route::get('/download/apk', [App\Http\Controllers\DownloadController::class, 'downloadApk'])->name('download.apk');

// Robots.txt
Route::get('/robots.txt', [App\Http\Controllers\RobotsController::class, 'index'])->name('robots');

// Sitemaps
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap/home', [App\Http\Controllers\SitemapController::class, 'home'])->name('sitemap.home');
Route::get('/sitemap/movies', [App\Http\Controllers\SitemapController::class, 'movies'])->name('sitemap.movies');
Route::get('/sitemap/movies/index', [App\Http\Controllers\SitemapController::class, 'moviesIndex'])->name('sitemap.movies.index');
Route::get('/sitemap/movies/top-rated', [App\Http\Controllers\SitemapController::class, 'moviesTopRated'])->name('sitemap.movies.top-rated');
Route::get('/sitemap/movies/trending', [App\Http\Controllers\SitemapController::class, 'moviesTrending'])->name('sitemap.movies.trending');
Route::get('/sitemap/movies/today', [App\Http\Controllers\SitemapController::class, 'moviesToday'])->name('sitemap.movies.today');
Route::get('/sitemap/tvshows', [App\Http\Controllers\SitemapController::class, 'tvShows'])->name('sitemap.tvshows');
Route::get('/sitemap/tvshows/index', [App\Http\Controllers\SitemapController::class, 'tvShowsIndex'])->name('sitemap.tvshows.index');
Route::get('/sitemap/tvshows/popular', [App\Http\Controllers\SitemapController::class, 'tvShowsPopular'])->name('sitemap.tvshows.popular');
Route::get('/sitemap/tvshows/top-rated', [App\Http\Controllers\SitemapController::class, 'tvShowsTopRated'])->name('sitemap.tvshows.top-rated');
Route::get('/sitemap/trending', [App\Http\Controllers\SitemapController::class, 'trending'])->name('sitemap.trending');
Route::get('/sitemap/search', [App\Http\Controllers\SitemapController::class, 'search'])->name('sitemap.search');
Route::get('/sitemap/episodes/today', [App\Http\Controllers\SitemapController::class, 'episodesToday'])->name('sitemap.episodes.today');
Route::get('/sitemap/genres', [App\Http\Controllers\SitemapController::class, 'genres'])->name('sitemap.genres');

// Legacy routes for compatibility
Route::get('/movie/{id}', [MovieController::class, 'show'])->name('movie.show');
Route::get('/tvshow/{id}', [TVShowController::class, 'show'])->name('tvshow.show');

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [App\Http\Controllers\AdminController::class, 'login'])->name('login');
    Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/admins', [App\Http\Controllers\AdminController::class, 'management'])->name('management');
    
    Route::prefix('movies')->name('movies.')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminController::class, 'movies'])->name('index');
        Route::get('/create', [App\Http\Controllers\AdminController::class, 'movieCreate'])->name('create');
        Route::get('/{id}', [App\Http\Controllers\AdminController::class, 'movieDetail'])->name('detail');
    });
    
    Route::prefix('tvshows')->name('tvshows.')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminController::class, 'tvshows'])->name('index');
        Route::get('/create', [App\Http\Controllers\AdminController::class, 'tvshowCreate'])->name('create');
        Route::get('/{id}/seasons', [App\Http\Controllers\AdminController::class, 'tvshowSeasons'])->name('seasons');
        Route::get('/{id}', [App\Http\Controllers\AdminController::class, 'tvshowDetail'])->name('detail');
    });
    
    Route::get('/featured', [App\Http\Controllers\AdminController::class, 'featured'])->name('featured');
    Route::get('/requests', [App\Http\Controllers\AdminController::class, 'requests'])->name('requests');
    Route::get('/reports', [App\Http\Controllers\AdminController::class, 'reports'])->name('reports');
    Route::get('/comments', [App\Http\Controllers\AdminController::class, 'comments'])->name('comments');
    Route::get('/ads', [App\Http\Controllers\AdminController::class, 'ads'])->name('ads');
});
