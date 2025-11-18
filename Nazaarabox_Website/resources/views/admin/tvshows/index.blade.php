@extends('layouts.admin')

@section('title', 'TV Shows Management - Admin Panel')

@push('styles')
<style>
    .stat-card {
        background-color: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 8px;
    }
    .tvshow-card {
        background-color: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 6px;
        transition: all 0.2s ease;
        padding: 8px;
    }
    .tvshow-card:hover {
        border-color: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
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
                    <h1 style="font-size: 20px; font-weight: bold; color: white; margin: 0;">TV Shows Management</h1>
                </div>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <button onclick="window.location.href='/admin/tvshows/create'" class="flex items-center gap-2 bg-primary-600 hover:bg-red-700 text-white px-4 py-2 rounded transition-colors text-sm">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Add TV Show</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main style="max-width: 1280px; margin: 0 auto; padding: 32px 16px;">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Total TV Shows</p>
                <p style="font-size: 24px; font-weight: bold; color: white;" id="stats-total">0</p>
            </div>
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Active</p>
                <p style="font-size: 24px; font-weight: bold; color: #4ade80;" id="stats-active">0</p>
            </div>
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Inactive</p>
                <p style="font-size: 24px; font-weight: bold; color: #f87171;" id="stats-inactive">0</p>
            </div>
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Featured</p>
                <p style="font-size: 24px; font-weight: bold; color: #fbbf24;" id="stats-featured">0</p>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="stat-card p-6 mb-6">
            <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: center;">
                <div style="flex: 1; min-width: 200px;">
                    <input 
                        type="text" 
                        id="search-input" 
                        placeholder="Search TV shows..." 
                        class="w-full bg-dark-700 text-white border border-dark-600 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-600"
                        style="background-color: #2a2a2a; border-color: #3a3a3a; color: white; padding: 8px 16px; border-radius: 6px;"
                    >
                </div>
                <select 
                    id="status-filter" 
                    class="bg-dark-700 text-white border border-dark-600 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-600"
                    style="background-color: #2a2a2a; border-color: #3a3a3a; color: white; padding: 8px 16px; border-radius: 6px;"
                >
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="pending">Pending</option>
                </select>
                <select 
                    id="featured-filter" 
                    class="bg-dark-700 text-white border border-dark-600 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-600"
                    style="background-color: #2a2a2a; border-color: #3a3a3a; color: white; padding: 8px 16px; border-radius: 6px;"
                >
                    <option value="">All</option>
                    <option value="1">Featured</option>
                    <option value="0">Not Featured</option>
                </select>
                <button 
                    onclick="refreshTVShows()" 
                    class="flex items-center gap-2 bg-transparent border border-gray-600 text-white px-4 py-2 rounded hover:bg-dark-700 transition-colors"
                >
                    <svg id="refresh-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 0020 13a8.001 8.001 0 00-8 8c-2.1 0-4.06-.9-5.5-2.4L4 17"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    <span>Refresh</span>
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loading-state" style="display: flex; justify-content: center; align-items: center; min-height: 400px;">
            <div class="spinner"></div>
        </div>

        <!-- TV Shows Grid -->
        <div id="tvshows-container" style="display: none;">
            <div id="tvshows-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4"></div>
            
            <!-- Pagination -->
            <div id="pagination" class="mt-8 flex justify-center items-center gap-4" style="display: none;"></div>
        </div>

        <!-- Empty State -->
        <div id="empty-state" style="display: none; text-align: center; padding: 64px 16px;">
            <p style="color: #9ca3af; font-size: 18px; margin-bottom: 16px;">No TV shows found</p>
            <button onclick="window.location.href='/admin/tvshows/create'" class="bg-primary-600 hover:bg-red-700 text-white px-6 py-2 rounded transition-colors">
                Add Your First TV Show
            </button>
        </div>
    </main>
</div>

@push('scripts')
<script>
    let currentPage = 1;
    let totalPages = 1;
    let isLoading = false;

    async function fetchTVShows(page = 1) {
        if (isLoading) return;
        isLoading = true;

        const token = localStorage.getItem('adminAccessToken');
        if (!token) {
            window.location.href = '/admin/login';
            return;
        }

        const search = document.getElementById('search-input').value;
        const status = document.getElementById('status-filter').value;
        const featured = document.getElementById('featured-filter').value;

        let url = `${API_BASE_URL}/admin/tvshows?page=${page}`;
        if (search) url += `&search=${encodeURIComponent(search)}`;
        if (status) url += `&status=${encodeURIComponent(status)}`;
        if (featured !== '') url += `&featured=${encodeURIComponent(featured)}`;

        try {
            document.getElementById('loading-state').style.display = 'flex';
            document.getElementById('tvshows-container').style.display = 'none';
            document.getElementById('empty-state').style.display = 'none';

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

            const data = await response.json();

            if (data.success) {
                const tvShows = data.data.tvShows || data.data.tvshows || [];
                const pagination = data.data.pagination || {};

                currentPage = pagination.current_page || pagination.currentPage || 1;
                totalPages = pagination.total_pages || pagination.totalPages || 1;

                updateStats(data.data.stats || {});
                renderTVShows(tvShows);
                renderPagination(pagination);

                if (tvShows.length === 0) {
                    document.getElementById('empty-state').style.display = 'block';
                } else {
                    document.getElementById('tvshows-container').style.display = 'block';
                }
            }
        } catch (error) {
            console.error('Error fetching TV shows:', error);
        } finally {
            document.getElementById('loading-state').style.display = 'none';
            isLoading = false;
        }
    }

    function updateStats(stats) {
        document.getElementById('stats-total').textContent = stats.total || 0;
        document.getElementById('stats-active').textContent = stats.active || 0;
        document.getElementById('stats-inactive').textContent = stats.inactive || 0;
        document.getElementById('stats-featured').textContent = stats.featured || 0;
    }

    function renderTVShows(tvShows) {
        const grid = document.getElementById('tvshows-grid');
        grid.innerHTML = '';

        tvShows.forEach(tvShow => {
            const posterPath = tvShow.poster_path 
                ? (tvShow.poster_path.startsWith('http') ? tvShow.poster_path : `https://image.tmdb.org/t/p/w300${tvShow.poster_path}`)
                : '/images/placeholder.svg';

            const card = document.createElement('div');
            card.className = 'tvshow-card';
            card.style.position = 'relative';
            
            card.innerHTML = `
                <div style="position: relative; margin-bottom: 8px; cursor: pointer;" onclick="window.location.href='/admin/tvshows/${tvShow.id}'">
                    <img 
                        src="${posterPath}" 
                        alt="${tvShow.name || tvShow.title || 'Untitled'}" 
                        style="width: 100%; aspect-ratio: 2/3; object-fit: cover; border-radius: 4px;"
                        onerror="this.src='/images/placeholder.svg'"
                    >
                    ${tvShow.is_featured ? `
                        <div style="position: absolute; top: 4px; right: 4px; background-color: #fbbf24; color: #000; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: 600; line-height: 1.2;">
                            ⭐
                        </div>
                    ` : ''}
                    <div style="position: absolute; bottom: 4px; left: 4px; background-color: rgba(0,0,0,0.8); color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: 500; line-height: 1.2;">
                        ${(tvShow.status || 'N/A').substring(0, 3).toUpperCase()}
                    </div>
                </div>
                <div style="padding: 0 4px;">
                    <h3 style="color: white; font-weight: 600; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 13px; line-height: 1.3; cursor: pointer;" onclick="window.location.href='/admin/tvshows/${tvShow.id}'">
                        ${tvShow.name || tvShow.title || 'Untitled'}
                    </h3>
                    <p style="color: #9ca3af; font-size: 11px; margin: 0 0 4px 0;">
                        ${tvShow.first_air_date ? new Date(tvShow.first_air_date).getFullYear() : 'N/A'}
                    </p>
                    <div style="display: flex; gap: 6px; font-size: 10px; color: #9ca3af; line-height: 1.2; margin-bottom: 8px;">
                        <span>⭐ ${(tvShow.vote_average || 0).toFixed(1)}</span>
                        <span>👁 ${tvShow.view_count || 0}</span>
                    </div>
                    <button 
                        onclick="event.stopPropagation(); deleteTVShow(${tvShow.id}, '${(tvShow.name || tvShow.title || 'Untitled').replace(/'/g, "\\'")}')" 
                        style="width: 100%; background-color: #dc2626; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; border: none; font-weight: 500; transition: background-color 0.2s;"
                        onmouseover="this.style.backgroundColor='#b91c1c'"
                        onmouseout="this.style.backgroundColor='#dc2626'"
                    >
                        🗑️ Delete
                    </button>
                </div>
            `;
            
            grid.appendChild(card);
        });
    }

    function renderPagination(pagination) {
        const paginationEl = document.getElementById('pagination');
        
        const totalPages = pagination.total_pages || pagination.totalPages || 1;
        const currentPage = pagination.current_page || pagination.currentPage || 1;
        const hasNext = pagination.has_next || pagination.hasNextPage || false;
        const hasPrev = pagination.has_prev || pagination.hasPrevPage || false;

        if (totalPages <= 1) {
            paginationEl.style.display = 'none';
            return;
        }

        paginationEl.style.display = 'flex';
        
        let html = '';
        
        // Previous button
        if (hasPrev) {
            html += `
                <button 
                    onclick="fetchTVShows(${currentPage - 1})" 
                    class="bg-dark-700 hover:bg-dark-600 text-white px-4 py-2 rounded transition-colors"
                    style="background-color: #2a2a2a; padding: 8px 16px; border-radius: 6px; border: 1px solid #3a3a3a;"
                >
                    Previous
                </button>
            `;
        }

        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            html += `<button onclick="fetchTVShows(1)" class="bg-dark-700 hover:bg-dark-600 text-white px-4 py-2 rounded transition-colors" style="background-color: #2a2a2a; padding: 8px 16px; border-radius: 6px; border: 1px solid #3a3a3a;">1</button>`;
            if (startPage > 2) html += `<span style="color: #9ca3af; padding: 0 8px;">...</span>`;
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `
                <button 
                    onclick="fetchTVShows(${i})" 
                    class="${i === currentPage ? 'bg-primary-600' : 'bg-dark-700 hover:bg-dark-600'} text-white px-4 py-2 rounded transition-colors"
                    style="padding: 8px 16px; border-radius: 6px; border: 1px solid #3a3a3a;"
                >
                    ${i}
                </button>
            `;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += `<span style="color: #9ca3af; padding: 0 8px;">...</span>`;
            html += `<button onclick="fetchTVShows(${totalPages})" class="bg-dark-700 hover:bg-dark-600 text-white px-4 py-2 rounded transition-colors" style="background-color: #2a2a2a; padding: 8px 16px; border-radius: 6px; border: 1px solid #3a3a3a;">${totalPages}</button>`;
        }

        // Next button
        if (hasNext) {
            html += `
                <button 
                    onclick="fetchTVShows(${currentPage + 1})" 
                    class="bg-dark-700 hover:bg-dark-600 text-white px-4 py-2 rounded transition-colors"
                    style="background-color: #2a2a2a; padding: 8px 16px; border-radius: 6px; border: 1px solid #3a3a3a;"
                >
                    Next
                </button>
            `;
        }

        paginationEl.innerHTML = html;
    }

    function refreshTVShows() {
        fetchTVShows(1);
    }

    async function deleteTVShow(tvshowId, tvshowName) {
        if (!confirm(`Are you sure you want to delete "${tvshowName}"?\n\nThis action cannot be undone and will delete all associated seasons and episodes.`)) {
            return;
        }

        const token = localStorage.getItem('adminAccessToken');
        if (!token) {
            window.location.href = '/admin/login';
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}/admin/tvshows/${tvshowId}`, {
                method: 'DELETE',
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
                alert(`TV show "${tvshowName}" deleted successfully!`);
                // Refresh the list
                fetchTVShows(currentPage);
            } else {
                alert('Error deleting TV show: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error deleting TV show:', error);
            alert('Error deleting TV show: ' + error.message);
        }
    }

    // Event listeners
    document.getElementById('search-input').addEventListener('input', debounce(() => {
        fetchTVShows(1);
    }, 500));

    document.getElementById('status-filter').addEventListener('change', () => {
        fetchTVShows(1);
    });

    document.getElementById('featured-filter').addEventListener('change', () => {
        fetchTVShows(1);
    });

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        fetchTVShows(1);
    });
</script>
@endpush
@endsection

