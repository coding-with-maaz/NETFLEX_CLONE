@extends('layouts.app')

@section('title', 'Trending - Nazaara Box')

@section('seo_title', 'Trending Movies & TV Shows - Nazaara Box')
@section('seo_description', 'Discover trending movies and TV shows that are popular right now. Watch the most talked-about content on Nazaara Box.')
@section('seo_type', 'website')
@section('seo_url', route('trending'))

@push('styles')
<style>
    .trending-page-wrapper {
        padding-top: 64px;
    }

    @media (min-width: 768px) {
        .trending-page-wrapper {
            padding-top: 80px;
        }
    }

    /* Button Styles - Ensuring Tailwind classes work */
    .content-type-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        font-size: 14px;
    }

    .content-type-btn.active {
        background-color: #dc2626 !important;
        color: white !important;
    }

    .content-type-btn.inactive {
        background-color: #1f2937 !important;
        color: #d1d5db !important;
    }

    .content-type-btn.inactive:hover {
        background-color: #374151 !important;
    }

    .period-btn {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .period-btn.active {
        background-color: #dc2626 !important;
        color: white !important;
    }

    .period-btn.inactive {
        background-color: #1f2937 !important;
        color: #d1d5db !important;
    }

    .period-btn.inactive:hover {
        background-color: #374151 !important;
    }

    /* Trending Badge */
    .trending-badge {
        position: absolute;
        top: -6px;
        left: -6px;
        z-index: 10;
        background: linear-gradient(to bottom right, #facc15, #fb923c);
        color: white;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 11px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    /* Container responsive padding */
    .trending-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 32px 16px;
    }

    @media (min-width: 768px) {
        .trending-container {
            padding: 32px 32px;
        }
    }

    @media (min-width: 1024px) {
        .trending-container {
            padding: 32px 64px;
        }
    }
</style>
@endpush

@section('content')
<div style="min-height: 100vh; background-color: #000000; padding-bottom: 48px;" class="trending-page-wrapper">
    <div class="trending-container">
        <div style="margin-bottom: 32px; padding-top: 32px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 32px; height: 32px; color: #dc2626;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <h1 style="font-size: 30px; font-weight: bold; color: white; margin: 0;">
                    <span style="display: block;">Trending Now</span>
                </h1>
            </div>
            <p style="color: #9ca3af; margin: 0;">
                Discover what's hot right now based on what people are watching
            </p>
        </div>

        <!-- Filters - Matching Frontend TrendingPage.jsx -->
        <div style="margin-bottom: 32px;">
            <!-- Content Type Filter - Matching Frontend TrendingPage.jsx -->
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 16px;">
                <button onclick="setContentType('all')" id="type-all" class="content-type-btn active">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    <span>All</span>
                </button>
                <button onclick="setContentType('movies')" id="type-movies" class="content-type-btn inactive">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                    </svg>
                    <span>Movies</span>
                </button>
                <button onclick="setContentType('tvshows')" id="type-tvshows" class="content-type-btn inactive">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span>TV Shows</span>
                </button>
            </div>

            <!-- Period Filter - Matching Frontend TrendingPage.jsx -->
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px;">
                <span style="color: #9ca3af; font-size: 14px; font-weight: 500;">Time Period:</span>
                <button onclick="setPeriod('today')" id="period-today" class="period-btn active">Today</button>
                <button onclick="setPeriod('week')" id="period-week" class="period-btn inactive">This Week</button>
                <button onclick="setPeriod('month')" id="period-month" class="period-btn inactive">This Month</button>
                <button onclick="setPeriod('overall')" id="period-overall" class="period-btn inactive">All Time</button>
            </div>
        </div>

        <div id="loading-state" style="display: none; justify-content: center; align-items: center; padding: 80px 0;">
            <div class="spinner"></div>
        </div>

        <div id="trending-content">
            <div id="results-info" style="color: #9ca3af; margin-bottom: 16px;"></div>
            <div id="content-grid"></div>
            <style>
                #content-grid {
                    display: grid;
                    gap: 12px;
                    grid-template-columns: repeat(2, 1fr);
                }
                
                @media (min-width: 640px) {
                    #content-grid {
                        grid-template-columns: repeat(3, 1fr);
                    }
                }
                
                @media (min-width: 768px) {
                    #content-grid {
                        grid-template-columns: repeat(4, 1fr) !important;
                    }
                }
                
                @media (min-width: 1024px) {
                    #content-grid {
                        grid-template-columns: repeat(4, 1fr) !important;
                    }
                }
            </style>
            <div id="empty-state" style="display: none; text-align: center; padding: 80px 0;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 64px; height: 64px; color: #6b7280; margin: 0 auto 16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <p style="color: #9ca3af; font-size: 18px; margin: 0;">No trending content found for this period.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let trendingContent = [];
    let period = 'today';
    let contentType = 'all';
    let loading = false;

    function setContentType(type) {
        contentType = type;
        ['all', 'movies', 'tvshows'].forEach(t => {
            const btn = document.getElementById(`type-${t}`);
            if (btn) {
                btn.className = t === type ? 'content-type-btn active' : 'content-type-btn inactive';
            }
        });
        fetchTrendingContent();
    }

    function setPeriod(p) {
        period = p;
        ['today', 'week', 'month', 'overall'].forEach(per => {
            const btn = document.getElementById(`period-${per}`);
            if (btn) {
                btn.className = per === p ? 'period-btn active' : 'period-btn inactive';
            }
        });
        fetchTrendingContent();
    }

    async function fetchTrendingContent() {
        loading = true;
        document.getElementById('loading-state').style.display = 'flex';
        document.getElementById('trending-content').style.display = 'none';
        
        try {
            let endpoint;
            if (contentType === 'all') {
                // For "all", combine movies and TV shows
                endpoint = `${API_BASE_URL}/leaderboard/trending?period=${period}&limit=50`;
            } else if (contentType === 'movies') {
                endpoint = `${API_BASE_URL}/leaderboard/movies/leaderboard?period=${period}&limit=50`;
            } else {
                endpoint = `${API_BASE_URL}/leaderboard/tvshows/leaderboard?period=${period}&limit=50`;
            }
            
            console.log('[Trending] Fetching from:', endpoint);
            const response = await apiFetch(endpoint);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('[Trending] HTTP error:', response.status, errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('[Trending] Response data:', result);
            
            // Extract content based on response structure
            if (contentType === 'all') {
                // For "all", combine movies and TV shows
                let allMoviesData = result.data?.movies || [];
                const tvShowsData = result.data?.tvShows || [];
                
                // Filter out movies with 18+ genre
                const moviesData = allMoviesData.filter(movie => {
                    if (!movie.genres || !Array.isArray(movie.genres)) return true;
                    return !movie.genres.some(genre => {
                        const genreName = (genre.name || genre || '').toLowerCase();
                        return genreName.includes('18+') || genreName.includes('18');
                    });
                });
                
                // Combine and sort by view_count or popularity
                trendingContent = [...moviesData.map(m => ({...m, type: 'movie'})), 
                                   ...tvShowsData.map(t => ({...t, type: 'tvshow'}))]
                    .sort((a, b) => (b.view_count || b.viewCount || 0) - (a.view_count || a.viewCount || 0))
                    .slice(0, 50);
                    
                console.log('[Trending] Combined content:', trendingContent.length, 'items');
            } else if (contentType === 'movies') {
                let allMovies = result.data?.movies || result.data || [];
                
                // Filter out movies with 18+ genre
                trendingContent = allMovies.filter(movie => {
                    if (!movie.genres || !Array.isArray(movie.genres)) return true;
                    return !movie.genres.some(genre => {
                        const genreName = (genre.name || genre || '').toLowerCase();
                        return genreName.includes('18+') || genreName.includes('18');
                    });
                });
                
                console.log('[Trending] Movies:', trendingContent.length);
            } else {
                trendingContent = result.data?.tvShows || result.data || [];
                console.log('[Trending] TV Shows:', trendingContent.length);
            }
            
            renderContent();
        } catch (error) {
            console.error('[Trending] Error fetching trending content:', error);
            trendingContent = [];
            renderContent();
        } finally {
            loading = false;
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('trending-content').style.display = 'block';
        }
    }

    function renderContent() {
        const grid = document.getElementById('content-grid');
        const emptyState = document.getElementById('empty-state');
        const resultsInfo = document.getElementById('results-info');
        grid.innerHTML = '';
        
        if (trendingContent.length > 0) {
            const typeText = contentType === 'all' ? 'items' : contentType === 'movies' ? 'movies' : 'TV shows';
            resultsInfo.textContent = `${trendingContent.length} trending ${typeText}`;
            emptyState.style.display = 'none';
            
            trendingContent.forEach((item, index) => {
                // Determine if it's a movie based on contentType or item structure
                let isMovie = false;
                if (contentType === 'movies') {
                    isMovie = true;
                } else if (contentType === 'tvshows') {
                    isMovie = false;
                } else if (contentType === 'all') {
                    // Check if item has 'title' (movie) or 'name' (TV show), or type field
                    isMovie = item.type === 'movie' || (item.title && !item.name) || (!item.name && item.title);
                }
                
                const content = item;
                console.log('[Trending] Creating card:', index + 1, 'isMovie:', isMovie, 'content:', content);
                const card = createCard(content, isMovie, index + 1);
                grid.appendChild(card);
            });
        } else {
            resultsInfo.textContent = '';
            emptyState.style.display = 'block';
        }
    }

    function createCard(content, isMovie, rank) {
        const cardWrapper = document.createElement('div');
        cardWrapper.className = 'relative';
        cardWrapper.style.cssText = 'cursor: pointer;';
        cardWrapper.onclick = () => {
            window.location.href = isMovie ? `/movie/${content.id}` : `/tvshow/${content.id}`;
        };

        // Trending Badge - Matching Frontend TrendingPage.jsx
        const badge = document.createElement('div');
        badge.className = 'trending-badge';
        badge.textContent = `#${rank}`;
        cardWrapper.appendChild(badge);

        const imageUrl = content.poster_path ? `https://image.tmdb.org/t/p/w500${content.poster_path}` : '/images/placeholder.svg';
        const title = isMovie ? (content.title || 'Untitled') : (content.name || 'Untitled');
        const viewCount = content.view_count || content.viewCount || 0;

        // Create card using MovieCard or TVShowCard style with inline styles for reliability
        cardWrapper.innerHTML += `
            <div style="overflow: hidden; transition: all 0.3s; cursor: pointer;" onmouseenter="this.style.transform='scale(1.05)'" onmouseleave="this.style.transform='scale(1)'">
                <div style="position: relative; aspect-ratio: 2/3; overflow: hidden; background-color: #1f2937; border-radius: 6px;">
                    <img src="${imageUrl}" alt="${title}" loading="lazy" onerror="this.src='/images/placeholder.svg'" 
                         style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
                         onmouseenter="this.style.transform='scale(1.1)'" onmouseleave="this.style.transform='scale(1)'">
                    
                    <!-- Overlay on Hover -->
                    <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); opacity: 0; transition: opacity 0.3s;"
                         onmouseenter="this.style.opacity='1'" onmouseleave="this.style.opacity='0'">
                        <div style="position: absolute; bottom: 10px; left: 10px; right: 10px;">
                            <button onclick="event.stopPropagation(); window.location.href='/${isMovie ? 'movie' : 'tvshow'}/${content.id}';" 
                                    style="display: flex; align-items: center; justify-content: center; gap: 6px; background-color: #dc2626; color: white; padding: 6px 12px; border-radius: 4px; width: 100%; border: none; cursor: pointer; transition: background-color 0.2s; font-size: 12px; font-weight: 600;"
                                    onmouseenter="this.style.backgroundColor='#b91c1c'" onmouseleave="this.style.backgroundColor='#dc2626'">
                                <svg fill="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                                <span>Watch Now</span>
                            </button>
                        </div>
                    </div>

                    <!-- Rating Badge -->
                    ${content.vote_average ? `
                    <div style="position: absolute; top: 6px; right: 6px; background-color: rgba(234, 179, 8, 0.9); backdrop-filter: blur(4px); border-radius: 9999px; padding: 3px 6px; display: flex; align-items: center; gap: 2px;">
                        <span style="font-size: 10px; font-weight: 500; color: white;">⭐ ${parseFloat(content.vote_average).toFixed(1)}</span>
                    </div>
                    ` : ''}
                </div>
                
                <!-- Title -->
                <h3 style="margin-top: 6px; color: white; font-weight: 600; font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; transition: color 0.2s;"
                    onmouseenter="this.style.color='#dc2626'" onmouseleave="this.style.color='white'">
                    ${title}
                </h3>
                
                <!-- View Count - Matching Frontend -->
                ${viewCount ? `
                <div style="margin-top: 2px; text-align: center;">
                    <span style="font-size: 10px; color: #9ca3af;">
                        ${parseInt(viewCount).toLocaleString()} views
                    </span>
                </div>
                ` : ''}
            </div>
        `;

        return cardWrapper;
    }

    document.addEventListener('DOMContentLoaded', fetchTrendingContent);
</script>
@endpush
@endsection
