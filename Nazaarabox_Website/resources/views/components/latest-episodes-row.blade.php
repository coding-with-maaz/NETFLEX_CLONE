{{-- Latest Episodes Row Component - Matching Frontend LatestEpisodesRow --}}
@props([
    'title' => 'Latest Episodes',
    'limit' => 20,
    'viewMoreLink' => null
])

<div class="latest-episodes-row px-4 md:px-8 lg:px-16 mb-8" data-limit="{{ $limit }}">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-white text-2xl font-bold">{{ $title }}</h2>
        @if($viewMoreLink)
        <a href="{{ $viewMoreLink }}" class="flex items-center space-x-1 text-red-500 hover:text-red-400 transition-colors">
            <span class="text-sm font-semibold">View More</span>
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
        @endif
    </div>
    <div class="episodes-scroll flex space-x-4 overflow-x-auto scrollbar-hide pb-4" id="episodes-scroll-{{ md5($title . $limit) }}">
        <div class="spinner mx-auto"></div>
    </div>
</div>

@push('scripts')
<script>
    (function() {
        const row = document.querySelector('[data-limit="{{ $limit }}"]');
        if (!row) return;

        const container = row.querySelector('.episodes-scroll');
        const limit = parseInt('{{ $limit }}');
        const containerId = container.id;

        async function loadLatestEpisodes() {
            try {
                console.log(`[Latest Episodes] Starting to load episodes...`);
                console.log(`[Latest Episodes] API_BASE_URL:`, API_BASE_URL);
                console.log(`[Latest Episodes] Limit:`, limit);
                
                container.innerHTML = '<div class="spinner mx-auto"></div>';
                
                const url = `${API_BASE_URL}/episodes/latest/all?limit=${limit}`;
                console.log(`[Latest Episodes] Fetching from:`, url);
                
                const response = await apiFetch(url);
                console.log(`[Latest Episodes] Response status:`, response.status, response.statusText);
                console.log(`[Latest Episodes] Response ok:`, response.ok);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error(`[Latest Episodes] HTTP error! status: ${response.status}`, errorText);
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                console.log(`[Latest Episodes] Response data:`, result);
                console.log(`[Latest Episodes] result.success:`, result.success);
                console.log(`[Latest Episodes] result.data:`, result.data);
                console.log(`[Latest Episodes] result.data type:`, Array.isArray(result.data) ? 'Array' : typeof result.data);
                
                const episodes = (result.success && result.data) ? result.data : (result.data || result || []);
                console.log(`[Latest Episodes] Extracted episodes:`, episodes);
                console.log(`[Latest Episodes] Episodes length:`, episodes.length);

                if (!episodes || episodes.length === 0) {
                    console.warn(`[Latest Episodes] No episodes found`);
                    container.innerHTML = '<p class="text-gray-400 text-center py-4">No episodes available</p>';
                    return;
                }

                console.log(`[Latest Episodes] Rendering ${episodes.length} episodes`);
                container.innerHTML = '';
                episodes.forEach((episode, index) => {
                    console.log(`[Latest Episodes] Creating card ${index + 1}:`, episode);
                    const card = createEpisodeCard(episode);
                    container.appendChild(card);
                });
                console.log(`[Latest Episodes] Successfully loaded ${episodes.length} episodes`);
            } catch (error) {
                console.error(`[Latest Episodes] Error loading episodes (${containerId}):`, error);
                console.error(`[Latest Episodes] Error stack:`, error.stack);
                container.innerHTML = '<p class="text-gray-400 text-center py-4">Failed to load episodes</p>';
            }
        }

        function createEpisodeCard(episode) {
            const card = document.createElement('div');
            card.className = 'episode-card flex-shrink-0 w-[300px] md:w-[350px] cursor-pointer group';
            
            const tvShowId = episode.tvshow_id || episode.tvShow?.id || episode.tvshow?.id;
            const tvShowName = episode.tvshow?.name || episode.tvShow?.name || '';
            const episodeName = episode.name || episode.title || `Episode ${episode.episode_number || episode.number || ''}`;
            const seasonNumber = episode.season_number || episode.seasonNumber || '';
            const episodeNumber = episode.episode_number || episode.number || '';
            
            // Image URL - Matching Frontend EpisodeCard logic
            const imageUrl = episode.still_path
                ? `https://image.tmdb.org/t/p/w500${episode.still_path}`
                : episode.tvShow?.backdrop_path || episode.tvshow?.backdrop_path
                ? `https://image.tmdb.org/t/p/w500${episode.tvShow?.backdrop_path || episode.tvshow?.backdrop_path}`
                : episode.tvShow?.poster_path || episode.tvshow?.poster_path
                ? `https://image.tmdb.org/t/p/w500${episode.tvShow?.poster_path || episode.tvshow?.poster_path}`
                : '/images/placeholder.svg';

            card.innerHTML = `
                <div onclick="window.location.href='/tvshow/${tvShowId}'">
                    <div class="relative aspect-video rounded-lg overflow-hidden bg-gray-800">
                        <img src="${imageUrl}" alt="${episodeName}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110" loading="lazy" onerror="this.src='/images/placeholder.svg'">
                        
                        <!-- Overlay on Hover -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <div class="absolute bottom-0 left-0 right-0 p-4">
                                <button class="flex items-center space-x-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition-colors">
                                    <svg fill="currentColor" viewBox="0 0 24 24" class="w-4 h-4">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                    <span class="text-sm font-semibold">Watch Now</span>
                                </button>
                            </div>
                        </div>

                        <!-- Episode Badge -->
                        ${seasonNumber && episodeNumber ? `
                        <div class="absolute top-2 left-2 bg-red-600 text-white px-2 py-1 rounded text-xs font-bold">
                            S${seasonNumber}E${episodeNumber}
                        </div>
                        ` : ''}
                    </div>

                    <!-- Episode Info -->
                    <div class="mt-3">
                        <h3 class="text-white font-semibold text-sm line-clamp-1 group-hover:text-red-500 transition-colors">
                            ${tvShowName}
                        </h3>
                        <p class="text-gray-400 text-xs line-clamp-1 mt-1">
                            ${episodeName}
                        </p>
                        ${episode.air_date ? `
                        <div class="flex items-center space-x-1 mt-1 text-gray-500 text-xs">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-3 h-3">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>${new Date(episode.air_date).toLocaleDateString()}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;

            return card;
        }

        // Wait for API_BASE_URL to be defined, then load episodes
        function initializeLatestEpisodes() {
            if (typeof API_BASE_URL === 'undefined') {
                console.warn(`[Latest Episodes] API_BASE_URL not defined yet, waiting...`);
                setTimeout(initializeLatestEpisodes, 100);
                return;
            }
            console.log(`[Latest Episodes] API_BASE_URL is defined, starting to load`);
            loadLatestEpisodes();
        }
        
        // Load episodes when page is ready and API_BASE_URL is available
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                console.log(`[Latest Episodes] DOM loaded, initializing`);
                initializeLatestEpisodes();
            });
        } else {
            console.log(`[Latest Episodes] Document ready, initializing`);
            initializeLatestEpisodes();
        }
    })();
</script>
@endpush

