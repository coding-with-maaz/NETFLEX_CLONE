@extends('layouts.admin')

@section('title', 'Featured Content Management - Admin Panel')

@push('styles')
<style>
    .stat-card {
        background-color: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 8px;
    }
    .featured-item {
        background-color: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        transition: all 0.2s ease;
        cursor: move;
        position: relative;
    }
    .featured-item:hover {
        border-color: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }
    .featured-item.selected {
        border-color: #dc2626;
        background-color: rgba(220, 38, 38, 0.1);
    }
    .featured-item.dragging {
        opacity: 0.5;
        transform: scale(0.95);
    }
    .featured-item.drag-over {
        border-top: 3px solid #dc2626;
        margin-top: 12px;
    }
    .drag-handle {
        cursor: grab;
        color: #9ca3af;
        padding: 8px;
        display: inline-flex;
        align-items: center;
        position: absolute;
        left: 8px;
        top: 50%;
        transform: translateY(-50%);
    }
    .drag-handle:active {
        cursor: grabbing;
    }
    .drag-handle:hover {
        color: #dc2626;
    }
    .badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .badge-movie {
        background-color: rgba(59, 130, 246, 0.2);
        color: #60a5fa;
        border: 1px solid rgba(59, 130, 246, 0.3);
    }
    .badge-tvshow {
        background-color: rgba(168, 85, 247, 0.2);
        color: #a78bfa;
        border: 1px solid rgba(168, 85, 247, 0.3);
    }
    .badge-featured {
        background-color: rgba(251, 191, 36, 0.2);
        color: #fbbf24;
        border: 1px solid rgba(251, 191, 36, 0.3);
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
                    <h1 style="font-size: 20px; font-weight: bold; color: white; margin: 0;">Featured Content Management</h1>
                </div>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <button onclick="saveOrder(); return false;" id="save-order-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition-colors text-sm" style="display: none;">
                        Save Order
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
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Total Featured</p>
                <p style="font-size: 24px; font-weight: bold; color: white;" id="stats-total">0</p>
            </div>
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Featured Movies</p>
                <p style="font-size: 24px; font-weight: bold; color: #60a5fa;" id="stats-movies">0</p>
            </div>
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Featured TV Shows</p>
                <p style="font-size: 24px; font-weight: bold; color: #a78bfa;" id="stats-tvshows">0</p>
            </div>
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Order Changed</p>
                <p style="font-size: 24px; font-weight: bold; color: #fbbf24;" id="stats-changed">No</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="stat-card p-6 mb-6">
            <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: center; margin-bottom: 16px;">
                <select 
                    id="type-filter" 
                    class="bg-dark-700 text-white border border-dark-600 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-600"
                    style="background-color: #2a2a2a; border-color: #3a3a3a; color: white; padding: 8px 16px; border-radius: 6px;"
                >
                    <option value="all">All Content</option>
                    <option value="movies">Movies Only</option>
                    <option value="tvshows">TV Shows Only</option>
                </select>
                <select 
                    id="featured-filter" 
                    class="bg-dark-700 text-white border border-dark-600 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-600"
                    style="background-color: #2a2a2a; border-color: #3a3a3a; color: white; padding: 8px 16px; border-radius: 6px;"
                >
                    <option value="all">All Items</option>
                    <option value="featured">Featured Only</option>
                    <option value="unfeatured">Unfeatured Only</option>
                </select>
                <input 
                    type="text" 
                    id="search-input" 
                    placeholder="Search content..." 
                    class="w-full bg-dark-700 text-white border border-dark-600 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-600"
                    style="background-color: #2a2a2a; border-color: #3a3a3a; color: white; padding: 8px 16px; border-radius: 6px; flex: 1; min-width: 200px;"
                >
                <button onclick="refreshFeatured(); return false;" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded transition-colors text-sm">
                    Refresh
                </button>
            </div>
            <!-- Bulk Actions -->
            <div id="bulk-actions" style="display: none; padding-top: 16px; border-top: 1px solid #2a2a2a;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <label style="display: flex; align-items: center; gap: 8px; color: #9ca3af; cursor: pointer;">
                            <input type="checkbox" id="select-all-checkbox" onchange="toggleSelectAll(this.checked)" 
                                   style="width: 18px; height: 18px; cursor: pointer; accent-color: #dc2626;">
                            <span style="font-weight: 500;">Select All (<span id="selected-count">0</span> selected)</span>
                        </label>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button onclick="bulkAddToFeatured(); return false;" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition-colors text-sm">
                            Add Selected to Featured
                        </button>
                        <button onclick="bulkRemoveFromFeatured(); return false;" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition-colors text-sm">
                            Remove Selected from Featured
                        </button>
                        <button onclick="clearSelection(); return false;" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded transition-colors text-sm">
                            Clear Selection
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loading-state" style="display: flex; justify-content: center; align-items: center; padding: 64px;">
            <div style="text-align: center;">
                <div style="border: 3px solid #2a2a2a; border-top-color: #dc2626; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 16px;"></div>
                <p style="color: #9ca3af;">Loading featured content...</p>
            </div>
        </div>

        <!-- Content List -->
        <div id="featured-content" style="display: none;">
            <div class="stat-card p-6 mb-4">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h2 style="font-size: 18px; font-weight: bold; color: white;" id="content-title">Featured Content</h2>
                    <p style="color: #9ca3af; font-size: 14px;" id="reorder-hint">
                        <svg style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                        </svg>
                        Drag and drop to reorder
                    </p>
                </div>
                <div id="featured-list" style="min-height: 200px;">
                    <!-- Items will be rendered here -->
                </div>
                <div id="empty-state" style="text-align: center; padding: 64px; color: #9ca3af; display: none;">
                    <svg style="width: 64px; height: 64px; margin: 0 auto 16px; opacity: 0.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    <p style="font-size: 16px; margin-bottom: 8px;" id="empty-message">No content found</p>
                    <p style="font-size: 14px; opacity: 0.7;" id="empty-submessage"></p>
                </div>
            </div>
        </div>
    </main>
</div>

@push('scripts')
<script>
    // API_BASE_URL is already declared in layouts/admin.blade.php
    let featuredItems = [];
    let allItems = [];
    let selectedItems = new Set();
    let originalOrder = [];
    let draggedElement = null;
    let draggedIndex = null;

    async function fetchFeatured(type = 'all', featuredFilter = 'all') {
        try {
            const token = localStorage.getItem('adminAccessToken');
            if (!token) {
                window.location.href = '/admin/login';
                return;
            }

            const loadingState = document.getElementById('loading-state');
            const featuredContent = document.getElementById('featured-content');
            
            if (loadingState) loadingState.style.display = 'flex';
            if (featuredContent) featuredContent.style.display = 'none';

            const response = await fetch(`${API_BASE_URL}/admin/featured?type=${type}&featured=${featuredFilter}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            }).catch(err => {
                console.error('Fetch error:', err);
                throw new Error('Network error: ' + err.message);
            });

            if (response.status === 401) {
                window.location.href = '/admin/login';
                return;
            }

            if (!response.ok) {
                const errorText = await response.text().catch(() => 'Unknown error');
                throw new Error(`Failed to fetch content: ${response.status} ${errorText}`);
            }

            const data = await response.json().catch(err => {
                console.error('JSON parse error:', err);
                throw new Error('Invalid response format');
            });
            
            if (data.success) {
                if (type === 'all') {
                    allItems = data.data.items || [];
                    featuredItems = data.data.featured || [];
                    updateStats(data.data.movies_count || 0, data.data.tvshows_count || 0, featuredItems.length);
                } else {
                    allItems = type === 'movies' ? (data.data.movies || []) : (data.data.tvshows || []);
                    featuredItems = allItems.filter(item => item.is_featured);
                    updateStats(
                        type === 'movies' ? featuredItems.length : 0,
                        type === 'tvshows' ? featuredItems.length : 0,
                        featuredItems.length
                    );
                }
                originalOrder = JSON.parse(JSON.stringify(featuredItems));
                renderFeatured();
                updateBulkActions();
            } else {
                throw new Error(data.message || 'Failed to fetch content');
            }
        } catch (error) {
            console.error('Error fetching content:', error);
            // Only show alert if it's not a navigation error
            if (!error.message.includes('login')) {
                alert('Failed to load content: ' + (error.message || 'Unknown error'));
            }
        } finally {
            const loadingState = document.getElementById('loading-state');
            const featuredContent = document.getElementById('featured-content');
            if (loadingState) loadingState.style.display = 'none';
            if (featuredContent) featuredContent.style.display = 'block';
        }
    }

    function updateStats(moviesCount, tvshowsCount, totalCount) {
        document.getElementById('stats-total').textContent = totalCount;
        document.getElementById('stats-movies').textContent = moviesCount;
        document.getElementById('stats-tvshows').textContent = tvshowsCount;
    }

    function renderFeatured() {
        const container = document.getElementById('featured-list');
        const emptyState = document.getElementById('empty-state');
        const featuredFilter = document.getElementById('featured-filter')?.value || 'all';
        const itemsToRender = featuredFilter === 'featured' ? featuredItems : allItems;

        // Update title and hint
        const title = document.getElementById('content-title');
        const reorderHint = document.getElementById('reorder-hint');
        if (featuredFilter === 'featured') {
            title.textContent = 'Featured Content';
            reorderHint.style.display = 'block';
        } else if (featuredFilter === 'unfeatured') {
            title.textContent = 'Unfeatured Content';
            reorderHint.style.display = 'none';
        } else {
            title.textContent = 'All Content';
            reorderHint.style.display = 'block';
        }

        if (itemsToRender.length === 0) {
            container.innerHTML = '';
            emptyState.style.display = 'block';
            const emptyMessage = document.getElementById('empty-message');
            const emptySubmessage = document.getElementById('empty-submessage');
            if (featuredFilter === 'featured') {
                emptyMessage.textContent = 'No featured content found';
                emptySubmessage.textContent = 'Mark movies or TV shows as featured to see them here';
            } else if (featuredFilter === 'unfeatured') {
                emptyMessage.textContent = 'No unfeatured content found';
                emptySubmessage.textContent = 'All items are currently featured';
            } else {
                emptyMessage.textContent = 'No content found';
                emptySubmessage.textContent = '';
            }
            return;
        }

        emptyState.style.display = 'none';
        
        // Show bulk actions
        document.getElementById('bulk-actions').style.display = 'block';
        
        container.innerHTML = itemsToRender.map((item, index) => {
            const posterUrl = item.poster_path 
                ? (item.poster_path.startsWith('http') 
                    ? item.poster_path 
                    : `https://image.tmdb.org/t/p/w200${item.poster_path}`)
                : '/images/placeholder.svg';
            
            const date = item.release_date || item.first_air_date || 'N/A';
            const year = date !== 'N/A' ? new Date(date).getFullYear() : 'N/A';
            const type = item.type === 'movie' ? 'Movie' : 'TV Show';
            const badgeClass = item.type === 'movie' ? 'badge-movie' : 'badge-tvshow';
            const itemKey = `${item.type}-${item.id}`;
            const isSelected = selectedItems.has(itemKey);
            const isDraggable = item.is_featured && featuredFilter !== 'unfeatured';
            const dragHandles = isDraggable ? `
                <div class="drag-handle" style="pointer-events: none;">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                    </svg>
                </div>` : '';
            const dragAttrs = isDraggable ? `
                draggable="true"
                ondragstart="handleDragStart(event, ${index})"
                ondragover="handleDragOver(event)"
                ondragleave="handleDragLeave(event)"
                ondrop="handleDrop(event, ${index})"
                ondragend="handleDragEnd(event)"` : '';

            return `
                <div class="featured-item ${isSelected ? 'selected' : ''}" 
                     ${dragAttrs}
                     data-index="${index}"
                     data-id="${item.id}"
                     data-type="${item.type}"
                     data-key="${itemKey}">
                    <div style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); z-index: 10;">
                        <input type="checkbox" 
                               class="item-checkbox" 
                               data-key="${itemKey}"
                               data-id="${item.id}"
                               data-type="${item.type}"
                               ${isSelected ? 'checked' : ''}
                               onchange="toggleItemSelection('${itemKey}')"
                               style="width: 18px; height: 18px; cursor: pointer; accent-color: #dc2626;">
                    </div>
                    ${dragHandles}
                    <div style="display: flex; gap: 16px; padding-left: ${isDraggable ? '40px' : '40px'};">
                        <div style="flex-shrink: 0;">
                            <img src="${posterUrl}" 
                                 alt="${item.title}" 
                                 style="width: 80px; height: 120px; object-fit: cover; border-radius: 6px; background-color: #2a2a2a;">
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                                <h3 style="font-size: 18px; font-weight: bold; color: white; margin: 0;">${escapeHtml(item.title)}</h3>
                                <span class="badge ${badgeClass}">${type}</span>
                                ${item.is_featured ? `<span class="badge badge-featured">Featured${item.featured_order ? ' - Order: ' + item.featured_order : ''}</span>` : '<span class="badge" style="background-color: rgba(107, 114, 128, 0.2); color: #9ca3af; border: 1px solid rgba(107, 114, 128, 0.3);">Unfeatured</span>'}
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 8px;">
                                <span style="color: #9ca3af; font-size: 14px;">Year: ${year}</span>
                                <span style="color: #9ca3af; font-size: 14px;">Rating: ${item.vote_average?.toFixed(1) || 'N/A'}</span>
                                <span style="color: #9ca3af; font-size: 14px;">Views: ${formatNumber(item.view_count || 0)}</span>
                                <span style="color: #9ca3af; font-size: 14px;">Status: ${item.status || 'N/A'}</span>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <a href="/admin/${item.type === 'movie' ? 'movies' : 'tvshows'}/${item.id}" 
                                   style="color: #60a5fa; text-decoration: none; font-size: 14px; padding: 4px 8px; border: 1px solid #3b82f6; border-radius: 4px; transition: all 0.2s;" 
                                   onmouseover="this.style.backgroundColor='rgba(59, 130, 246, 0.2)'" 
                                   onmouseout="this.style.backgroundColor='transparent'">
                                    View Details
                                </a>
                                ${item.is_featured ? `
                                <button onclick="toggleFeatured('${item.type}', ${item.id}, ${index})" 
                                        style="color: #f87171; background: transparent; border: 1px solid #f87171; border-radius: 4px; padding: 4px 8px; font-size: 14px; cursor: pointer; transition: all 0.2s;"
                                        onmouseover="this.style.backgroundColor='rgba(248, 113, 113, 0.2)'" 
                                        onmouseout="this.style.backgroundColor='transparent'">
                                    Remove from Featured
                                </button>` : `
                                <button onclick="toggleFeatured('${item.type}', ${item.id}, ${index})" 
                                        style="color: #4ade80; background: transparent; border: 1px solid #4ade80; border-radius: 4px; padding: 4px 8px; font-size: 14px; cursor: pointer; transition: all 0.2s;"
                                        onmouseover="this.style.backgroundColor='rgba(74, 222, 128, 0.2)'" 
                                        onmouseout="this.style.backgroundColor='transparent'">
                                    Add to Featured
                                </button>`}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        // Only check order if showing featured items
        if (featuredFilter === 'featured' || featuredFilter === 'all') {
            checkOrderChanged();
        }
    }
    
    function toggleItemSelection(itemKey) {
        if (selectedItems.has(itemKey)) {
            selectedItems.delete(itemKey);
        } else {
            selectedItems.add(itemKey);
        }
        updateBulkActions();
        // Update checkbox state in DOM
        const checkbox = document.querySelector(`.item-checkbox[data-key="${itemKey}"]`);
        if (checkbox) {
            checkbox.checked = selectedItems.has(itemKey);
        }
        // Update item visual state
        const item = document.querySelector(`.featured-item[data-key="${itemKey}"]`);
        if (item) {
            if (selectedItems.has(itemKey)) {
                item.classList.add('selected');
            } else {
                item.classList.remove('selected');
            }
        }
    }
    
    function toggleSelectAll(checked) {
        const featuredFilter = document.getElementById('featured-filter')?.value || 'all';
        const itemsToSelect = featuredFilter === 'featured' ? featuredItems : allItems;
        
        if (checked) {
            itemsToSelect.forEach(item => {
                const itemKey = `${item.type}-${item.id}`;
                selectedItems.add(itemKey);
            });
        } else {
            selectedItems.clear();
        }
        
        // Update all checkboxes
        document.querySelectorAll('.item-checkbox').forEach(checkbox => {
            const itemKey = checkbox.getAttribute('data-key');
            checkbox.checked = selectedItems.has(itemKey);
            const item = document.querySelector(`.featured-item[data-key="${itemKey}"]`);
            if (item) {
                if (selectedItems.has(itemKey)) {
                    item.classList.add('selected');
                } else {
                    item.classList.remove('selected');
                }
            }
        });
        
        updateBulkActions();
    }
    
    function clearSelection() {
        selectedItems.clear();
        document.querySelectorAll('.item-checkbox').forEach(checkbox => {
            checkbox.checked = false;
            const itemKey = checkbox.getAttribute('data-key');
            const item = document.querySelector(`.featured-item[data-key="${itemKey}"]`);
            if (item) {
                item.classList.remove('selected');
            }
        });
        document.getElementById('select-all-checkbox').checked = false;
        updateBulkActions();
    }
    
    function updateBulkActions() {
        const count = selectedItems.size;
        document.getElementById('selected-count').textContent = count;
        document.getElementById('select-all-checkbox').checked = count > 0 && count === (document.getElementById('featured-filter')?.value === 'featured' ? featuredItems.length : allItems.length);
    }
    
    async function bulkAddToFeatured() {
        const items = Array.from(selectedItems).map(key => {
            const [type, id] = key.split('-');
            return { type, id: parseInt(id) };
        }).filter(item => {
            const fullItem = allItems.find(i => i.id === item.id && i.type === item.type);
            return fullItem && !fullItem.is_featured;
        });
        
        if (items.length === 0) {
            alert('No unfeatured items selected.');
            return;
        }
        
        if (!confirm(`Add ${items.length} item(s) to featured?`)) {
            return;
        }
        
        await bulkToggleFeatured(items, 'add');
    }
    
    async function bulkRemoveFromFeatured() {
        const items = Array.from(selectedItems).map(key => {
            const [type, id] = key.split('-');
            return { type, id: parseInt(id) };
        }).filter(item => {
            const fullItem = allItems.find(i => i.id === item.id && i.type === item.type);
            return fullItem && fullItem.is_featured;
        });
        
        if (items.length === 0) {
            alert('No featured items selected.');
            return;
        }
        
        if (!confirm(`Remove ${items.length} item(s) from featured?`)) {
            return;
        }
        
        await bulkToggleFeatured(items, 'remove');
    }
    
    async function bulkToggleFeatured(items, action) {
        const token = localStorage.getItem('adminAccessToken');
        if (!token) {
            window.location.href = '/admin/login';
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}/admin/featured/bulk`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ items, action })
            });

            if (response.status === 401) {
                window.location.href = '/admin/login';
                return;
            }

            const data = await response.json();
            if (data.success) {
                alert(`Successfully ${action === 'add' ? 'added' : 'removed'} ${data.data.updated_count || items.length} item(s).`);
                selectedItems.clear();
                const type = document.getElementById('type-filter').value;
                const featuredFilter = document.getElementById('featured-filter').value;
                await fetchFeatured(type, featuredFilter);
            } else {
                alert('Failed to update items: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error updating items:', error);
            alert('Failed to update items. Please try again.');
        }
    }

    function checkOrderChanged() {
        const changed = JSON.stringify(featuredItems.map(i => ({id: i.id, type: i.type}))) !== 
                       JSON.stringify(originalOrder.map(i => ({id: i.id, type: i.type})));
        
        document.getElementById('stats-changed').textContent = changed ? 'Yes' : 'No';
        document.getElementById('save-order-btn').style.display = changed ? 'block' : 'none';
    }

    async function saveOrder() {
        const token = localStorage.getItem('adminAccessToken');
        if (!token) {
            window.location.href = '/admin/login';
            return;
        }

        const items = featuredItems.map((item, index) => ({
            id: item.id,
            type: item.type
        }));

        try {
            const btn = document.getElementById('save-order-btn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Saving...';

            const response = await fetch(`${API_BASE_URL}/admin/featured/order`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ items })
            });

            if (response.status === 401) {
                window.location.href = '/admin/login';
                return;
            }

            const data = await response.json();
            if (data.success) {
                originalOrder = JSON.parse(JSON.stringify(featuredItems));
                checkOrderChanged();
                alert('Featured order saved successfully!');
                const type = document.getElementById('type-filter').value;
                const featuredFilter = document.getElementById('featured-filter').value;
                await fetchFeatured(type, featuredFilter);
            } else {
                alert('Failed to save order: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error saving order:', error);
            alert('Failed to save order. Please try again.');
        } finally {
            const btn = document.getElementById('save-order-btn');
            btn.disabled = false;
            btn.innerHTML = 'Save Order';
        }
    }

    async function toggleFeatured(type, id, index) {
        const item = allItems.find(i => i.id === id && i.type === type);
        const isCurrentlyFeatured = item?.is_featured || false;
        const action = isCurrentlyFeatured ? 'remove from' : 'add to';
        
        if (!confirm(`Are you sure you want to ${action} featured this ${type}?`)) {
            return;
        }

        const token = localStorage.getItem('adminAccessToken');
        if (!token) {
            window.location.href = '/admin/login';
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}/admin/featured/${type}/${id}/toggle`, {
                method: 'PATCH',
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
                const currentType = document.getElementById('type-filter').value;
                const currentFeaturedFilter = document.getElementById('featured-filter').value;
                selectedItems.clear();
                await fetchFeatured(currentType, currentFeaturedFilter);
            } else {
                alert('Failed to update featured status: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error toggling featured:', error);
            alert('Failed to update featured status. Please try again.');
        }
    }

    function handleDragStart(e, index) {
        // Only allow dragging featured items
        const itemId = parseInt(e.currentTarget.getAttribute('data-id'));
        const itemType = e.currentTarget.getAttribute('data-type');
        const item = featuredItems.find(i => i.id === itemId && i.type === itemType);
        
        if (!item || !item.is_featured) {
            e.preventDefault();
            return;
        }
        
        draggedElement = e.currentTarget;
        draggedIndex = featuredItems.findIndex(i => i.id === itemId && i.type === itemType);
        e.currentTarget.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
    }

    function handleDragOver(e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
        e.dataTransfer.dropEffect = 'move';
        
        const item = e.currentTarget.closest('.featured-item');
        if (item && item !== draggedElement) {
            // Only allow drop on featured items
            const itemId = parseInt(item.getAttribute('data-id'));
            const itemType = item.getAttribute('data-type');
            const dropItem = featuredItems.find(i => i.id === itemId && i.type === itemType);
            if (dropItem && dropItem.is_featured) {
                item.classList.add('drag-over');
            }
        }
        return false;
    }

    function handleDragLeave(e) {
        const item = e.currentTarget.closest('.featured-item');
        if (item) {
            item.classList.remove('drag-over');
        }
    }

    function handleDrop(e, dropIndex) {
        if (e.stopPropagation) {
            e.stopPropagation();
        }

        const dropItemId = parseInt(e.currentTarget.getAttribute('data-id'));
        const dropItemType = e.currentTarget.getAttribute('data-type');
        const dropItemIndex = featuredItems.findIndex(i => i.id === dropItemId && i.type === dropItemType);

        if (draggedIndex !== null && draggedIndex !== dropItemIndex && dropItemIndex >= 0) {
            const draggedItem = featuredItems[draggedIndex];
            featuredItems.splice(draggedIndex, 1);
            featuredItems.splice(dropItemIndex, 0, draggedItem);
            renderFeatured();
        }

        const item = e.currentTarget.closest('.featured-item');
        if (item) {
            item.classList.remove('drag-over');
        }

        return false;
    }

    function handleDragEnd(e) {
        e.currentTarget.classList.remove('dragging');
        draggedElement = null;
        draggedIndex = null;
        
        // Remove all drag-over classes
        document.querySelectorAll('.featured-item').forEach(item => {
            item.classList.remove('drag-over');
        });
    }

    function refreshFeatured() {
        const type = document.getElementById('type-filter').value;
        const featuredFilter = document.getElementById('featured-filter').value;
        selectedItems.clear();
        fetchFeatured(type, featuredFilter);
    }

    function formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toString();
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize event listeners when DOM is ready
    function initializeEventListeners() {
        try {
            const typeFilter = document.getElementById('type-filter');
            const featuredFilter = document.getElementById('featured-filter');
            const searchInput = document.getElementById('search-input');
            
            if (typeFilter) {
                typeFilter.addEventListener('change', function() {
                    try {
                        const featuredFilterValue = document.getElementById('featured-filter')?.value || 'all';
                        fetchFeatured(this.value, featuredFilterValue).catch(err => {
                            console.error('Error in type filter change:', err);
                        });
                    } catch (err) {
                        console.error('Error handling type filter change:', err);
                    }
                });
            }
            
            if (featuredFilter) {
                featuredFilter.addEventListener('change', function() {
                    try {
                        const type = document.getElementById('type-filter')?.value || 'all';
                        selectedItems.clear();
                        fetchFeatured(type, this.value).catch(err => {
                            console.error('Error in featured filter change:', err);
                        });
                    } catch (err) {
                        console.error('Error handling featured filter change:', err);
                    }
                });
            }
            
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    try {
                        const searchTerm = this.value.toLowerCase();
                        const items = document.querySelectorAll('.featured-item');
                        items.forEach(item => {
                            const titleEl = item.querySelector('h3');
                            if (titleEl) {
                                const title = titleEl.textContent.toLowerCase();
                                if (title.includes(searchTerm)) {
                                    item.style.display = '';
                                } else {
                                    item.style.display = 'none';
                                }
                            }
                        });
                    } catch (err) {
                        console.error('Error in search input:', err);
                    }
                });
            }
        } catch (err) {
            console.error('Error initializing event listeners:', err);
        }
    }
    
    // Global error handler for unhandled promise rejections
    // This helps catch errors that might be caused by browser extensions
    window.addEventListener('unhandledrejection', function(event) {
        // Ignore browser extension errors (they often show this message)
        if (event.reason && typeof event.reason === 'string' && 
            (event.reason.includes('message channel closed') || 
             event.reason.includes('Extension context invalidated'))) {
            // Silently ignore browser extension errors
            event.preventDefault();
            return;
        }
        console.error('Unhandled promise rejection:', event.reason);
        // Prevent default browser error handling for real errors
        event.preventDefault();
    });
    
    // Global error handler for JavaScript errors
    window.addEventListener('error', function(event) {
        // Ignore browser extension errors
        if (event.message && (
            event.message.includes('message channel closed') ||
            event.message.includes('Extension context')
        )) {
            return;
        }
        console.error('JavaScript error:', event.error);
    });
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            // Initialize data
            fetchFeatured('all', 'all').catch(err => {
                console.error('Error initializing featured content:', err);
            });
        });
    } else {
        // DOM is already loaded
        initializeEventListeners();
        // Initialize data
        fetchFeatured('all', 'all').catch(err => {
            console.error('Error initializing featured content:', err);
        });
    }
</script>
<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endpush

