@extends('layouts.app')

@section('title', 'Search - Nazaara Box')

@section('seo_title', 'Search Movies & TV Shows - Nazaara Box')
@section('seo_description', 'Search for your favorite movies, TV shows, and episodes on Nazaara Box. Find content by title, genre, year, and more.')
@section('seo_type', 'website')
@section('seo_url', route('search'))

@push('styles')
<style>
    .search-page-wrapper {
        padding-top: 64px;
    }

    @media (min-width: 768px) {
        .search-page-wrapper {
            padding-top: 80px;
        }
    }

    .search-tab {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .search-tab.active {
        background-color: #dc2626;
        color: white;
    }

    .search-tab.inactive {
        background-color: #1f2937;
        color: #9ca3af;
    }

    .search-tab.inactive:hover {
        color: white;
        background-color: #374151;
    }

    .filter-select {
        background-color: #1f2937;
        color: white;
        border: 1px solid #374151;
        border-radius: 6px;
        padding: 8px 16px;
        outline: none;
        font-size: 14px;
        cursor: pointer;
        width: 100%;
    }

    .filter-select:focus {
        border-color: #dc2626;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-black search-page-wrapper pb-12">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <div class="flex items-center space-x-4 mb-6">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-8 w-8 text-red-600">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <h1 class="text-3xl font-bold text-white">Search Results</h1>
            </div>

            <div class="flex flex-col md:flex-row gap-4 mb-6">
                <div class="flex-1 relative">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input
                        type="text"
                        id="search-input"
                        placeholder="Search movies, TV shows..."
                        value="{{ request()->get('q') }}"
                        class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 pl-10 outline-none focus:border-red-600"
                        onkeypress="if(event.key === 'Enter') handleSearch()"
                    >
                </div>
                <button onclick="handleSearch()" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded font-semibold transition-colors">
                    Search
                </button>
                <button onclick="toggleFilters()" class="px-6 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded font-semibold transition-colors flex items-center gap-2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filters
                </button>
            </div>

            <div class="flex space-x-1 mb-6" id="search-tabs">
                <button onclick="setSearchType('all')" id="tab-all" class="search-tab active">All (<span id="count-all">0</span>)</button>
                <button onclick="setSearchType('movies')" id="tab-movies" class="search-tab inactive">Movies (<span id="count-movies">0</span>)</button>
                <button onclick="setSearchType('tvshows')" id="tab-tvshows" class="search-tab inactive">TV Shows (<span id="count-tvshows">0</span>)</button>
            </div>

            <div id="filters-panel" class="bg-gray-900 border border-gray-800 rounded-lg p-6 mb-6" style="display: none;">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-white">Filters</h2>
                    <button onclick="resetFilters()" class="text-red-500 hover:text-red-400 text-sm">Reset Filters</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Genre</label>
                        <select id="filter-genre" class="filter-select">
                            <option value="">All Genres</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Country</label>
                        <select id="filter-country" class="filter-select">
                            <option value="">All Countries</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Year</label>
                        <select id="filter-year" class="filter-select">
                            <option value="">All Years</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Language</label>
                        <select id="filter-language" class="filter-select">
                            <option value="">All Languages</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Sort By</label>
                        <select id="filter-sort" class="filter-select">
                            <option value="popularity">Popularity</option>
                            <option value="title">Title</option>
                            <option value="release_date">Release Date</option>
                            <option value="vote_average">Rating</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Order</label>
                        <select id="filter-order" class="filter-select">
                            <option value="desc">Descending</option>
                            <option value="asc">Ascending</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div id="loading-state" class="flex items-center justify-center py-12" style="display: none;">
            <div class="spinner"></div>
        </div>

        <div id="search-results">
            <div id="results-summary" class="mb-6"></div>
            
            <div id="movies-section" class="mb-8" style="display: none;">
                <h2 class="text-2xl font-bold text-white mb-4 flex items-center">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-6 w-6 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                    </svg>
                    Movies (<span id="movies-count">0</span>)
                </h2>
                <div id="movies-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6"></div>
            </div>

            <div id="tvshows-section" class="mb-8" style="display: none;">
                <h2 class="text-2xl font-bold text-white mb-4 flex items-center">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-6 w-6 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    TV Shows (<span id="tvshows-count">0</span>)
                </h2>
                <div id="tvshows-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6"></div>
            </div>

            <div id="no-results" class="text-center py-12" style="display: none;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-16 w-16 text-gray-600 mx-auto mb-4">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-white mb-2">No results found</h3>
                <p class="text-gray-400 mb-4">Try adjusting your search terms or filters</p>
                <button onclick="resetFilters()" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded font-semibold transition-colors">Clear Filters</button>
            </div>

            <div id="start-search" class="text-center py-12" style="display: none;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-16 w-16 text-gray-600 mx-auto mb-4">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-white mb-2">Start your search</h3>
                <p class="text-gray-400">Enter a movie or TV show title to get started</p>
            </div>

            <div id="pagination" class="flex justify-center items-center space-x-2 mt-8"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let searchResults = { movies: [], tvShows: [], totalMovies: 0, totalTVShows: 0 };
    let searchType = 'all';
    let currentPage = 1;
    let totalPages = 1;
    let filters = {};
    let utilsData = { genres: [], countries: [], languages: [], years: [] };

    async function loadUtils() {
        try {
            const response = await apiFetch(`${API_BASE_URL}/utils/all`);
            const result = await response.json();
            if (result.success) {
                utilsData = result.data || utilsData;
                populateFilters();
            }
        } catch (error) {
            console.error('Failed to load filters:', error);
        }
    }

    function populateFilters() {
        const genreSelect = document.getElementById('filter-genre');
        const countrySelect = document.getElementById('filter-country');
        const yearSelect = document.getElementById('filter-year');
        const languageSelect = document.getElementById('filter-language');

        if (genreSelect && utilsData.genres) {
            utilsData.genres
                .filter(genre => {
                    const genreName = (genre.name || '').toLowerCase();
                    return !genreName.includes('18+') && !genreName.includes('18');
                })
                .forEach(genre => {
                    const option = document.createElement('option');
                    option.value = genre.id;
                    option.textContent = genre.name;
                    genreSelect.appendChild(option);
                });
        }

        if (countrySelect && utilsData.countries) {
            utilsData.countries.forEach(country => {
                const option = document.createElement('option');
                option.value = country.id;
                option.textContent = country.name;
                countrySelect.appendChild(option);
            });
        }

        if (yearSelect && utilsData.years) {
            utilsData.years.forEach(year => {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                yearSelect.appendChild(option);
            });
        }

        if (languageSelect && utilsData.languages) {
            utilsData.languages.forEach(lang => {
                const option = document.createElement('option');
                option.value = lang.name || lang;
                option.textContent = lang.name || lang;
                languageSelect.appendChild(option);
            });
        }
    }

    function toggleFilters() {
        const panel = document.getElementById('filters-panel');
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    }

    function resetFilters() {
        document.getElementById('filter-genre').value = '';
        document.getElementById('filter-country').value = '';
        document.getElementById('filter-year').value = '';
        document.getElementById('filter-language').value = '';
        document.getElementById('filter-sort').value = 'popularity';
        document.getElementById('filter-order').value = 'desc';
        filters = {};
        currentPage = 1;
        if (document.getElementById('search-input').value) {
            performSearch();
        }
    }

    function setSearchType(type) {
        searchType = type;
        ['all', 'movies', 'tvshows'].forEach(t => {
            const btn = document.getElementById(`tab-${t}`);
            if (btn) btn.className = t === type ? 'search-tab active' : 'search-tab inactive';
        });
        renderResults();
    }

    function handleSearch() {
        const query = document.getElementById('search-input').value.trim();
        if (query) {
            currentPage = 1;
            const urlParams = new URLSearchParams();
            urlParams.set('q', query);
            window.history.pushState({}, '', `${window.location.pathname}?${urlParams}`);
            performSearch();
        }
    }

    async function performSearch() {
        const query = document.getElementById('search-input').value.trim();
        if (!query) {
            document.getElementById('start-search').style.display = 'block';
            document.getElementById('no-results').style.display = 'none';
            return;
        }

        document.getElementById('loading-state').style.display = 'flex';
        document.getElementById('search-results').style.display = 'none';
        document.getElementById('start-search').style.display = 'none';

        try {
            filters = {
                genre: document.getElementById('filter-genre').value,
                country: document.getElementById('filter-country').value,
                year: document.getElementById('filter-year').value,
                language: document.getElementById('filter-language').value,
                sort_by: document.getElementById('filter-sort').value,
                order: document.getElementById('filter-order').value
            };

            const params = new URLSearchParams();
            params.append('q', query);
            params.append('page', currentPage.toString());
            params.append('limit', '20');
            
            // Add filters only if they have values
            if (filters.genre && filters.genre !== '') {
                params.append('genre', filters.genre);
            }
            if (filters.year && filters.year !== '') {
                params.append('year', filters.year);
            }
            if (filters.language && filters.language !== '') {
                params.append('language', filters.language);
            }
            if (filters.sort_by && filters.sort_by !== '') {
                params.append('sort_by', filters.sort_by);
            }
            if (filters.order && filters.order !== '') {
                params.append('order', filters.order);
            }

            const results = { movies: [], tvShows: [], totalMovies: 0, totalTVShows: 0 };

            if (searchType === 'all' || searchType === 'movies') {
                try {
                    const response = await apiFetch(`${API_BASE_URL}/movies/search?${params}`);
                    const data = await response.json();
                    if (data.success) {
                        results.movies = data.data.movies || [];
                        results.totalMovies = data.data.pagination?.totalItems || 0;
                    }
                } catch (err) {
                    console.error('Movie search error:', err);
                }
            }

            if (searchType === 'all' || searchType === 'tvshows') {
                try {
                    const response = await apiFetch(`${API_BASE_URL}/tvshows/search?${params}`);
                    const data = await response.json();
                    if (data.success) {
                        results.tvShows = data.data.tvShows || [];
                        results.totalTVShows = data.data.pagination?.totalItems || 0;
                    }
                } catch (err) {
                    console.error('TV show search error:', err);
                }
            }

            searchResults = results;
            const totalResults = results.totalMovies + results.totalTVShows;
            totalPages = Math.ceil(totalResults / 20);

            renderResults();
        } catch (error) {
            console.error('Search error:', error);
        } finally {
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('search-results').style.display = 'block';
        }
    }

    function renderResults() {
        const totalResults = searchResults.totalMovies + searchResults.totalTVShows;
        document.getElementById('count-all').textContent = totalResults;
        document.getElementById('count-movies').textContent = searchResults.totalMovies;
        document.getElementById('count-tvshows').textContent = searchResults.totalTVShows;

        const query = document.getElementById('search-input').value.trim();
        const summary = document.getElementById('results-summary');
        if (query && totalResults > 0) {
            summary.innerHTML = `<p class="text-gray-400">Found ${totalResults} result${totalResults !== 1 ? 's' : ''} for "${query}"</p>`;
        } else {
            summary.innerHTML = '';
        }

        const moviesSection = document.getElementById('movies-section');
        const tvshowsSection = document.getElementById('tvshows-section');
        const noResults = document.getElementById('no-results');
        const startSearch = document.getElementById('start-search');

        if (!query) {
            startSearch.style.display = 'block';
            moviesSection.style.display = 'none';
            tvshowsSection.style.display = 'none';
            noResults.style.display = 'none';
            return;
        }

        if (totalResults === 0) {
            startSearch.style.display = 'none';
            moviesSection.style.display = 'none';
            tvshowsSection.style.display = 'none';
            noResults.style.display = 'block';
            return;
        }

        startSearch.style.display = 'none';
        noResults.style.display = 'none';

        if ((searchType === 'all' || searchType === 'movies') && searchResults.movies.length > 0) {
            moviesSection.style.display = 'block';
            document.getElementById('movies-count').textContent = searchResults.totalMovies;
            const grid = document.getElementById('movies-grid');
            grid.innerHTML = '';
            searchResults.movies.forEach(movie => {
                const card = createMovieCard(movie);
                grid.appendChild(card);
            });
        } else {
            moviesSection.style.display = 'none';
        }

        if ((searchType === 'all' || searchType === 'tvshows') && searchResults.tvShows.length > 0) {
            tvshowsSection.style.display = 'block';
            document.getElementById('tvshows-count').textContent = searchResults.totalTVShows;
            const grid = document.getElementById('tvshows-grid');
            grid.innerHTML = '';
            searchResults.tvShows.forEach(tvshow => {
                const card = createTVShowCard(tvshow);
                grid.appendChild(card);
            });
        } else {
            tvshowsSection.style.display = 'none';
        }

        renderPagination();
    }

    function createMovieCard(movie) {
        const card = document.createElement('div');
        card.className = 'cursor-pointer transform transition-transform hover:scale-105';
        card.onclick = () => window.location.href = `/movie/${movie.id}`;
        const imageUrl = movie.poster_path ? (movie.poster_path.startsWith('http') ? movie.poster_path : `https://image.tmdb.org/t/p/w500${movie.poster_path}`) : '/images/placeholder.svg';
        card.innerHTML = `
            <div class="relative bg-gray-900 rounded-lg overflow-hidden border border-gray-800">
                <div class="relative aspect-[2/3] overflow-hidden bg-gray-800">
                    <img src="${imageUrl}" alt="${movie.title || 'Untitled'}" loading="lazy" onerror="this.src='/images/placeholder.svg'" class="w-full h-full object-cover">
                </div>
            </div>
            <h3 class="text-white font-semibold mt-2 line-clamp-2">${movie.title || 'Untitled'}</h3>
        `;
        return card;
    }

    function createTVShowCard(tvshow) {
        const card = document.createElement('div');
        card.className = 'cursor-pointer transform transition-transform hover:scale-105';
        card.onclick = () => window.location.href = `/tvshow/${tvshow.id}`;
        const imageUrl = tvshow.poster_path ? (tvshow.poster_path.startsWith('http') ? tvshow.poster_path : `https://image.tmdb.org/t/p/w500${tvshow.poster_path}`) : '/images/placeholder.svg';
        card.innerHTML = `
            <div class="relative bg-gray-900 rounded-lg overflow-hidden border border-gray-800">
                <div class="relative aspect-[2/3] overflow-hidden bg-gray-800">
                    <img src="${imageUrl}" alt="${tvshow.name || 'Untitled'}" loading="lazy" onerror="this.src='/images/placeholder.svg'" class="w-full h-full object-cover">
                </div>
            </div>
            <h3 class="text-white font-semibold mt-2 line-clamp-2">${tvshow.name || 'Untitled'}</h3>
        `;
        return card;
    }

    function renderPagination() {
        const paginationDiv = document.getElementById('pagination');
        paginationDiv.innerHTML = '';
        if (totalPages <= 1) return;

        const container = document.createElement('div');
        container.className = 'flex justify-center items-center space-x-2';

        const prevBtn = document.createElement('button');
        prevBtn.textContent = 'Previous';
        prevBtn.className = 'px-4 py-2 bg-gray-800 text-white rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-700 transition-colors';
        prevBtn.disabled = currentPage === 1;
        prevBtn.onclick = () => {
            if (currentPage > 1) {
                currentPage--;
                performSearch();
            }
        };
        container.appendChild(prevBtn);

        const pageInfo = document.createElement('span');
        pageInfo.className = 'text-white px-4';
        pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
        container.appendChild(pageInfo);

        const nextBtn = document.createElement('button');
        nextBtn.textContent = 'Next';
        nextBtn.className = 'px-4 py-2 bg-gray-800 text-white rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-700 transition-colors';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.onclick = () => {
            if (currentPage < totalPages) {
                currentPage++;
                performSearch();
            }
        };
        container.appendChild(nextBtn);

        paginationDiv.appendChild(container);
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadUtils();
        const urlParams = new URLSearchParams(window.location.search);
        const query = urlParams.get('q');
        if (query) {
            document.getElementById('search-input').value = query;
            performSearch();
        } else {
            document.getElementById('start-search').style.display = 'block';
        }
    });
</script>
@endpush
@endsection
