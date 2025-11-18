{{-- Lazy Content Row Component - Matching Frontend LazyContentRow --}}
@props([
    'title' => '',
    'endpoint' => '',
    'type' => 'movies',
    'contentType' => null,
    'viewMoreLink' => null,
    'limit' => 8
])

<div class="lazy-content-row" 
     data-endpoint="{{ $endpoint }}" 
     data-type="{{ $type }}" 
     data-content-type="{{ $contentType }}" 
     data-limit="{{ $limit }}"
     data-loaded="false"
     data-title="{{ $title }}"
     data-unique-id="{{ md5($title . $endpoint) }}"
     style="min-height: 300px;">
    <!-- Loading Skeleton - Matching Frontend -->
    <div class="lazy-skeleton px-4 md:px-8 lg:px-16 py-8">
        <div class="h-8 w-48 bg-gray-800 rounded animate-pulse mb-6"></div>
        <div class="flex gap-4 overflow-hidden">
            <div class="flex-shrink-0 w-40 h-60 bg-gray-800 rounded animate-pulse"></div>
            <div class="flex-shrink-0 w-40 h-60 bg-gray-800 rounded animate-pulse"></div>
            <div class="flex-shrink-0 w-40 h-60 bg-gray-800 rounded animate-pulse"></div>
            <div class="flex-shrink-0 w-40 h-60 bg-gray-800 rounded animate-pulse"></div>
            <div class="flex-shrink-0 w-40 h-60 bg-gray-800 rounded animate-pulse"></div>
            <div class="flex-shrink-0 w-40 h-60 bg-gray-800 rounded animate-pulse"></div>
            <div class="flex-shrink-0 w-40 h-60 bg-gray-800 rounded animate-pulse"></div>
            <div class="flex-shrink-0 w-40 h-60 bg-gray-800 rounded animate-pulse"></div>
        </div>
    </div>
    <!-- Content will be loaded here -->
    <div class="lazy-content" style="display: none;"></div>
</div>

@push('styles')
<style>
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
@endpush

