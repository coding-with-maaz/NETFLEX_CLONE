@extends('layouts.admin')

@section('title', 'Seasons Management - Admin Panel')

@push('styles')
<style>
    .stat-card {
        background-color: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 8px;
    }
    .season-card {
        background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
        border: 2px solid #3a3a3a;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }
    .season-card:hover {
        border-color: #dc2626;
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(220, 38, 38, 0.3);
    }
    .episode-card {
        background: linear-gradient(135deg, #2a2a2a 0%, #1a1a1a 100%);
        border: 1px solid #3a3a3a;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 12px;
        transition: all 0.2s ease;
    }
    .episode-card:hover {
        border-color: #dc2626;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-dark-900">
    <!-- Header -->
    <header class="admin-header">
        <div style="max-width: 1280px; margin: 0 auto; padding: 0 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; height: 64px;">
                <div style="display: flex; align-items: center; gap: 24px;">
                    <a href="/admin/tvshows/{{ $id }}" style="color: #9ca3af; text-decoration: none;">← Back to TV Show</a>
                    <h1 style="font-size: 20px; font-weight: bold; color: white; margin: 0;">Seasons Management</h1>
                </div>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <button onclick="openCreateSeasonModal()" class="flex items-center gap-2 bg-primary-600 hover:bg-red-700 text-white px-4 py-2 rounded transition-colors text-sm">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Create Season</span>
                    </button>
                    <button onclick="openFetchSeasonModal()" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition-colors text-sm">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        <span>Fetch Season (Bulk)</span>
                    </button>
                    <button onclick="openFetchEpisodeModal()" class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition-colors text-sm">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span>Fetch Single Episode</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main style="max-width: 1280px; margin: 0 auto; padding: 32px 16px;">
        <!-- TV Show Info -->
        <div class="stat-card p-6 mb-6">
            <div style="display: flex; gap: 24px; align-items: center;">
                <img id="tvshow-poster" src="/images/placeholder.svg" alt="TV Show Poster" 
                     style="width: 120px; aspect-ratio: 2/3; object-fit: cover; border-radius: 8px;">
                <div>
                    <h2 id="tvshow-name" style="font-size: 24px; font-weight: bold; color: white; margin-bottom: 8px;">Loading...</h2>
                    <p id="tvshow-overview" style="color: #9ca3af; margin-bottom: 12px; max-width: 600px;">Loading...</p>
                    <div style="display: flex; gap: 16px; color: #9ca3af; font-size: 14px;">
                        <span>⭐ <span id="tvshow-rating">-</span></span>
                        <span>👁 <span id="tvshow-views">0</span></span>
                        <span>📺 <span id="tvshow-seasons-count">0</span> Seasons</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loading-state" style="display: flex; justify-content: center; align-items: center; min-height: 400px;">
            <div class="spinner"></div>
        </div>

        <!-- Seasons Container -->
        <div id="seasons-container" style="display: none;"></div>

        <!-- Empty State -->
        <div id="empty-state" style="display: none; text-align: center; padding: 64px 16px;">
            <p style="color: #9ca3af; font-size: 18px; margin-bottom: 16px;">No seasons found</p>
            <button onclick="openCreateSeasonModal()" class="bg-primary-600 hover:bg-red-700 text-white px-6 py-2 rounded transition-colors">
                Create First Season
            </button>
        </div>
    </main>
</div>

<!-- Create Season Modal -->
<div id="create-season-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background-color: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 12px; padding: 24px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; position: relative; z-index: 1001;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="color: white; font-size: 20px; font-weight: 600;">Create Season</h3>
            <button onclick="closeCreateSeasonModal()" style="background: none; border: none; color: #9ca3af; font-size: 24px; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">&times;</button>
        </div>
        <form id="create-season-form" onsubmit="event.preventDefault(); createSeason();">
            <div style="margin-bottom: 16px;">
                <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Season Number *</label>
                <input type="number" id="create-season-number" required min="0" 
                       style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Name</label>
                <input type="text" id="create-season-name" 
                       style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Overview</label>
                <textarea id="create-season-overview" rows="4" 
                          style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; resize: vertical; pointer-events: auto;"></textarea>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Air Date</label>
                <input type="date" id="create-season-air-date" 
                       style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;">
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeCreateSeasonModal()" 
                        style="background-color: #2a2a2a; color: white; padding: 10px 20px; border-radius: 6px; border: 1px solid #3a3a3a; cursor: pointer; pointer-events: auto;">
                    Cancel
                </button>
                <button type="submit" 
                        style="background-color: #dc2626; color: white; padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; pointer-events: auto;">
                    Create
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Season Modal -->
<div id="edit-season-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background-color: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 12px; padding: 24px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; position: relative; z-index: 1001;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="color: white; font-size: 20px; font-weight: 600;">Edit Season</h3>
            <button onclick="closeEditSeasonModal()" style="background: none; border: none; color: #9ca3af; font-size: 24px; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">&times;</button>
        </div>
        <form id="edit-season-form" onsubmit="event.preventDefault(); updateSeason();">
            <input type="hidden" id="edit-season-id">
            <input type="hidden" id="edit-season-number">
            <div style="margin-bottom: 16px;">
                <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Season Number</label>
                <input type="number" id="edit-season-number-display" disabled 
                       style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: #9ca3af; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Name</label>
                <input type="text" id="edit-season-name" 
                       style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Overview</label>
                <textarea id="edit-season-overview" rows="4" 
                          style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; resize: vertical; pointer-events: auto;"></textarea>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Poster Path</label>
                <input type="text" id="edit-season-poster-path" 
                       style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Air Date</label>
                <input type="date" id="edit-season-air-date" 
                       style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;">
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeEditSeasonModal()" 
                        style="background-color: #2a2a2a; color: white; padding: 10px 20px; border-radius: 6px; border: 1px solid #3a3a3a; cursor: pointer; pointer-events: auto;">
                    Cancel
                </button>
                <button type="submit" 
                        style="background-color: #dc2626; color: white; padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; pointer-events: auto;">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const tvshowId = {{ $id }};
    let tvShow = null;
    
    // TMDB Configuration
    const TMDB_BASE_URL = '{{ config("services.tmdb.base_url") }}';
    const TMDB_ACCESS_TOKEN = '{{ config("services.tmdb.access_token") }}';
    const TMDB_IMAGE_URL = '{{ config("services.tmdb.image_url") }}';

    async function loadTVShow() {
        const token = localStorage.getItem('adminAccessToken');
        if (!token) {
            window.location.href = '/admin/login';
            return;
        }

        try {
            // Load with episodes for full management
            const url = `${API_BASE_URL}/admin/tvshows/${tvshowId}?load_episodes=1`;
            const response = await fetch(url, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            if (response.status === 401) {
                window.location.href = '/admin/login';
                return;
            }

            const result = await response.json();
            if (result.success) {
                tvShow = result.data.tvShow;
                renderTVShowInfo();
                renderSeasons();
                document.getElementById('loading-state').style.display = 'none';
                document.getElementById('seasons-container').style.display = 'block';
                
                if (!tvShow.seasons || tvShow.seasons.length === 0) {
                    document.getElementById('empty-state').style.display = 'block';
                    document.getElementById('seasons-container').style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Error loading TV show:', error);
        }
    }

    function renderTVShowInfo() {
        let posterPath = '/images/placeholder.svg';
        if (tvShow.poster_path) {
            if (tvShow.poster_path.startsWith('http')) {
                posterPath = tvShow.poster_path;
            } else if (tvShow.poster_path.startsWith('/')) {
                posterPath = `https://image.tmdb.org/t/p/w300${tvShow.poster_path}`;
            } else {
                posterPath = `https://image.tmdb.org/t/p/w300/${tvShow.poster_path}`;
            }
        }

        document.getElementById('tvshow-poster').src = posterPath;
        document.getElementById('tvshow-name').textContent = tvShow.name || 'Untitled';
        document.getElementById('tvshow-overview').textContent = tvShow.overview || 'No overview available.';
        document.getElementById('tvshow-rating').textContent = (tvShow.vote_average || 0).toFixed(1);
        document.getElementById('tvshow-views').textContent = tvShow.view_count || 0;
        document.getElementById('tvshow-seasons-count').textContent = tvShow.seasons?.length || 0;
    }

    function renderSeasons() {
        const container = document.getElementById('seasons-container');
        if (!tvShow.seasons || tvShow.seasons.length === 0) {
            return;
        }

        // Sort seasons by season_number
        const sortedSeasons = [...tvShow.seasons].sort((a, b) => a.season_number - b.season_number);

        container.innerHTML = sortedSeasons.map(season => {
            const seasonPoster = season.poster_path 
                ? (season.poster_path.startsWith('http') ? season.poster_path : `https://image.tmdb.org/t/p/w300${season.poster_path}`)
                : '/images/placeholder.svg';

            return `
                <div class="season-card">
                    <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                        <img src="${seasonPoster}" alt="${season.name || `Season ${season.season_number}`}" 
                             style="width: 120px; aspect-ratio: 2/3; object-fit: cover; border-radius: 8px;"
                             onerror="this.src='/images/placeholder.svg'">
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                                <div>
                                    <h3 style="color: white; font-size: 20px; font-weight: 600; margin-bottom: 4px;">
                                        ${season.name || `Season ${season.season_number}`}
                                    </h3>
                                    <p style="color: #9ca3af; font-size: 14px; margin-bottom: 8px;">
                                        ${season.overview || 'No overview available.'}
                                    </p>
                                    <div style="display: flex; gap: 16px; color: #9ca3af; font-size: 12px;">
                                        <span>Episodes: ${season.episodes?.length || season.episode_count || 0}</span>
                                        ${season.air_date ? `<span>Air Date: ${new Date(season.air_date).getFullYear()}</span>` : ''}
                                    </div>
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    <button onclick="openEditSeasonModal(${season.id}, ${season.season_number}, '${(season.name || '').replace(/'/g, "\\'")}', '${(season.overview || '').replace(/'/g, "\\'")}', '${(season.poster_path || '').replace(/'/g, "\\'")}', '${season.air_date || ''}')" 
                                            style="background-color: #2563eb; color: white; padding: 8px 16px; border-radius: 6px; font-size: 12px; cursor: pointer; border: none; font-weight: 500;">
                                        ✏️ Edit
                                    </button>
                                    <button onclick="deleteSeason(${season.id}, '${(season.name || `Season ${season.season_number}`).replace(/'/g, "\\'")}')" 
                                            style="background-color: #dc2626; color: white; padding: 8px 16px; border-radius: 6px; font-size: 12px; cursor: pointer; border: none; font-weight: 500;">
                                        🗑️ Delete
                                    </button>
                                </div>
                            </div>
                            <div style="margin-top: 16px;">
                                <h4 style="color: white; font-size: 16px; font-weight: 600; margin-bottom: 12px;">Episodes (${season.episodes?.length || 0})</h4>
                                <div style="display: grid; gap: 12px;">
                                    ${(season.episodes || []).map(episode => {
                                        const episodeStill = episode.still_path 
                                            ? (episode.still_path.startsWith('http') ? episode.still_path : `https://image.tmdb.org/t/p/w300${episode.still_path}`)
                                            : '/images/placeholder.svg';
                                        
                                        return `
                                            <div class="episode-card">
                                                <div style="display: flex; gap: 12px;">
                                                    <img src="${episodeStill}" alt="${episode.name || `Episode ${episode.episode_number}`}" 
                                                         style="width: 120px; aspect-ratio: 16/9; object-fit: cover; border-radius: 6px;"
                                                         onerror="this.src='/images/placeholder.svg'">
                                                    <div style="flex: 1;">
                                                        <h5 style="color: white; font-size: 14px; font-weight: 600; margin-bottom: 4px;">
                                                            ${episode.episode_number}. ${episode.name || `Episode ${episode.episode_number}`}
                                                        </h5>
                                                        <p style="color: #9ca3af; font-size: 12px; margin-bottom: 8px; line-height: 1.4;">
                                                            ${(episode.overview || 'No overview available.').substring(0, 150)}${episode.overview && episode.overview.length > 150 ? '...' : ''}
                                                        </p>
                                                        <div style="display: flex; gap: 12px; color: #9ca3af; font-size: 11px;">
                                                            ${episode.air_date ? `<span>📅 ${new Date(episode.air_date).toLocaleDateString()}</span>` : ''}
                                                            ${episode.runtime ? `<span>⏱ ${episode.runtime} min</span>` : ''}
                                                            <span>⭐ ${(episode.vote_average || 0).toFixed(1)}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        `;
                                    }).join('') || '<p style="color: #9ca3af; font-size: 14px; padding: 20px; text-align: center;">No episodes yet</p>'}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Modal functions (same as detail.blade.php - simplified version)
    window.openCreateSeasonModal = function() {
        document.getElementById('create-season-modal').style.display = 'flex';
    };

    window.closeCreateSeasonModal = function() {
        document.getElementById('create-season-modal').style.display = 'none';
        document.getElementById('create-season-form').reset();
    };

    window.openEditSeasonModal = function(seasonId, seasonNumber, name, overview, posterPath, airDate) {
        document.getElementById('edit-season-id').value = seasonId;
        document.getElementById('edit-season-number').value = seasonNumber;
        document.getElementById('edit-season-number-display').value = seasonNumber;
        document.getElementById('edit-season-name').value = name || '';
        document.getElementById('edit-season-overview').value = overview || '';
        document.getElementById('edit-season-poster-path').value = posterPath || '';
        document.getElementById('edit-season-air-date').value = airDate || '';
        document.getElementById('edit-season-modal').style.display = 'flex';
    };

    window.closeEditSeasonModal = function() {
        document.getElementById('edit-season-modal').style.display = 'none';
    };

    // Fetch functions (reuse from detail.blade.php pattern)
    window.openFetchSeasonModal = function() {
        if (!tvShow || !tvShow.tmdb_id) {
            alert('TV show must have a TMDB ID to fetch seasons. Please add TMDB ID first.');
            return;
        }
        const seasonNumber = prompt(`Enter Season Number to fetch from TMDB:\n\nTV Show: ${tvShow.name || 'Untitled'}\nTMDB ID: ${tvShow.tmdb_id}`);
        if (seasonNumber === null || seasonNumber === '') return;
        const seasonNum = parseInt(seasonNumber);
        if (isNaN(seasonNum) || seasonNum < 0) {
            alert('Please enter a valid season number.');
            return;
        }
        if (confirm(`Fetch Season ${seasonNum} with ALL episodes from TMDB?`)) {
            fetchSeasonFromTMDB(seasonNum);
        }
    };

    window.openFetchEpisodeModal = function() {
        if (!tvShow || !tvShow.tmdb_id) {
            alert('TV show must have a TMDB ID to fetch episodes. Please add TMDB ID first.');
            return;
        }
        const seasonNumber = prompt(`Enter Season Number:\n\nTV Show: ${tvShow.name || 'Untitled'}`);
        if (seasonNumber === null || seasonNumber === '') return;
        const seasonNum = parseInt(seasonNumber);
        if (isNaN(seasonNum) || seasonNum < 0) {
            alert('Please enter a valid season number.');
            return;
        }
        const episodeNumber = prompt(`Enter Episode Number:\n\nSeason: ${seasonNum}`);
        if (episodeNumber === null || episodeNumber === '') return;
        const episodeNum = parseInt(episodeNumber);
        if (isNaN(episodeNum) || episodeNum < 1) {
            alert('Please enter a valid episode number.');
            return;
        }
        if (confirm(`Fetch Season ${seasonNum}, Episode ${episodeNum} from TMDB?`)) {
            fetchEpisodeFromTMDB(seasonNum, episodeNum);
        }
    };

    // Reuse fetch and CRUD functions from detail.blade.php (can be extracted to shared JS)
    async function createSeason() {
        const token = localStorage.getItem('adminAccessToken');
        const seasonData = {
            season_number: parseInt(document.getElementById('create-season-number').value),
            name: document.getElementById('create-season-name').value || null,
            overview: document.getElementById('create-season-overview').value || null,
            air_date: document.getElementById('create-season-air-date').value || null,
        };

        try {
            const response = await fetch(`${API_BASE_URL}/admin/tvshows/${tvshowId}/seasons/create`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(seasonData)
            });

            const result = await response.json();
            if (result.success) {
                alert('Season created successfully!');
                closeCreateSeasonModal();
                await loadTVShow();
            } else {
                alert('Error: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error creating season:', error);
            alert('Error creating season: ' + error.message);
        }
    }

    async function updateSeason() {
        const token = localStorage.getItem('adminAccessToken');
        const seasonId = parseInt(document.getElementById('edit-season-id').value);
        const seasonData = {
            season_number: parseInt(document.getElementById('edit-season-number').value),
            name: document.getElementById('edit-season-name').value || null,
            overview: document.getElementById('edit-season-overview').value || null,
            poster_path: document.getElementById('edit-season-poster-path').value || null,
            air_date: document.getElementById('edit-season-air-date').value || null,
        };

        try {
            const response = await fetch(`${API_BASE_URL}/admin/tvshows/${tvshowId}/seasons/${seasonId}`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(seasonData)
            });

            const result = await response.json();
            if (result.success) {
                alert('Season updated successfully!');
                closeEditSeasonModal();
                await loadTVShow();
            } else {
                alert('Error: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error updating season:', error);
            alert('Error updating season: ' + error.message);
        }
    }

    async function deleteSeason(seasonId, seasonName) {
        if (!confirm(`Are you sure you want to delete "${seasonName}"?\n\nThis action cannot be undone and will delete all associated episodes.`)) {
            return;
        }

        const token = localStorage.getItem('adminAccessToken');
        try {
            const response = await fetch(`${API_BASE_URL}/admin/tvshows/${tvshowId}/seasons/${seasonId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();
            if (result.success) {
                alert(`Season "${seasonName}" deleted successfully!`);
                await loadTVShow();
            } else {
                alert('Error: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error deleting season:', error);
            alert('Error deleting season: ' + error.message);
        }
    }

    // Fetch from TMDB functions (same pattern as detail.blade.php)
    window.fetchSeasonFromTMDB = async function(seasonNumber) {
        if (!tvShow || !tvShow.tmdb_id) {
            alert('TV show must have a TMDB ID.');
            return;
        }

        try {
            const response = await fetch(`${TMDB_BASE_URL}/tv/${tvShow.tmdb_id}/season/${seasonNumber}`, {
                headers: {
                    'Authorization': `Bearer ${TMDB_ACCESS_TOKEN}`,
                    'accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`TMDB API error: ${response.status}`);
            }

            const seasonData = await response.json();
            
            const formattedSeason = {
                season_number: seasonData.season_number,
                name: seasonData.name || `Season ${seasonData.season_number}`,
                overview: seasonData.overview || null,
                poster_path: seasonData.poster_path || null,
                air_date: seasonData.air_date || null,
                episodes: (seasonData.episodes || []).map(ep => ({
                    episode_number: ep.episode_number,
                    name: ep.name || `Episode ${ep.episode_number}`,
                    overview: ep.overview || null,
                    still_path: ep.still_path || null,
                    air_date: ep.air_date || null,
                    runtime: ep.runtime || null,
                    vote_average: ep.vote_average || 0,
                    vote_count: ep.vote_count || 0,
                }))
            };

            const token = localStorage.getItem('adminAccessToken');
            const saveResponse = await fetch(`${API_BASE_URL}/admin/tvshows/${tvshowId}/seasons`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    seasons: [formattedSeason]
                })
            });

            const saveResult = await saveResponse.json();
            
            if (saveResult.success) {
                alert(`Successfully added Season ${seasonNumber} with ${formattedSeason.episodes.length} episodes!`);
                await loadTVShow();
            } else {
                alert('Error saving season: ' + (saveResult.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error fetching season from TMDB:', error);
            alert('Error fetching season from TMDB: ' + error.message);
        }
    };

    window.fetchEpisodeFromTMDB = async function(seasonNumber, episodeNumber) {
        if (!tvShow || !tvShow.tmdb_id) {
            alert('TV show must have a TMDB ID.');
            return;
        }

        try {
            const episodeResponse = await fetch(`${TMDB_BASE_URL}/tv/${tvShow.tmdb_id}/season/${seasonNumber}/episode/${episodeNumber}`, {
                headers: {
                    'Authorization': `Bearer ${TMDB_ACCESS_TOKEN}`,
                    'accept': 'application/json'
                }
            });

            if (!episodeResponse.ok) {
                throw new Error(`TMDB API error: ${episodeResponse.status}`);
            }

            const episodeData = await episodeResponse.json();
            const seasonResponse = await fetch(`${TMDB_BASE_URL}/tv/${tvShow.tmdb_id}/season/${seasonNumber}`, {
                headers: {
                    'Authorization': `Bearer ${TMDB_ACCESS_TOKEN}`,
                    'accept': 'application/json'
                }
            });
            const seasonData = await seasonResponse.json();

            const formattedSeason = {
                season_number: seasonData.season_number,
                name: seasonData.name || `Season ${seasonData.season_number}`,
                overview: seasonData.overview || null,
                poster_path: seasonData.poster_path || null,
                air_date: seasonData.air_date || null,
                episodes: [{
                    episode_number: episodeData.episode_number,
                    name: episodeData.name || `Episode ${episodeData.episode_number}`,
                    overview: episodeData.overview || null,
                    still_path: episodeData.still_path || null,
                    air_date: episodeData.air_date || null,
                    runtime: episodeData.runtime || null,
                    vote_average: episodeData.vote_average || 0,
                    vote_count: episodeData.vote_count || 0,
                }]
            };

            const token = localStorage.getItem('adminAccessToken');
            const saveResponse = await fetch(`${API_BASE_URL}/admin/tvshows/${tvshowId}/seasons`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    seasons: [formattedSeason]
                })
            });

            const saveResult = await saveResponse.json();
            
            if (saveResult.success) {
                alert(`Successfully added Season ${seasonNumber}, Episode ${episodeNumber}!`);
                await loadTVShow();
            } else {
                alert('Error saving episode: ' + (saveResult.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error fetching episode from TMDB:', error);
            alert('Error fetching episode from TMDB: ' + error.message);
        }
    };

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        loadTVShow();
    });
</script>
@endpush
@endsection

