{{-- Content Row Component - Matching Flutter ContentRow --}}
@props([
    'title' => '',
    'endpoint' => '',
    'type' => 'movies', // 'movies', 'tvshows', 'leaderboard'
    'contentType' => null, // For leaderboard: 'movies' or 'tvShows'
    'viewMoreLink' => null,
    'limit' => 8
])

<div class="content-row" data-endpoint="{{ $endpoint }}" data-type="{{ $type }}" data-content-type="{{ $contentType }}" data-limit="{{ $limit }}">
    <div class="content-row-header">
        <h2 class="content-row-title">{{ $title }}</h2>
        @if($viewMoreLink)
        <a href="{{ $viewMoreLink }}" class="content-row-link">
            <span>View More</span>
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
        @endif
    </div>
    <div class="content-row-scroll" id="content-row-{{ md5($title . $endpoint) }}">
        <div class="spinner"></div>
    </div>
</div>

@push('scripts')
<script>
    (function() {
        // Use a more specific selector with the container ID to ensure uniqueness
        const containerId = 'content-row-{{ md5($title . $endpoint) }}';
        const container = document.getElementById(containerId);
        if (!container) {
            console.warn(`[Content Row: ${containerId}] Container not found in DOM`);
            return;
        }
        
        const row = container.closest('.content-row');
        if (!row) {
            console.warn(`[Content Row: ${containerId}] Row not found`);
            return;
        }

        // Store row reference for later use (to hide if no content)
        const rowElement = row;

        const endpoint = row.dataset.endpoint;
        const type = row.dataset.type;
        const contentType = row.dataset.contentType;
        const limit = row.dataset.limit;

        async function loadContentRow() {
            try {
                console.log(`[Content Row: ${containerId}] Starting to load...`);
                console.log(`[Content Row: ${containerId}] Endpoint:`, endpoint);
                console.log(`[Content Row: ${containerId}] Type:`, type);
                console.log(`[Content Row: ${containerId}] ContentType:`, contentType);
                console.log(`[Content Row: ${containerId}] Limit:`, limit);
                
                // Use API_BASE_URL with endpoint
                const url = endpoint.startsWith('/api/v1') 
                    ? `${window.location.origin}${endpoint}`
                    : `${API_BASE_URL}${endpoint.startsWith('/') ? endpoint : '/' + endpoint}`;
                
                console.log(`[Content Row: ${containerId}] API_BASE_URL:`, API_BASE_URL);
                console.log(`[Content Row: ${containerId}] Fetching from:`, url);
                
                const response = await apiFetch(url);
                console.log(`[Content Row: ${containerId}] Response status:`, response.status, response.statusText);
                console.log(`[Content Row: ${containerId}] Response ok:`, response.ok);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error(`[Content Row: ${containerId}] HTTP error! status: ${response.status}`, errorText);
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                console.log(`[Content Row: ${containerId}] Response data:`, result);
                console.log(`[Content Row: ${containerId}] result.success:`, result.success);
                console.log(`[Content Row: ${containerId}] result.data:`, result.data);

                let items = [];
                if (result.success && result.data) {
                    // Handle different response structures - Matching Frontend ContentRow.jsx
                    if (type === 'leaderboard') {
                        // Leaderboard endpoints return data.movies or data.tvShows
                        if (contentType === 'all' || contentType === 'mixed') {
                            // Mix movies and TV shows for trending/mixed content
                            const movies = result.data.movies || [];
                            const tvShows = result.data.tvShows || [];
                            // Combine and sort by view_count or popularity
                            items = [...movies, ...tvShows].sort((a, b) => {
                                const scoreA = (a.view_count || 0) + ((a.popularity || 0) * 10);
                                const scoreB = (b.view_count || 0) + ((b.popularity || 0) * 10);
                                return scoreB - scoreA;
                            });
                            console.log(`[Content Row: ${containerId}] Mixed - movies: ${movies.length}, tvShows: ${tvShows.length}, total: ${items.length}`);
                        } else {
                            items = contentType === 'tvShows' ? (result.data.tvShows || []) : (result.data.movies || []);
                            console.log(`[Content Row: ${containerId}] Leaderboard - contentType: ${contentType}, items:`, items);
                        }
                    } else if (type === 'movies') {
                        items = result.data.movies || [];
                        console.log(`[Content Row: ${containerId}] Movies - items:`, items);
                    } else if (type === 'tvshows') {
                        items = result.data.tvShows || [];
                        console.log(`[Content Row: ${containerId}] TV Shows - items:`, items);
                    } else if (type === 'mixed' || type === 'all') {
                        // Handle mixed content type
                        const movies = result.data.movies || [];
                        const tvShows = result.data.tvShows || [];
                        items = [...movies, ...tvShows].sort((a, b) => {
                            const scoreA = (a.view_count || 0) + ((a.popularity || 0) * 10);
                            const scoreB = (b.view_count || 0) + ((b.popularity || 0) * 10);
                            return scoreB - scoreA;
                        });
                        console.log(`[Content Row: ${containerId}] Mixed - items:`, items);
                    }
                } else {
                    items = result.data || result || [];
                    console.log(`[Content Row: ${containerId}] Fallback - items:`, items);
                }

                console.log(`[Content Row: ${containerId}] Final items array:`, items);
                console.log(`[Content Row: ${containerId}] Items length:`, items.length);

                if (!items || items.length === 0) {
                    console.warn(`[Content Row: ${containerId}] No items found - hiding row`);
                    // Hide the entire row when no content is available
                    if (rowElement && rowElement.parentElement) {
                        rowElement.style.display = 'none';
                    }
                    return;
                }

                console.log(`[Content Row: ${containerId}] Rendering ${items.length} items (limit: ${limit})`);
                container.innerHTML = '';
                items.slice(0, parseInt(limit)).forEach((item, index) => {
                    console.log(`[Content Row: ${containerId}] Creating card ${index + 1}:`, item);
                    // Determine item type - check if it has 'name' (TV show) or 'title' (movie)
                    let itemType = 'movie';
                    if (type === 'tvshows' || contentType === 'tvShows') {
                        itemType = 'tvshow';
                    } else if (type === 'mixed' || contentType === 'all' || contentType === 'mixed') {
                        // For mixed content, determine by checking item properties
                        itemType = (item.name !== undefined) ? 'tvshow' : 'movie';
                    }
                    const card = createContentCard(item, itemType);
                    container.appendChild(card);
                });
                console.log(`[Content Row: ${containerId}] Successfully loaded ${Math.min(items.length, parseInt(limit))} items`);
            } catch (error) {
                console.error(`[Content Row: ${containerId}] Error loading content:`, error);
                console.error(`[Content Row: ${containerId}] Error stack:`, error.stack);
                container.innerHTML = '<p class="text-gray-400 text-center py-4">Failed to load content</p>';
            }
        }

        function createContentCard(item, itemType) {
            // Determine if item is a movie or TV show
            const isTVShow = item.name !== undefined || itemType === 'tvshow';
            const itemTypeFinal = isTVShow ? 'tvshow' : 'movie';
            
            const card = document.createElement('a');
            card.className = 'content-card';
            card.href = itemTypeFinal === 'tvshow' 
                ? `/tvshow/${item.id}${item.name ? '?name=' + encodeURIComponent(item.name) : ''}`
                : `/movie/${item.id}`;

            // Handle poster path - support both relative paths and full URLs
            let poster = item.poster_path || item.poster || '/images/placeholder.svg';
            
            // If poster_path starts with / and is not placeholder, it's likely a TMDB path
            if (poster && poster.startsWith('/') && !poster.startsWith('/images/')) {
                // TMDB image paths - prepend base URL
                poster = 'https://image.tmdb.org/t/p/w500' + poster;
            } else if (poster && !poster.startsWith('http') && !poster.startsWith('/')) {
                // If it's a relative path without leading slash, add it
                if (poster.startsWith('images/')) {
                    poster = '/' + poster;
                } else {
                    poster = '/images/placeholder.svg';
                }
            } else if (!poster || poster === '') {
                poster = '/images/placeholder.svg';
            }
            
            const title = item.title || item.name || 'Untitled';
            const imageWrapper = document.createElement('div');
            imageWrapper.style.position = 'relative';

            const img = document.createElement('img');
            img.src = poster;
            img.alt = title;
            img.className = 'content-card-image';
            img.loading = 'lazy';
            img.onerror = function() { this.src = '/images/placeholder.svg'; };

            imageWrapper.appendChild(img);

            // View count badge (if available)
            if (item.view_count && item.view_count > 0) {
                const badge = document.createElement('div');
                badge.className = 'content-card-badge';
                const formattedCount = formatViewCount(item.view_count);
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
            titleDiv.textContent = title;

            card.appendChild(imageWrapper);
            card.appendChild(titleDiv);

            return card;
        }

        function formatViewCount(count) {
            if (count >= 1000000) {
                return (count / 1000000).toFixed(1) + 'M';
            } else if (count >= 1000) {
                return (count / 1000).toFixed(1) + 'K';
            }
            return count.toString();
        }

        // Wait for API_BASE_URL to be defined, then load content
        function initializeContentRow() {
            console.log(`[Content Row: ${containerId}] initializeContentRow called`);
            console.log(`[Content Row: ${containerId}] API_BASE_URL defined:`, typeof API_BASE_URL !== 'undefined');
            console.log(`[Content Row: ${containerId}] API_BASE_URL value:`, typeof API_BASE_URL !== 'undefined' ? API_BASE_URL : 'undefined');
            
            if (typeof API_BASE_URL === 'undefined') {
                console.warn(`[Content Row: ${containerId}] API_BASE_URL not defined yet, waiting...`);
                setTimeout(initializeContentRow, 100);
                return;
            }
            console.log(`[Content Row: ${containerId}] API_BASE_URL is defined, starting to load`);
            loadContentRow().catch(error => {
                console.error(`[Content Row: ${containerId}] Unhandled error in loadContentRow:`, error);
                container.innerHTML = '<p class="text-gray-400 text-center py-4">Failed to load content</p>';
            });
        }
        
        // Load content when page is ready and API_BASE_URL is available
        console.log(`[Content Row: ${containerId}] Script executing for "{{ $title }}", document.readyState:`, document.readyState);
        
        function startInitialization() {
            // Wait for both container element and API_BASE_URL
            const checkAndInit = () => {
                const containerElement = document.getElementById(containerId);
                
                // Check if container exists
                if (!containerElement) {
                    console.log(`[Content Row: ${containerId}] Container not found yet, retrying...`);
                    setTimeout(checkAndInit, 100);
                    return;
                }
                
                // Check if API_BASE_URL is defined
                if (typeof API_BASE_URL === 'undefined') {
                    console.log(`[Content Row: ${containerId}] API_BASE_URL not defined yet, retrying...`);
                    setTimeout(checkAndInit, 100);
                    return;
                }
                
                // Both conditions met, initialize
                console.log(`[Content Row: ${containerId}] Container and API_BASE_URL ready, initializing`);
                initializeContentRow();
            };
            
            // Start checking immediately
            checkAndInit();
        }
        
        // Start initialization after a small delay to ensure DOM is ready
        setTimeout(startInitialization, 50);
    })();
</script>
@endpush

