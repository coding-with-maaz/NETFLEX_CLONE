@extends('layouts.app')

@section('title', ($name ? ucwords(str_replace('-', ' ', $name)) : 'Genre') . ' - Nazaara Box')

@php
    $genreName = $name ? ucwords(str_replace('-', ' ', $name)) : 'Genre';
@endphp

@section('seo_title', "{$genreName} Movies & TV Shows - Nazaara Box")
@section('seo_description', "Watch {$genreName} movies and TV shows on Nazaara Box. Discover the best {$genreName} content in HD quality.")
@section('seo_type', 'website')
@section('seo_url', url()->current())

@push('styles')
<style>
    /* Adjust padding for fixed header */
    .genre-page-wrapper {
        padding-top: 64px; /* Mobile header height */
    }

    @media (min-width: 768px) {
        .genre-page-wrapper {
            padding-top: 80px; /* Desktop header height */
        }
    }

    /* Tab Styles - Matching Frontend */
    .tab-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 8px;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        font-weight: 600;
    }

    .tab-btn.active {
        background-color: #dc2626; /* bg-red-600 */
        color: white;
    }

    .tab-btn.inactive {
        background-color: #1f2937; /* bg-gray-800 */
        color: #d1d5db; /* text-gray-300 */
    }

    .tab-btn.inactive:hover {
        background-color: #374151; /* hover:bg-gray-700 */
    }

    /* Sort Dropdown Styles - Matching Frontend */
    #sort-select {
        background-color: #1f2937; /* bg-gray-800 */
        color: white;
        border: 1px solid #374151; /* border-gray-700 */
        border-radius: 6px;
        padding: 8px 16px;
        outline: none;
        font-size: 14px;
        cursor: pointer;
        transition: border-color 0.2s;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 8px center;
        background-repeat: no-repeat;
        background-size: 16px;
        padding-right: 40px;
    }

    #sort-select:focus {
        border-color: #dc2626; /* border-red-600 */
    }

    #sort-select:hover {
        border-color: #4b5563;
    }

    #sort-select option {
        background-color: #1f2937;
        color: white;
        padding: 8px;
    }

    /* Filter Button Styles - Matching Frontend */
    #filter-toggle-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        background-color: #1f2937; /* bg-gray-800 */
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        border: none;
        font-size: 14px;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    #filter-toggle-btn:hover {
        background-color: #374151; /* hover:bg-gray-700 */
    }

    #filter-badge {
        background-color: #dc2626; /* bg-red-600 */
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-black genre-page-wrapper">
    <div class="container mx-auto px-4 md:px-8 lg:px-16 py-8">
        <!-- Loading State -->
        <div id="loading-state" class="flex justify-center items-center py-20">
            <div class="spinner"></div>
        </div>

        <!-- Content -->
        <div id="genre-content" style="display: none;">
            <!-- Header -->
            <div class="mb-8">
                <button
                    onclick="window.history.back()"
                    class="flex items-center space-x-2 text-white hover:text-gray-300 transition-colors mb-4"
                >
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Back</span>
                </button>

                <h1 id="genre-title" class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-2"></h1>
                <p id="genre-description" class="text-gray-400"></p>
            </div>

            <!-- Tabs -->
            <div class="flex items-center space-x-2 mb-8">
                <button
                    id="movies-tab"
                    onclick="switchTab('movies')"
                    class="tab-btn active"
                >
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                    </svg>
                    <span>Movies</span>
                </button>
                <button
                    id="tvshows-tab"
                    onclick="switchTab('tvshows')"
                    class="tab-btn inactive"
                >
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span>TV Shows</span>
                </button>
            </div>

            <!-- Filters and Sort -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
                <div class="flex items-center space-x-4">
                    <!-- Sort Dropdown -->
                    <select
                        id="sort-select"
                    >
                        <option value="hottest">Hottest</option>
                        <option value="latest">Latest</option>
                        <option value="rating">Top Rated</option>
                        <option value="title">A-Z</option>
                    </select>

                    <!-- Filter Toggle -->
                    <button
                        id="filter-toggle-btn"
                        onclick="toggleFilters()"
                    >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        <span>Filters</span>
                        <span id="filter-badge" style="display: none;">!</span>
                    </button>
                </div>

                <!-- Results Info -->
                <div id="results-info" class="text-gray-400"></div>
            </div>

            <!-- Filters Panel -->
            <div id="filters-panel" class="bg-gray-900 border border-gray-800 rounded-lg p-6 mb-8" style="display: none;">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-white">Filters</h2>
                    <div class="flex items-center space-x-4">
                        <button
                            id="clear-filters-btn"
                            onclick="clearFilters()"
                            class="text-red-500 hover:text-red-400 text-sm flex items-center space-x-1"
                            style="display: none;"
                        >
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span>Clear All</span>
                        </button>
                        <button
                            onclick="toggleFilters()"
                            class="text-gray-400 hover:text-white"
                        >
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Year Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Year</label>
                        <select
                            id="year-select"
                            class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 outline-none focus:border-red-600"
                        >
                            <option value="">All Years</option>
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                            <option value="2022">2022</option>
                            <option value="2021">2021</option>
                            <option value="2020">2020</option>
                            <option value="2010s">2010s</option>
                            <option value="2000s">2000s</option>
                            <option value="1990s">1990s</option>
                            <option value="1980s">1980s</option>
                        </select>
                    </div>

                    <!-- Rating Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Minimum Rating</label>
                        <select
                            id="rating-select"
                            class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 outline-none focus:border-red-600"
                        >
                            <option value="">All Ratings</option>
                            <option value="9+">9+</option>
                            <option value="8+">8+</option>
                            <option value="7+">7+</option>
                            <option value="6+">6+</option>
                            <option value="5+">5+</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div id="content-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-8"></div>

            <!-- Empty State -->
            <div id="empty-state" class="text-center py-20" style="display: none;">
                <p id="empty-message" class="text-gray-400 text-lg"></p>
                <button
                    id="clear-filters-empty-btn"
                    onclick="clearFilters()"
                    class="mt-4 text-red-500 hover:text-red-400 underline"
                    style="display: none;"
                >
                    Clear all filters
                </button>
            </div>

            <!-- Pagination -->
            <div id="pagination" class="flex justify-center items-center space-x-2 mt-8"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // State management - Matching Frontend GenreDetailPage.jsx
    let movies = [];
    let tvShows = [];
    let loading = false;
    let pagination = {};
    let currentPage = 1;
    let sortBy = 'hottest';
    let minRating = '';
    let year = '';
    let showFilters = false;
    let activeTab = 'movies'; // movies or tvshows

    const genreId = {{ $id }};
    const genreNameParam = @json($name);
    const genreName = genreNameParam 
        ? genreNameParam.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
        : '';

    // Initialize page
    document.addEventListener('DOMContentLoaded', () => {
        // Set genre title and description
        const genreTitle = document.getElementById('genre-title');
        const genreDescription = document.getElementById('genre-description');
        
        if (genreTitle) genreTitle.textContent = genreName;
        if (genreDescription) {
            genreDescription.textContent = `Explore our collection of ${genreName.toLowerCase()} content`;
        }

        // Event listeners
        document.getElementById('sort-select').addEventListener('change', (e) => {
            sortBy = e.target.value;
            currentPage = 1;
            fetchContent();
        });

        document.getElementById('year-select').addEventListener('change', (e) => {
            year = e.target.value;
            currentPage = 1;
            updateFilterBadge();
            fetchContent();
        });

        document.getElementById('rating-select').addEventListener('change', (e) => {
            minRating = e.target.value;
            currentPage = 1;
            updateFilterBadge();
            fetchContent();
        });

        // Initial fetch
        fetchContent();
    });

    // Switch tab - Matching Frontend
    function switchTab(tab) {
        activeTab = tab;
        currentPage = 1;

        // Update tab buttons
        const moviesTab = document.getElementById('movies-tab');
        const tvshowsTab = document.getElementById('tvshows-tab');

        if (tab === 'movies') {
            moviesTab.className = 'tab-btn active';
            tvshowsTab.className = 'tab-btn inactive';
        } else {
            moviesTab.className = 'tab-btn inactive';
            tvshowsTab.className = 'tab-btn active';
        }

        fetchContent();
    }

    // Toggle filters panel
    function toggleFilters() {
        showFilters = !showFilters;
        const filtersPanel = document.getElementById('filters-panel');
        if (filtersPanel) {
            filtersPanel.style.display = showFilters ? 'block' : 'none';
        }
    }

    // Clear filters - Matching Frontend
    function clearFilters() {
        minRating = '';
        year = '';
        sortBy = 'hottest';
        currentPage = 1;

        document.getElementById('year-select').value = '';
        document.getElementById('rating-select').value = '';
        document.getElementById('sort-select').value = 'hottest';

        updateFilterBadge();
        fetchContent();
    }

    // Update filter badge visibility
    function updateFilterBadge() {
        const hasActiveFilters = minRating || year;
        const filterBadge = document.getElementById('filter-badge');
        const clearFiltersBtn = document.getElementById('clear-filters-btn');
        const clearFiltersEmptyBtn = document.getElementById('clear-filters-empty-btn');

        if (filterBadge) {
            filterBadge.style.display = hasActiveFilters ? 'flex' : 'none';
        }
        if (clearFiltersBtn) {
            clearFiltersBtn.style.display = hasActiveFilters ? 'flex' : 'none';
        }
        if (clearFiltersEmptyBtn) {
            clearFiltersEmptyBtn.style.display = hasActiveFilters ? 'inline-block' : 'none';
        }
    }

    // Fetch content - Matching Frontend
    async function fetchContent() {
        loading = true;
        document.getElementById('loading-state').style.display = 'flex';
        document.getElementById('genre-content').style.display = 'none';

        try {
            const params = {
                page: currentPage,
                limit: 24,
                sort_by: sortBy,
                genre: genreId
            };

            if (minRating && minRating !== 'All') {
                params.min_rating = parseFloat(minRating.replace('+', ''));
            }
            if (year && year !== 'All') {
                params.year = year;
            }

            let url;
            if (activeTab === 'movies') {
                url = `${API_BASE_URL}/movies?${new URLSearchParams(params)}`;
            } else {
                url = `${API_BASE_URL}/tvshows?${new URLSearchParams(params)}`;
            }

            const response = await apiFetch(url);
            const result = await response.json();

            if (activeTab === 'movies') {
                movies = result.data?.movies || result.data || [];
                pagination = result.data?.pagination || {};
            } else {
                tvShows = result.data?.tvShows || result.data || [];
                pagination = result.data?.pagination || {};
            }

            renderContent();
        } catch (error) {
            console.error('Error fetching genre content:', error);
            if (activeTab === 'movies') {
                movies = [];
            } else {
                tvShows = [];
            }
            pagination = {};
            renderContent();
        } finally {
            loading = false;
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('genre-content').style.display = 'block';
        }
    }

    // Render content
    function renderContent() {
        const grid = document.getElementById('content-grid');
        const emptyState = document.getElementById('empty-state');
        const resultsInfo = document.getElementById('results-info');
        const emptyMessage = document.getElementById('empty-message');

        grid.innerHTML = '';

        const items = activeTab === 'movies' ? movies : tvShows;

        // Update results info
        if (pagination.total > 0) {
            resultsInfo.textContent = `${pagination.total} ${activeTab === 'movies' ? 'movies' : 'TV shows'} found`;
        } else {
            resultsInfo.textContent = '';
        }

        if (items.length === 0) {
            emptyState.style.display = 'block';
            emptyMessage.textContent = `No ${genreName.toLowerCase()} ${activeTab === 'movies' ? 'movies' : 'TV shows'} found.`;
            return;
        }

        emptyState.style.display = 'none';

        // Render cards
        items.forEach(item => {
            const card = activeTab === 'movies' ? createMovieCard(item) : createTVShowCard(item);
            grid.appendChild(card);
        });

        renderPagination();
    }

    // Create movie card - Matching Frontend MovieCard.jsx
    function createMovieCard(movie) {
        const card = document.createElement('div');
        card.className = 'movie-card group';
        card.style.cssText = 'position: relative; flex-shrink: 0; width: 160px; transition: all 0.3s; cursor: pointer;';
        
        if (window.innerWidth >= 768) card.style.width = '192px';
        if (window.innerWidth >= 1024) card.style.width = '224px';
        
        const imageUrl = movie.poster_path 
            ? `https://image.tmdb.org/t/p/w500${movie.poster_path}`
            : '/images/placeholder.jpg';

        const rating = movie.vote_average ? parseFloat(movie.vote_average).toFixed(1) : null;
        const matchPercent = movie.vote_average ? Math.round(parseFloat(movie.vote_average) * 10) : null;
        const movieYear = movie.release_date ? new Date(movie.release_date).getFullYear() : null;
        const runtime = movie.runtime ? `${Math.floor(movie.runtime / 60)}h ${movie.runtime % 60}m` : null;

        card.innerHTML = `
            <div class="movie-card-poster" style="position: relative; border-radius: 8px; overflow: hidden;">
                <img src="${imageUrl}" alt="${movie.title || 'Untitled'}" loading="lazy" onerror="this.src='/images/placeholder.jpg'" 
                     style="width: 100%; height: 240px; object-fit: cover; transition: transform 0.3s;" 
                     class="movie-card-image">
                <div class="movie-card-gradient" style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent, transparent); opacity: 0; transition: opacity 0.3s;"></div>
                
                ${movie.view_count !== undefined && movie.view_count > 0 ? `
                <div style="position: absolute; top: 8px; right: 8px; background-color: var(--primary-red); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                    <svg fill="currentColor" viewBox="0 0 24 24" style="width: 12px; height: 12px;">
                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                    </svg>
                    <span>${movie.view_count}</span>
                </div>
                ` : ''}
                
                ${rating ? `
                <div style="position: absolute; top: 8px; left: 8px; background-color: rgba(234, 179, 8, 0.9); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                    <span>⭐</span>
                    <span>${rating}</span>
                </div>
                ` : ''}
            </div>

            <div class="movie-card-hover-info" style="position: absolute; left: 0; right: 0; top: 100%; margin-top: 8px; background-color: #181818; border-radius: 8px; box-shadow: 0 8px 16px rgba(0,0,0,0.5); padding: 16px; z-index: 30; transform: scale(1.1); transform-origin: top; display: none;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <button onclick="event.stopPropagation(); window.location.href='/movie/${movie.id}';" 
                            style="background-color: white; color: black; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; border: none; cursor: pointer;"
                            onmouseover="this.style.backgroundColor='rgba(255,255,255,0.8)'"
                            onmouseout="this.style.backgroundColor='white'">
                        <svg fill="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </button>
                    <button onclick="event.stopPropagation();" 
                            style="border: 2px solid #9ca3af; color: white; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; background: transparent; cursor: pointer;"
                            onmouseover="this.style.borderColor='white'"
                            onmouseout="this.style.borderColor='#9ca3af'">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </button>
                    <button onclick="event.stopPropagation(); window.location.href='/movie/${movie.id}';" 
                            style="border: 2px solid #9ca3af; color: white; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; background: transparent; cursor: pointer; margin-left: auto;"
                            onmouseover="this.style.borderColor='white'"
                            onmouseout="this.style.borderColor='#9ca3af'">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
                <h3 style="color: white; font-weight: 600; font-size: 14px; margin-bottom: 8px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${movie.title || 'Untitled'}</h3>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; margin-bottom: 8px;">
                    ${matchPercent ? `<span style="color: #4ade80; font-weight: 600;">${matchPercent}% Match</span>` : ''}
                    ${movieYear ? `<span style="color: #9ca3af;">${movieYear}</span>` : ''}
                    ${runtime ? `<span style="color: #9ca3af;">${runtime}</span>` : ''}
                </div>
                ${movie.genres && movie.genres.length > 0 ? `
                <div style="display: flex; flex-wrap: wrap; gap: 4px; font-size: 12px; color: #9ca3af;">
                    ${movie.genres.slice(0, 3).map((genre, index) => {
                        const genreName = genre.name || genre;
                        return `<span>${genreName}</span>${index < Math.min(2, movie.genres.length - 1) ? ' • ' : ''}`;
                    }).join('')}
                </div>
                ` : ''}
            </div>
        `;

        card.addEventListener('click', () => {
            window.location.href = `/movie/${movie.id}`;
        });

        card.addEventListener('mouseenter', () => {
            const hoverInfo = card.querySelector('.movie-card-hover-info');
            const gradient = card.querySelector('.movie-card-gradient');
            const image = card.querySelector('.movie-card-image');
            if (hoverInfo) hoverInfo.style.display = 'block';
            if (gradient) gradient.style.opacity = '1';
            if (image) image.style.transform = 'scale(1.1)';
        });

        card.addEventListener('mouseleave', () => {
            const hoverInfo = card.querySelector('.movie-card-hover-info');
            const gradient = card.querySelector('.movie-card-gradient');
            const image = card.querySelector('.movie-card-image');
            if (hoverInfo) hoverInfo.style.display = 'none';
            if (gradient) gradient.style.opacity = '0';
            if (image) image.style.transform = 'scale(1)';
        });

        return card;
    }

    // Create TV show card - Matching Frontend TVShowCard.jsx
    function createTVShowCard(tvShow) {
        const card = document.createElement('div');
        card.className = 'tvshow-card group';
        card.style.cssText = 'position: relative; flex-shrink: 0; width: 160px; transition: all 0.3s; cursor: pointer;';
        
        if (window.innerWidth >= 768) card.style.width = '192px';
        if (window.innerWidth >= 1024) card.style.width = '224px';
        
        const imageUrl = tvShow.poster_path 
            ? `https://image.tmdb.org/t/p/w500${tvShow.poster_path}`
            : '/images/placeholder.jpg';

        const rating = tvShow.vote_average ? parseFloat(tvShow.vote_average).toFixed(1) : null;
        const matchPercent = tvShow.vote_average ? Math.round(parseFloat(tvShow.vote_average) * 10) : null;
        const tvShowYear = tvShow.first_air_date ? new Date(tvShow.first_air_date).getFullYear() : null;
        const seasons = tvShow.number_of_seasons ? `${tvShow.number_of_seasons} Season${tvShow.number_of_seasons > 1 ? 's' : ''}` : null;
        const episodes = tvShow.number_of_episodes || null;
        const name = tvShow.name ? encodeURIComponent(tvShow.name) : '';

        card.innerHTML = `
            <div class="tvshow-card-poster" style="position: relative; border-radius: 8px; overflow: hidden;">
                <img src="${imageUrl}" alt="${tvShow.name || 'Untitled'}" loading="lazy" onerror="this.src='/images/placeholder.jpg'"
                     style="width: 100%; height: 240px; object-fit: cover; transition: transform 0.3s;" 
                     class="tvshow-card-image">
                <div class="tvshow-card-gradient" style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent, transparent); opacity: 0; transition: opacity 0.3s;"></div>
                
                ${tvShow.view_count !== undefined && tvShow.view_count > 0 ? `
                <div style="position: absolute; top: 8px; right: 8px; background-color: var(--primary-red); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                    <svg fill="currentColor" viewBox="0 0 24 24" style="width: 12px; height: 12px;">
                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                    </svg>
                    <span>${tvShow.view_count}</span>
                </div>
                ` : ''}
                
                ${rating ? `
                <div style="position: absolute; top: 8px; left: 8px; background-color: rgba(234, 179, 8, 0.9); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                    <span>⭐</span>
                    <span>${rating}</span>
                </div>
                ` : ''}

                ${episodes ? `
                <div style="position: absolute; bottom: 8px; left: 8px; background-color: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                    ${episodes} Episodes
                </div>
                ` : ''}
            </div>

            <div class="tvshow-card-hover-info" style="position: absolute; left: 0; right: 0; top: 100%; margin-top: 8px; background-color: #181818; border-radius: 8px; box-shadow: 0 8px 16px rgba(0,0,0,0.5); padding: 16px; z-index: 30; transform: scale(1.1); transform-origin: top; display: none;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <button onclick="event.stopPropagation(); window.location.href='/tvshow/${tvShow.id}${name ? '?name=' + name : ''}';" 
                            style="background-color: white; color: black; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; border: none; cursor: pointer;"
                            onmouseover="this.style.backgroundColor='rgba(255,255,255,0.8)'"
                            onmouseout="this.style.backgroundColor='white'">
                        <svg fill="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </button>
                    <button onclick="event.stopPropagation();" 
                            style="border: 2px solid #9ca3af; color: white; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; background: transparent; cursor: pointer;"
                            onmouseover="this.style.borderColor='white'"
                            onmouseout="this.style.borderColor='#9ca3af'">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </button>
                    <button onclick="event.stopPropagation(); window.location.href='/tvshow/${tvShow.id}${name ? '?name=' + name : ''}';" 
                            style="border: 2px solid #9ca3af; color: white; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; background: transparent; cursor: pointer; margin-left: auto;"
                            onmouseover="this.style.borderColor='white'"
                            onmouseout="this.style.borderColor='#9ca3af'">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
                <h3 style="color: white; font-weight: 600; font-size: 14px; margin-bottom: 8px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${tvShow.name || 'Untitled'}</h3>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; margin-bottom: 8px;">
                    ${matchPercent ? `<span style="color: #4ade80; font-weight: 600;">${matchPercent}% Match</span>` : ''}
                    ${tvShowYear ? `<span style="color: #9ca3af;">${tvShowYear}</span>` : ''}
                    ${seasons ? `<span style="color: #9ca3af;">${seasons}</span>` : ''}
                </div>
                ${tvShow.genres && tvShow.genres.length > 0 ? `
                <div style="display: flex; flex-wrap: wrap; gap: 4px; font-size: 12px; color: #9ca3af;">
                    ${tvShow.genres.slice(0, 3).map((genre, index) => {
                        const genreName = genre.name || genre;
                        return `<span>${genreName}</span>${index < Math.min(2, tvShow.genres.length - 1) ? ' • ' : ''}`;
                    }).join('')}
                </div>
                ` : ''}
            </div>
        `;

        card.addEventListener('click', () => {
            window.location.href = `/tvshow/${tvShow.id}${name ? '?name=' + name : ''}`;
        });

        card.addEventListener('mouseenter', () => {
            const hoverInfo = card.querySelector('.tvshow-card-hover-info');
            const gradient = card.querySelector('.tvshow-card-gradient');
            const image = card.querySelector('.tvshow-card-image');
            if (hoverInfo) hoverInfo.style.display = 'block';
            if (gradient) gradient.style.opacity = '1';
            if (image) image.style.transform = 'scale(1.1)';
        });

        card.addEventListener('mouseleave', () => {
            const hoverInfo = card.querySelector('.tvshow-card-hover-info');
            const gradient = card.querySelector('.tvshow-card-gradient');
            const image = card.querySelector('.tvshow-card-image');
            if (hoverInfo) hoverInfo.style.display = 'none';
            if (gradient) gradient.style.opacity = '0';
            if (image) image.style.transform = 'scale(1)';
        });

        return card;
    }

    // Render pagination - Matching Frontend
    function renderPagination() {
        const paginationDiv = document.getElementById('pagination');
        paginationDiv.innerHTML = '';

        if (!pagination.totalPages || pagination.totalPages <= 1) {
            return;
        }

        const paginationContainer = document.createElement('div');
        paginationContainer.className = 'flex justify-center items-center space-x-2';

        // Previous button
        const prevBtn = document.createElement('button');
        prevBtn.textContent = 'Previous';
        prevBtn.className = 'px-4 py-2 bg-gray-800 text-white rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-700 transition-colors';
        prevBtn.disabled = currentPage === 1;
        prevBtn.onclick = () => {
            currentPage = Math.max(1, currentPage - 1);
            fetchContent();
        };
        paginationContainer.appendChild(prevBtn);

        // Page numbers
        const pageNumbersContainer = document.createElement('div');
        pageNumbersContainer.className = 'flex items-center space-x-2';

        const totalPages = pagination.totalPages;
        const maxPages = 5;
        let pages = [];

        if (totalPages <= maxPages) {
            pages = Array.from({ length: totalPages }, (_, i) => i + 1);
        } else if (currentPage <= 3) {
            pages = Array.from({ length: maxPages }, (_, i) => i + 1);
        } else if (currentPage >= totalPages - 2) {
            pages = Array.from({ length: maxPages }, (_, i) => totalPages - maxPages + i + 1);
        } else {
            pages = Array.from({ length: maxPages }, (_, i) => currentPage - 2 + i);
        }

        pages.forEach(pageNum => {
            const pageBtn = document.createElement('button');
            pageBtn.textContent = pageNum;
            pageBtn.className = `px-4 py-2 rounded transition-colors ${
                currentPage === pageNum
                    ? 'bg-red-600 text-white'
                    : 'bg-gray-800 text-white hover:bg-gray-700'
            }`;
            pageBtn.onclick = () => {
                currentPage = pageNum;
                fetchContent();
            };
            pageNumbersContainer.appendChild(pageBtn);
        });

        paginationContainer.appendChild(pageNumbersContainer);

        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.textContent = 'Next';
        nextBtn.className = 'px-4 py-2 bg-gray-800 text-white rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-700 transition-colors';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.onclick = () => {
            currentPage = Math.min(totalPages, currentPage + 1);
            fetchContent();
        };
        paginationContainer.appendChild(nextBtn);

        paginationDiv.appendChild(paginationContainer);
    }
</script>
@endpush
@endsection