@push('scripts')
<script>
    (function() {
        // Use a more unique selector - find by lazy-content-row class with endpoint data attribute
        // Wait a bit for DOM to be ready
        const endpointValue = '{{ $endpoint }}';
        const titleValue = '{{ $title }}';
        const uniqueId = `lazy-row-${btoa(endpointValue + titleValue).replace(/[+/=]/g, '').substring(0, 16)}`;
        
        // Helper to normalize URL - decode HTML entities and handle & vs &amp;
        function normalizeUrl(url) {
            if (!url) return '';
            // Decode HTML entities (dataset already does this, but be safe)
            const temp = document.createElement('div');
            temp.innerHTML = url;
            let decoded = temp.textContent || temp.innerText || url;
            // Normalize &amp; to &
            decoded = decoded.replace(/&amp;/g, '&');
            return decoded;
        }
        
        function findRow() {
            // Use title first as it's more reliable (no encoding issues)
            const targetUniqueId = '{{ md5($title . $endpoint) }}';
            console.log(`[Lazy Content Row: ${uniqueId}] Searching for row with unique-id:`, targetUniqueId, 'title:', titleValue);
            
            const rows = document.querySelectorAll('.lazy-content-row');
            for (let r of rows) {
                const rowUniqueId = r.dataset.uniqueId || '';
                const rowTitle = r.dataset.title || '';
                
                // Match by unique ID first (most reliable)
                if (rowUniqueId === targetUniqueId) {
                    console.log(`[Lazy Content Row: ${uniqueId}] Found matching row by unique-id!`);
                    return r;
                }
                
                // Fallback: match by title
                if (rowTitle === titleValue) {
                    console.log(`[Lazy Content Row: ${uniqueId}] Found matching row by title!`);
                    return r;
                }
            }
            
            // Last resort: try to match by endpoint (with normalization)
            const normalizedEndpoint = normalizeUrl(endpointValue);
            console.log(`[Lazy Content Row: ${uniqueId}] Trying endpoint matching with:`, normalizedEndpoint);
            for (let r of rows) {
                const rowEndpoint = r.dataset.endpoint || '';
                const normalizedRowEndpoint = normalizeUrl(rowEndpoint);
                if (normalizedRowEndpoint === normalizedEndpoint || rowEndpoint === endpointValue) {
                    console.log(`[Lazy Content Row: ${uniqueId}] Found matching row by endpoint!`);
                    return r;
                }
            }
            
            console.warn(`[Lazy Content Row: ${uniqueId}] No matching row found. Available rows:`, Array.from(rows).map(r => ({
                uniqueId: r.dataset.uniqueId,
                title: r.dataset.title,
                endpoint: r.dataset.endpoint
            })));
            return null;
        }
        
        let row = findRow();
        let retryCount = 0;
        const maxRetries = 10;
        
        if (!row) {
            console.warn(`[Lazy Content Row: ${uniqueId}] Row not found for endpoint: ${endpointValue}, retrying...`);
            // Try again after a delay
            const retryFind = () => {
                row = findRow();
                if (row) {
                    console.log(`[Lazy Content Row: ${uniqueId}] Row found after retry`);
                    // Continue with initialization
                    continueInitialization();
                } else if (retryCount < maxRetries) {
                    retryCount++;
                    setTimeout(retryFind, 100);
                } else {
                    console.error(`[Lazy Content Row: ${uniqueId}] Failed to find row after ${maxRetries} retries`);
                }
            };
            setTimeout(retryFind, 100);
            return;
        }
        
        // Declare variables that will be set in continueInitialization
        let lazySkeleton, lazyContent, endpoint, type, contentType, limit, hasLoaded;

        function continueInitialization() {
            // Verify row is available
            if (!row) {
                row = findRow();
                if (!row) {
                    console.error(`[Lazy Content Row: ${uniqueId}] Row not available for initialization`);
                    return;
                }
            }
            
            // Add unique identifier to help debugging
            row.dataset.uniqueId = uniqueId;

            lazySkeleton = row.querySelector('.lazy-skeleton');
            lazyContent = row.querySelector('.lazy-content');
            endpoint = row.dataset.endpoint;
            type = row.dataset.type;
            contentType = row.dataset.contentType;
            limit = row.dataset.limit;
            hasLoaded = row.dataset.loaded === 'true';
            
            console.log(`[Lazy Content Row: ${uniqueId}] Initialized for "${titleValue}"`);
            
            // Now initialize the observer
            initializeLazyContentRow();
        }

        function loadContent() {
            console.log(`[Lazy Content Row: ${uniqueId}] loadContent() called for "${titleValue}"`);
            if (hasLoaded) {
                console.log(`[Lazy Content Row: ${uniqueId}] Already loaded, skipping`);
                return;
            }
            hasLoaded = true;
            row.dataset.loaded = 'true';

            // Create ContentRow structure
            const viewMoreLink = '{{ $viewMoreLink }}';
            const title = '{{ $title }}';
            const containerId = `content-row-${btoa(endpoint).replace(/[+/=]/g, '')}`;

            console.log(`[Lazy Content Row: ${uniqueId}] Creating content row structure`);
            lazyContent.innerHTML = `
                <div class="content-row" data-endpoint="${endpoint}" data-type="${type}" data-content-type="${contentType}" data-limit="${limit}">
                    <div class="content-row-header">
                        <h2 class="content-row-title">${title}</h2>
                        ${viewMoreLink ? `<a href="${viewMoreLink}" class="content-row-link"><span>View More</span><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></a>` : ''}
                    </div>
                    <div class="content-row-scroll" id="${containerId}">
                        <div class="spinner"></div>
                    </div>
                </div>
            `;

            lazySkeleton.style.display = 'none';
            lazyContent.style.display = 'block';

            console.log(`[Lazy Content Row] Starting to load actual content`);
            // Load the actual content
            loadContentRowData(endpoint, type, contentType, limit, lazyContent.querySelector('.content-row-scroll'));
        }

        async function loadContentRowData(endpoint, type, contentType, limit, container) {
            try {
                console.log(`[Lazy Content Row] Loading data for endpoint:`, endpoint);
                console.log(`[Lazy Content Row] Type:`, type, 'ContentType:', contentType, 'Limit:', limit);
                
                // Use API_BASE_URL with endpoint
                const url = endpoint.startsWith('/api/v1') 
                    ? `${window.location.origin}${endpoint}`
                    : `${API_BASE_URL}${endpoint.startsWith('/') ? endpoint : '/' + endpoint}`;
                
                console.log(`[Lazy Content Row] Fetching from:`, url);
                const response = await apiFetch(url);
                console.log(`[Lazy Content Row] Response status:`, response.status);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error(`[Lazy Content Row] HTTP error! status: ${response.status}`, errorText);
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                console.log(`[Lazy Content Row] Response data:`, result);

                let items = [];
                if (result.success && result.data) {
                    if (type === 'leaderboard') {
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
                            console.log(`[Lazy Content Row] Mixed - movies: ${movies.length}, tvShows: ${tvShows.length}, total: ${items.length}`);
                        } else {
                            items = contentType === 'tvShows' ? (result.data.tvShows || []) : (result.data.movies || []);
                            console.log(`[Lazy Content Row] Leaderboard items:`, items);
                        }
                    } else if (type === 'movies') {
                        items = result.data.movies || [];
                        console.log(`[Lazy Content Row] Movies items:`, items);
                    } else if (type === 'tvshows') {
                        items = result.data.tvShows || [];
                        console.log(`[Lazy Content Row] TV Shows items:`, items);
                    } else if (type === 'mixed' || type === 'all') {
                        // Handle mixed content type
                        const movies = result.data.movies || [];
                        const tvShows = result.data.tvShows || [];
                        items = [...movies, ...tvShows].sort((a, b) => {
                            const scoreA = (a.view_count || 0) + ((a.popularity || 0) * 10);
                            const scoreB = (b.view_count || 0) + ((b.popularity || 0) * 10);
                            return scoreB - scoreA;
                        });
                        console.log(`[Lazy Content Row] Mixed - items:`, items);
                    }
                } else {
                    items = result.data || [];
                    console.log(`[Lazy Content Row] Fallback items:`, items);
                }

                console.log(`[Lazy Content Row: ${uniqueId}] Final items count:`, items.length);

                if (!items || items.length === 0) {
                    console.warn(`[Lazy Content Row: ${uniqueId}] No items found - hiding row`);
                    // Hide the entire row when no content is available
                    const currentRow = findRow();
                    if (currentRow && currentRow.parentElement) {
                        currentRow.style.display = 'none';
                    }
                    // Also hide the skeleton if still visible
                    if (lazySkeleton) {
                        lazySkeleton.style.display = 'none';
                    }
                    if (lazyContent) {
                        lazyContent.style.display = 'none';
                    }
                    return;
                }

                container.innerHTML = '';
                console.log(`[Lazy Content Row] Rendering ${items.length} items`);
                items.slice(0, parseInt(limit)).forEach((item, index) => {
                    console.log(`[Lazy Content Row] Creating card ${index + 1}:`, item);
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
                console.log(`[Lazy Content Row] Successfully loaded ${Math.min(items.length, parseInt(limit))} items`);
            } catch (error) {
                console.error(`[Lazy Content Row] Error loading lazy content:`, error);
                console.error(`[Lazy Content Row] Error stack:`, error.stack);
                container.innerHTML = '<p class="text-gray-400 text-center py-4">Failed to load content</p>';
            }
        }

        function createContentCard(item, itemType) {
            const card = document.createElement('a');
            card.className = 'content-card';
            card.href = itemType === 'tvshow' 
                ? `/tvshow/${item.id}${item.name ? '?name=' + encodeURIComponent(item.name) : ''}`
                : `/movie/${item.id}`;

            // Handle poster path - support both relative paths and full URLs (matching content-row)
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

        // Intersection Observer for lazy loading - Matching Frontend
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach(entry => {
                    console.log(`[Lazy Content Row: ${uniqueId}] Intersection observed - isIntersecting:`, entry.isIntersecting, 'hasLoaded:', hasLoaded);
                    if (entry.isIntersecting && !hasLoaded) {
                        console.log(`[Lazy Content Row: ${uniqueId}] Triggering load for "${titleValue}"`);
                        loadContent();
                        observer.disconnect();
                    }
                });
            },
            {
                root: null,
                rootMargin: '400px', // Load 400px before element comes into view
                threshold: 0.1
            }
        );

        // Wait for API_BASE_URL to be defined before observing
        function initializeLazyRow() {
            // Verify row still exists
            const currentRow = findRow();
            if (!currentRow) {
                console.warn(`[Lazy Content Row: ${uniqueId}] Row not found during initialization`);
                setTimeout(initializeLazyRow, 100);
                return;
            }
            
            if (typeof API_BASE_URL === 'undefined') {
                console.log(`[Lazy Content Row: ${uniqueId}] API_BASE_URL not defined yet, waiting...`);
                setTimeout(initializeLazyRow, 100);
                return;
            }
            console.log(`[Lazy Content Row: ${uniqueId}] API_BASE_URL is defined, starting observer for "${titleValue}"`);
            observer.observe(currentRow);
        }
        
        // Initialize after a short delay to ensure DOM is ready
        function initializeLazyContentRow() {
            setTimeout(initializeLazyRow, 50);
        }
        
        // If row is found immediately, continue with initialization
        if (row) {
            continueInitialization();
        }
    })();
</script>
@endpush

