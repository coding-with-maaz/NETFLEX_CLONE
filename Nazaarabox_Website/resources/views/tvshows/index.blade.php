@extends('layouts.app')

@section('title', 'TV Shows - Nazaara Box')

@section('seo_title', 'Browse All TV Shows - Nazaara Box')
@section('seo_description', 'Browse and filter thousands of TV shows on Nazaara Box. Find shows by genre, year, rating, and more. Watch complete series with all episodes.')
@section('seo_type', 'website')
@section('seo_url', route('tvshows.index'))

@push('styles')
<style>
    /* Adjust padding for fixed header - Matching header height */
    .tvshows-page-wrapper {
        padding-top: 64px; /* Mobile header height */
    }

    @media (min-width: 768px) {
        .tvshows-page-wrapper {
            padding-top: 80px; /* Desktop header height */
        }
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
        background-color: #dc2626; /* bg-red-600 */
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        border: none;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    #filter-toggle-btn:hover {
        background-color: #b91c1c; /* hover:bg-red-700 */
    }

    #filter-toggle-btn svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
    }

    #filter-toggle-btn span {
        white-space: nowrap;
    }

    #filter-badge {
        background-color: white;
        color: #dc2626;
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

    /* TV Show Card Styles - Matching Frontend TVShowCard.jsx */
    .tvshow-card {
        position: relative;
        width: 100%;
        transition: all 0.3s;
        cursor: pointer;
    }

    .tvshow-card-poster {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        width: 100%;
        aspect-ratio: 2/3; /* TMDB poster aspect ratio */
    }

    .tvshow-card-poster img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .tvshow-card:hover .tvshow-card-poster img {
        transform: scale(1.05);
    }

    .tvshow-card-gradient {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent, transparent);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .tvshow-card:hover .tvshow-card-gradient {
        opacity: 1;
    }

    .tvshow-card-hover-info {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 12px);
        background-color: #181818;
        border-radius: 8px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.5);
        padding: 16px;
        z-index: 50;
        display: none;
        width: 100%;
        pointer-events: none;
    }

    .tvshow-card:hover .tvshow-card-hover-info {
        display: block !important;
    }
    
    /* Ensure hover info buttons are clickable */
    .tvshow-card-hover-info button {
        pointer-events: auto;
    }
    
    /* TV Show Title Below Card */
    .tvshow-card-title-below {
        color: white;
        font-weight: 600;
        font-size: 14px;
        margin-top: 12px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        transition: color 0.3s;
    }
    
    .tvshow-card:hover .tvshow-card-title-below {
        color: #dc2626; /* Red on hover */
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-black tvshows-page-wrapper">
    <div class="w-full px-4 md:px-8 lg:px-12 xl:px-16 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-4 md:mb-0">TV Shows</h1>
            
            <div class="flex items-center space-x-4">
                <!-- Sort Dropdown -->
                <select
                    id="sort-select"
                    class="bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 outline-none focus:border-red-600"
                >
                    <option value="foryou">For You</option>
                    <option value="hottest">Hottest</option>
                    <option value="latest">Latest</option>
                    <option value="rating">Top Rated</option>
                    <option value="name">A-Z</option>
                </select>

                <!-- Filter Toggle -->
                <button
                    id="filter-toggle-btn"
                    onclick="openFilterSidebar()"
                    class="flex items-center space-x-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition-colors"
                >
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <span>Filters</span>
                    <span id="filter-badge" class="bg-white text-red-600 rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold" style="display: none;">
                        !
                    </span>
                </button>
            </div>
        </div>

        <!-- Filters Panel -->
        <div id="filters-panel" class="bg-gray-900 border border-gray-800 rounded-lg p-6 mb-8" style="display: none;">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-white">Filters</h2>
                <div class="flex items-center space-x-4">
                    <button
                        id="clear-filters-btn"
                        class="text-red-500 hover:text-red-400 text-sm flex items-center space-x-1"
                        style="display: none;"
                    >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span>Clear All</span>
                    </button>
                    <button
                        id="close-filters-btn"
                        class="text-gray-400 hover:text-white"
                    >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Genre Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Genre</label>
                    <select
                        id="genre-filter"
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 outline-none focus:border-red-600"
                    >
                        <option value="">All Genres</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Category</label>
                    <select
                        id="category-filter"
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 outline-none focus:border-red-600"
                    >
                        <option value="">All Categories</option>
                    </select>
                </div>

                <!-- Country Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Country</label>
                    <select
                        id="country-filter"
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 outline-none focus:border-red-600"
                    >
                        <option value="">All Countries</option>
                    </select>
                </div>

                <!-- Year Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Year</label>
                    <select
                        id="year-filter"
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
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- Language Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Language</label>
                    <select
                        id="language-filter"
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 outline-none focus:border-red-600"
                    >
                        <option value="">All Languages</option>
                    </select>
                </div>

                <!-- Rating Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Rating</label>
                    <select
                        id="rating-filter"
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

        <!-- Loading State -->
        <div id="loading-state" class="flex justify-center items-center py-20">
            <div class="spinner"></div>
        </div>

        <!-- Content -->
        <div id="tvshows-content" style="display: none;">
            <!-- Results Info -->
            <div id="results-info" class="text-gray-400 mb-4"></div>

            <!-- TV Shows Grid -->
            <div id="tvshows-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6 mb-8"></div>

            <!-- No Results Message -->
            <div id="no-results" class="text-center py-20" style="display: none;">
                <p class="text-gray-400 text-lg">No TV shows found matching your filters.</p>
                <button
                    id="clear-filters-no-results"
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

{{-- Filter Sidebar Component --}}
<x-filter-sidebar />

@push('scripts')
<script>
    // State management - Matching Frontend TVShowsPage.jsx
    let tvShows = [];
    let loading = false;
    let pagination = {};
    let showFilters = false;
    
    // Filter states
    let genres = [];
    let countries = [];
    let categories = [];
    let languages = [];
    let utilsData = {}; // Store for sidebar
    
    // Selected filters
    let selectedGenre = '';
    let selectedCountry = '';
    let selectedCategory = '';
    let selectedYear = '';
    let selectedLanguage = '';
    let selectedRating = '';
    let sortBy = 'foryou';
    let currentPage = 1;

    // Initialize from URL params
    function initFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        selectedGenre = urlParams.get('genre') || '';
        selectedCountry = urlParams.get('country') || '';
        selectedCategory = urlParams.get('category') || '';
        selectedYear = urlParams.get('year') || '';
        selectedLanguage = urlParams.get('language') || '';
        selectedRating = urlParams.get('min_rating') || '';
        sortBy = urlParams.get('sort_by') || 'foryou';
        currentPage = parseInt(urlParams.get('page')) || 1;
    }

    // Check if any filters are active
    function hasActiveFilters() {
        return selectedGenre || selectedCountry || selectedCategory || selectedYear || selectedLanguage || selectedRating;
    }

    // Update URL with current filters
    function updateURL() {
        const urlParams = new URLSearchParams();
        if (selectedGenre && selectedGenre !== 'All') urlParams.set('genre', selectedGenre);
        if (selectedCountry && selectedCountry !== 'All') urlParams.set('country', selectedCountry);
        if (selectedCategory && selectedCategory !== 'All') urlParams.set('category', selectedCategory);
        if (selectedYear && selectedYear !== 'All') urlParams.set('year', selectedYear);
        if (selectedLanguage && selectedLanguage !== 'All') urlParams.set('language', selectedLanguage);
        if (selectedRating && selectedRating !== 'All') urlParams.set('min_rating', selectedRating);
        if (sortBy && sortBy !== 'foryou') urlParams.set('sort_by', sortBy);
        if (currentPage !== 1) urlParams.set('page', currentPage);
        
        const newURL = urlParams.toString() ? `${window.location.pathname}?${urlParams}` : window.location.pathname;
        window.history.pushState({}, '', newURL);
    }

    // Update filter badge visibility
    function updateFilterBadge() {
        const badge = document.getElementById('filter-badge');
        const clearBtn = document.getElementById('clear-filters-btn');
        const clearNoResults = document.getElementById('clear-filters-no-results');
        
        if (hasActiveFilters()) {
            badge.style.display = 'flex';
            clearBtn.style.display = 'flex';
            if (clearNoResults) clearNoResults.style.display = 'block';
        } else {
            badge.style.display = 'none';
            clearBtn.style.display = 'none';
            if (clearNoResults) clearNoResults.style.display = 'none';
        }
    }

    // Clear all filters
    function clearFilters() {
        selectedGenre = '';
        selectedCountry = '';
        selectedCategory = '';
        selectedYear = '';
        selectedLanguage = '';
        selectedRating = '';
        sortBy = 'foryou';
        currentPage = 1;

        document.getElementById('genre-filter').value = '';
        document.getElementById('country-filter').value = '';
        document.getElementById('category-filter').value = '';
        document.getElementById('year-filter').value = '';
        document.getElementById('language-filter').value = '';
        document.getElementById('rating-filter').value = '';
        document.getElementById('sort-select').value = 'foryou';

        updateFilterBadge();
        updateURL();
        fetchTVShows(); // Fetch results immediately after clearing
    }

    // Fetch utility data - Matching Frontend
    async function fetchUtilityData() {
        try {
            const response = await apiFetch(`${API_BASE_URL}/utils/all`);
            const result = await response.json();
            const data = result.data || result;

            genres = data.genres || [];
            countries = data.countries || [];
            categories = data.categories || [];
            languages = data.languages || [];
            utilsData = data; // Store for sidebar

            // Populate genre filter
            const genreFilter = document.getElementById('genre-filter');
            genres.forEach(genre => {
                const option = document.createElement('option');
                option.value = genre.name || genre.id;
                option.textContent = genre.name;
                if (selectedGenre === option.value) option.selected = true;
                genreFilter.appendChild(option);
            });

            // Populate category filter
            const categoryFilter = document.getElementById('category-filter');
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.name || category.id;
                option.textContent = category.name;
                if (selectedCategory === option.value) option.selected = true;
                categoryFilter.appendChild(option);
            });

            // Populate country filter
            const countryFilter = document.getElementById('country-filter');
            countries.forEach(country => {
                const option = document.createElement('option');
                option.value = country.name || country.id;
                option.textContent = country.name;
                if (selectedCountry === option.value) option.selected = true;
                countryFilter.appendChild(option);
            });

            // Populate language filter
            const languageFilter = document.getElementById('language-filter');
            languages.forEach(language => {
                const option = document.createElement('option');
                option.value = language.name || language.id;
                option.textContent = language.name;
                if (selectedLanguage === option.value) option.selected = true;
                languageFilter.appendChild(option);
            });

            // Also populate sidebar filters
            if (typeof populateSidebarFilters === 'function') {
                populateSidebarFilters(utilsData);
            }
        } catch (error) {
            console.error('Error fetching utility data:', error);
        }
    }

    // Apply filters from sidebar - Matching Frontend FilterSidebar.jsx
    function applyFilters(filters) {
        document.getElementById('genre-filter').value = filters.genre || '';
        document.getElementById('category-filter').value = filters.category || '';
        document.getElementById('country-filter').value = filters.country || '';
        document.getElementById('year-filter').value = filters.year || '';
        document.getElementById('language-filter').value = filters.language || '';
        document.getElementById('sort-select').value = filters.sort_by || 'foryou';
        // Apply filters to current state
        selectedGenre = filters.genre || '';
        selectedCategory = filters.category || '';
        selectedCountry = filters.country || '';
        selectedYear = filters.year || '';
        selectedLanguage = filters.language || '';
        sortBy = filters.sort_by || 'foryou';
        order = filters.order || 'desc';
        currentPage = 1;
        updateFilterBadge();
        updateURL();
        fetchTVShows();
    }

    // Fetch TV shows - Matching Frontend
    async function fetchTVShows() {
        loading = true;
        document.getElementById('loading-state').style.display = 'flex';
        document.getElementById('tvshows-content').style.display = 'none';

        try {
            const params = new URLSearchParams({
                page: currentPage,
                limit: 20,
                sort_by: sortBy,
            });

            if (selectedGenre && selectedGenre !== 'All') params.append('genre', selectedGenre);
            if (selectedCountry && selectedCountry !== 'All') params.append('country', selectedCountry);
            if (selectedCategory && selectedCategory !== 'All') params.append('category', selectedCategory);
            if (selectedYear && selectedYear !== 'All') params.append('year', selectedYear);
            if (selectedLanguage && selectedLanguage !== 'All') params.append('language', selectedLanguage);
            if (selectedRating && selectedRating !== 'All') {
                params.append('min_rating', parseFloat(selectedRating.replace('+', '')));
            }

            const response = await apiFetch(`${API_BASE_URL}/tvshows?${params}`);
            const result = await response.json();

            tvShows = result.data?.tvShows || result.data || [];
            pagination = result.data?.pagination || result.pagination || {};

            // Update URL to reflect current state
            updateURL();

            renderTVShows();
        } catch (error) {
            console.error('Error fetching TV shows:', error);
        } finally {
            loading = false;
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('tvshows-content').style.display = 'block';
        }
    }

    // Render TV shows - Matching Frontend TVShowCard.jsx
    function renderTVShows() {
        const grid = document.getElementById('tvshows-grid');
        const resultsInfo = document.getElementById('results-info');
        const noResults = document.getElementById('no-results');

        grid.innerHTML = '';

        if (tvShows.length === 0) {
            noResults.style.display = 'block';
            resultsInfo.textContent = '';
            return;
        }

        noResults.style.display = 'none';

        // Results info
        if (pagination.total > 0) {
            const start = ((currentPage - 1) * 20) + 1;
            const end = Math.min(currentPage * 20, pagination.total);
            resultsInfo.textContent = `Showing ${start} - ${end} of ${pagination.total} TV shows`;
        }

        // Render TV show cards
        tvShows.forEach(tvShow => {
            const card = createTVShowCard(tvShow);
            grid.appendChild(card);
        });

        renderPagination();
    }

    // Create TV show card - Matching Frontend TVShowCard.jsx
    function createTVShowCard(tvShow) {
        const card = document.createElement('div');
        card.className = 'tvshow-card group';
        card.style.cssText = 'position: relative; width: 100%; transition: all 0.3s; cursor: pointer;';
        
        // Handle poster path - support both relative paths and full URLs
        let imageUrl = tvShow.poster_path || '/images/placeholder.svg';
        if (imageUrl && !imageUrl.startsWith('http') && !imageUrl.startsWith('/')) {
            if (imageUrl.startsWith('images/')) {
                imageUrl = '/' + imageUrl;
            } else {
                imageUrl = '/images/placeholder.svg';
            }
        } else if (imageUrl && imageUrl.startsWith('/') && !imageUrl.startsWith('/images/')) {
            // TMDB image path - prepend base URL
            imageUrl = 'https://image.tmdb.org/t/p/w500' + imageUrl;
        }

        const rating = tvShow.vote_average ? parseFloat(tvShow.vote_average).toFixed(1) : null;
        const matchPercent = tvShow.vote_average ? Math.round(parseFloat(tvShow.vote_average) * 10) : null;
        const year = tvShow.first_air_date ? new Date(tvShow.first_air_date).getFullYear() : null;
        const seasons = tvShow.number_of_seasons ? `${tvShow.number_of_seasons} Season${tvShow.number_of_seasons > 1 ? 's' : ''}` : null;
        const episodes = tvShow.number_of_episodes || null;
        const name = tvShow.name ? encodeURIComponent(tvShow.name) : '';

        card.innerHTML = `
            <div class="tvshow-card-poster">
                <img src="${imageUrl}" alt="${tvShow.name || 'Untitled'}" loading="lazy" onerror="this.src='/images/placeholder.svg'" class="tvshow-card-image">
                <div class="tvshow-card-gradient"></div>
                
                ${tvShow.view_count !== undefined && tvShow.view_count > 0 ? `
                <div style="position: absolute; top: 8px; right: 8px; background-color: #dc2626; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
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

            <!-- TV Show Title Below Card -->
            <div class="tvshow-card-title-below">${tvShow.name || 'Untitled'}</div>

            <!-- Hover Info Below Card -->
            <div class="tvshow-card-hover-info">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <button onclick="event.stopPropagation(); window.location.href='/tvshow/${tvShow.id}${name ? '?name=' + name : ''}';" 
                            style="background-color: white; color: black; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; border: none; cursor: pointer;"
                            onmouseover="this.style.backgroundColor='rgba(255,255,255,0.8)'"
                            onmouseout="this.style.backgroundColor='white'">
                        <svg fill="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </button>
                    <button onclick="event.stopPropagation(); window.location.href='/tvshow/${tvShow.id}${name ? '?name=' + name : ''}';" 
                            style="border: 2px solid #9ca3af; color: white; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; background: transparent; cursor: pointer; margin-left: auto;"
                            onmouseover="this.style.borderColor='white'"
                            onmouseout="this.style.borderColor='#9ca3af'">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </button>
                </div>
                <h3 style="color: white; font-weight: 600; font-size: 14px; margin-bottom: 8px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${tvShow.name || 'Untitled'}</h3>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; margin-bottom: 8px;">
                    ${matchPercent ? `<span style="color: #4ade80; font-weight: 600;">${matchPercent}% Match</span>` : ''}
                    ${year ? `<span style="color: #9ca3af;">${year}</span>` : ''}
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

        // Add click handler
        card.addEventListener('click', () => {
            window.location.href = `/tvshow/${tvShow.id}${name ? '?name=' + name : ''}`;
        });

        // Hover effects are handled by CSS
        return card;
    }

    // Render pagination - Matching Frontend
    function renderPagination() {
        const paginationDiv = document.getElementById('pagination');
        paginationDiv.innerHTML = '';

        if (!pagination.totalPages || pagination.totalPages <= 1) {
            return;
        }

        // Previous button
        const prevBtn = document.createElement('button');
        prevBtn.textContent = 'Previous';
        prevBtn.className = 'px-4 py-2 bg-gray-800 text-white rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-700 transition-colors';
        prevBtn.disabled = currentPage === 1;
        prevBtn.addEventListener('click', () => {
            currentPage = Math.max(1, currentPage - 1);
            fetchTVShows();
        });
        paginationDiv.appendChild(prevBtn);

        // Page numbers
        const pageNumbersDiv = document.createElement('div');
        pageNumbersDiv.className = 'flex items-center space-x-2';

        const maxVisible = 5;
        let startPage, endPage;

        if (pagination.totalPages <= maxVisible) {
            startPage = 1;
            endPage = pagination.totalPages;
        } else if (currentPage <= 3) {
            startPage = 1;
            endPage = maxVisible;
        } else if (currentPage >= pagination.totalPages - 2) {
            startPage = pagination.totalPages - maxVisible + 1;
            endPage = pagination.totalPages;
        } else {
            startPage = currentPage - 2;
            endPage = currentPage + 2;
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.textContent = i;
            pageBtn.className = `px-4 py-2 rounded transition-colors ${
                currentPage === i
                    ? 'bg-red-600 text-white'
                    : 'bg-gray-800 text-white hover:bg-gray-700'
            }`;
            pageBtn.addEventListener('click', () => {
                currentPage = i;
                fetchTVShows();
            });
            pageNumbersDiv.appendChild(pageBtn);
        }

        paginationDiv.appendChild(pageNumbersDiv);

        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.textContent = 'Next';
        nextBtn.className = 'px-4 py-2 bg-gray-800 text-white rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-700 transition-colors';
        nextBtn.disabled = currentPage === pagination.totalPages;
        nextBtn.addEventListener('click', () => {
            currentPage = Math.min(pagination.totalPages, currentPage + 1);
            fetchTVShows();
        });
        paginationDiv.appendChild(nextBtn);
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', () => {
        initFromURL();
        fetchUtilityData().then(() => {
            updateFilterBadge();
            fetchTVShows();
        });

        // Filter toggle - Open sidebar instead
        document.getElementById('filter-toggle-btn').addEventListener('click', (e) => {
            if (e.target.onclick) return; // If onclick is set, use it (sidebar)
            showFilters = !showFilters;
            document.getElementById('filters-panel').style.display = showFilters ? 'block' : 'none';
        });

        // Close filters
        document.getElementById('close-filters-btn').addEventListener('click', () => {
            showFilters = false;
            document.getElementById('filters-panel').style.display = 'none';
        });

        // Clear filters
        document.getElementById('clear-filters-btn').addEventListener('click', clearFilters);
        document.getElementById('clear-filters-no-results')?.addEventListener('click', clearFilters);

        // Helper function to update filters and fetch immediately
        function updateFilterAndFetch() {
            currentPage = 1;
            updateFilterBadge();
            updateURL();
            fetchTVShows(); // Fetch results immediately
        }

        // Filter change handlers - Fetch results immediately on change
        document.getElementById('genre-filter').addEventListener('change', (e) => {
            selectedGenre = e.target.value || '';
            updateFilterAndFetch();
        });

        document.getElementById('category-filter').addEventListener('change', (e) => {
            selectedCategory = e.target.value || '';
            updateFilterAndFetch();
        });

        document.getElementById('country-filter').addEventListener('change', (e) => {
            selectedCountry = e.target.value || '';
            updateFilterAndFetch();
        });

        document.getElementById('year-filter').addEventListener('change', (e) => {
            selectedYear = e.target.value || '';
            updateFilterAndFetch();
        });

        document.getElementById('language-filter').addEventListener('change', (e) => {
            selectedLanguage = e.target.value || '';
            updateFilterAndFetch();
        });

        document.getElementById('rating-filter').addEventListener('change', (e) => {
            selectedRating = e.target.value || '';
            updateFilterAndFetch();
        });

        // Sort change handler - Fetch results immediately
        document.getElementById('sort-select').addEventListener('change', (e) => {
            sortBy = e.target.value || 'foryou';
            currentPage = 1;
            updateURL();
            fetchTVShows(); // Fetch results immediately
        });

        // Set initial values from URL
        document.getElementById('sort-select').value = sortBy;
        document.getElementById('genre-filter').value = selectedGenre;
        document.getElementById('category-filter').value = selectedCategory;
        document.getElementById('country-filter').value = selectedCountry;
        document.getElementById('year-filter').value = selectedYear;
        document.getElementById('language-filter').value = selectedLanguage;
        document.getElementById('rating-filter').value = selectedRating;
    });
</script>
@endpush
@endsection
