@extends('layouts.admin')

@section('title', 'Embed Reports Management - Admin Panel')

@push('styles')
<style>
    .stat-card {
        background-color: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 8px;
    }
    .report-item {
        background-color: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        transition: all 0.2s ease;
    }
    .report-item:hover {
        border-color: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }
    .report-item.selected {
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
    .badge-reviewed {
        background-color: rgba(59, 130, 246, 0.2);
        color: #3b82f6;
        border: 1px solid rgba(59, 130, 246, 0.3);
    }
    .badge-fixed {
        background-color: rgba(34, 197, 94, 0.2);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }
    .badge-dismissed {
        background-color: rgba(107, 114, 128, 0.2);
        color: #9ca3af;
        border: 1px solid rgba(107, 114, 128, 0.3);
    }
    .badge-not-working {
        background-color: rgba(239, 68, 68, 0.2);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }
    .badge-wrong-content {
        background-color: rgba(251, 191, 36, 0.2);
        color: #fbbf24;
        border: 1px solid rgba(251, 191, 36, 0.3);
    }
    .badge-poor-quality {
        background-color: rgba(168, 85, 247, 0.2);
        color: #a78bfa;
        border: 1px solid rgba(168, 85, 247, 0.3);
    }
    .badge-broken-link {
        background-color: rgba(239, 68, 68, 0.2);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }
    .badge-other {
        background-color: rgba(107, 114, 128, 0.2);
        color: #9ca3af;
        border: 1px solid rgba(107, 114, 128, 0.3);
    }
    .badge-movie {
        background-color: rgba(59, 130, 246, 0.2);
        color: #60a5fa;
        border: 1px solid rgba(59, 130, 246, 0.3);
    }
    .badge-episode {
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
                    <h1 style="font-size: 20px; font-weight: bold; color: white; margin: 0;">Embed Reports Management</h1>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main style="max-width: 1280px; margin: 0 auto; padding: 32px 16px;">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Total Reports</p>
                <p style="font-size: 24px; font-weight: bold; color: white;" id="stats-total">0</p>
            </div>
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Pending</p>
                <p style="font-size: 24px; font-weight: bold; color: #fbbf24;" id="stats-pending">0</p>
            </div>
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Fixed</p>
                <p style="font-size: 24px; font-weight: bold; color: #22c55e;" id="stats-fixed">0</p>
            </div>
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Reviewed</p>
                <p style="font-size: 24px; font-weight: bold; color: #3b82f6;" id="stats-reviewed">0</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="stat-card p-6 mb-6">
            <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: center; margin-bottom: 16px;">
                <select 
                    id="content-type-filter" 
                    class="bg-dark-700 text-white border border-dark-600 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-600"
                    style="background-color: #2a2a2a; border-color: #3a3a3a; color: white; padding: 8px 16px; border-radius: 6px;"
                >
                    <option value="all">All Types</option>
                    <option value="movie">Movies Only</option>
                    <option value="episode">Episodes Only</option>
                </select>
                <select 
                    id="status-filter" 
                    class="bg-dark-700 text-white border border-dark-600 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-600"
                    style="background-color: #2a2a2a; border-color: #3a3a3a; color: white; padding: 8px 16px; border-radius: 6px;"
                >
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="reviewed">Reviewed</option>
                    <option value="fixed">Fixed</option>
                    <option value="dismissed">Dismissed</option>
                </select>
                <select 
                    id="report-type-filter" 
                    class="bg-dark-700 text-white border border-dark-600 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-600"
                    style="background-color: #2a2a2a; border-color: #3a3a3a; color: white; padding: 8px 16px; border-radius: 6px;"
                >
                    <option value="all">All Report Types</option>
                    <option value="not_working">Not Working</option>
                    <option value="wrong_content">Wrong Content</option>
                    <option value="poor_quality">Poor Quality</option>
                    <option value="broken_link">Broken Link</option>
                    <option value="other">Other</option>
                </select>
                <input 
                    type="text" 
                    id="search-input" 
                    placeholder="Search reports..." 
                    class="w-full bg-dark-700 text-white border border-dark-600 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-600"
                    style="background-color: #2a2a2a; border-color: #3a3a3a; color: white; padding: 8px 16px; border-radius: 6px; flex: 1; min-width: 200px;"
                >
                <button onclick="refreshReports(); return false;" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded transition-colors text-sm">
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
                            <option value="reviewed">Reviewed</option>
                            <option value="fixed">Fixed</option>
                            <option value="dismissed">Dismissed</option>
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
                <p style="color: #9ca3af;">Loading reports...</p>
            </div>
        </div>

        <!-- Reports List -->
        <div id="reports-content" style="display: none;">
            <div class="stat-card p-6 mb-4">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h2 style="font-size: 18px; font-weight: bold; color: white;" id="content-title">Embed Reports</h2>
                </div>
                <div id="reports-list" style="min-height: 200px;">
                    <!-- Items will be rendered here -->
                </div>
                <div id="empty-state" style="text-align: center; padding: 64px; color: #9ca3af; display: none;">
                    <svg style="width: 64px; height: 64px; margin: 0 auto 16px; opacity: 0.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <p style="font-size: 16px; margin-bottom: 8px;" id="empty-message">No reports found</p>
                </div>
            </div>
        </div>
    </main>
</div>

@push('scripts')
<script>
    let reports = [];
    let selectedItems = new Set();

    async function fetchReports(contentType = 'all', status = 'all', reportType = 'all') {
        try {
            const token = localStorage.getItem('adminAccessToken');
            if (!token) {
                window.location.href = '/admin/login';
                return;
            }

            const loadingState = document.getElementById('loading-state');
            const reportsContent = document.getElementById('reports-content');
            
            if (loadingState) loadingState.style.display = 'flex';
            if (reportsContent) reportsContent.style.display = 'none';

            const params = new URLSearchParams();
            if (contentType !== 'all') params.append('content_type', contentType);
            if (status !== 'all') params.append('status', status);
            if (reportType !== 'all') params.append('report_type', reportType);
            params.append('sort_by', 'reported_at');
            params.append('sort_order', 'desc');

            const response = await fetch(`${API_BASE_URL}/admin/reports/embed?${params.toString()}`, {
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
                throw new Error(`Failed to fetch reports: ${response.status}`);
            }

            const data = await response.json();
            if (data.success) {
                reports = data.data.reports || [];
                updateStats(data.data.stats || {});
                renderReports();
                updateBulkActions();
            } else {
                throw new Error(data.message || 'Failed to fetch reports');
            }
        } catch (error) {
            console.error('Error fetching reports:', error);
            alert('Failed to load reports: ' + (error.message || 'Unknown error'));
        } finally {
            const loadingState = document.getElementById('loading-state');
            const reportsContent = document.getElementById('reports-content');
            if (loadingState) loadingState.style.display = 'none';
            if (reportsContent) reportsContent.style.display = 'block';
        }
    }

    function updateStats(stats) {
        document.getElementById('stats-total').textContent = stats.total || 0;
        document.getElementById('stats-pending').textContent = stats.pending || 0;
        document.getElementById('stats-fixed').textContent = stats.fixed || 0;
        document.getElementById('stats-reviewed').textContent = stats.reviewed || 0;
    }

    function renderReports() {
        const container = document.getElementById('reports-list');
        const emptyState = document.getElementById('empty-state');

        if (reports.length === 0) {
            container.innerHTML = '';
            emptyState.style.display = 'block';
            document.getElementById('bulk-actions').style.display = 'none';
            return;
        }

        emptyState.style.display = 'none';
        document.getElementById('bulk-actions').style.display = 'block';

        container.innerHTML = reports.map((report) => {
            const statusClass = `badge-${report.status}`;
            const reportTypeClass = `badge-${report.report_type.replace('_', '-')}`;
            const contentTypeClass = report.content_type === 'movie' ? 'badge-movie' : 'badge-episode';
            const contentTypeLabel = report.content_type === 'movie' ? 'Movie' : 'Episode';
            const reportTypeLabel = report.report_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
            const isSelected = selectedItems.has(report.id);

            return `
                <div class="report-item ${isSelected ? 'selected' : ''}" 
                     data-id="${report.id}">
                    <div style="position: absolute; left: 12px; top: 12px; z-index: 10;">
                        <input type="checkbox" 
                               class="report-checkbox" 
                               data-id="${report.id}"
                               ${isSelected ? 'checked' : ''}
                               onchange="toggleItemSelection(${report.id})"
                               style="width: 18px; height: 18px; cursor: pointer; accent-color: #dc2626;">
                    </div>
                    <div style="padding-left: 40px;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px; flex-wrap: wrap;">
                            <span class="badge ${contentTypeClass}">${contentTypeLabel}</span>
                            <span class="badge ${reportTypeClass}">${reportTypeLabel}</span>
                            <span class="badge ${statusClass}">${report.status.charAt(0).toUpperCase() + report.status.slice(1)}</span>
                            ${report.report_count > 1 ? `<span class="badge" style="background-color: rgba(251, 191, 36, 0.2); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3);">${report.report_count} reports</span>` : ''}
                        </div>
                        <div style="margin-bottom: 8px;">
                            ${report.content_type === 'movie' ? `
                                <p style="color: #9ca3af; font-size: 14px;">
                                    <strong>Content ID:</strong> ${report.content_id}
                                    ${report.embed_id ? ` | <strong>Embed ID:</strong> ${report.embed_id}` : ''}
                                    ${report.content && report.content.title ? ` | <strong>Movie:</strong> ${escapeHtml(report.content.title)}` : ''}
                                </p>
                            ` : `
                                <p style="color: #9ca3af; font-size: 14px;">
                                    <strong>Episode ID:</strong> ${report.content_id}
                                    ${report.embed_id ? ` | <strong>Embed ID:</strong> ${report.embed_id}` : ''}
                                    ${report.content && report.content.season && report.content.season.tv_show ? `
                                        | <strong>TV Show ID:</strong> ${report.content.season.tv_show.id}
                                        | <strong>TV Show:</strong> ${escapeHtml(report.content.season.tv_show.name)}
                                    ` : ''}
                                    ${report.content && report.content.season ? `
                                        | <strong>Season:</strong> ${report.content.season.season_number || 'N/A'}
                                    ` : ''}
                                    ${report.content && report.content.episode_number ? `
                                        | <strong>Episode:</strong> ${report.content.episode_number}
                                    ` : ''}
                                    ${report.content && report.content.name ? `
                                        | <strong>Episode Name:</strong> ${escapeHtml(report.content.name)}
                                    ` : ''}
                                </p>
                            `}
                        </div>
                        ${report.description ? `<p style="color: #9ca3af; font-size: 14px; margin-bottom: 8px;">${escapeHtml(report.description)}</p>` : ''}
                        <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 8px;">
                            <span style="color: #9ca3af; font-size: 14px;">Reported: ${formatDate(report.reported_at)}</span>
                            ${report.processed_at ? `<span style="color: #9ca3af; font-size: 14px;">Processed: ${formatDate(report.processed_at)}</span>` : ''}
                        </div>
                        ${report.admin_notes ? `<div style="background-color: #2a2a2a; padding: 8px; border-radius: 4px; margin-top: 8px;">
                            <p style="color: #9ca3af; font-size: 12px; margin: 0;"><strong>Admin Notes:</strong> ${escapeHtml(report.admin_notes)}</p>
                        </div>` : ''}
                        <div style="display: flex; gap: 8px; margin-top: 12px;">
                            <select id="status-select-${report.id}" onchange="updateReportStatus(${report.id}, this.value)" 
                                    style="background-color: #2a2a2a; border-color: #3a3a3a; color: white; padding: 4px 8px; border-radius: 4px; font-size: 14px;">
                                <option value="pending" ${report.status === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="reviewed" ${report.status === 'reviewed' ? 'selected' : ''}>Reviewed</option>
                                <option value="fixed" ${report.status === 'fixed' ? 'selected' : ''}>Fixed</option>
                                <option value="dismissed" ${report.status === 'dismissed' ? 'selected' : ''}>Dismissed</option>
                            </select>
                            <button onclick="showAdminNotes(${report.id}); return false;" 
                                    style="color: #60a5fa; background: transparent; border: 1px solid #3b82f6; border-radius: 4px; padding: 4px 8px; font-size: 14px; cursor: pointer;">
                                Add Notes
                            </button>
                            ${report.content ? (
                                report.content_type === 'movie' 
                                    ? `<a href="/admin/movies/${report.content_id}" 
                                            style="color: #60a5fa; text-decoration: none; font-size: 14px; padding: 4px 8px; border: 1px solid #3b82f6; border-radius: 4px;">
                                        View Content
                                    </a>`
                                    : (report.content_type === 'episode' && report.content.season && report.content.season.tv_show)
                                        ? `<a href="/admin/tvshows/${report.content.season.tv_show.id}" 
                                                style="color: #60a5fa; text-decoration: none; font-size: 14px; padding: 4px 8px; border: 1px solid #3b82f6; border-radius: 4px;">
                                            View TV Show
                                        </a>`
                                        : ''
                            ) : ''}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    async function updateReportStatus(id, status) {
        const token = localStorage.getItem('adminAccessToken');
        if (!token) {
            window.location.href = '/admin/login';
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}/admin/reports/embed/${id}`, {
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
                const contentType = document.getElementById('content-type-filter').value;
                const statusFilter = document.getElementById('status-filter').value;
                const reportType = document.getElementById('report-type-filter').value;
                await fetchReports(contentType, statusFilter, reportType);
            } else {
                alert('Failed to update report: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error updating report:', error);
            alert('Failed to update report. Please try again.');
        }
    }

    function showAdminNotes(id) {
        const notes = prompt('Enter admin notes (optional):');
        if (notes !== null) {
            updateReportWithNotes(id, notes);
        }
    }

    async function updateReportWithNotes(id, notes) {
        const token = localStorage.getItem('adminAccessToken');
        if (!token) {
            window.location.href = '/admin/login';
            return;
        }

        const report = reports.find(r => r.id === id);
        if (!report) return;

        try {
            const response = await fetch(`${API_BASE_URL}/admin/reports/embed/${id}`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    status: report.status,
                    admin_notes: notes
                })
            });

            if (response.status === 401) {
                window.location.href = '/admin/login';
                return;
            }

            const data = await response.json();
            if (data.success) {
                const contentType = document.getElementById('content-type-filter').value;
                const statusFilter = document.getElementById('status-filter').value;
                const reportType = document.getElementById('report-type-filter').value;
                await fetchReports(contentType, statusFilter, reportType);
            } else {
                alert('Failed to update report: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error updating report:', error);
            alert('Failed to update report. Please try again.');
        }
    }

    function toggleItemSelection(id) {
        if (selectedItems.has(id)) {
            selectedItems.delete(id);
        } else {
            selectedItems.add(id);
        }
        updateBulkActions();
        renderReports();
    }

    function toggleSelectAll(checked) {
        if (checked) {
            reports.forEach(report => selectedItems.add(report.id));
        } else {
            selectedItems.clear();
        }
        updateBulkActions();
        renderReports();
    }

    function clearSelection() {
        selectedItems.clear();
        updateBulkActions();
        renderReports();
    }

    function updateBulkActions() {
        const count = selectedItems.size;
        document.getElementById('selected-count').textContent = count;
        document.getElementById('select-all-checkbox').checked = count > 0 && count === reports.length;
    }

    async function bulkUpdateStatus() {
        if (selectedItems.size === 0) {
            alert('Please select at least one report.');
            return;
        }

        const status = document.getElementById('bulk-status').value;
        if (!confirm(`Update ${selectedItems.size} report(s) to "${status}"?`)) {
            return;
        }

        const token = localStorage.getItem('adminAccessToken');
        if (!token) {
            window.location.href = '/admin/login';
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}/admin/reports/embed/bulk-update`, {
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
                const contentType = document.getElementById('content-type-filter').value;
                const statusFilter = document.getElementById('status-filter').value;
                const reportType = document.getElementById('report-type-filter').value;
                await fetchReports(contentType, statusFilter, reportType);
                alert(`Successfully updated ${data.data.updated_count || selectedItems.size} report(s).`);
            } else {
                alert('Failed to update reports: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error updating reports:', error);
            alert('Failed to update reports. Please try again.');
        }
    }

    function refreshReports() {
        const contentType = document.getElementById('content-type-filter').value;
        const status = document.getElementById('status-filter').value;
        const reportType = document.getElementById('report-type-filter').value;
        selectedItems.clear();
        fetchReports(contentType, status, reportType);
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
        const contentTypeFilter = document.getElementById('content-type-filter');
        const statusFilter = document.getElementById('status-filter');
        const reportTypeFilter = document.getElementById('report-type-filter');
        const searchInput = document.getElementById('search-input');
        
        if (contentTypeFilter) {
            contentTypeFilter.addEventListener('change', function() {
                const status = document.getElementById('status-filter').value;
                const reportType = document.getElementById('report-type-filter').value;
                fetchReports(this.value, status, reportType);
            });
        }
        
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                const contentType = document.getElementById('content-type-filter').value;
                const reportType = document.getElementById('report-type-filter').value;
                fetchReports(contentType, this.value, reportType);
            });
        }
        
        if (reportTypeFilter) {
            reportTypeFilter.addEventListener('change', function() {
                const contentType = document.getElementById('content-type-filter').value;
                const status = document.getElementById('status-filter').value;
                fetchReports(contentType, status, this.value);
            });
        }
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const items = document.querySelectorAll('.report-item');
                items.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
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
            fetchReports('all', 'all', 'all');
        });
    } else {
        initializeEventListeners();
        fetchReports('all', 'all', 'all');
    }
</script>
<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endpush

