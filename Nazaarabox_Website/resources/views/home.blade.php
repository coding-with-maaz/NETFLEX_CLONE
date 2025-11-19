@extends('layouts.app')

@section('title', 'Home - Nazaara Box')

@section('seo_title', 'Nazaara Box - Stream Movies & TV Shows Online | Watch Latest Episodes')
@section('seo_description', 'Discover and stream thousands of movies and TV shows on Nazaara Box. Watch latest episodes, trending content, top-rated movies, and popular TV series in HD quality.')
@section('seo_type', 'website')
@section('seo_url', url('/'))

@section('content')
<div class="min-h-screen bg-black">
    <!-- Loading State - Matching Frontend -->
    <div id="loading-state" class="min-h-screen bg-black flex items-center justify-center">
        <div class="spinner"></div>
    </div>

    <!-- Content - Initially Hidden -->
    <div id="home-content" style="display: none;">
        <!-- Hero/Banner Section - ONLY shows featured content -->
        <div id="hero-container"></div>

        <!-- Content Rows - Matching Frontend structure with conditional margin -->
        <div id="content-rows-wrapper" class="relative z-10 pb-20">
            <!-- Latest Episodes -->
            <x-latest-episodes-row
                title="Latest Episodes"
                :limit="20"
                view-more-link="{{ route('tvshows.today') }}"
            />

            <!-- Trending - Mix of Movies and TV Shows -->
            <x-content-row
                title="Trending Now"
                endpoint="/api/v1/leaderboard/trending?period=week&limit=16"
                type="leaderboard"
                content-type="all"
                view-more-link="{{ route('trending') }}"
                :limit="16"
            />

            <!-- Top Rated Movies -->
            <x-content-row
                title="Top Rated Movies"
                endpoint="/api/v1/movies?sort_by=rating&min_rating=7.5&min_votes=100&limit=8"
                type="movies"
                view-more-link="{{ route('movies.top-rated') }}"
                :limit="8"
            />

            <!-- Top Rated TV Shows -->
            <x-content-row
                title="Top Rated TV Shows"
                endpoint="/api/v1/tvshows?sort_by=rating&min_rating=7.5&min_votes=100&limit=8"
                type="tvshows"
                view-more-link="{{ route('tvshows.top-rated') }}"
                :limit="8"
            />

            <!-- Mixed Content - Movies and TV Shows with Auto-Load Pagination -->
            <div class="mixed-content-section">
                <div class="content-row-header">
                    <h2 class="content-row-title">All Content</h2>
                </div>
                <div id="mixed-content-grid" class="mixed-content-grid">
                    <div class="spinner"></div>
                </div>
                <div id="mixed-content-loading" class="mixed-content-loading" style="display: block; min-height: 100px;">
                    <div class="spinner" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Fetch featured content - Matching Frontend HomePage.jsx
    async function fetchFeaturedContent() {
        try {
            console.log('[Home Page] Starting to fetch featured content...');
            console.log('[Home Page] API_BASE_URL:', API_BASE_URL);
            
            const loadingState = document.getElementById('loading-state');
            const homeContent = document.getElementById('home-content');
            const contentRowsWrapper = document.getElementById('content-rows-wrapper');
            const heroContainer = document.getElementById('hero-container');

            // Fetch ONLY featured movies and TV shows (matching frontend)
            let movieData = { success: false, data: { movies: [] } };
            let tvShowData = { success: false, data: { tvShows: [] } };

            try {
                const movieUrl = `${API_BASE_URL}/movies?is_featured=true&limit=5&sort_by=popularity`;
                console.log('[Home Page] Fetching featured movies from:', movieUrl);
                const movieResponse = await apiFetch(movieUrl);
                console.log('[Home Page] Movie response status:', movieResponse.status, movieResponse.statusText);
                console.log('[Home Page] Movie response ok:', movieResponse.ok);
                
                if (movieResponse.ok) {
                    movieData = await movieResponse.json();
                    console.log('[Home Page] Movie data:', movieData);
                } else {
                    const errorText = await movieResponse.text();
                    console.error('[Home Page] Movie response error:', errorText);
                }
            } catch (e) {
                console.error('[Home Page] Error fetching featured movies:', e);
                console.error('[Home Page] Error stack:', e.stack);
            }

            try {
                const tvShowUrl = `${API_BASE_URL}/tvshows?is_featured=true&limit=5&sort_by=popularity`;
                console.log('[Home Page] Fetching featured TV shows from:', tvShowUrl);
                const tvShowResponse = await apiFetch(tvShowUrl);
                console.log('[Home Page] TV Show response status:', tvShowResponse.status, tvShowResponse.statusText);
                console.log('[Home Page] TV Show response ok:', tvShowResponse.ok);
                
                if (tvShowResponse.ok) {
                    tvShowData = await tvShowResponse.json();
                    console.log('[Home Page] TV Show data:', tvShowData);
                } else {
                    const errorText = await tvShowResponse.text();
                    console.error('[Home Page] TV Show response error:', errorText);
                }
            } catch (e) {
                console.error('[Home Page] Error fetching featured TV shows:', e);
                console.error('[Home Page] Error stack:', e.stack);
            }

            // Extract featured content - Matching Frontend logic
            console.log('[Home Page] Extracting featured content...');
            console.log('[Home Page] movieData:', movieData);
            console.log('[Home Page] tvShowData:', tvShowData);
            
            const featuredMovies = movieData.success && movieData.data?.movies ? movieData.data.movies : [];
            const featuredTVShows = tvShowData.success && tvShowData.data?.tvShows ? tvShowData.data.tvShows : [];
            
            console.log('[Home Page] Featured movies extracted:', featuredMovies);
            console.log('[Home Page] Featured TV shows extracted:', featuredTVShows);

            // Only include items that are explicitly marked as featured
            // Sort by created_at (most recent first) - Matching Frontend
            const featured = [...featuredMovies, ...featuredTVShows]
                .filter(item => item.is_featured === true)
                .sort((a, b) => {
                    const dateA = new Date(a.created_at || 0);
                    const dateB = new Date(b.created_at || 0);
                    return dateB - dateA;
                });
            
            console.log('[Home Page] Combined featured items:', featured);
            console.log('[Home Page] Featured items count:', featured.length);

            // Always hide loading and show content, even if no featured items
            console.log('[Home Page] Hiding loading state, showing content');
            loadingState.style.display = 'none';
            homeContent.style.display = 'block';

            if (featured.length > 0) {
                console.log('[Home Page] Rendering hero section with', featured.length, 'items');
                // Render hero section - Matching Frontend HeroSection.jsx
                renderHeroSection(featured, featured[0]);

                // Apply conditional margin - Matching Frontend: -mt-32 when featured, pt-20 when not
                contentRowsWrapper.style.marginTop = '-128px'; // -mt-32 equivalent
                contentRowsWrapper.style.paddingTop = '80px'; // pt-20 equivalent
            } else {
                console.warn('[Home Page] No featured content found');
                // No featured content - matching frontend behavior
                heroContainer.innerHTML = '';
                contentRowsWrapper.style.marginTop = '0';
                contentRowsWrapper.style.paddingTop = '80px';
            }
            
            console.log('[Home Page] Featured content fetch completed successfully');
        } catch (error) {
            console.error('[Home Page] Error fetching featured content:', error);
            console.error('[Home Page] Error stack:', error.stack);
            // Always show content even on error
            const loadingState = document.getElementById('loading-state');
            const homeContent = document.getElementById('home-content');
            const contentRowsWrapper = document.getElementById('content-rows-wrapper');
            
            if (loadingState) loadingState.style.display = 'none';
            if (homeContent) homeContent.style.display = 'block';
            if (contentRowsWrapper) contentRowsWrapper.style.paddingTop = '80px';
            
            console.log('[Home Page] Error handled, showing content anyway');
        }
    }

    function renderHeroSection(featuredItems, currentContent) {
        const heroContainer = document.getElementById('hero-container');
        let currentIndex = 0;

        // Determine if content is a movie or TV show
        const isMovie = currentContent.title !== undefined;
        
        // Get background image
        const backgroundImage = currentContent.backdrop_path 
            ? `https://image.tmdb.org/t/p/original${currentContent.backdrop_path}`
            : currentContent.poster_path 
            ? `https://image.tmdb.org/t/p/original${currentContent.poster_path}`
            : '/images/placeholder.svg';

        const displayContent = featuredItems.length > 0 ? featuredItems[currentIndex] : currentContent;
        const year = displayContent.release_date || displayContent.first_air_date 
            ? new Date(displayContent.release_date || displayContent.first_air_date).getFullYear()
            : null;
        const rating = displayContent.vote_average 
            ? Math.round(parseFloat(displayContent.vote_average) * 10)
            : null;

        heroContainer.innerHTML = `
            <div class="hero-section-fullscreen" id="hero-section-fullscreen">
                <!-- Background Image -->
                <div class="hero-background">
                    <img src="${backgroundImage}" alt="${displayContent.title || displayContent.name}" class="hero-backdrop-img" onerror="this.src='/images/placeholder.svg'">
                    <!-- Gradient Overlays - Matching Frontend -->
                    <div class="hero-gradient-left"></div>
                    <div class="hero-gradient-top"></div>
                </div>

                <!-- Navigation Arrows -->
                ${featuredItems.length > 1 ? `
                <button class="hero-nav-arrow hero-nav-left" id="hero-nav-left">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button class="hero-nav-arrow hero-nav-right" id="hero-nav-right">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                ` : ''}

                <!-- Content -->
                <div class="hero-content-container">
                    <div class="hero-content-wrapper">
                        <div class="hero-content-inner">
                            <!-- Content Type Badges -->
                            <div class="hero-badges">
                                <span class="hero-badge hero-badge-primary">${isMovie ? 'Movie' : 'TV Show'}</span>
                                ${displayContent.is_featured ? '<span class="hero-badge hero-badge-featured">Featured</span>' : ''}
                            </div>

                            <!-- Title -->
                            <h1 class="hero-title-large">
                                ${displayContent.title || displayContent.name}
                            </h1>

                            <!-- Metadata -->
                            <div class="hero-metadata">
                                ${rating ? `
                                <div class="hero-match">
                                    <span>${rating}% Match</span>
                                </div>
                                ` : ''}
                                ${year ? `<span>${year}</span>` : ''}
                                ${displayContent.runtime ? `
                                <span>${Math.floor(displayContent.runtime / 60)}h ${displayContent.runtime % 60}m</span>
                                ` : ''}
                                ${displayContent.number_of_seasons ? `
                                <span>${displayContent.number_of_seasons} Season${displayContent.number_of_seasons > 1 ? 's' : ''}</span>
                                ` : ''}
                                ${displayContent.vote_average ? `
                                <div class="hero-rating-badge">
                                    <span>⭐</span>
                                    <span>${parseFloat(displayContent.vote_average).toFixed(1)}</span>
                                </div>
                                ` : ''}
                            </div>

                            <!-- Genres -->
                            ${displayContent.genres && displayContent.genres.length > 0 ? `
                            <div class="hero-genres">
                                ${displayContent.genres
                                    .filter(genre => !genre.name || (!genre.name.toLowerCase().includes('18+') && !genre.name.toLowerCase().includes('18')))
                                    .slice(0, 4).map(genre => `
                                    <a href="${isMovie ? '/movies?genre=' + genre.id : '/tvshows?genre=' + genre.id}" 
                                       class="hero-genre-tag">
                                        ${genre.name}
                                    </a>
                                `).join('')}
                            </div>
                            ` : ''}

                            <!-- Overview -->
                            ${displayContent.overview ? `
                            <p class="hero-overview">${displayContent.overview}</p>
                            ` : ''}

                            <!-- Action Buttons -->
                            <div class="hero-actions">
                                <a href="${isMovie ? '/movie/' + displayContent.id : '/tvshow/' + displayContent.id}" 
                                   class="hero-btn hero-btn-primary">
                                    <svg fill="currentColor" viewBox="0 0 24 24" class="hero-btn-icon">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                    <span>Play</span>
                                </a>
                                <a href="${isMovie ? '/movie/' + displayContent.id : '/tvshow/' + displayContent.id}" 
                                   class="hero-btn hero-btn-secondary">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="hero-btn-icon">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>More Info</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Volume Control -->
                <button class="hero-volume-btn" id="hero-volume-btn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" id="volume-on-icon" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
                    </svg>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" id="volume-off-icon">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M1 1l22 22"></path>
                    </svg>
                </button>
            </div>
        `;

        // Auto-rotate every 8 seconds (matching frontend)
        if (featuredItems.length > 1) {
            setInterval(() => {
                currentIndex = (currentIndex + 1) % featuredItems.length;
                updateHeroContent(featuredItems[currentIndex]);
            }, 8000);

            // Navigation arrows
            document.getElementById('hero-nav-left')?.addEventListener('click', () => {
                currentIndex = (currentIndex - 1 + featuredItems.length) % featuredItems.length;
                updateHeroContent(featuredItems[currentIndex]);
            });

            document.getElementById('hero-nav-right')?.addEventListener('click', () => {
                currentIndex = (currentIndex + 1) % featuredItems.length;
                updateHeroContent(featuredItems[currentIndex]);
            });
        }

        // Volume toggle
        let muted = true;
        document.getElementById('hero-volume-btn')?.addEventListener('click', () => {
            muted = !muted;
            document.getElementById('volume-on-icon').style.display = muted ? 'none' : 'block';
            document.getElementById('volume-off-icon').style.display = muted ? 'block' : 'none';
        });
    }

    function updateHeroContent(content) {
        const isMovie = content.title !== undefined;
        const backgroundImage = content.backdrop_path 
            ? `https://image.tmdb.org/t/p/original${content.backdrop_path}`
            : content.poster_path 
            ? `https://image.tmdb.org/t/p/original${content.poster_path}`
            : '/images/placeholder.svg';

        const backdropImg = document.querySelector('.hero-backdrop-img');
        if (backdropImg) backdropImg.src = backgroundImage;

        const titleEl = document.querySelector('.hero-title-large');
        if (titleEl) titleEl.textContent = content.title || content.name;

        // Update other content dynamically
        const year = content.release_date || content.first_air_date 
            ? new Date(content.release_date || content.first_air_date).getFullYear()
            : null;
        const rating = content.vote_average 
            ? Math.round(parseFloat(content.vote_average) * 10)
            : null;

        // Update metadata, genres, overview, etc.
        // (Full implementation would update all elements)
    }

    // Load mixed content (movies + TV shows) with pagination
    let moviesPage = 1;
    let tvShowsPage = 1;
    const itemsPerPage = 20;
    let isLoading = false;
    let hasMoreMovies = true;
    let hasMoreTVShows = true;
    let allLoadedContent = [];
    let contentUpdateInterval = null;
    let lastContentCheck = null;

    async function loadMixedContent(append = false) {
        // Check if we should continue loading
        if (isLoading || (!hasMoreMovies && !hasMoreTVShows && append)) {
            return;
        }

        isLoading = true;
        const container = document.getElementById('mixed-content-grid');
        const loadingIndicator = document.getElementById('mixed-content-loading');

        if (!append && container) {
            container.innerHTML = '<div class="spinner"></div>';
        }

        if (append && loadingIndicator) {
            loadingIndicator.style.display = 'block';
            const spinner = loadingIndicator.querySelector('.spinner');
            if (spinner) spinner.style.display = 'block';
        }

        try {
            console.log(`[Home Page] Loading mixed content - Movies Page: ${moviesPage}, TV Shows Page: ${tvShowsPage}`);
            
            // Fetch movies and TV shows in parallel (only if they have more pages)
            const fetchPromises = [];
            
            if (hasMoreMovies) {
                const moviesUrl = `${API_BASE_URL}/movies?page=${moviesPage}&limit=${itemsPerPage}&sort_by=popularity`;
                fetchPromises.push(
                    apiFetch(moviesUrl)
                        .then(response => ({ type: 'movies', response }))
                        .catch(err => {
                            console.error('[Home Page] Error fetching movies:', err);
                            return { type: 'movies', response: null };
                        })
                );
            }
            
            if (hasMoreTVShows) {
                const tvShowsUrl = `${API_BASE_URL}/tvshows?page=${tvShowsPage}&limit=${itemsPerPage}&sort_by=popularity`;
                fetchPromises.push(
                    apiFetch(tvShowsUrl)
                        .then(response => ({ type: 'tvshows', response }))
                        .catch(err => {
                            console.error('[Home Page] Error fetching TV shows:', err);
                            return { type: 'tvshows', response: null };
                        })
                );
            }

            const results = await Promise.all(fetchPromises);

            let movies = [];
            let tvShows = [];
            let moviesHasNext = false;
            let tvShowsHasNext = false;

            // Process responses
            for (const result of results) {
                if (!result.response || !result.response.ok) continue;

                const apiResult = await result.response.json();
                
                if (result.type === 'movies' && apiResult.success && apiResult.data) {
                    movies = apiResult.data.movies || [];
                    const pagination = apiResult.data.pagination || {};
                    moviesHasNext = pagination.has_next || false;
                    console.log(`[Home Page] Movies loaded: ${movies.length}, has_next: ${moviesHasNext}`);
                } else if (result.type === 'tvshows' && apiResult.success && apiResult.data) {
                    tvShows = apiResult.data.tvShows || [];
                    const pagination = apiResult.data.pagination || {};
                    tvShowsHasNext = pagination.has_next || false;
                    console.log(`[Home Page] TV Shows loaded: ${tvShows.length}, has_next: ${tvShowsHasNext}`);
                }
            }

            // Update pagination state first
            hasMoreMovies = moviesHasNext;
            hasMoreTVShows = tvShowsHasNext;

            // Increment page numbers for next load (only if we got results)
            // This prepares the next page number for the next call
            if (movies.length > 0) {
                moviesPage++;
                console.log(`[Home Page] Movies page incremented to: ${moviesPage}, hasMore: ${hasMoreMovies}`);
            }
            if (tvShows.length > 0) {
                tvShowsPage++;
                console.log(`[Home Page] TV Shows page incremented to: ${tvShowsPage}, hasMore: ${hasMoreTVShows}`);
            }

            // Combine and sort by popularity/created date
            const newContent = [
                ...movies.map(m => ({ ...m, type: 'movie' })),
                ...tvShows.map(tv => ({ ...tv, type: 'tvshow' }))
            ].sort((a, b) => {
                // Sort by created_at (newest first), then by popularity
                const dateA = new Date(a.created_at || 0);
                const dateB = new Date(b.created_at || 0);
                if (dateB - dateA !== 0) return dateB - dateA;
                
                const aPopularity = (a.popularity || 0) * 0.7 + (a.view_count || 0) * 0.3;
                const bPopularity = (b.popularity || 0) * 0.7 + (b.view_count || 0) * 0.3;
                return bPopularity - aPopularity;
            });

            // Remove duplicates based on ID and type
            const existingIds = new Set(allLoadedContent.map(item => `${item.type}-${item.id}`));
            const uniqueNewContent = newContent.filter(item => !existingIds.has(`${item.type}-${item.id}`));

            if (uniqueNewContent.length > 0) {
                allLoadedContent = [...allLoadedContent, ...uniqueNewContent];
                
                if (container) {
                    if (!append) {
                        container.innerHTML = '';
                    }

                    uniqueNewContent.forEach(item => {
                        const card = item.type === 'movie' ? createMovieCard(item) : createTVShowCard(item);
                        container.appendChild(card);
                    });
                }
                
                console.log(`[Home Page] Total content loaded: ${allLoadedContent.length}`);
            } else if (!append && container) {
                // No content on initial load
                container.innerHTML = '<p class="text-gray-400 text-center py-4">No content available</p>';
            }

            // If no new content and both APIs are exhausted, we're done
            if (uniqueNewContent.length === 0 && !hasMoreMovies && !hasMoreTVShows && append) {
                console.log('[Home Page] All content loaded');
            }

            // Update last check time
            lastContentCheck = new Date();

        } catch (error) {
            console.error('[Home Page] Error loading mixed content:', error);
            if (container && !append) {
                container.innerHTML = '<p class="text-gray-400 text-center py-4">Error loading content</p>';
            }
        } finally {
            isLoading = false;
            if (loadingIndicator) {
                // Keep the loading indicator visible but hide the spinner
                // This ensures IntersectionObserver can still detect it
                const spinner = loadingIndicator.querySelector('.spinner');
                if (spinner) spinner.style.display = 'none';
            }
        }
    }

    // Check for new content and update the display
    async function checkForNewContent() {
        if (isLoading) {
            console.log('[Home Page] Already loading, skipping content check');
            return;
        }

        try {
            console.log('[Home Page] Checking for new content...');
            
            // Fetch first page of both movies and TV shows to check for new items
            const moviesUrl = `${API_BASE_URL}/movies?page=1&limit=${itemsPerPage}&sort_by=created_at&order=desc`;
            const tvShowsUrl = `${API_BASE_URL}/tvshows?page=1&limit=${itemsPerPage}&sort_by=created_at&order=desc`;

            const [moviesResponse, tvShowsResponse] = await Promise.all([
                apiFetch(moviesUrl).catch(err => {
                    console.error('[Home Page] Error checking movies:', err);
                    return null;
                }),
                apiFetch(tvShowsUrl).catch(err => {
                    console.error('[Home Page] Error checking TV shows:', err);
                    return null;
                })
            ]);

            let newMovies = [];
            let newTVShows = [];

            if (moviesResponse && moviesResponse.ok) {
                const moviesResult = await moviesResponse.json();
                newMovies = moviesResult.success && moviesResult.data ? (moviesResult.data.movies || []) : [];
            }

            if (tvShowsResponse && tvShowsResponse.ok) {
                const tvShowsResult = await tvShowsResponse.json();
                newTVShows = tvShowsResult.success && tvShowsResult.data ? (tvShowsResult.data.tvShows || []) : [];
            }

            // Combine new content
            const newContent = [
                ...newMovies.map(m => ({ ...m, type: 'movie' })),
                ...newTVShows.map(tv => ({ ...tv, type: 'tvshow' }))
            ].sort((a, b) => {
                const dateA = new Date(a.created_at || 0);
                const dateB = new Date(b.created_at || 0);
                return dateB - dateA;
            });

            // Find items that don't exist in our current loaded content
            const existingIds = new Set(allLoadedContent.map(item => `${item.type}-${item.id}`));
            const trulyNewContent = newContent.filter(item => !existingIds.has(`${item.type}-${item.id}`));

            if (trulyNewContent.length > 0) {
                console.log(`[Home Page] Found ${trulyNewContent.length} new items, updating display`);
                
                // Prepend new content to the beginning
                allLoadedContent = [...trulyNewContent, ...allLoadedContent];
                
                const container = document.getElementById('mixed-content-grid');
                if (container) {
                    // Prepend new cards to the grid
                    trulyNewContent.reverse().forEach(item => {
                        const card = item.type === 'movie' ? createMovieCard(item) : createTVShowCard(item);
                        container.insertBefore(card, container.firstChild);
                    });
                }

                // Show a notification that new content was added
                showNewContentNotification(trulyNewContent.length);
            } else {
                console.log('[Home Page] No new content found');
            }

        } catch (error) {
            console.error('[Home Page] Error checking for new content:', error);
        }
    }

    // Show notification when new content is added
    function showNewContentNotification(count) {
        // Remove existing notification if any
        const existingNotification = document.getElementById('new-content-notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        // Create notification
        const notification = document.createElement('div');
        notification.id = 'new-content-notification';
        notification.className = 'new-content-notification';
        notification.innerHTML = `
            <div class="new-content-notification-content">
                <svg fill="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <span>${count} new ${count === 1 ? 'item' : 'items'} added</span>
                <button onclick="this.parentElement.parentElement.remove()" class="new-content-notification-close">×</button>
            </div>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }

    // Start periodic content checking
    function startContentUpdates() {
        // Check every 30 seconds for new content
        if (contentUpdateInterval) {
            clearInterval(contentUpdateInterval);
        }
        
        contentUpdateInterval = setInterval(() => {
            checkForNewContent();
        }, 30000); // 30 seconds

        console.log('[Home Page] Started periodic content updates (every 30 seconds)');
    }

    // Stop periodic content checking
    function stopContentUpdates() {
        if (contentUpdateInterval) {
            clearInterval(contentUpdateInterval);
            contentUpdateInterval = null;
            console.log('[Home Page] Stopped periodic content updates');
        }
    }

    // Setup infinite scroll
    function setupInfiniteScroll() {
        const loadingIndicator = document.getElementById('mixed-content-loading');
        const container = document.getElementById('mixed-content-grid');
        
        if (!loadingIndicator || !container) {
            console.error('[Home Page] Missing elements for infinite scroll');
            return;
        }

        let scrollTimeout;
        
        // IntersectionObserver for loading indicator
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && (hasMoreMovies || hasMoreTVShows) && !isLoading) {
                    console.log('[Home Page] Loading indicator visible, triggering load');
                    console.log('[Home Page] State - hasMoreMovies:', hasMoreMovies, 'hasMoreTVShows:', hasMoreTVShows, 'isLoading:', isLoading);
                    loadMixedContent(true);
                }
            });
        }, {
            rootMargin: '300px',
            threshold: 0.1
        });

        observer.observe(loadingIndicator);
        
        // Scroll event listener as fallback
        const handleScroll = () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                if (isLoading || (!hasMoreMovies && !hasMoreTVShows)) return;
                
                const rect = loadingIndicator.getBoundingClientRect();
                const isVisible = rect.top < window.innerHeight + 500;
                
                if (isVisible && (hasMoreMovies || hasMoreTVShows) && !isLoading) {
                    console.log('[Home Page] Scroll detected near loading indicator, triggering load');
                    loadMixedContent(true);
                }
            }, 100);
        };
        
        window.addEventListener('scroll', handleScroll, { passive: true });
        
        // Also check on initial setup if we're already near the bottom
        setTimeout(() => {
            const rect = loadingIndicator.getBoundingClientRect();
            if (rect.top < window.innerHeight + 500 && (hasMoreMovies || hasMoreTVShows) && !isLoading) {
                console.log('[Home Page] Already near bottom on load, triggering initial pagination');
                loadMixedContent(true);
            }
        }, 1000);
    }
    
    // Create a movie card element
    function createMovieCard(movie) {
        const card = document.createElement('a');
        card.className = 'content-card';
        card.href = `/movie/${movie.id}`;
        
        let poster = movie.poster_path || '/images/placeholder.svg';
        if (poster && poster.startsWith('/') && !poster.startsWith('/images/')) {
            poster = 'https://image.tmdb.org/t/p/w500' + poster;
        } else if (!poster || poster === '') {
            poster = '/images/placeholder.svg';
        }
        
        const imageWrapper = document.createElement('div');
        imageWrapper.style.position = 'relative';
        
        const img = document.createElement('img');
        img.src = poster;
        img.alt = movie.title || 'Untitled';
        img.className = 'content-card-image';
        img.loading = 'lazy';
        img.onerror = function() { this.src = '/images/placeholder.svg'; };
        
        imageWrapper.appendChild(img);
        
        if (movie.view_count && movie.view_count > 0) {
            const badge = document.createElement('div');
            badge.className = 'content-card-badge';
            const formattedCount = movie.view_count >= 1000000 
                ? (movie.view_count / 1000000).toFixed(1) + 'M'
                : movie.view_count >= 1000 
                ? (movie.view_count / 1000).toFixed(1) + 'K'
                : movie.view_count.toString();
            badge.innerHTML = `
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                </svg>
                <span>${formattedCount}</span>
            `;
            imageWrapper.appendChild(badge);
        }
        
        const titleDiv = document.createElement('div');
        titleDiv.className = 'content-card-title';
        titleDiv.textContent = movie.title || 'Untitled';
        
        card.appendChild(imageWrapper);
        card.appendChild(titleDiv);
        
        return card;
    }
    
    // Create a TV show card element
    function createTVShowCard(tvShow) {
        const card = document.createElement('a');
        card.className = 'content-card';
        card.href = `/tvshow/${tvShow.id}`;
        
        let poster = tvShow.poster_path || '/images/placeholder.svg';
        if (poster && poster.startsWith('/') && !poster.startsWith('/images/')) {
            poster = 'https://image.tmdb.org/t/p/w500' + poster;
        } else if (!poster || poster === '') {
            poster = '/images/placeholder.svg';
        }
        
        const imageWrapper = document.createElement('div');
        imageWrapper.style.position = 'relative';
        
        const img = document.createElement('img');
        img.src = poster;
        img.alt = tvShow.name || 'Untitled';
        img.className = 'content-card-image';
        img.loading = 'lazy';
        img.onerror = function() { this.src = '/images/placeholder.svg'; };
        
        imageWrapper.appendChild(img);
        
        if (tvShow.view_count && tvShow.view_count > 0) {
            const badge = document.createElement('div');
            badge.className = 'content-card-badge';
            const formattedCount = tvShow.view_count >= 1000000 
                ? (tvShow.view_count / 1000000).toFixed(1) + 'M'
                : tvShow.view_count >= 1000 
                ? (tvShow.view_count / 1000).toFixed(1) + 'K'
                : tvShow.view_count.toString();
            badge.innerHTML = `
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                </svg>
                <span>${formattedCount}</span>
            `;
            imageWrapper.appendChild(badge);
        }
        
        const titleDiv = document.createElement('div');
        titleDiv.className = 'content-card-title';
        titleDiv.textContent = tvShow.name || 'Untitled';
        
        card.appendChild(imageWrapper);
        card.appendChild(titleDiv);
        
        return card;
    }

    // Load on page ready
    document.addEventListener('DOMContentLoaded', () => {
        fetchFeaturedContent();
        // Load mixed content after API_BASE_URL is available
        function initMixedContent() {
            if (typeof API_BASE_URL !== 'undefined') {
                loadMixedContent(false);
                setupInfiniteScroll();
                // Start checking for new content after initial load
                setTimeout(() => {
                    startContentUpdates();
                }, 5000); // Wait 5 seconds after initial load
            } else {
                setTimeout(initMixedContent, 100);
            }
        }
        initMixedContent();

        // Stop updates when page is hidden (to save resources)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stopContentUpdates();
            } else {
                startContentUpdates();
                // Check immediately when page becomes visible
                checkForNewContent();
            }
        });
    });
</script>
@endpush

@push('styles')
<style>
    /* Hero Section Full Screen - Matching Frontend HeroSection.jsx */
    .hero-section-fullscreen {
        position: relative;
        height: 100vh;
        overflow: hidden;
    }

    .hero-background {
        position: absolute;
        inset: 0;
    }

    .hero-backdrop-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .hero-gradient-left {
        position: absolute;
        inset: 0;
        background: linear-gradient(to right, rgba(0,0,0,1), rgba(0,0,0,0.5), transparent);
    }

    .hero-gradient-top {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,1), transparent, transparent);
    }

    .hero-nav-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 20;
        padding: 8px;
        border-radius: 50%;
        background: rgba(0,0,0,0.5);
        border: none;
        color: white;
        cursor: pointer;
        transition: background-color 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero-nav-arrow:hover {
        background: rgba(0,0,0,0.7);
    }

    .hero-nav-left {
        left: 16px;
    }

    .hero-nav-right {
        right: 16px;
    }

    .hero-nav-arrow svg {
        width: 32px;
        height: 32px;
    }

    .hero-content-container {
        position: relative;
        z-index: 10;
        height: 100%;
        display: flex;
        align-items: center;
    }

    .hero-content-wrapper {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 16px;
        width: 100%;
    }

    @media (min-width: 768px) {
        .hero-content-wrapper {
            padding: 0 32px;
        }
    }

    @media (min-width: 1024px) {
        .hero-content-wrapper {
            padding: 0 64px;
        }
    }

    .hero-content-inner {
        max-width: 512px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .hero-badges {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .hero-badge {
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .hero-badge-primary {
        background-color: var(--primary-red);
        color: white;
    }

    .hero-badge-featured {
        background-color: #d97706;
        color: white;
    }

    .hero-title-large {
        font-size: 32px;
        font-weight: bold;
        color: white;
        text-shadow: 0 2px 4px rgba(0,0,0,0.8);
        line-height: 1.1;
    }

    @media (min-width: 768px) {
        .hero-title-large {
            font-size: 40px;
        }
    }

    @media (min-width: 1024px) {
        .hero-title-large {
            font-size: 48px;
        }
    }

    .hero-metadata {
        display: flex;
        align-items: center;
        gap: 16px;
        color: white;
        font-size: 14px;
    }

    @media (min-width: 768px) {
        .hero-metadata {
            font-size: 16px;
        }
    }

    .hero-match {
        color: #4ade80;
        font-weight: 600;
    }

    .hero-rating-badge {
        display: flex;
        align-items: center;
        gap: 4px;
        background: rgba(234, 179, 8, 0.2);
        padding: 4px 8px;
        border-radius: 4px;
    }

    .hero-rating-badge span:first-child {
        color: #fbbf24;
    }

    .hero-rating-badge span:last-child {
        color: #fbbf24;
        font-weight: 600;
    }

    .hero-genres {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .hero-genre-tag {
        color: #d1d5db;
        font-size: 14px;
        background: rgba(255,255,255,0.1);
        padding: 4px 12px;
        border-radius: 9999px;
        text-decoration: none;
        transition: background-color 0.2s;
    }

    .hero-genre-tag:hover {
        background: rgba(255,255,255,0.2);
    }

    .hero-overview {
        color: white;
        font-size: 16px;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-shadow: 0 2px 4px rgba(0,0,0,0.8);
    }

    @media (min-width: 768px) {
        .hero-overview {
            font-size: 18px;
        }
    }

    .hero-actions {
        display: flex;
        align-items: center;
        gap: 16px;
        padding-top: 16px;
    }

    .hero-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 32px;
        font-size: 18px;
        font-weight: 600;
        border-radius: 4px;
        text-decoration: none;
        transition: all 0.2s;
    }

    .hero-btn-primary {
        background-color: white;
        color: black;
    }

    .hero-btn-primary:hover {
        background-color: rgba(255,255,255,0.9);
    }

    .hero-btn-secondary {
        background-color: rgba(75,85,99,0.7);
        color: white;
        border: none;
        backdrop-filter: blur(4px);
    }

    .hero-btn-secondary:hover {
        background-color: rgba(75,85,99,0.9);
    }

    .hero-btn-icon {
        width: 24px;
        height: 24px;
    }

    .hero-volume-btn {
        position: absolute;
        bottom: 128px;
        right: 32px;
        z-index: 20;
        padding: 12px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.5);
        background: transparent;
        color: white;
        cursor: pointer;
        transition: background-color 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero-volume-btn:hover {
        background: rgba(255,255,255,0.2);
    }

    .hero-volume-btn svg {
        width: 24px;
        height: 24px;
    }

    /* Episode Card Styles */
    .episode-card {
        flex-shrink: 0;
        width: 160px;
        text-decoration: none;
    }

    .episode-card .relative {
        position: relative;
    }

    /* Scrollbar Hide */
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }

    /* Mixed Content Grid Styles */
    .mixed-content-section {
        margin-top: 40px;
        padding: 0 16px;
    }

    @media (min-width: 768px) {
        .mixed-content-section {
            padding: 0 32px;
        }
    }

    @media (min-width: 1024px) {
        .mixed-content-section {
            padding: 0 64px;
        }
    }

    .mixed-content-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin-top: 24px;
    }

    @media (min-width: 640px) {
        .mixed-content-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (min-width: 768px) {
        .mixed-content-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
    }

    @media (min-width: 1024px) {
        .mixed-content-grid {
            grid-template-columns: repeat(5, 1fr);
            gap: 24px;
        }
    }

    @media (min-width: 1280px) {
        .mixed-content-grid {
            grid-template-columns: repeat(6, 1fr);
        }
    }

    .mixed-content-loading {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 40px 0;
        margin-top: 20px;
    }

    /* New Content Notification */
    .new-content-notification {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 1000;
        background: rgba(0, 0, 0, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        padding: 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        animation: slideInRight 0.3s ease-out;
        transition: opacity 0.3s ease-out;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .new-content-notification-content {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        color: white;
        font-size: 14px;
        font-weight: 500;
    }

    .new-content-notification-content svg {
        color: #4ade80;
        flex-shrink: 0;
    }

    .new-content-notification-close {
        background: transparent;
        border: none;
        color: white;
        font-size: 24px;
        line-height: 1;
        cursor: pointer;
        padding: 0;
        margin-left: auto;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: background-color 0.2s;
    }

    .new-content-notification-close:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    @media (max-width: 640px) {
        .new-content-notification {
            top: 60px;
            right: 10px;
            left: 10px;
        }
    }
</style>
@endpush
@endsection
