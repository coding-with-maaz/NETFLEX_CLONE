@extends('layouts.admin')

@section('title', 'Content Requests Management - Admin Panel')

@push('styles')
<style>
    .stat-card {
        background-color: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 8px;
    }
    .request-item {
        background-color: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        transition: all 0.2s ease;
    }
    .request-item:hover {
        border-color: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }
    .request-item.selected {
        border-color: #dc2626;
        background-color: rgba(220, 38, 38, 0.1);
    }
    .badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .badge-pending {
        background-color: rgba(251, 191, 36, 0.2);
        color: #fbbf24;
        border: 1px solid rgba(251, 191, 36, 0.3);
    }
    .badge-approved {
        background-color: rgba(34, 197, 94, 0.2);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }
    .badge-rejected {
        background-color: rgba(239, 68, 68, 0.2);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }
    .badge-completed {
        background-color: rgba(59, 130, 246, 0.2);
        color: #3b82f6;
        border: 1px solid rgba(59, 130, 246, 0.3);
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
                    <h1 style="font-size: 20px; font-weight: bold; color: white; margin: 0;">Content Requests Management</h1>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main style="max-width: 1280px; margin: 0 auto; padding: 32px 16px;">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Total Requests</p>
                <p style="font-size: 24px; font-weight: bold; color: white;" id="stats-total">0</p>
            </div>
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Pending</p>
                <p style="font-size: 24px; font-weight: bold; color: #fbbf24;" id="stats-pending">0</p>
            </div>
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Approved</p>
                <p style="font-size: 24px; font-weight: bold; color: #22c55e;" id="stats-approved">0</p>
            </div>
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Completed</p>
                <p style="font-size: 24px; font-weight: bold; color: #3b82f6;" id="stats-completed">0</p>
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
                    <option value="all">All Types</option>
                    <option value="movie">Movies Only</option>
                    <option value="tvshow">TV Shows Only</option>
                </select>
                <select 
                    id="status-filter" 
                    class="bg-dark-700 text-white border border-dark-600 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-600"
                    style="background-color: #2a2a2a; border-color: #3a3a3a; color: white; padding: 8px 16px; border-radius: 6px;"
                >
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="completed">Completed</option>
                </select>
                <input 
                    type="text" 
                    id="search-input" 
                    placeholder="Search requests..." 
                    class="w-full bg-dark-700 text-white border border-dark-600 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-600"
                    style="background-color: #2a2a2a; border-color: #3a3a3a; color: white; padding: 8px 16px; border-radius: 6px; flex: 1; min-width: 200px;"
                >
                <button onclick="refreshRequests(); return false;" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded transition-colors text-sm">
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
                        <select id="bulk-status" style="background-color: #2a2a2a; border-color: #3a3a3a; color: white; padding: 8px 16px; border-radius: 6px;">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="completed">Completed</option>
                        </select>
                        <button onclick="bulkUpdateStatus(); return false;" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition-colors text-sm">
                            Update Selected
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
                <p style="color: #9ca3af;">Loading requests...</p>
            </div>
        </div>

        <!-- Requests List -->
        <div id="requests-content" style="display: none;">
            <div class="stat-card p-6 mb-4">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h2 style="font-size: 18px; font-weight: bold; color: white;" id="content-title">Content Requests</h2>
                </div>
                <div id="requests-list" style="min-height: 200px;">
                    <!-- Items will be rendered here -->
                </div>
                <div id="empty-state" style="text-align: center; padding: 64px; color: #9ca3af; display: none;">
                    <svg style="width: 64px; height: 64px; margin: 0 auto 16px; opacity: 0.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p style="font-size: 16px; margin-bottom: 8px;" id="empty-message">No requests found</p>
                </div>
            </div>
        </div>
    </main>
</div>

@push('scripts')
<script>
    let requests = [];
    let selectedItems = new Set();

    async function fetchRequests(type = 'all', status = 'all') {
        try {
            const token = localStorage.getItem('adminAccessToken');
            if (!token) {
                window.location.href = '/admin/login';
                return;
            }

            const loadingState = document.getElementById('loading-state');
            const requestsContent = document.getElementById('requests-content');
            
            if (loadingState) loadingState.style.display = 'flex';
            if (requestsContent) requestsContent.style.display = 'none';

            const params = new URLSearchParams();
            if (type !== 'all') params.append('type', type);
            if (status !== 'all') params.append('status', status);
            params.append('sort_by', 'requested_at');
            params.append('sort_order', 'desc');

            const response = await fetch(`${API_BASE_URL}/admin/requests?${params.toString()}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            if (response.status === 401) {
                window.location.href = '/admin/login';
                return;
            }

            if (!response.ok) {
                throw new Error(`Failed to fetch requests: ${response.status}`);
            }

            const data = await response.json();
            if (data.success) {
                requests = data.data.requests || [];
                updateStats(data.data.stats || {});
                renderRequests();
                updateBulkActions();
            } else {
                throw new Error(data.message || 'Failed to fetch requests');
            }
        } catch (error) {
            console.error('Error fetching requests:', error);
            alert('Failed to load requests: ' + (error.message || 'Unknown error'));
        } finally {
            const loadingState = document.getElementById('loading-state');
            const requestsContent = document.getElementById('requests-content');
            if (loadingState) loadingState.style.display = 'none';
            if (requestsContent) requestsContent.style.display = 'block';
        }
    }

    function updateStats(stats) {
        document.getElementById('stats-total').textContent = stats.total || 0;
        document.getElementById('stats-pending').textContent = stats.pending || 0;
        document.getElementById('stats-approved').textContent = stats.approved || 0;
        document.getElementById('stats-completed').textContent = stats.completed || 0;
    }

    function renderRequests() {
        const container = document.getElementById('requests-list');
        const emptyState = document.getElementById('empty-state');

        if (requests.length === 0) {
            container.innerHTML = '';
            emptyState.style.display = 'block';
            document.getElementById('bulk-actions').style.display = 'none';
            return;
        }

        emptyState.style.display = 'none';
        document.getElementById('bulk-actions').style.display = 'block';

        container.innerHTML = requests.map((request) => {
            const statusClass = `badge-${request.status}`;
            const typeClass = request.type === 'movie' ? 'badge-movie' : 'badge-tvshow';
            const typeLabel = request.type === 'movie' ? 'Movie' : 'TV Show';
            const isSelected = selectedItems.has(request.id);

            return `
                <div class="request-item ${isSelected ? 'selected' : ''}" 
                     data-id="${request.id}">
                    <div style="position: absolute; left: 12px; top: 12px; z-index: 10;">
                        <input type="checkbox" 
                               class="request-checkbox" 
                               data-id="${request.id}"
                               ${isSelected ? 'checked' : ''}
                               onchange="toggleItemSelection(${request.id})"
                               style="width: 18px; height: 18px; cursor: pointer; accent-color: #dc2626;">
                    </div>
                    <div style="padding-left: 40px;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                            <h3 style="font-size: 18px; font-weight: bold; color: white; margin: 0;">${escapeHtml(request.title)}</h3>
                            <span class="badge ${typeClass}">${typeLabel}</span>
                            <span class="badge ${statusClass}">${request.status.charAt(0).toUpperCase() + request.status.slice(1)}</span>
                            ${request.request_count > 1 ? `<span class="badge" style="background-color: rgba(251, 191, 36, 0.2); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3);">${request.request_count} requests</span>` : ''}
                        </div>
                        ${request.description ? `<p style="color: #9ca3af; font-size: 14px; margin-bottom: 8px;">${escapeHtml(request.description)}</p>` : ''}
                        <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 8px;">
                            ${request.year ? `<span style="color: #9ca3af; font-size: 14px;">Year: ${request.year}</span>` : ''}
                            ${request.tmdb_id ? `<span style="color: #9ca3af; font-size: 14px;">TMDB ID: ${request.tmdb_id}</span>` : ''}
                            <span style="color: #9ca3af; font-size: 14px;">Requested: ${formatDate(request.requested_at)}</span>
                            ${request.processed_at ? `<span style="color: #9ca3af; font-size: 14px;">Processed: ${formatDate(request.processed_at)}</span>` : ''}
                        </div>
                        ${request.admin_notes ? `<div style="background-color: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); padding: 12px; border-radius: 6px; margin-top: 12px;">
                            <div style="display: flex; align-items: start; gap: 8px;">
                                <svg style="width: 18px; height: 18px; color: #60a5fa; flex-shrink: 0; margin-top: 2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <div style="flex: 1;">
                                    <p style="color: #60a5fa; font-size: 13px; font-weight: 600; margin: 0 0 6px 0;">Admin Response:</p>
                                    <p style="color: #93c5fd; font-size: 14px; margin: 0; white-space: pre-wrap;">${escapeHtml(request.admin_notes)}</p>
                                </div>
                            </div>
                        </div>` : ''}
                        <div style="display: flex; gap: 8px; margin-top: 12px;">
                            <select id="status-select-${request.id}" onchange="updateRequestStatus(${request.id}, this.value)" 
                                    style="background-color: #2a2a2a; border-color: #3a3a3a; color: white; padding: 4px 8px; border-radius: 4px; font-size: 14px;">
                                <option value="pending" ${request.status === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="approved" ${request.status === 'approved' ? 'selected' : ''}>Approved</option>
                                <option value="rejected" ${request.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                                <option value="completed" ${request.status === 'completed' ? 'selected' : ''}>Completed</option>
                            </select>
                            <button onclick="showAdminNotes(${request.id}); return false;" 
                                    style="color: #60a5fa; background: transparent; border: 1px solid #3b82f6; border-radius: 4px; padding: 4px 8px; font-size: 14px; cursor: pointer;">
                                Add Notes
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    async function updateRequestStatus(id, status) {
        const token = localStorage.getItem('adminAccessToken');
        if (!token) {
            window.location.href = '/admin/login';
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}/admin/requests/${id}`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status })
            });

            if (response.status === 401) {
                window.location.href = '/admin/login';
                return;
            }

            const data = await response.json();
            if (data.success) {
                const type = document.getElementById('type-filter').value;
                const statusFilter = document.getElementById('status-filter').value;
                await fetchRequests(type, statusFilter);
            } else {
                alert('Failed to update request: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error updating request:', error);
            alert('Failed to update request. Please try again.');
        }
    }

    function showAdminNotes(id) {
        const notes = prompt('Enter admin notes (optional):');
        if (notes !== null) {
            updateRequestWithNotes(id, notes);
        }
    }

    async function updateRequestWithNotes(id, notes) {
        const token = localStorage.getItem('adminAccessToken');
        if (!token) {
            window.location.href = '/admin/login';
            return;
        }

        const request = requests.find(r => r.id === id);
        if (!request) return;

        try {
            const response = await fetch(`${API_BASE_URL}/admin/requests/${id}`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    status: request.status,
                    admin_notes: notes
                })
            });

            if (response.status === 401) {
                window.location.href = '/admin/login';
                return;
            }

            const data = await response.json();
            if (data.success) {
                const type = document.getElementById('type-filter').value;
                const statusFilter = document.getElementById('status-filter').value;
                await fetchRequests(type, statusFilter);
            } else {
                alert('Failed to update request: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error updating request:', error);
            alert('Failed to update request. Please try again.');
        }
    }

    function toggleItemSelection(id) {
        if (selectedItems.has(id)) {
            selectedItems.delete(id);
        } else {
            selectedItems.add(id);
        }
        updateBulkActions();
        renderRequests();
    }

    function toggleSelectAll(checked) {
        if (checked) {
            requests.forEach(request => selectedItems.add(request.id));
        } else {
            selectedItems.clear();
        }
        updateBulkActions();
        renderRequests();
    }

    function clearSelection() {
        selectedItems.clear();
        updateBulkActions();
        renderRequests();
    }

    function updateBulkActions() {
        const count = selectedItems.size;
        document.getElementById('selected-count').textContent = count;
        document.getElementById('select-all-checkbox').checked = count > 0 && count === requests.length;
    }

    async function bulkUpdateStatus() {
        if (selectedItems.size === 0) {
            alert('Please select at least one request.');
            return;
        }

        const status = document.getElementById('bulk-status').value;
        if (!confirm(`Update ${selectedItems.size} request(s) to "${status}"?`)) {
            return;
        }

        const token = localStorage.getItem('adminAccessToken');
        if (!token) {
            window.location.href = '/admin/login';
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}/admin/requests/bulk-update`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ids: Array.from(selectedItems),
                    status: status
                })
            });

            if (response.status === 401) {
                window.location.href = '/admin/login';
                return;
            }

            const data = await response.json();
            if (data.success) {
                selectedItems.clear();
                const type = document.getElementById('type-filter').value;
                const statusFilter = document.getElementById('status-filter').value;
                await fetchRequests(type, statusFilter);
                alert(`Successfully updated ${data.data.updated_count || selectedItems.size} request(s).`);
            } else {
                alert('Failed to update requests: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error updating requests:', error);
            alert('Failed to update requests. Please try again.');
        }
    }

    function refreshRequests() {
        const type = document.getElementById('type-filter').value;
        const status = document.getElementById('status-filter').value;
        selectedItems.clear();
        fetchRequests(type, status);
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize event listeners
    function initializeEventListeners() {
        const typeFilter = document.getElementById('type-filter');
        const statusFilter = document.getElementById('status-filter');
        const searchInput = document.getElementById('search-input');
        
        if (typeFilter) {
            typeFilter.addEventListener('change', function() {
                const status = document.getElementById('status-filter').value;
                fetchRequests(this.value, status);
            });
        }
        
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                const type = document.getElementById('type-filter').value;
                fetchRequests(type, this.value);
            });
        }
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const items = document.querySelectorAll('.request-item');
                items.forEach(item => {
                    const title = item.querySelector('h3').textContent.toLowerCase();
                    if (title.includes(searchTerm)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            fetchRequests('all', 'all');
        });
    } else {
        initializeEventListeners();
        fetchRequests('all', 'all');
    }
</script>
<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endpush

