@extends('layouts.app')

@section('title', "Today's Episodes - Nazaara Box")

@section('seo_title', "Today's Episodes - Latest TV Show Episodes - Nazaara Box")
@section('seo_description', "Watch today's latest TV show episodes on Nazaara Box. Stay updated with new episodes from your favorite series.")
@section('seo_type', 'website')
@section('seo_url', route('episodes.today'))

@push('styles')
<style>
    /* Adjust padding for fixed header */
    .episodes-page-wrapper {
        padding-top: 64px; /* Mobile header height */
    }

    @media (min-width: 768px) {
        .episodes-page-wrapper {
            padding-top: 80px; /* Desktop header height */
        }
    }

    /* Episode Card Styles - Matching Frontend EpisodeCard.jsx */
    .episode-card {
        width: 100%;
        cursor: pointer;
        transition: transform 0.3s;
    }

    .episode-card:hover {
        transform: scale(1.02);
    }

    .episode-card-poster {
        position: relative;
        aspect-ratio: 16/9;
        border-radius: 8px;
        overflow: hidden;
        background-color: #1f2937; /* bg-gray-800 */
    }

    .episode-card-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .episode-card:hover .episode-card-image {
        transform: scale(1.1);
    }

    .episode-card-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,1), rgba(0,0,0,0.5), transparent);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .episode-card:hover .episode-card-overlay {
        opacity: 1;
    }

    .episode-card-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        background-color: #dc2626; /* bg-red-600 */
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }

    .episode-card-watch-btn {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .episode-card-watch-btn button {
        display: flex;
        align-items: center;
        gap: 8px;
        background-color: #dc2626; /* bg-red-600 */
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s;
        font-size: 14px;
        font-weight: 600;
    }

    .episode-card-watch-btn button:hover {
        background-color: #b91c1c; /* hover:bg-red-700 */
    }

    /* Date Picker Styles */
    .date-input {
        padding: 8px 16px;
        background-color: #1f2937; /* bg-gray-800 */
        color: white;
        border-radius: 8px;
        border: 1px solid #374151; /* border-gray-700 */
        outline: none;
        transition: all 0.2s;
        cursor: pointer;
        min-width: 140px;
    }

    .date-input:hover {
        border-color: #4b5563; /* border-gray-600 */
        background-color: #111827; /* bg-gray-900 */
    }

    .date-input:focus {
        border-color: #dc2626; /* focus:border-red-500 */
        box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.2);
    }
    
    .date-input:invalid {
        border-color: #ef4444; /* border-red-500 */
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
</style>
@endpush

@section('content')
<div class="min-h-screen bg-black episodes-page-wrapper pb-12">
    <div class="w-full px-4 md:px-8 lg:px-16">
        <!-- Loading State -->
        <div id="loading-state" class="min-h-screen bg-black flex items-center justify-center" style="padding-top: 64px;">
            <div class="spinner"></div>
        </div>

        <!-- Content -->
        <div id="episodes-content" style="display: none;">
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
                                Today's Episodes
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
                            min="2020-01-01"
                            title="Select a date to view episodes"
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

                <!-- Episode Count -->
                <div class="flex items-center space-x-4 mt-4">
                    <div class="flex items-center space-x-2 bg-gray-800 px-4 py-2 rounded-lg">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5 text-red-500">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span id="episode-count" class="text-white font-semibold">0</span>
                        <span id="episode-count-label" class="text-gray-400">Episodes Uploaded</span>
                    </div>
                </div>
            </div>

            <!-- Episodes Grid -->
            <div id="episodes-grid" class="grid gap-4" style="grid-template-columns: repeat(1, 1fr);"></div>
            <style>
                #episodes-grid {
                    display: grid;
                    gap: 16px;
                    grid-template-columns: repeat(1, 1fr);
                }
                
                @media (min-width: 640px) {
                    #episodes-grid {
                        grid-template-columns: repeat(2, 1fr);
                    }
                }
                
                @media (min-width: 768px) {
                    #episodes-grid {
                        grid-template-columns: repeat(4, 1fr) !important;
                    }
                }
                
                @media (min-width: 1024px) {
                    #episodes-grid {
                        grid-template-columns: repeat(4, 1fr) !important;
                    }
                }
                
                @media (min-width: 1280px) {
                    #episodes-grid {
                        grid-template-columns: repeat(4, 1fr) !important;
                    }
                }
                
                @media (min-width: 1536px) {
                    #episodes-grid {
                        grid-template-columns: repeat(4, 1fr) !important;
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
                        href="{{ route('tvshows.index') }}"
                        class="inline-flex items-center space-x-2 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors"
                    >
                        <svg fill="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        <span>Browse All TV Shows</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function() {
        'use strict';
        
        console.log('[Today Episodes] Script loading...');
        
        // State management - Matching Frontend TodayEpisodesPage.jsx
        let episodes = [];
        let loading = false;
        let selectedDate = '';
        
        // Ensure API_BASE_URL is defined
        if (typeof API_BASE_URL === 'undefined') {
            console.error('[Today Episodes] API_BASE_URL is not defined in global scope!');
        }

        // Get today's date in local timezone (YYYY-MM-DD format) - Matching Frontend
        function getTodayLocalDate() {
            const todayDate = new Date();
            const year = todayDate.getFullYear();
            const month = String(todayDate.getMonth() + 1).padStart(2, '0');
            const day = String(todayDate.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
        
        console.log('[Today Episodes] getTodayLocalDate defined:', typeof getTodayLocalDate);

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
            console.log('[Today Episodes] initDate called');
            const urlParams = new URLSearchParams(window.location.search);
            const dateParam = urlParams.get('date');
            selectedDate = dateParam || getTodayLocalDate();
            
            console.log('[Today Episodes] Date param from URL:', dateParam);
            console.log('[Today Episodes] Selected date set to:', selectedDate);
            
            const datePicker = document.getElementById('date-picker');
            const maxDate = getTodayLocalDate();
            
            if (datePicker) {
                datePicker.value = selectedDate;
                datePicker.max = maxDate;
                datePicker.min = '2020-01-01';
                console.log('[Today Episodes] Date picker set to:', selectedDate);
                console.log('[Today Episodes] Date picker max:', maxDate);
                console.log('[Today Episodes] Date picker min: 2020-01-01');
            } else {
                console.error('[Today Episodes] Date picker element not found!');
            }

            updateUI();
            console.log('[Today Episodes] initDate completed, selectedDate:', selectedDate);
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
                pageTitle.textContent = isTodayDate ? "Today's Episodes" : "Episodes by Date";
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

            // Update URL without triggering navigation
            const urlParams = new URLSearchParams();
            urlParams.set('date', selectedDate);
            const newURL = `${window.location.pathname}?${urlParams}`;
            window.history.pushState({ date: selectedDate }, '', newURL);
            
            console.log('[Today Episodes] URL updated to:', newURL);
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
            fetchEpisodesByDate(selectedDate);
        }

        // Go to next day - Matching Frontend
        function goToNextDay() {
            if (isToday()) return;
            
            const nextDate = new Date(selectedDate + 'T00:00:00');
            nextDate.setDate(nextDate.getDate() + 1);
            const todayDate = getTodayLocalDate();
            const year = nextDate.getFullYear();
            const month = String(nextDate.getMonth() + 1).padStart(2, '0');
            const day = String(nextDate.getDate()).padStart(2, '0');
            const nextDateStr = `${year}-${month}-${day}`;
            
            if (nextDateStr <= todayDate) {
                selectedDate = nextDateStr;
                const datePicker = document.getElementById('date-picker');
                if (datePicker) datePicker.value = selectedDate;
                updateUI();
                fetchEpisodesByDate(selectedDate);
            }
        }

        // Go to today - Matching Frontend
        function goToToday() {
            console.log('[Today Episodes] goToToday called');
            selectedDate = getTodayLocalDate();
            console.log('[Today Episodes] selectedDate:', selectedDate);
            const datePicker = document.getElementById('date-picker');
            if (datePicker) datePicker.value = selectedDate;
            updateUI();
            fetchEpisodesByDate(selectedDate);
        }

        // Handle date change - Matching Frontend
        function handleDateChange(e) {
            console.log('[Today Episodes] Date picker changed:', e.target.value);
            const maxDate = getTodayLocalDate();
            const selected = e.target.value;
            
            if (!selected) {
                console.log('[Today Episodes] No date selected, keeping current date');
                return;
            }
            
            if (selected <= maxDate) {
                console.log('[Today Episodes] Valid date selected:', selected);
                selectedDate = selected;
                updateUI();
                fetchEpisodesByDate(selectedDate);
            } else {
                console.warn('[Today Episodes] Date is in the future, resetting to current:', selectedDate);
                e.target.value = selectedDate;
                // Show a brief message to user
                const datePicker = document.getElementById('date-picker');
                if (datePicker) {
                    datePicker.style.borderColor = '#ef4444';
                    setTimeout(() => {
                        datePicker.style.borderColor = '';
                    }, 1000);
                }
            }
        }

        // Fetch episodes by date - Matching Frontend
        async function fetchEpisodesByDate(date) {
            console.log('[Today Episodes] fetchEpisodesByDate called with date:', date);
            
            if (!date) {
                console.error('[Today Episodes] No date provided to fetchEpisodesByDate!');
                // Still hide loading and show content
                loading = false;
                const loadingStateEl = document.getElementById('loading-state');
                const episodesContentEl = document.getElementById('episodes-content');
                if (loadingStateEl) loadingStateEl.style.display = 'none';
                if (episodesContentEl) episodesContentEl.style.display = 'block';
                return;
            }
            
            loading = true;
            const loadingState = document.getElementById('loading-state');
            const episodesContent = document.getElementById('episodes-content');
            
            if (loadingState) {
                loadingState.style.display = 'flex';
            }
            if (episodesContent) {
                episodesContent.style.display = 'none';
            }

            try {
                // Check if API_BASE_URL is defined
                if (typeof API_BASE_URL === 'undefined') {
                    console.error('[Today Episodes] API_BASE_URL is not defined!');
                    throw new Error('API_BASE_URL is not defined. Check the layout file.');
                }
                
                console.log('[Today Episodes] Fetching episodes for date:', date);
                const endpoint = `${API_BASE_URL}/episodes/today/all?date=${date}`;
                console.log('[Today Episodes] Endpoint:', endpoint);
                console.log('[Today Episodes] API_BASE_URL:', API_BASE_URL);
                
                const response = await apiFetch(endpoint);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('[Today Episodes] HTTP error:', response.status, errorText);
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                console.log('[Today Episodes] Response:', result);
                
                if (result.success && result.data) {
                    episodes = Array.isArray(result.data) ? result.data : [];
                } else if (Array.isArray(result)) {
                    episodes = result;
                } else {
                    episodes = [];
                }
                console.log('[Today Episodes] Episodes count:', episodes.length);
                
                renderEpisodes();
            } catch (error) {
                console.error('[Today Episodes] Error fetching episodes by date:', error);
                console.error('[Today Episodes] Error details:', error.message, error.stack);
                episodes = [];
                renderEpisodes();
            } finally {
                loading = false;
                
                // Always hide loading and show content, even on error
                const loadingStateEl = document.getElementById('loading-state');
                const episodesContentEl = document.getElementById('episodes-content');
                
                if (loadingStateEl) {
                    loadingStateEl.style.display = 'none';
                    console.log('[Today Episodes] Loading state hidden');
                } else {
                    console.error('[Today Episodes] loading-state element not found!');
                }
                
                if (episodesContentEl) {
                    episodesContentEl.style.display = 'block';
                    console.log('[Today Episodes] Episodes content shown');
                } else {
                    console.error('[Today Episodes] episodes-content element not found!');
                }
            }
        }

        // Render episodes - Matching Frontend EpisodeCard.jsx
        function renderEpisodes() {
            const grid = document.getElementById('episodes-grid');
            const emptyState = document.getElementById('empty-state');
            const episodeCount = document.getElementById('episode-count');
            const episodeCountLabel = document.getElementById('episode-count-label');

            if (!grid) {
                console.error('[Today Episodes] episodes-grid element not found!');
                return;
            }

            grid.innerHTML = '';

            // Update episode count
            if (episodeCount) {
                episodeCount.textContent = episodes.length;
            }
            if (episodeCountLabel) {
                episodeCountLabel.textContent = episodes.length === 1 ? 'Episode Uploaded' : 'Episodes Uploaded';
            }

            if (episodes.length === 0) {
                if (emptyState) {
                    emptyState.style.display = 'block';
                }
                const emptyTitle = document.getElementById('empty-title');
                const emptyDescription = document.getElementById('empty-description');
                
                if (emptyTitle) {
                    emptyTitle.textContent = `No Episodes Uploaded on ${formatDate(selectedDate)}`;
                }
                if (emptyDescription) {
                    const isTodayDate = isToday();
                    emptyDescription.textContent = isTodayDate 
                        ? 'Check back later for new episodes!' 
                        : 'Try selecting a different date or browse all TV shows.';
                }
                console.log('[Today Episodes] No episodes found, showing empty state');
                return;
            }

            if (emptyState) {
                emptyState.style.display = 'none';
            }

            console.log('[Today Episodes] Rendering', episodes.length, 'episodes');
            // Render episode cards
            episodes.forEach((episode, index) => {
                const card = createEpisodeCard(episode);
                grid.appendChild(card);
                console.log('[Today Episodes] Card', index + 1, 'rendered');
            });
            console.log('[Today Episodes] All episodes rendered');
        }

        // Create episode card - Matching Frontend EpisodeCard.jsx
        function createEpisodeCard(episode) {
            const card = document.createElement('div');
            card.className = 'episode-card group';
            card.style.cssText = 'width: 100%; cursor: pointer; transition: transform 0.3s;';

            const tvShow = episode.tvShow || episode.tvshow || {};
            const tvShowId = tvShow.id || episode.tvshow_id || episode.tvShow?.id;
            const tvShowName = tvShow.name || episode.tvshow?.name || '';
            const episodeName = episode.name || episode.title || `Episode ${episode.episode_number || episode.number || ''}`;
            const seasonNumber = episode.season_number || episode.seasonNumber || '';
            const episodeNumber = episode.episode_number || episode.number || '';
            
            // Image URL - Matching Frontend EpisodeCard logic
            let imageUrl = '/images/placeholder.svg';
            if (episode.still_path) {
                if (episode.still_path.startsWith('http')) {
                    imageUrl = episode.still_path;
                } else if (episode.still_path.startsWith('/')) {
                    imageUrl = `https://image.tmdb.org/t/p/w500${episode.still_path}`;
                } else {
                    imageUrl = `https://image.tmdb.org/t/p/w500/${episode.still_path}`;
                }
            } else if (tvShow.backdrop_path || episode.tvshow?.backdrop_path) {
                const backdropPath = tvShow.backdrop_path || episode.tvshow?.backdrop_path;
                if (backdropPath.startsWith('http')) {
                    imageUrl = backdropPath;
                } else if (backdropPath.startsWith('/')) {
                    imageUrl = `https://image.tmdb.org/t/p/w500${backdropPath}`;
                } else {
                    imageUrl = `https://image.tmdb.org/t/p/w500/${backdropPath}`;
                }
            } else if (tvShow.poster_path || episode.tvshow?.poster_path) {
                const posterPath = tvShow.poster_path || episode.tvshow?.poster_path;
                if (posterPath.startsWith('http')) {
                    imageUrl = posterPath;
                } else if (posterPath.startsWith('/')) {
                    imageUrl = `https://image.tmdb.org/t/p/w500${posterPath}`;
                } else {
                    imageUrl = `https://image.tmdb.org/t/p/w500/${posterPath}`;
                }
            }

            const name = tvShowName ? encodeURIComponent(tvShowName) : '';

            card.innerHTML = `
                <div class="episode-card-poster">
                    <img 
                        src="${imageUrl}" 
                        alt="${episodeName}" 
                        loading="lazy" 
                        onerror="this.src='/images/placeholder.svg'"
                        class="episode-card-image"
                    >
                    <div class="episode-card-overlay"></div>
                    
                    ${seasonNumber && episodeNumber ? `
                    <div class="episode-card-badge">
                        S${seasonNumber}E${episodeNumber}
                    </div>
                    ` : ''}

                    <div class="episode-card-watch-btn">
                        <button onclick="event.stopPropagation(); window.location.href='/tvshow/${tvShowId}${name ? '?name=' + name : ''}'">
                            <svg fill="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                            <span>Watch Now</span>
                        </button>
                    </div>
                </div>

                <!-- Episode Info -->
                <div style="margin-top: 12px;">
                    <h3 class="text-white font-semibold text-sm line-clamp-1 group-hover:text-red-500 transition-colors">
                        ${tvShowName || 'Unknown TV Show'}
                    </h3>
                    <p class="text-gray-400 text-xs line-clamp-1 mt-1">
                        ${episodeName}
                    </p>
                    ${episode.air_date ? `
                    <div class="flex items-center space-x-1 mt-1 text-gray-500 text-xs">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 12px; height: 12px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>${new Date(episode.air_date).toLocaleDateString()}</span>
                    </div>
                    ` : ''}
                </div>
            `;

            // Add click handler
            card.addEventListener('click', () => {
                window.location.href = `/tvshow/${tvShowId}${name ? '?name=' + name : ''}`;
            });

            return card;
        }

        // Make functions globally accessible for onclick handlers - MUST be done immediately
        console.log('[Today Episodes] Making functions globally accessible...');
        console.log('[Today Episodes] goToPreviousDay type:', typeof goToPreviousDay);
        console.log('[Today Episodes] goToNextDay type:', typeof goToNextDay);
        console.log('[Today Episodes] goToToday type:', typeof goToToday);
        
        window.goToPreviousDay = goToPreviousDay;
        window.goToNextDay = goToNextDay;
        window.goToToday = goToToday;
        
        console.log('[Today Episodes] Functions assigned to window:', {
            goToPreviousDay: typeof window.goToPreviousDay,
            goToNextDay: typeof window.goToNextDay,
            goToToday: typeof window.goToToday
        });

        // Initialize on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializePage);
        } else {
            // DOM is already loaded
            initializePage();
        }
        
        function initializePage() {
            console.log('[Today Episodes] initializePage called');
            console.log('[Today Episodes] document.readyState:', document.readyState);
            console.log('[Today Episodes] Checking functions:', {
                goToPreviousDay: typeof window.goToPreviousDay,
                goToNextDay: typeof window.goToNextDay,
                goToToday: typeof window.goToToday
            });
            console.log('[Today Episodes] API_BASE_URL:', typeof API_BASE_URL !== 'undefined' ? API_BASE_URL : 'UNDEFINED');
            
            try {
                // Check if API_BASE_URL is defined
                if (typeof API_BASE_URL === 'undefined') {
                    console.error('[Today Episodes] API_BASE_URL is not defined!');
                    // Hide loading state anyway
                    const loadingState = document.getElementById('loading-state');
                    const episodesContent = document.getElementById('episodes-content');
                    if (loadingState) loadingState.style.display = 'none';
                    if (episodesContent) episodesContent.style.display = 'block';
                    
                    // Show error message
                    const grid = document.getElementById('episodes-grid');
                    if (grid) {
                        grid.innerHTML = '<div class="text-center py-20 text-red-500">API configuration error. Please refresh the page.</div>';
                    }
                    return;
                }
                
                // Initialize date first, then fetch
                initDate();
                console.log('[Today Episodes] After initDate, selectedDate:', selectedDate);
                
                // Fetch immediately after initDate
                if (selectedDate) {
                    console.log('[Today Episodes] Fetching episodes for initialized date:', selectedDate);
                    fetchEpisodesByDate(selectedDate);
                } else {
                    console.error('[Today Episodes] selectedDate is not set!');
                    const todayDateStr = getTodayLocalDate();
                    console.log('[Today Episodes] Using today as fallback:', todayDateStr);
                    selectedDate = todayDateStr;
                    fetchEpisodesByDate(todayDateStr);
                }
            } catch (error) {
                console.error('[Today Episodes] Error in initializePage:', error);
                console.error('[Today Episodes] Error stack:', error.stack);
                
                // Always hide loading state
                const loadingState = document.getElementById('loading-state');
                const episodesContent = document.getElementById('episodes-content');
                if (loadingState) loadingState.style.display = 'none';
                if (episodesContent) episodesContent.style.display = 'block';
            }

            // Date picker change handler - listen to both change and input events
            const datePicker = document.getElementById('date-picker');
            if (datePicker) {
                // Change event fires when date is selected and input loses focus
                datePicker.addEventListener('change', handleDateChange);
                // Input event fires immediately when date changes
                datePicker.addEventListener('input', handleDateChange);
                
                // Also add min date restriction
                const minDate = '2020-01-01'; // Adjust as needed
                datePicker.min = minDate;
                datePicker.max = getTodayLocalDate();
                
                console.log('[Today Episodes] Date picker event listeners attached');
            } else {
                console.error('[Today Episodes] Date picker not found on DOMContentLoaded');
            }
            
            // Also listen for popstate (back/forward browser navigation)
            window.addEventListener('popstate', () => {
                console.log('[Today Episodes] Browser navigation detected, reinitializing date');
                initDate();
                if (selectedDate) {
                    fetchEpisodesByDate(selectedDate);
                }
            });
        }
        
        console.log('[Today Episodes] Script loaded, functions available:', {
            goToPreviousDay: typeof window.goToPreviousDay,
            goToNextDay: typeof window.goToNextDay,
            goToToday: typeof window.goToToday
        });
    })();
</script>
@endpush
@endsection
