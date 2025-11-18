@extends('layouts.app')

@section('title', 'Trending Movies - Nazaara Box')

@section('seo_title', 'Trending Movies - Popular Films Right Now - Nazaara Box')
@section('seo_description', 'Discover trending movies that everyone is watching. Watch the most popular and talked-about films right now on Nazaara Box.')
@section('seo_type', 'website')
@section('seo_url', route('movies.trending'))

@push('styles')
<style>
    .trending-movies-page-wrapper {
        padding-top: 64px;
    }

    @media (min-width: 768px) {
        .trending-movies-page-wrapper {
            padding-top: 80px;
        }
    }

    .period-filter-container {
        display: flex;
        align-items: center;
        gap: 8px;
        background-color: #1f2937;
        border-radius: 8px;
        padding: 4px;
    }

    .period-btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        font-size: 14px;
    }

    .period-btn.active {
        background-color: #dc2626;
        color: white;
    }

    .period-btn.inactive {
        color: #9ca3af;
        background: transparent;
    }

    .period-btn.inactive:hover {
        color: white;
    }

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

    .rank-badge.rank-1 { background: linear-gradient(to bottom right, #facc15, #eab308); color: black; }
    .rank-badge.rank-2 { background: linear-gradient(to bottom right, #d1d5db, #9ca3af); color: black; }
    .rank-badge.rank-3 { background: linear-gradient(to bottom right, #fb923c, #ea580c); color: white; }
    .rank-badge.rank-top10 { background: linear-gradient(to bottom right, #dc2626, #991b1b); color: white; }
    .rank-badge.rank-default { background: linear-gradient(to bottom right, #374151, #111827); color: white; }

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
        background-color: #111827;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #1f2937;
        transition: border-color 0.2s;
    }

    .movie-card:hover .movie-card-inner {
        border-color: #dc2626;
    }

    .movie-poster {
        position: relative;
        aspect-ratio: 2/3;
        overflow: hidden;
        background-color: #1f2937;
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
        background-color: #eab308;
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

    .view-count-badge {
        position: absolute;
        bottom: 8px;
        right: 8px;
        background-color: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(4px);
        color: white;
        padding: 4px 8px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        font-weight: 600;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }

    .view-count-badge svg {
        width: 12px;
        height: 12px;
        color: #f87171;
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
<div class="min-h-screen bg-black trending-movies-page-wrapper pb-12">
    <div class="container mx-auto px-4 md:px-8 lg:px-16">
        <div id="loading-state" class="min-h-screen bg-black flex items-center justify-center" style="padding-top: 64px;">
            <div class="spinner"></div>
        </div>

        <div id="trending-content" style="display: none;">
            <div class="mb-8">
                <button onclick="window.history.back()" class="flex items-center space-x-2 text-gray-400 hover:text-white transition-colors mb-6">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span>Back</span>
                </button>

                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                    <div class="flex items-center space-x-4 mb-4 md:mb-0">
                        <div class="p-3 bg-red-600 rounded-lg">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-8 w-8 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl md:text-4xl font-bold text-white">Trending Movies</h1>
                            <p class="text-gray-400 mt-1">Most watched movies right now</p>
                        </div>
                    </div>

                    <div class="period-filter-container">
                        <button onclick="setPeriod('today')" id="period-today" class="period-btn active">Today</button>
                        <button onclick="setPeriod('week')" id="period-week" class="period-btn inactive">Week</button>
                        <button onclick="setPeriod('month')" id="period-month" class="period-btn inactive">Month</button>
                        <button onclick="setPeriod('overall')" id="period-overall" class="period-btn inactive">Overall</button>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2 bg-gray-800 px-4 py-2 rounded-lg">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5 text-red-500">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                        </svg>
                        <span id="movie-count" class="text-white font-semibold">0</span>
                        <span class="text-gray-400">Movies</span>
                    </div>
                </div>
            </div>

            <div id="movies-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4"></div>

            <div id="empty-state" class="text-center py-20" style="display: none;">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-800 rounded-full mb-6">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-10 w-10 text-gray-600">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">No Movies Found</h2>
                <p class="text-gray-400 mb-8">Try selecting a different time period.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let movies = [];
    let period = 'today';

    function getRankBadgeColor(rank) {
        if (rank === 1) return 'rank-1';
        if (rank === 2) return 'rank-2';
        if (rank === 3) return 'rank-3';
        if (rank <= 10) return 'rank-top10';
        return 'rank-default';
    }

    function setPeriod(p) {
        period = p;
        ['today', 'week', 'month', 'overall'].forEach(per => {
            const btn = document.getElementById(`period-${per}`);
            if (btn) btn.className = per === p ? 'period-btn active' : 'period-btn inactive';
        });
        fetchTrendingMovies();
    }

    async function fetchTrendingMovies() {
        document.getElementById('loading-state').style.display = 'flex';
        document.getElementById('trending-content').style.display = 'none';
        try {
            const response = await apiFetch(`${API_BASE_URL}/leaderboard/movies/leaderboard?period=${period}&limit=50`);
            const result = await response.json();
            movies = result.data?.movies || result.data || [];
            renderMovies();
        } catch (error) {
            console.error('Error fetching trending movies:', error);
            movies = [];
            renderMovies();
        } finally {
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('trending-content').style.display = 'block';
        }
    }

    function renderMovies() {
        const grid = document.getElementById('movies-grid');
        const emptyState = document.getElementById('empty-state');
        const movieCount = document.getElementById('movie-count');
        grid.innerHTML = '';
        if (movieCount) movieCount.textContent = movies.length;
        if (movies.length === 0) {
            emptyState.style.display = 'block';
            return;
        }
        emptyState.style.display = 'none';
        movies.forEach((movie, index) => {
            const rank = index + 1;
            const cardWrapper = document.createElement('div');
            cardWrapper.className = 'relative group cursor-pointer transform transition-all duration-300 hover:scale-105 movie-card';
            cardWrapper.onclick = () => window.location.href = `/movies/${movie.id}`;
            
            const rankBadge = document.createElement('div');
            rankBadge.className = `rank-badge ${getRankBadgeColor(rank)}`;
            rankBadge.textContent = `#${rank}`;
            cardWrapper.appendChild(rankBadge);

            const card = document.createElement('div');
            card.className = 'movie-card-inner';
            const imageUrl = movie.poster_path ? `https://image.tmdb.org/t/p/w500${movie.poster_path}` : '/images/placeholder.jpg';
            const rating = movie.vote_average ? parseFloat(movie.vote_average).toFixed(1) : null;
            
            card.innerHTML = `
                <div class="movie-poster">
                    ${movie.poster_path ? `<img src="${imageUrl}" alt="${movie.title || 'Untitled'}" loading="lazy" onerror="this.src='/images/placeholder.jpg'">` : 
                    `<div class="w-full h-full flex items-center justify-center"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-16 w-16 text-gray-600"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path></svg></div>`}
                    ${rating ? `<div class="rating-badge"><svg fill="currentColor" viewBox="0 0 24 24" class="h-3 w-3"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg><span>${rating}</span></div>` : ''}
                    ${movie.view_count > 0 ? `<div class="view-count-badge"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg><span>${movie.view_count.toLocaleString()}</span></div>` : ''}
                    <div class="hover-overlay"><div class="hover-title"><h3>${movie.title || 'Untitled'}</h3></div></div>
                </div>
            `;
            cardWrapper.appendChild(card);
            grid.appendChild(cardWrapper);
        });
    }

    document.addEventListener('DOMContentLoaded', fetchTrendingMovies);
</script>
@endpush
@endsection
