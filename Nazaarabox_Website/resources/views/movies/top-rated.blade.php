@extends('layouts.app')

@section('title', 'Top Rated Movies - Nazaara Box')

@section('seo_title', 'Top Rated Movies - Highest Rated Films - Nazaara Box')
@section('seo_description', 'Watch the highest rated movies of all time on Nazaara Box. Discover critically acclaimed films and award-winning cinema.')
@section('seo_type', 'website')
@section('seo_url', route('movies.top-rated'))

@push('styles')
<style>
    /* Adjust padding for fixed header */
    .top-rated-movies-page-wrapper {
        padding-top: 64px; /* Mobile header height */
    }

    @media (min-width: 768px) {
        .top-rated-movies-page-wrapper {
            padding-top: 80px; /* Desktop header height */
        }
    }

    /* Rank Badge Styles - Matching Frontend */
    .rank-badge {
        position: absolute;
        top: -12px;
        left: -12px;
        z-index: 10;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 18px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        border: 4px solid black;
    }

    .rank-badge.rank-1 {
        background: linear-gradient(to bottom right, #facc15, #eab308);
        color: black;
    }

    .rank-badge.rank-2 {
        background: linear-gradient(to bottom right, #d1d5db, #9ca3af);
        color: black;
    }

    .rank-badge.rank-3 {
        background: linear-gradient(to bottom right, #fb923c, #ea580c);
        color: white;
    }

    .rank-badge.rank-top10 {
        background: linear-gradient(to bottom right, #16a34a, #15803d);
        color: white;
    }

    .rank-badge.rank-default {
        background: linear-gradient(to bottom right, #374151, #111827);
        color: white;
    }

    /* Card Styles */
    .movie-card {
        position: relative;
        cursor: pointer;
        transform: scale(1);
        transition: all 0.3s;
    }

    .movie-card:hover {
        transform: scale(1.05);
    }

    .movie-card-inner {
        position: relative;
        background-color: #111827; /* bg-gray-900 */
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #1f2937; /* border-gray-800 */
        transition: border-color 0.2s;
    }

    .movie-card:hover .movie-card-inner {
        border-color: #16a34a; /* border-green-500 */
    }

    .movie-poster {
        position: relative;
        aspect-ratio: 2/3;
        overflow: hidden;
        background-color: #1f2937; /* bg-gray-800 */
    }

    .movie-poster img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .rating-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        background-color: #eab308; /* bg-yellow-500 */
        color: black;
        padding: 4px 8px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
        font-weight: bold;
        font-size: 14px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }

    .hover-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,1), rgba(0,0,0,0.5), transparent);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .movie-card:hover .hover-overlay {
        opacity: 1;
    }

    .hover-title {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 12px;
    }

    .hover-title h3 {
        color: white;
        font-weight: 600;
        font-size: 14px;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-black top-rated-movies-page-wrapper pb-12">
    <div class="container mx-auto px-4 md:px-8 lg:px-16">
        <!-- Loading State -->
        <div id="loading-state" class="min-h-screen bg-black flex items-center justify-center" style="padding-top: 64px;">
            <div class="spinner"></div>
        </div>

        <!-- Content -->
        <div id="movies-content" style="display: none;">
            <!-- Header -->
            <div class="mb-8">
                <button
                    onclick="window.history.back()"
                    class="flex items-center space-x-2 text-gray-400 hover:text-white transition-colors mb-6"
                >
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Back</span>
                </button>

                <div class="flex items-center space-x-4 mb-6">
                    <div class="p-3 bg-green-600 rounded-lg">
                        <svg fill="currentColor" viewBox="0 0 24 24" class="h-8 w-8 text-white">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold text-white">
                            Top Rated Movies
                        </h1>
                        <p class="text-gray-400 mt-1">
                            Highest rated movies with 7.0+ rating
                        </p>
                    </div>
                </div>

                <!-- Movie Count -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2 bg-gray-800 px-4 py-2 rounded-lg">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5 text-green-500">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                        </svg>
                        <span id="movie-count" class="text-white font-semibold">0</span>
                        <span class="text-gray-400">Total Movies</span>
                    </div>
                </div>
            </div>

            <!-- Movies Grid -->
            <div id="movies-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4"></div>

            <!-- Empty State -->
            <div id="empty-state" class="text-center py-20" style="display: none;">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-800 rounded-full mb-6">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-10 w-10 text-gray-600">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">
                    No Movies Found
                </h2>
            </div>

            <!-- Pagination -->
            <div id="pagination" class="flex items-center justify-center space-x-2 mt-12"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // State management - Matching Frontend TopRatedMoviesPage.jsx
    let movies = [];
    let loading = false;
    let page = 1;
    let pagination = null;

    // Get rank badge color - Matching Frontend
    function getRankBadgeColor(rank) {
        if (rank === 1) return 'rank-1';
        if (rank === 2) return 'rank-2';
        if (rank === 3) return 'rank-3';
        if (rank <= 10) return 'rank-top10';
        return 'rank-default';
    }

    // Fetch top rated movies - Matching Frontend
    async function fetchTopRatedMovies() {
        loading = true;
        document.getElementById('loading-state').style.display = 'flex';
        document.getElementById('movies-content').style.display = 'none';

        try {
            const response = await apiFetch(`${API_BASE_URL}/movies?sort_by=rating&min_rating=7.0&min_votes=100&page=${page}&limit=24`);
            const result = await response.json();
            let allMovies = result.data?.movies || result.data || [];
            
            // Filter out movies with 18+ genre
            movies = allMovies.filter(movie => {
                if (!movie.genres || !Array.isArray(movie.genres)) return true;
                return !movie.genres.some(genre => {
                    const genreName = (genre.name || genre || '').toLowerCase();
                    return genreName.includes('18+') || genreName.includes('18');
                });
            });
            
            pagination = result.data?.pagination || result.pagination || null;
            
            renderMovies();
        } catch (error) {
            console.error('Error fetching top rated movies:', error);
            movies = [];
            pagination = null;
            renderMovies();
        } finally {
            loading = false;
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('movies-content').style.display = 'block';
        }
    }

    // Render movies - Matching Frontend
    function renderMovies() {
        const grid = document.getElementById('movies-grid');
        const emptyState = document.getElementById('empty-state');
        const movieCount = document.getElementById('movie-count');

        grid.innerHTML = '';

        if (movieCount && pagination) {
            movieCount.textContent = pagination.total_items || pagination.total || 0;
        }

        if (movies.length === 0) {
            emptyState.style.display = 'block';
            renderPagination();
            return;
        }

        emptyState.style.display = 'none';

        movies.forEach((movie, index) => {
            const rank = (page - 1) * 24 + index + 1;
            const card = createMovieCard(movie, rank);
            grid.appendChild(card);
        });

        renderPagination();
    }

    // Create movie card - Matching Frontend
    function createMovieCard(movie, rank) {
        const cardWrapper = document.createElement('div');
        cardWrapper.className = 'relative group cursor-pointer transform transition-all duration-300 hover:scale-105 movie-card';
        cardWrapper.onclick = () => {
            window.location.href = `/movies/${movie.id}`;
        };

        // Rank Badge
        const rankBadge = document.createElement('div');
        rankBadge.className = `rank-badge ${getRankBadgeColor(rank)}`;
        rankBadge.textContent = `#${rank}`;
        cardWrapper.appendChild(rankBadge);

        // Card
        const card = document.createElement('div');
        card.className = 'movie-card-inner';

        const imageUrl = movie.poster_path 
            ? `https://image.tmdb.org/t/p/w500${movie.poster_path}`
            : '/images/placeholder.jpg';

        const rating = movie.vote_average ? parseFloat(movie.vote_average).toFixed(1) : null;

        card.innerHTML = `
            <div class="movie-poster">
                ${movie.poster_path ? `
                <img src="${imageUrl}" alt="${movie.title || 'Untitled'}" loading="lazy" onerror="this.src='/images/placeholder.jpg'">
                ` : `
                <div class="w-full h-full flex items-center justify-center">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-16 w-16 text-gray-600">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                    </svg>
                </div>
                `}
                
                ${rating ? `
                <div class="rating-badge">
                    <svg fill="currentColor" viewBox="0 0 24 24" class="h-3 w-3">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    <span>${rating}</span>
                </div>
                ` : ''}
                
                <div class="hover-overlay">
                    <div class="hover-title">
                        <h3>${movie.title || 'Untitled'}</h3>
                    </div>
                </div>
            </div>
        `;

        cardWrapper.appendChild(card);
        return cardWrapper;
    }

    // Render pagination - Matching Frontend
    function renderPagination() {
        const paginationDiv = document.getElementById('pagination');
        paginationDiv.innerHTML = '';

        if (!pagination || !pagination.total_pages || pagination.total_pages <= 1) {
            return;
        }

        const paginationContainer = document.createElement('div');
        paginationContainer.className = 'flex items-center justify-center space-x-2';

        // Previous button
        const prevBtn = document.createElement('button');
        prevBtn.textContent = 'Previous';
        prevBtn.className = page === 1 
            ? 'px-4 py-2 rounded-lg font-medium transition-colors bg-gray-800 text-gray-600 cursor-not-allowed'
            : 'px-4 py-2 rounded-lg font-medium transition-colors bg-gray-800 text-white hover:bg-gray-700';
        prevBtn.disabled = page === 1;
        prevBtn.onclick = () => {
            if (page > 1) {
                page--;
                fetchTopRatedMovies();
            }
        };
        paginationContainer.appendChild(prevBtn);

        // Page info
        const pageInfo = document.createElement('span');
        pageInfo.className = 'text-white px-4';
        pageInfo.textContent = `Page ${page} of ${pagination.total_pages}`;
        paginationContainer.appendChild(pageInfo);

        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.textContent = 'Next';
        nextBtn.className = page === pagination.total_pages
            ? 'px-4 py-2 rounded-lg font-medium transition-colors bg-gray-800 text-gray-600 cursor-not-allowed'
            : 'px-4 py-2 rounded-lg font-medium transition-colors bg-gray-800 text-white hover:bg-gray-700';
        nextBtn.disabled = page === pagination.total_pages;
        nextBtn.onclick = () => {
            if (page < pagination.total_pages) {
                page++;
                fetchTopRatedMovies();
            }
        };
        paginationContainer.appendChild(nextBtn);

        paginationDiv.appendChild(paginationContainer);
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        fetchTopRatedMovies();
    });
</script>
@endpush
@endsection
