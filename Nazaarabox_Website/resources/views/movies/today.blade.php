@extends('layouts.app')

@section('title', "Today's Movies - Nazaara Box")

@section('seo_title', "Today's Movies - New Releases - Nazaara Box")
@section('seo_description', "Watch today's new movie releases and latest additions to Nazaara Box. Stay updated with the newest films.")
@section('seo_type', 'website')
@section('seo_url', route('movies.today'))

@push('styles')
<style>
    /* Adjust padding for fixed header */
    .movies-today-page-wrapper {
        padding-top: 64px; /* Mobile header height */
    }

    @media (min-width: 768px) {
        .movies-today-page-wrapper {
            padding-top: 80px; /* Desktop header height */
        }
    }

    /* Date Picker Styles */
    .date-input {
        padding: 8px 16px;
        background-color: #1f2937; /* bg-gray-800 */
        color: white;
        border-radius: 8px;
        border: 1px solid #374151; /* border-gray-700 */
        outline: none;
        transition: border-color 0.2s;
    }

    .date-input:focus {
        border-color: #dc2626; /* focus:border-red-500 */
    }

    .date-nav-btn {
        padding: 8px;
        background-color: #1f2937; /* bg-gray-800 */
        color: white;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .date-nav-btn:hover:not(:disabled) {
        background-color: #374151; /* hover:bg-gray-700 */
    }

    .date-nav-btn:disabled {
        background-color: #111827; /* bg-gray-900 */
        color: #4b5563; /* text-gray-600 */
        cursor: not-allowed;
    }

    .today-btn {
        padding: 8px 16px;
        background-color: #dc2626; /* bg-red-600 */
        color: white;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        transition: background-color 0.2s;
    }

    .today-btn:hover {
        background-color: #b91c1c; /* hover:bg-red-700 */
    }

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
        aspect-ratio: 2/3; /* TMDB poster aspect ratio */
        background-color: #1f2937;
    }

    .movie-card-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .movie-card:hover .movie-card-image {
        transform: scale(1.1);
    }

    .movie-card-gradient {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.95), rgba(0,0,0,0.7), transparent);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .movie-card:hover .movie-card-gradient {
        opacity: 1;
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
        display: block !important;
    }
    
    /* Ensure hover info buttons are clickable */
    .movie-card-hover-info button {
        pointer-events: auto;
    }
    
    /* Prevent hover info from being clipped by grid */
    #movies-grid {
        align-items: start;
    }

    .movie-card-title {
        color: white;
        font-weight: 600;
        font-size: 14px;
        margin-top: 12px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        transition: color 0.3s;
    }

    .movie-card:hover .movie-card-title {
        color: #dc2626; /* Red on hover */
    }

    .movie-card:hover .movie-card-poster img {
        transform: scale(1.05);
    }
    
    /* Ensure grid has enough spacing for hover info */
    #movies-grid .movie-card {
        position: relative;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-black movies-today-page-wrapper pb-12">
    <div class="w-full px-4 md:px-8 lg:px-12 xl:px-16">
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

                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                    <div class="flex items-center space-x-4 mb-4 md:mb-0">
                        <div class="p-3 bg-red-600 rounded-lg">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-8 w-8 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 id="page-title" class="text-3xl md:text-4xl font-bold text-white">
                                Today's Movies
                            </h1>
                            <p id="page-date" class="text-gray-400 mt-1"></p>
                        </div>
                    </div>

                    <!-- Date Picker Controls -->
                    <div class="flex items-center space-x-2">
                        <button
                            onclick="goToPreviousDay()"
                            class="date-nav-btn"
                            title="Previous Day"
                        >
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        
                        <input
                            type="date"
                            id="date-picker"
                            class="date-input"
                            max=""
                        />
                        
                        <button
                            onclick="goToNextDay()"
                            id="next-day-btn"
                            class="date-nav-btn"
                            title="Next Day"
                        >
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                        
                        <button
                            onclick="goToToday()"
                            id="today-btn"
                            class="today-btn"
                            style="display: none;"
                        >
                            Today
                        </button>
                    </div>
                </div>

                <!-- Movie Count -->
                <div class="flex items-center space-x-4 mt-4">
                    <div class="flex items-center space-x-2 bg-gray-800 px-4 py-2 rounded-lg">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5 text-red-500">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                        </svg>
                        <span id="movie-count" class="text-white font-semibold">0</span>
                        <span id="movie-count-label" class="text-gray-400">Movies Uploaded</span>
                    </div>
                </div>
            </div>

            <!-- Movies Grid -->
            <div id="movies-grid" class="grid gap-6 mb-8"></div>
            <style>
                #movies-grid {
                    display: grid;
                    gap: 12px;
                    grid-template-columns: repeat(2, 1fr);
                }
                
                @media (min-width: 640px) {
                    #movies-grid {
                        grid-template-columns: repeat(3, 1fr);
                        gap: 12px;
                    }
                }
                
                @media (min-width: 768px) {
                    #movies-grid {
                        grid-template-columns: repeat(4, 1fr) !important;
                        gap: 12px;
                    }
                }
            </style>

            <!-- Empty State -->
            <div id="empty-state" class="text-center py-20" style="display: none;">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-800 rounded-full mb-6">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-10 w-10 text-gray-600">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h2 id="empty-title" class="text-2xl font-bold text-white mb-2"></h2>
                <p id="empty-description" class="text-gray-400 mb-8"></p>
                <div class="flex items-center justify-center space-x-4">
                    <button
                        onclick="goToToday()"
                        id="empty-today-btn"
                        class="inline-flex items-center space-x-2 bg-gray-800 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors"
                        style="display: none;"
                    >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>Go to Today</span>
                    </button>
                    <a
                        href="{{ route('movies.index') }}"
                        class="inline-flex items-center space-x-2 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors"
                    >
                        <svg fill="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        <span>Browse All Movies</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // State management - Matching Frontend TodayMoviesPage.jsx
    let movies = [];
    let loading = false;
    let selectedDate = '';

    // Get today's date in local timezone (YYYY-MM-DD format) - Matching Frontend
    function getTodayLocalDate() {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Format date for display - Matching Frontend
    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    }

    // Check if selected date is today
    function isToday() {
        return selectedDate === getTodayLocalDate();
    }

    // Initialize date from URL params or use today
    function initDate() {
        const urlParams = new URLSearchParams(window.location.search);
        const dateParam = urlParams.get('date');
        selectedDate = dateParam || getTodayLocalDate();
        
        const datePicker = document.getElementById('date-picker');
        const maxDate = getTodayLocalDate();
        
        if (datePicker) {
            datePicker.value = selectedDate;
            datePicker.max = maxDate;
        }

        updateUI();
    }

    // Update UI based on date
    function updateUI() {
        const isTodayDate = isToday();
        const pageTitle = document.getElementById('page-title');
        const pageDate = document.getElementById('page-date');
        const nextDayBtn = document.getElementById('next-day-btn');
        const todayBtn = document.getElementById('today-btn');
        const emptyTodayBtn = document.getElementById('empty-today-btn');

        if (pageTitle) {
            pageTitle.textContent = isTodayDate ? "Today's Movies" : "Movies by Date";
        }

        if (pageDate) {
            pageDate.textContent = formatDate(selectedDate);
        }

        if (nextDayBtn) {
            if (isTodayDate) {
                nextDayBtn.disabled = true;
                nextDayBtn.className = 'date-nav-btn';
                nextDayBtn.style.backgroundColor = '#111827';
                nextDayBtn.style.color = '#4b5563';
                nextDayBtn.style.cursor = 'not-allowed';
            } else {
                nextDayBtn.disabled = false;
                nextDayBtn.className = 'date-nav-btn';
                nextDayBtn.style.backgroundColor = '#1f2937';
                nextDayBtn.style.color = 'white';
                nextDayBtn.style.cursor = 'pointer';
            }
        }

        if (todayBtn) {
            todayBtn.style.display = isTodayDate ? 'none' : 'block';
        }

        if (emptyTodayBtn) {
            emptyTodayBtn.style.display = isTodayDate ? 'none' : 'block';
        }

        // Update URL
        const urlParams = new URLSearchParams();
        urlParams.set('date', selectedDate);
        const newURL = `${window.location.pathname}?${urlParams}`;
        window.history.pushState({}, '', newURL);
    }

    // Go to previous day - Matching Frontend
    function goToPreviousDay() {
        const prevDate = new Date(selectedDate + 'T00:00:00');
        prevDate.setDate(prevDate.getDate() - 1);
        const year = prevDate.getFullYear();
        const month = String(prevDate.getMonth() + 1).padStart(2, '0');
        const day = String(prevDate.getDate()).padStart(2, '0');
        selectedDate = `${year}-${month}-${day}`;
        
        const datePicker = document.getElementById('date-picker');
        if (datePicker) datePicker.value = selectedDate;
        
        updateUI();
        fetchMoviesByDate(selectedDate);
    }

    // Go to next day - Matching Frontend
    function goToNextDay() {
        if (isToday()) return;
        
        const nextDate = new Date(selectedDate + 'T00:00:00');
        nextDate.setDate(nextDate.getDate() + 1);
        const today = getTodayLocalDate();
        const year = nextDate.getFullYear();
        const month = String(nextDate.getMonth() + 1).padStart(2, '0');
        const day = String(nextDate.getDate()).padStart(2, '0');
        const nextDateStr = `${year}-${month}-${day}`;
        
        if (nextDateStr <= today) {
            selectedDate = nextDateStr;
            const datePicker = document.getElementById('date-picker');
            if (datePicker) datePicker.value = selectedDate;
            updateUI();
            fetchMoviesByDate(selectedDate);
        }
    }

    // Go to today - Matching Frontend
    function goToToday() {
        selectedDate = getTodayLocalDate();
        const datePicker = document.getElementById('date-picker');
        if (datePicker) datePicker.value = selectedDate;
        updateUI();
        fetchMoviesByDate(selectedDate);
    }

    // Handle date change - Matching Frontend
    function handleDateChange(e) {
        const maxDate = getTodayLocalDate();
        const selected = e.target.value;
        
        if (selected <= maxDate) {
            selectedDate = selected;
            updateUI();
            fetchMoviesByDate(selectedDate);
        } else {
            e.target.value = selectedDate;
        }
    }

    // Fetch movies by date - Matching Frontend
    async function fetchMoviesByDate(date) {
        loading = true;
        document.getElementById('loading-state').style.display = 'flex';
        document.getElementById('movies-content').style.display = 'none';

        try {
            console.log('[Today Movies] Fetching movies for date:', date);
            const endpoint = `${API_BASE_URL}/movies/today/all?date=${date}`;
            console.log('[Today Movies] Endpoint:', endpoint);
            
            const response = await apiFetch(endpoint);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('[Today Movies] HTTP error:', response.status, errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('[Today Movies] Response:', result);
            
            let allMovies = result.data || result || [];
            
            // Filter out movies with 18+ genre
            movies = allMovies.filter(movie => {
                if (!movie.genres || !Array.isArray(movie.genres)) return true;
                return !movie.genres.some(genre => {
                    const genreName = (genre.name || genre || '').toLowerCase();
                    return genreName.includes('18+') || genreName.includes('18');
                });
            });
            
            console.log('[Today Movies] Movies count:', movies.length);
            
            renderMovies();
        } catch (error) {
            console.error('[Today Movies] Error fetching movies by date:', error);
            movies = [];
            renderMovies();
        } finally {
            loading = false;
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('movies-content').style.display = 'block';
        }
    }

    // Render movies - Matching Frontend MovieCard.jsx
    function renderMovies() {
        const grid = document.getElementById('movies-grid');
        const emptyState = document.getElementById('empty-state');
        const movieCount = document.getElementById('movie-count');
        const movieCountLabel = document.getElementById('movie-count-label');

        grid.innerHTML = '';

        // Update movie count
        if (movieCount) {
            movieCount.textContent = movies.length;
        }
        if (movieCountLabel) {
            movieCountLabel.textContent = movies.length === 1 ? 'Movie Uploaded' : 'Movies Uploaded';
        }

        if (movies.length === 0) {
            emptyState.style.display = 'block';
            const emptyTitle = document.getElementById('empty-title');
            const emptyDescription = document.getElementById('empty-description');
            
            if (emptyTitle) {
                emptyTitle.textContent = `No Movies Uploaded on ${formatDate(selectedDate)}`;
            }
            if (emptyDescription) {
                const isTodayDate = isToday();
                emptyDescription.textContent = isTodayDate 
                    ? 'Check back later for new movies!' 
                    : 'Try selecting a different date or browse all movies.';
            }
            return;
        }

        emptyState.style.display = 'none';

        // Render movie cards
        movies.forEach(movie => {
            const card = createMovieCard(movie);
            grid.appendChild(card);
        });
    }

    // Create movie card - Matching Frontend MovieCard.jsx (same as movies index)
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
            <h3 class="movie-card-title">${movie.title || 'Untitled'}</h3>

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

        // Hover effects are handled by CSS
        return card;
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        initDate();
        fetchMoviesByDate(selectedDate);

        // Date picker change handler
        const datePicker = document.getElementById('date-picker');
        if (datePicker) {
            datePicker.addEventListener('change', handleDateChange);
        }
    });
</script>
@endpush
@endsection
