@extends('layouts.app')

@section('title', 'Top Rated TV Shows - Nazaara Box')

@section('seo_title', 'Top Rated TV Shows - Highest Rated Series - Nazaara Box')
@section('seo_description', 'Watch the highest rated TV shows of all time on Nazaara Box. Discover critically acclaimed series and award-winning television.')
@section('seo_type', 'website')
@section('seo_url', route('tvshows.top-rated'))

@push('styles')
<style>
    /* Adjust padding for fixed header */
    .top-rated-tvshows-page-wrapper {
        padding-top: 64px; /* Mobile header height */
    }

    @media (min-width: 768px) {
        .top-rated-tvshows-page-wrapper {
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
        background: linear-gradient(to bottom right, #2563eb, #1d4ed8);
        color: white;
    }

    .rank-badge.rank-default {
        background: linear-gradient(to bottom right, #374151, #111827);
        color: white;
    }

    /* Card Styles */
    .tvshow-card {
        position: relative;
        cursor: pointer;
        transform: scale(1);
        transition: all 0.3s;
    }

    .tvshow-card:hover {
        transform: scale(1.05);
    }

    .tvshow-card-inner {
        position: relative;
        background-color: #111827;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #1f2937;
        transition: border-color 0.2s;
    }

    .tvshow-card:hover .tvshow-card-inner {
        border-color: #2563eb; /* border-blue-500 */
    }

    .tvshow-poster {
        position: relative;
        aspect-ratio: 2/3;
        overflow: hidden;
        background-color: #1f2937;
    }

    .tvshow-poster img {
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
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }

    .hover-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,1), rgba(0,0,0,0.5), transparent);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .tvshow-card:hover .hover-overlay {
        opacity: 1;
    }

    .hover-title {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 16px;
    }

    .hover-title h3 {
        color: white;
        font-weight: 600;
        font-size: 18px;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-black top-rated-tvshows-page-wrapper pb-12">
    <div class="container mx-auto px-4 md:px-8 lg:px-16">
        <!-- Loading State -->
        <div id="loading-state" class="min-h-screen bg-black flex items-center justify-center" style="padding-top: 64px;">
            <div class="spinner"></div>
        </div>

        <!-- Content -->
        <div id="tvshows-content" style="display: none;">
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
                    <div class="p-3 bg-blue-600 rounded-lg">
                        <svg fill="currentColor" viewBox="0 0 24 24" class="h-8 w-8 text-white">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold text-white">
                            Top Rated TV Shows
                        </h1>
                        <p class="text-gray-400 mt-1">
                            Highest rated TV shows with 7.0+ rating
                        </p>
                    </div>
                </div>

                <!-- TV Show Count -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2 bg-gray-800 px-4 py-2 rounded-lg">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5 text-blue-500">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span id="tvshow-count" class="text-white font-semibold">0</span>
                        <span class="text-gray-400">Total TV Shows</span>
                    </div>
                </div>
            </div>

            <!-- TV Shows Grid -->
            <div id="tvshows-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"></div>

            <!-- Empty State -->
            <div id="empty-state" class="text-center py-20" style="display: none;">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-800 rounded-full mb-6">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-10 w-10 text-gray-600">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">
                    No TV Shows Found
                </h2>
            </div>

            <!-- Pagination -->
            <div id="pagination" class="flex items-center justify-center space-x-2 mt-12"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // State management - Matching Frontend TopRatedTVShowsPage.jsx
    let tvShows = [];
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

    // Fetch top rated TV shows - Matching Frontend
    async function fetchTopRatedTVShows() {
        loading = true;
        document.getElementById('loading-state').style.display = 'flex';
        document.getElementById('tvshows-content').style.display = 'none';

        try {
            const response = await apiFetch(`${API_BASE_URL}/tvshows?sort_by=rating&min_rating=7.0&min_votes=100&page=${page}&limit=24`);
            const result = await response.json();
            tvShows = result.data?.tvShows || result.data || [];
            pagination = result.data?.pagination || result.pagination || null;
            
            renderTVShows();
        } catch (error) {
            console.error('Error fetching top rated TV shows:', error);
            tvShows = [];
            pagination = null;
            renderTVShows();
        } finally {
            loading = false;
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('tvshows-content').style.display = 'block';
        }
    }

    // Render TV shows - Matching Frontend
    function renderTVShows() {
        const grid = document.getElementById('tvshows-grid');
        const emptyState = document.getElementById('empty-state');
        const tvshowCount = document.getElementById('tvshow-count');

        grid.innerHTML = '';

        if (tvshowCount && pagination) {
            tvshowCount.textContent = pagination.total_items || pagination.total || 0;
        }

        if (tvShows.length === 0) {
            emptyState.style.display = 'block';
            renderPagination();
            return;
        }

        emptyState.style.display = 'none';

        tvShows.forEach((show, index) => {
            const rank = (page - 1) * 24 + index + 1;
            const card = createTVShowCard(show, rank);
            grid.appendChild(card);
        });

        renderPagination();
    }

    // Create TV show card - Matching Frontend
    function createTVShowCard(show, rank) {
        const cardWrapper = document.createElement('div');
        cardWrapper.className = 'relative group cursor-pointer transform transition-all duration-300 hover:scale-105 tvshow-card';
        cardWrapper.onclick = () => {
            window.location.href = `/tvshow/${show.id}`;
        };

        // Rank Badge
        const rankBadge = document.createElement('div');
        rankBadge.className = `rank-badge ${getRankBadgeColor(rank)}`;
        rankBadge.textContent = `#${rank}`;
        cardWrapper.appendChild(rankBadge);

        // Card
        const card = document.createElement('div');
        card.className = 'tvshow-card-inner';

        const imageUrl = show.poster_path 
            ? `https://image.tmdb.org/t/p/w500${show.poster_path}`
            : '/images/placeholder.jpg';

        const rating = show.vote_average ? parseFloat(show.vote_average).toFixed(1) : null;

        card.innerHTML = `
            <div class="tvshow-poster">
                ${show.poster_path ? `
                <img src="${imageUrl}" alt="${show.name || 'Untitled'}" loading="lazy" onerror="this.src='/images/placeholder.jpg'">
                ` : `
                <div class="w-full h-full flex items-center justify-center">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-16 w-16 text-gray-600">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                `}
                
                ${rating ? `
                <div class="rating-badge">
                    <svg fill="currentColor" viewBox="0 0 24 24" class="h-4 w-4">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    <span>${rating}</span>
                </div>
                ` : ''}
                
                <div class="hover-overlay">
                    <div class="hover-title">
                        <h3>${show.name || 'Untitled'}</h3>
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
                fetchTopRatedTVShows();
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
                fetchTopRatedTVShows();
            }
        };
        paginationContainer.appendChild(nextBtn);

        paginationDiv.appendChild(paginationContainer);
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        fetchTopRatedTVShows();
    });
</script>
@endpush
@endsection
