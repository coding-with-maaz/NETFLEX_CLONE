<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\TVShow;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class SitemapController extends Controller
{
    /**
     * Main sitemap index that lists all sitemaps
     */
    public function index()
    {
        $baseUrl = url('/');
        $lastmod = now()->toAtomString();

        $sitemaps = [
            [
                'loc' => route('sitemap.home'),
                'lastmod' => $lastmod,
            ],
            [
                'loc' => route('sitemap.movies'),
                'lastmod' => $lastmod,
            ],
            [
                'loc' => route('sitemap.movies.index'),
                'lastmod' => $lastmod,
            ],
            [
                'loc' => route('sitemap.movies.top-rated'),
                'lastmod' => $lastmod,
            ],
            [
                'loc' => route('sitemap.movies.trending'),
                'lastmod' => $lastmod,
            ],
            [
                'loc' => route('sitemap.movies.today'),
                'lastmod' => $lastmod,
            ],
            [
                'loc' => route('sitemap.tvshows'),
                'lastmod' => $lastmod,
            ],
            [
                'loc' => route('sitemap.tvshows.index'),
                'lastmod' => $lastmod,
            ],
            [
                'loc' => route('sitemap.tvshows.popular'),
                'lastmod' => $lastmod,
            ],
            [
                'loc' => route('sitemap.tvshows.top-rated'),
                'lastmod' => $lastmod,
            ],
            [
                'loc' => route('sitemap.trending'),
                'lastmod' => $lastmod,
            ],
            [
                'loc' => route('sitemap.search'),
                'lastmod' => $lastmod,
            ],
            [
                'loc' => route('sitemap.episodes.today'),
                'lastmod' => $lastmod,
            ],
            [
                'loc' => route('sitemap.genres'),
                'lastmod' => $lastmod,
            ],
        ];

        return response()->view('sitemap.index', [
            'sitemaps' => $sitemaps,
        ])->header('Content-Type', 'application/xml');
    }

    /**
     * Home page sitemap
     */
    public function home()
    {
        $urls = [
            [
                'loc' => route('home'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
        ];

        return response()->view('sitemap.sitemap', [
            'urls' => $urls,
        ])->header('Content-Type', 'application/xml');
    }

    /**
     * Movies listing sitemap
     */
    public function moviesIndex()
    {
        $urls = [
            [
                'loc' => route('movies.index'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
        ];

        return response()->view('sitemap.sitemap', [
            'urls' => $urls,
        ])->header('Content-Type', 'application/xml');
    }

    /**
     * Top rated movies sitemap
     */
    public function moviesTopRated()
    {
        $urls = [
            [
                'loc' => route('movies.top-rated'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
        ];

        return response()->view('sitemap.sitemap', [
            'urls' => $urls,
        ])->header('Content-Type', 'application/xml');
    }

    /**
     * Trending movies sitemap
     */
    public function moviesTrending()
    {
        $urls = [
            [
                'loc' => route('movies.trending'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
        ];

        return response()->view('sitemap.sitemap', [
            'urls' => $urls,
        ])->header('Content-Type', 'application/xml');
    }

    /**
     * Today's movies sitemap
     */
    public function moviesToday()
    {
        $urls = [
            [
                'loc' => route('movies.today'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
        ];

        return response()->view('sitemap.sitemap', [
            'urls' => $urls,
        ])->header('Content-Type', 'application/xml');
    }

    /**
     * All movies sitemap (individual movie pages)
     */
    public function movies()
    {
        $movies = Movie::where('status', 'active')
            ->select('id', 'slug', 'updated_at', 'release_date', 'is_featured')
            ->orderBy('updated_at', 'desc')
            ->get();

        $urls = [];
        foreach ($movies as $movie) {
            $urls[] = [
                'loc' => route('movies.show', $movie->id),
                'lastmod' => $movie->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => $movie->is_featured ? '0.9' : '0.7',
            ];
        }

        return response()->view('sitemap.sitemap', [
            'urls' => $urls,
        ])->header('Content-Type', 'application/xml');
    }

    /**
     * TV Shows listing sitemap
     */
    public function tvShowsIndex()
    {
        $urls = [
            [
                'loc' => route('tvshows.index'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
        ];

        return response()->view('sitemap.sitemap', [
            'urls' => $urls,
        ])->header('Content-Type', 'application/xml');
    }

    /**
     * Popular TV shows sitemap
     */
    public function tvShowsPopular()
    {
        $urls = [
            [
                'loc' => route('tvshows.popular'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
        ];

        return response()->view('sitemap.sitemap', [
            'urls' => $urls,
        ])->header('Content-Type', 'application/xml');
    }

    /**
     * Top rated TV shows sitemap
     */
    public function tvShowsTopRated()
    {
        $urls = [
            [
                'loc' => route('tvshows.top-rated'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
        ];

        return response()->view('sitemap.sitemap', [
            'urls' => $urls,
        ])->header('Content-Type', 'application/xml');
    }

    /**
     * All TV shows sitemap (individual TV show pages)
     */
    public function tvShows()
    {
        $tvShows = TVShow::where('status', 'active')
            ->select('id', 'slug', 'updated_at', 'is_featured')
            ->orderBy('updated_at', 'desc')
            ->get();

        $urls = [];
        foreach ($tvShows as $tvShow) {
            $urls[] = [
                'loc' => route('tvshows.show', $tvShow->id),
                'lastmod' => $tvShow->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => $tvShow->is_featured ? '0.9' : '0.7',
            ];
        }

        return response()->view('sitemap.sitemap', [
            'urls' => $urls,
        ])->header('Content-Type', 'application/xml');
    }

    /**
     * Trending page sitemap
     */
    public function trending()
    {
        $urls = [
            [
                'loc' => route('trending'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
        ];

        return response()->view('sitemap.sitemap', [
            'urls' => $urls,
        ])->header('Content-Type', 'application/xml');
    }

    /**
     * Search page sitemap
     */
    public function search()
    {
        $urls = [
            [
                'loc' => route('search'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ],
        ];

        return response()->view('sitemap.sitemap', [
            'urls' => $urls,
        ])->header('Content-Type', 'application/xml');
    }

    /**
     * Today's episodes sitemap
     */
    public function episodesToday()
    {
        $urls = [
            [
                'loc' => route('episodes.today'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
        ];

        return response()->view('sitemap.sitemap', [
            'urls' => $urls,
        ])->header('Content-Type', 'application/xml');
    }

    /**
     * Genres sitemap
     */
    public function genres()
    {
        $genres = Genre::where('is_active', true)->orderBy('name')->get();

        $urls = [];
        foreach ($genres as $genre) {
            $urls[] = [
                'loc' => route('genre.show', ['id' => $genre->id, 'name' => $genre->slug ?? 'genre']),
                'lastmod' => ($genre->updated_at ?? now())->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];
        }

        return response()->view('sitemap.sitemap', [
            'urls' => $urls,
        ])->header('Content-Type', 'application/xml');
    }
}

