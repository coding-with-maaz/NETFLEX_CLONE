@extends('layouts.admin')

@section('title', 'Ads Management - Admin Panel')

@push('styles')
<style>
    .stat-card {
        background-color: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 8px;
    }
    .content-item {
        background-color: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        transition: all 0.2s ease;
    }
    .content-item:hover {
        border-color: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }
    .badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .badge-ad {
        background-color: rgba(220, 38, 38, 0.2);
        color: #f87171;
        border: 1px solid rgba(220, 38, 38, 0.3);
    }
    .badge-active {
        background-color: rgba(34, 197, 94, 0.2);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }
    .badge-inactive {
        background-color: rgba(239, 68, 68, 0.2);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
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
                    <a href="/admin/dashboard" style="color: #9ca3af; text-decoration: none;">← Back to Dashboard</a>
                    <h1 style="font-size: 20px; font-weight: bold; color: white; margin: 0;">Ads Management</h1>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main style="max-width: 1280px; margin: 0 auto; padding: 32px 16px;">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Movies with Ads</p>
                <p style="font-size: 24px; font-weight: bold; color: white;" id="stats-movies">0</p>
            </div>
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Episodes with Ads</p>
                <p style="font-size: 24px; font-weight: bold; color: white;" id="stats-episodes">0</p>
            </div>
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Total Ad Embeds</p>
                <p style="font-size: 24px; font-weight: bold; color: #dc2626;" id="stats-total-embeds">0</p>
            </div>
        </div>

        <!-- Tabs -->
        <div style="background-color: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
            <div style="display: flex; gap: 12px; border-bottom: 1px solid #2a2a2a; margin-bottom: 20px;">
                <button id="tab-movies" onclick="switchTab('movies')" style="padding: 12px 24px; background-color: #dc2626; color: white; border: none; border-radius: 6px 6px 0 0; cursor: pointer; font-weight: 500;">
                    Movies
                </button>
                <button id="tab-episodes" onclick="switchTab('episodes')" style="padding: 12px 24px; background-color: transparent; color: #9ca3af; border: none; border-radius: 6px 6px 0 0; cursor: pointer; font-weight: 500;">
                    Episodes
                </button>
            </div>

            <!-- Movies Tab -->
            <div id="content-movies" style="display: block;">
                <div id="movies-loading" style="text-align: center; padding: 40px; color: #9ca3af;">
                    Loading movies...
                </div>
                <div id="movies-list" style="display: none;"></div>
            </div>

            <!-- Episodes Tab -->
            <div id="content-episodes" style="display: none;">
                <div id="episodes-loading" style="text-align: center; padding: 40px; color: #9ca3af;">
                    Loading episodes...
                </div>
                <div id="episodes-list" style="display: none;"></div>
            </div>
        </div>
    </main>
</div>

@push('scripts')
<script>
    // API_BASE_URL and API_KEY are already declared in the admin layout
    let currentTab = 'movies';
    let moviesData = [];
    let episodesData = [];

    async function fetchMoviesWithAds() {
        try {
            document.getElementById('movies-loading').style.display = 'block';
            document.getElementById('movies-list').style.display = 'none';
            
            const token = localStorage.getItem('adminAccessToken');
            const response = await fetch(`${API_BASE_URL}/admin/ads/movies`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'X-API-Key': API_KEY,
                    'Content-Type': 'application/json'
                }
            });

            if (response.status === 401) {
                window.location.href = '/admin/login';
                return;
            }

            const data = await response.json();
            if (data.success) {
                moviesData = data.data || [];
                renderMovies();
                updateStats();
            } else {
                document.getElementById('movies-loading').innerHTML = `<p style="color: #ef4444;">Error: ${data.message || 'Failed to load movies'}</p>`;
            }
        } catch (error) {
            console.error('Error fetching movies:', error);
            document.getElementById('movies-loading').innerHTML = `<p style="color: #ef4444;">Error loading movies</p>`;
        }
    }

    async function fetchEpisodesWithAds() {
        try {
            document.getElementById('episodes-loading').style.display = 'block';
            document.getElementById('episodes-list').style.display = 'none';
            
            const token = localStorage.getItem('adminAccessToken');
            const response = await fetch(`${API_BASE_URL}/admin/ads/episodes`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'X-API-Key': API_KEY,
                    'Content-Type': 'application/json'
                }
            });

            if (response.status === 401) {
                window.location.href = '/admin/login';
                return;
            }

            const data = await response.json();
            if (data.success) {
                episodesData = data.data || [];
                renderEpisodes();
                updateStats();
            } else {
                document.getElementById('episodes-loading').innerHTML = `<p style="color: #ef4444;">Error: ${data.message || 'Failed to load episodes'}</p>`;
            }
        } catch (error) {
            console.error('Error fetching episodes:', error);
            document.getElementById('episodes-loading').innerHTML = `<p style="color: #ef4444;">Error loading episodes</p>`;
        }
    }

    function renderMovies() {
        const container = document.getElementById('movies-list');
        
        if (moviesData.length === 0) {
            container.innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 40px;">No movies with ads found</p>';
            document.getElementById('movies-loading').style.display = 'none';
            container.style.display = 'block';
            return;
        }

        const html = moviesData.map(movie => {
            const posterUrl = movie.poster_path 
                ? (movie.poster_path.startsWith('http') ? movie.poster_path : `https://image.tmdb.org/t/p/w154${movie.poster_path}`)
                : '/images/placeholder.svg';
            
            const embedsHtml = movie.embeds.map(embed => `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background-color: #2a2a2a; border-radius: 6px; margin-bottom: 8px;">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                            <span style="color: white; font-weight: 500;">${embed.server_name || 'Server'}</span>
                            ${embed.is_active ? '<span class="badge badge-active">Active</span>' : '<span class="badge badge-inactive">Inactive</span>'}
                            <span class="badge badge-ad">Requires Ad</span>
                            <span style="color: #9ca3af; font-size: 12px;">Priority: ${embed.priority || 0}</span>
                        </div>
                    </div>
                    <button onclick="toggleMovieEmbedAd(${movie.id}, ${embed.id})" style="background-color: #dc2626; color: white; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; font-size: 12px; font-weight: 500;">
                        Remove Ad
                    </button>
                </div>
            `).join('');

            return `
                <div class="content-item">
                    <div style="display: flex; gap: 16px;">
                        <img src="${posterUrl}" alt="${movie.title}" style="width: 80px; height: 120px; object-fit: cover; border-radius: 6px;" onerror="this.src='/images/placeholder.svg'">
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                                <div>
                                    <h3 style="color: white; font-size: 18px; font-weight: 600; margin-bottom: 4px;">
                                        <a href="/admin/movies/${movie.id}" style="color: white; text-decoration: none;">${escapeHtml(movie.title)}</a>
                                    </h3>
                                    ${movie.release_date ? `<p style="color: #9ca3af; font-size: 14px;">${new Date(movie.release_date).getFullYear()}</p>` : ''}
                                    <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; ${getStatusBadgeStyle(movie.status)}">
                                        ${movie.status}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <p style="color: #9ca3af; font-size: 14px; margin-bottom: 8px; font-weight: 500;">Embeds with Ads (${movie.embeds.length}):</p>
                                ${embedsHtml}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = html;
        document.getElementById('movies-loading').style.display = 'none';
        container.style.display = 'block';
    }

    function renderEpisodes() {
        const container = document.getElementById('episodes-list');
        
        if (episodesData.length === 0) {
            container.innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 40px;">No episodes with ads found</p>';
            document.getElementById('episodes-loading').style.display = 'none';
            container.style.display = 'block';
            return;
        }

        const html = episodesData.map(episode => {
            const stillUrl = episode.still_path 
                ? (episode.still_path.startsWith('http') ? episode.still_path : `https://image.tmdb.org/t/p/w154${episode.still_path}`)
                : (episode.tv_show && episode.tv_show.poster_path 
                    ? (episode.tv_show.poster_path.startsWith('http') ? episode.tv_show.poster_path : `https://image.tmdb.org/t/p/w154${episode.tv_show.poster_path}`)
                    : '/images/placeholder.svg');
            
            const embedsHtml = episode.embeds.map(embed => `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background-color: #2a2a2a; border-radius: 6px; margin-bottom: 8px;">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                            <span style="color: white; font-weight: 500;">${embed.server_name || 'Server'}</span>
                            ${embed.is_active ? '<span class="badge badge-active">Active</span>' : '<span class="badge badge-inactive">Inactive</span>'}
                            <span class="badge badge-ad">Requires Ad</span>
                            <span style="color: #9ca3af; font-size: 12px;">Priority: ${embed.priority || 0}</span>
                        </div>
                    </div>
                    <button onclick="toggleEpisodeEmbedAd(${episode.id}, ${embed.id})" style="background-color: #dc2626; color: white; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; font-size: 12px; font-weight: 500;">
                        Remove Ad
                    </button>
                </div>
            `).join('');

            return `
                <div class="content-item">
                    <div style="display: flex; gap: 16px;">
                        <img src="${stillUrl}" alt="${episode.name}" style="width: 80px; height: 60px; object-fit: cover; border-radius: 6px;" onerror="this.src='/images/placeholder.svg'">
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                                <div>
                                    <h3 style="color: white; font-size: 18px; font-weight: 600; margin-bottom: 4px;">
                                        ${episode.tv_show ? `<a href="/admin/tvshows/${episode.tv_show.id}" style="color: white; text-decoration: none;">${escapeHtml(episode.tv_show.name)}</a> - ` : ''}
                                        <span style="color: #9ca3af;">S${episode.season_number}E${episode.episode_number}</span>
                                    </h3>
                                    <p style="color: white; font-size: 16px; margin-bottom: 4px;">${escapeHtml(episode.name || `Episode ${episode.episode_number}`)}</p>
                                    ${episode.air_date ? `<p style="color: #9ca3af; font-size: 14px;">${new Date(episode.air_date).toLocaleDateString()}</p>` : ''}
                                </div>
                            </div>
                            <div>
                                <p style="color: #9ca3af; font-size: 14px; margin-bottom: 8px; font-weight: 500;">Embeds with Ads (${episode.embeds.length}):</p>
                                ${embedsHtml}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = html;
        document.getElementById('episodes-loading').style.display = 'none';
        container.style.display = 'block';
    }

    function switchTab(tab) {
        currentTab = tab;
        
        // Update tab buttons
        if (tab === 'movies') {
            document.getElementById('tab-movies').style.backgroundColor = '#dc2626';
            document.getElementById('tab-movies').style.color = 'white';
            document.getElementById('tab-episodes').style.backgroundColor = 'transparent';
            document.getElementById('tab-episodes').style.color = '#9ca3af';
            document.getElementById('content-movies').style.display = 'block';
            document.getElementById('content-episodes').style.display = 'none';
            
            if (moviesData.length === 0) {
                fetchMoviesWithAds();
            }
        } else {
            document.getElementById('tab-episodes').style.backgroundColor = '#dc2626';
            document.getElementById('tab-episodes').style.color = 'white';
            document.getElementById('tab-movies').style.backgroundColor = 'transparent';
            document.getElementById('tab-movies').style.color = '#9ca3af';
            document.getElementById('content-episodes').style.display = 'block';
            document.getElementById('content-movies').style.display = 'none';
            
            if (episodesData.length === 0) {
                fetchEpisodesWithAds();
            }
        }
    }

    async function toggleMovieEmbedAd(movieId, embedId) {
        if (!confirm('Are you sure you want to remove the ad requirement for this embed?')) {
            return;
        }

        try {
            const token = localStorage.getItem('adminAccessToken');
            const response = await fetch(`${API_BASE_URL}/admin/ads/movies/${movieId}/embeds/${embedId}/toggle`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'X-API-Key': API_KEY,
                    'Content-Type': 'application/json'
                }
            });

            if (response.status === 401) {
                window.location.href = '/admin/login';
                return;
            }

            const data = await response.json();
            if (data.success) {
                // Reload movies
                await fetchMoviesWithAds();
            } else {
                alert('Error: ' + (data.message || 'Failed to toggle ad requirement'));
            }
        } catch (error) {
            console.error('Error toggling ad:', error);
            alert('Error toggling ad requirement');
        }
    }

    async function toggleEpisodeEmbedAd(episodeId, embedId) {
        if (!confirm('Are you sure you want to remove the ad requirement for this embed?')) {
            return;
        }

        try {
            const token = localStorage.getItem('adminAccessToken');
            const response = await fetch(`${API_BASE_URL}/admin/ads/episodes/${episodeId}/embeds/${embedId}/toggle`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'X-API-Key': API_KEY,
                    'Content-Type': 'application/json'
                }
            });

            if (response.status === 401) {
                window.location.href = '/admin/login';
                return;
            }

            const data = await response.json();
            if (data.success) {
                // Reload episodes
                await fetchEpisodesWithAds();
            } else {
                alert('Error: ' + (data.message || 'Failed to toggle ad requirement'));
            }
        } catch (error) {
            console.error('Error toggling ad:', error);
            alert('Error toggling ad requirement');
        }
    }

    function updateStats() {
        const totalMovieEmbeds = moviesData.reduce((sum, movie) => sum + movie.embeds.length, 0);
        const totalEpisodeEmbeds = episodesData.reduce((sum, episode) => sum + episode.embeds.length, 0);
        
        document.getElementById('stats-movies').textContent = moviesData.length;
        document.getElementById('stats-episodes').textContent = episodesData.length;
        document.getElementById('stats-total-embeds').textContent = totalMovieEmbeds + totalEpisodeEmbeds;
    }

    function getStatusBadgeStyle(status) {
        const styles = {
            active: 'background-color: #166534; color: #86efac;',
            inactive: 'background-color: #991b1b; color: #fca5a5;',
            pending: 'background-color: #854d0e; color: #fde047;'
        };
        return styles[status] || 'background-color: #374151; color: #d1d5db;';
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', async () => {
        await fetchMoviesWithAds();
    });
</script>
@endpush
@endsection

