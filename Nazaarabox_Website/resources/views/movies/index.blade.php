@extends('layouts.app')

@section('title', 'Movies - Nazaara Box')

@section('seo_title', 'Browse All Movies - Nazaara Box')
@section('seo_description', 'Browse and filter thousands of movies on Nazaara Box. Find movies by genre, year, rating, and more. Watch your favorite films in HD quality.')
@section('seo_type', 'website')
@section('seo_url', route('movies.index'))

@push('styles')
<style>
    /* Adjust padding for fixed header - Matching header height */
    .movies-page-wrapper {
        padding-top: 64px; /* Mobile header height */
    }

    @media (min-width: 768px) {
        .movies-page-wrapper {
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
</style>
@endpush

@section('content')
<div class="min-h-screen bg-black movies-page-wrapper">
    <div class="w-full px-4 md:px-8 lg:px-12 xl:px-16 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-4 md:mb-0">Movies</h1>
            
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
                    <option value="title">A-Z</option>
                </select>

                <!-- Filter Toggle -->
                <button
                    id="filter-toggle-btn"
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

        <!-- Filters Panel - Matching Frontend MoviesPage.jsx -->
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
                <!-- Genre Filter - Matching Frontend MoviesPage.jsx -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Genre</label>
                    <select
                        id="genre-filter"
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 outline-none focus:border-red-600"
                    >
                        <option value="">All Genres</option>
                    </select>
                </div>

                <!-- Category Filter - Matching Frontend MoviesPage.jsx -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Category</label>
                    <select
                        id="category-filter"
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 outline-none focus:border-red-600"
                    >
                        <option value="">All Categories</option>
                    </select>
                </div>

                <!-- Country Filter - Matching Frontend MoviesPage.jsx -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Country</label>
                    <select
                        id="country-filter"
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 outline-none focus:border-red-600"
                    >
                        <option value="">All Countries</option>
                    </select>
                </div>

                <!-- Year Filter - Matching Frontend MoviesPage.jsx -->
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

                <!-- Language Filter - Matching Frontend MoviesPage.jsx -->
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Language</label>
                    <select
                        id="language-filter"
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 outline-none focus:border-red-600"
                    >
                        <option value="">All Languages</option>
                    </select>
                </div>

                <!-- Rating Filter - Matching Frontend MoviesPage.jsx -->
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
        <div id="movies-content" style="display: none;">
            <!-- Results Info -->
            <div id="results-info" class="text-gray-400 mb-4"></div>

            <!-- Movies Grid -->
            <div id="movies-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6 mb-8"></div>

            <!-- No Results Message -->
            <div id="no-results" class="text-center py-20" style="display: none;">
                <p class="text-gray-400 text-lg">No movies found matching your filters.</p>
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

@push('styles')
<style>
    /* Movie Card Styles - Matching Frontend MovieCard.jsx */
    .movie-card {
        position: relative;
        width: 100%;
        transition: all 0.3s;
        cursor: pointer;
    }

    .movie-card-poster {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        width: 100%;
        aspect-ratio: 2/3; /* TMDB poster aspect ratio */
    }

    .movie-card-poster img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .movie-card:hover .movie-card-poster img {
        transform: scale(1.1);
    }

    .movie-card-gradient {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent, transparent);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .movie-card:hover .movie-card-gradient {
        opacity: 1;
    }

    .movie-card-badge {
        position: absolute;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .movie-card-badge-view {
        top: 8px;
        right: 8px;
        background-color: var(--primary-red);
        color: white;
    }

    .movie-card-badge-rating {
        top: 8px;
        left: 8px;
        background-color: rgba(234, 179, 8, 0.9);
        color: white;
    }

    .movie-card-hover-info {
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

    .movie-card:hover .movie-card-hover-info {
        display: block;
    }
    
    /* Ensure hover info buttons are clickable */
    .movie-card-hover-info button {
        pointer-events: auto;
    }

    .movie-card-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }

    .movie-card-action-btn {
        padding: 8px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .movie-card-action-btn-primary {
        background-color: white;
        color: black;
    }

    .movie-card-action-btn-primary:hover {
        background-color: rgba(255,255,255,0.8);
    }

    .movie-card-action-btn-secondary {
        border: 2px solid #9ca3af;
        color: white;
        background: transparent;
    }

    .movie-card-action-btn-secondary:hover {
        border-color: white;
    }

    .movie-card-title {
        color: white;
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 8px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .movie-card-metadata {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        margin-bottom: 8px;
    }

    .movie-card-match {
        color: #4ade80;
        font-weight: 600;
    }

    .movie-card-meta-item {
        color: #9ca3af;
    }

    .movie-card-genres {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        font-size: 12px;
        color: #9ca3af;
    }

    .movie-card-genres span:not(:last-child)::after {
        content: ' • ';
        margin-left: 4px;
    }

    .movie-card:hover .movie-card-poster img {
        transform: scale(1.05);
    }

    .movie-card:hover .movie-card-gradient {
        opacity: 1;
    }

    .movie-card:hover .movie-card-hover-info {
        display: block !important;
    }
    
    /* Movie Title Below Card */
    .movie-card-title-below {
        color: white;
        font-weight: 600;
        font-size: 14px;
        margin-top: 12px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        transition: color 0.3s;
    }
    
    .movie-card:hover .movie-card-title-below {
        color: #dc2626; /* Red on hover */
    }
</style>
@endpush

{{-- Filter Sidebar Component --}}
<x-filter-sidebar />

@push('scripts')
<script>
    // State management - Matching Frontend MoviesPage.jsx
    let movies = [];
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
        fetchMovies();
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

            // Populate genre filter (excluding 18+)
            const genreFilter = document.getElementById('genre-filter');
            genres
                .filter(genre => {
                    const genreName = (genre.name || '').toLowerCase();
                    return !genreName.includes('18+') && !genreName.includes('18');
                })
                .forEach(genre => {
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

    // Fetch movies - Matching Frontend
    async function fetchMovies() {
        loading = true;
        document.getElementById('loading-state').style.display = 'flex';
        document.getElementById('movies-content').style.display = 'none';

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

            const response = await apiFetch(`${API_BASE_URL}/movies?${params}`);
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
            
            pagination = result.data?.pagination || result.pagination || {};

            // Update URL to reflect current state
            updateURL();

            renderMovies();
        } catch (error) {
            console.error('Error fetching movies:', error);
        } finally {
            loading = false;
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('movies-content').style.display = 'block';
        }
    }

    // Render movies - Matching Frontend MovieCard.jsx
    function renderMovies() {
        const grid = document.getElementById('movies-grid');
        const resultsInfo = document.getElementById('results-info');
        const noResults = document.getElementById('no-results');

        grid.innerHTML = '';

        if (movies.length === 0) {
            noResults.style.display = 'block';
            resultsInfo.textContent = '';
            return;
        }

        noResults.style.display = 'none';

        // Results info
        if (pagination.total > 0) {
            const start = ((currentPage - 1) * 20) + 1;
            const end = Math.min(currentPage * 20, pagination.total);
            resultsInfo.textContent = `Showing ${start} - ${end} of ${pagination.total} movies`;
        }

        // Render movie cards
        movies.forEach(movie => {
            const card = createMovieCard(movie);
            grid.appendChild(card);
        });

        renderPagination();
    }

    // Create movie card - Using same structure as Frontend MovieCard.jsx
    function createMovieCard(movie) {
        const card = document.createElement('div');
        card.className = 'movie-card group';
        card.style.cssText = 'position: relative; width: 100%; transition: all 0.3s; cursor: pointer;';
        
        // Handle poster path - support both relative paths and full URLs
        let imageUrl = movie.poster_path || '/images/placeholder.svg';
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

        const rating = movie.vote_average ? parseFloat(movie.vote_average).toFixed(1) : null;
        const matchPercent = movie.vote_average ? Math.round(parseFloat(movie.vote_average) * 10) : null;
        const year = movie.release_date ? new Date(movie.release_date).getFullYear() : null;
        const runtime = movie.runtime ? `${Math.floor(movie.runtime / 60)}h ${movie.runtime % 60}m` : null;

        card.innerHTML = `
            <div class="movie-card-poster">
                <img src="${imageUrl}" alt="${movie.title || 'Untitled'}" loading="lazy" onerror="this.src='/images/placeholder.svg'" class="movie-card-image">
                <div class="movie-card-gradient"></div>
                
                ${movie.view_count !== undefined && movie.view_count > 0 ? `
                <div style="position: absolute; top: 8px; right: 8px; background-color: #dc2626; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
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

            <!-- Movie Title Below Card -->
            <div class="movie-card-title-below">${movie.title || 'Untitled'}</div>

            <!-- Hover Info Below Card -->
            <div class="movie-card-hover-info">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <button onclick="event.stopPropagation(); window.location.href='/movie/${movie.id}';" 
                            style="background-color: white; color: black; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; border: none; cursor: pointer;"
                            onmouseover="this.style.backgroundColor='rgba(255,255,255,0.8)'"
                            onmouseout="this.style.backgroundColor='white'">
                        <svg fill="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </button>
                    <button onclick="event.stopPropagation(); window.location.href='/movie/${movie.id}';" 
                            style="border: 2px solid #9ca3af; color: white; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; background: transparent; cursor: pointer; margin-left: auto;"
                            onmouseover="this.style.borderColor='white'"
                            onmouseout="this.style.borderColor='#9ca3af'">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </button>
                </div>
                <h3 style="color: white; font-weight: 600; font-size: 14px; margin-bottom: 8px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${movie.title || 'Untitled'}</h3>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; margin-bottom: 8px;">
                    ${matchPercent ? `<span style="color: #4ade80; font-weight: 600;">${matchPercent}% Match</span>` : ''}
                    ${year ? `<span style="color: #9ca3af;">${year}</span>` : ''}
                    ${runtime ? `<span style="color: #9ca3af;">${runtime}</span>` : ''}
                </div>
                ${movie.genres && movie.genres.length > 0 ? `
                <div style="display: flex; flex-wrap: wrap; gap: 4px; font-size: 12px; color: #9ca3af;">
                    ${movie.genres
                        .filter(genre => {
                            const genreName = (genre.name || genre || '').toLowerCase();
                            return !genreName.includes('18+') && !genreName.includes('18');
                        })
                        .slice(0, 3).map((genre, index, filteredGenres) => {
                        const genreName = genre.name || genre;
                        return `<span>${genreName}</span>${index < Math.min(2, filteredGenres.length - 1) ? ' • ' : ''}`;
                    }).join('')}
                </div>
                ` : ''}
            </div>
        `;

        // Add click handler
        card.addEventListener('click', () => {
            window.location.href = `/movie/${movie.id}`;
        });

        // Hover effects are handled by CSS, but we keep these for compatibility
        // The CSS :hover selectors will handle the display of hover info

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
            fetchMovies();
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
                fetchMovies();
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
            fetchMovies();
        });
        paginationDiv.appendChild(nextBtn);
    }

    // Apply filters from sidebar - Matching Frontend FilterSidebar.jsx
    function applyFilters(filters) {
        document.getElementById('genre-filter').value = filters.genre || '';
        document.getElementById('country-filter').value = filters.country || '';
        document.getElementById('category-filter').value = filters.category || '';
        document.getElementById('year-filter').value = filters.year || '';
        document.getElementById('language-filter').value = filters.language || '';
        document.getElementById('sort-select').value = filters.sort_by || 'foryou';
        // Apply filters to current state
        selectedGenre = filters.genre || '';
        selectedCountry = filters.country || '';
        selectedCategory = filters.category || '';
        selectedYear = filters.year || '';
        selectedLanguage = filters.language || '';
        sortBy = filters.sort_by || 'foryou';
        order = filters.order || 'desc';
        currentPage = 1;
        updateFilterBadge();
        updateURL();
        fetchMovies();
    }

    // Clear filters function for sidebar
    function clearFilters() {
        selectedGenre = '';
        selectedCountry = '';
        selectedCategory = '';
        selectedYear = '';
        selectedLanguage = '';
        selectedRating = '';
        sortBy = 'foryou';
        order = 'desc';
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
        fetchMovies();
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', () => {
        initFromURL();
        fetchUtilityData().then(() => {
            updateFilterBadge();
            fetchMovies();
        });

        // Filter toggle - Matching Frontend MoviesPage.jsx
        document.getElementById('filter-toggle-btn').addEventListener('click', () => {
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
            fetchMovies(); // Fetch results immediately
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
            fetchMovies(); // Fetch results immediately
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
