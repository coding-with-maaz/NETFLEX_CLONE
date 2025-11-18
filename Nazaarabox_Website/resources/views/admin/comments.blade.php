@extends('layouts.admin')

@section('title', 'Comments Management - Admin Panel')

@push('styles')
<style>
    .stat-card {
        background-color: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 8px;
    }
    .comment-item {
        background-color: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        transition: all 0.2s ease;
    }
    .comment-item:hover {
        border-color: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }
    .comment-item.selected {
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
    .badge-spam {
        background-color: rgba(139, 92, 246, 0.2);
        color: #a78bfa;
        border: 1px solid rgba(139, 92, 246, 0.3);
    }
    .badge-admin {
        background-color: rgba(220, 38, 38, 0.2);
        color: #f87171;
        border: 1px solid rgba(220, 38, 38, 0.3);
    }
    .reply-indent {
        margin-left: 32px;
        border-left: 2px solid #2a2a2a;
        padding-left: 16px;
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
                    <h1 style="font-size: 20px; font-weight: bold; color: white; margin: 0;">Comments Management</h1>
                </div>
                <div style="display: flex; gap: 12px;">
                    <button onclick="exportEmails()" style="background-color: #3b82f6; color: white; padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-weight: 500;">
                        Export Emails
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main style="max-width: 1280px; margin: 0 auto; padding: 32px 16px;">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Total Comments</p>
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
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Rejected</p>
                <p style="font-size: 24px; font-weight: bold; color: #ef4444;" id="stats-rejected">0</p>
            </div>
            <div class="stat-card p-6">
                <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Spam</p>
                <p style="font-size: 24px; font-weight: bold; color: #a78bfa;" id="stats-spam">0</p>
            </div>
        </div>

        <!-- Filters -->
        <div style="background-color: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <div>
                    <label style="display: block; color: #9ca3af; font-size: 14px; margin-bottom: 8px;">Status</label>
                    <select id="filter-status" style="width: 100%; padding: 8px 12px; background-color: #2a2a2a; border: 1px solid #3a3a3a; border-radius: 6px; color: white;">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="spam">Spam</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; color: #9ca3af; font-size: 14px; margin-bottom: 8px;">Content Type</label>
                    <select id="filter-type" style="width: 100%; padding: 8px 12px; background-color: #2a2a2a; border: 1px solid #3a3a3a; border-radius: 6px; color: white;">
                        <option value="">All Types</option>
                        <option value="movie">Movies</option>
                        <option value="tvshow">TV Shows</option>
                        <option value="episode">Episodes</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; color: #9ca3af; font-size: 14px; margin-bottom: 8px;">Search</label>
                    <input type="text" id="filter-search" placeholder="Search comments..." style="width: 100%; padding: 8px 12px; background-color: #2a2a2a; border: 1px solid #3a3a3a; border-radius: 6px; color: white;">
                </div>
                <div style="display: flex; align-items: flex-end;">
                    <button onclick="applyFilters()" style="width: 100%; background-color: #dc2626; color: white; padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-weight: 500;">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div style="background-color: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 8px; padding: 16px; margin-bottom: 24px; display: none;" id="bulk-actions">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #9ca3af;" id="selected-count">0 selected</span>
                <div style="display: flex; gap: 12px;">
                    <select id="bulk-status" style="padding: 8px 12px; background-color: #2a2a2a; border: 1px solid #3a3a3a; border-radius: 6px; color: white;">
                        <option value="approved">Approve</option>
                        <option value="rejected">Reject</option>
                        <option value="spam">Mark as Spam</option>
                        <option value="pending">Set to Pending</option>
                    </select>
                    <button onclick="bulkUpdate()" style="background-color: #dc2626; color: white; padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-weight: 500;">
                        Apply
                    </button>
                    <button onclick="clearSelection()" style="background-color: #2a2a2a; color: #9ca3af; padding: 8px 16px; border-radius: 6px; border: 1px solid #3a3a3a; cursor: pointer;">
                        Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Comments List -->
        <div id="comments-list">
            <div style="text-align: center; padding: 40px; color: #9ca3af;">
                <p>Loading comments...</p>
            </div>
        </div>

        <!-- Pagination -->
        <div id="pagination" style="margin-top: 24px; display: none;"></div>
    </main>
</div>

<!-- Reply Modal -->
<div id="reply-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.8); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background-color: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 8px; padding: 24px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <h2 style="color: white; margin-top: 0; margin-bottom: 16px;">Reply as Admin</h2>
        <div style="margin-bottom: 16px;">
            <p style="color: #9ca3af; font-size: 14px; margin-bottom: 8px;">Original Comment:</p>
            <div style="background-color: #2a2a2a; padding: 12px; border-radius: 6px; color: #d1d5db;" id="modal-original-comment"></div>
        </div>
        <div style="margin-bottom: 16px;">
            <label style="display: block; color: #9ca3af; font-size: 14px; margin-bottom: 8px;">Your Reply:</label>
            <textarea id="modal-reply-text" rows="5" style="width: 100%; padding: 12px; background-color: #2a2a2a; border: 1px solid #3a3a3a; border-radius: 6px; color: white; resize: vertical;"></textarea>
        </div>
        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <button onclick="closeReplyModal()" style="background-color: #2a2a2a; color: #9ca3af; padding: 10px 20px; border-radius: 6px; border: 1px solid #3a3a3a; cursor: pointer;">
                Cancel
            </button>
            <button onclick="submitAdminReply()" style="background-color: #dc2626; color: white; padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; font-weight: 500;">
                Send Reply
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const API_BASE = '/api/v1/admin';
    let currentPage = 1;
    let selectedComments = new Set();
    let currentReplyCommentId = null;

    // Load comments on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadComments();
    });

    // Load comments
    function loadComments(page = 1) {
        currentPage = page;
        const status = document.getElementById('filter-status').value;
        const type = document.getElementById('filter-type').value;
        const search = document.getElementById('filter-search').value;

        const params = new URLSearchParams({
            page: page,
            limit: 20,
        });

        if (status) params.append('status', status);
        if (type) params.append('type', type);
        if (search) params.append('search', search);

        fetch(`${API_BASE}/comments?${params}`, {
            headers: {
                'Authorization': `Bearer ${getAdminToken()}`,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStats(data.data.stats);
                renderComments(data.data.comments);
                renderPagination(data.data.pagination);
            }
        })
        .catch(error => {
            console.error('Error loading comments:', error);
            document.getElementById('comments-list').innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;">Error loading comments</div>';
        });
    }

    // Update stats
    function updateStats(stats) {
        document.getElementById('stats-total').textContent = stats.total || 0;
        document.getElementById('stats-pending').textContent = stats.pending || 0;
        document.getElementById('stats-approved').textContent = stats.approved || 0;
        document.getElementById('stats-rejected').textContent = stats.rejected || 0;
        document.getElementById('stats-spam').textContent = stats.spam || 0;
    }

    // Render comments
    function renderComments(comments) {
        if (comments.length === 0) {
            document.getElementById('comments-list').innerHTML = '<div style="text-align: center; padding: 40px; color: #9ca3af;">No comments found</div>';
            return;
        }

        let html = '';
        comments.forEach(comment => {
            const isReply = comment.parent_id !== null;
            const indentClass = isReply ? 'reply-indent' : '';
            const statusBadge = getStatusBadge(comment.status);
            const adminBadge = comment.is_admin_reply ? '<span class="badge badge-admin">Admin Reply</span>' : '';
            const contentInfo = comment.content ? `${comment.content.type}: ${comment.content.title || comment.content.name || 'N/A'}` : 'Unknown Content';

            html += `
                <div class="comment-item ${indentClass}" data-comment-id="${comment.id}">
                    <div style="display: flex; gap: 12px; align-items: start;">
                        <input type="checkbox" class="comment-checkbox" value="${comment.id}" onchange="toggleSelection(${comment.id})" style="margin-top: 4px;">
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                                <div>
                                    <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 4px;">
                                        <strong style="color: white;">${escapeHtml(comment.name)}</strong>
                                        <span style="color: #9ca3af; font-size: 14px;">${escapeHtml(comment.email)}</span>
                                        ${statusBadge}
                                        ${adminBadge}
                                    </div>
                                    <p style="color: #9ca3af; font-size: 12px; margin: 0;">${contentInfo}</p>
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    <span style="color: #9ca3af; font-size: 12px;">${formatDate(comment.created_at)}</span>
                                </div>
                            </div>
                            <p style="color: #d1d5db; margin: 12px 0; line-height: 1.6;">${escapeHtml(comment.comment)}</p>
                            ${comment.parent ? `
                                <div style="background-color: #2a2a2a; padding: 12px; border-radius: 6px; margin-top: 12px; border-left: 3px solid #dc2626;">
                                    <p style="color: #9ca3af; font-size: 12px; margin: 0 0 4px 0;">Replying to:</p>
                                    <p style="color: #d1d5db; font-size: 14px; margin: 0;">${escapeHtml(comment.parent.comment.substring(0, 100))}${comment.parent.comment.length > 100 ? '...' : ''}</p>
                                </div>
                            ` : ''}
                            <div style="display: flex; gap: 8px; margin-top: 12px;">
                                ${comment.status === 'pending' ? `
                                    <button onclick="updateStatus(${comment.id}, 'approved')" style="background-color: #22c55e; color: white; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; font-size: 12px;">
                                        Approve
                                    </button>
                                ` : ''}
                                ${comment.status !== 'rejected' ? `
                                    <button onclick="updateStatus(${comment.id}, 'rejected')" style="background-color: #ef4444; color: white; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; font-size: 12px;">
                                        Reject
                                    </button>
                                ` : ''}
                                ${comment.status !== 'spam' ? `
                                    <button onclick="updateStatus(${comment.id}, 'spam')" style="background-color: #a78bfa; color: white; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; font-size: 12px;">
                                        Mark Spam
                                    </button>
                                ` : ''}
                                ${!comment.is_admin_reply ? `
                                    <button onclick="openReplyModal(${comment.id})" style="background-color: #3b82f6; color: white; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; font-size: 12px;">
                                        Reply
                                    </button>
                                ` : ''}
                                <button onclick="deleteComment(${comment.id})" style="background-color: #2a2a2a; color: #ef4444; padding: 6px 12px; border-radius: 4px; border: 1px solid #3a3a3a; cursor: pointer; font-size: 12px;">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        document.getElementById('comments-list').innerHTML = html;
    }

    // Get status badge
    function getStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge badge-pending">Pending</span>',
            'approved': '<span class="badge badge-approved">Approved</span>',
            'rejected': '<span class="badge badge-rejected">Rejected</span>',
            'spam': '<span class="badge badge-spam">Spam</span>',
        };
        return badges[status] || '';
    }

    // Render pagination
    function renderPagination(pagination) {
        if (!pagination || pagination.total_pages <= 1) {
            document.getElementById('pagination').style.display = 'none';
            return;
        }

        document.getElementById('pagination').style.display = 'block';
        let html = '<div style="display: flex; justify-content: center; gap: 8px;">';

        if (pagination.has_prev) {
            html += `<button onclick="loadComments(${pagination.current_page - 1})" style="background-color: #2a2a2a; color: white; padding: 8px 16px; border-radius: 6px; border: 1px solid #3a3a3a; cursor: pointer;">Previous</button>`;
        }

        for (let i = 1; i <= pagination.total_pages; i++) {
            if (i === pagination.current_page) {
                html += `<button style="background-color: #dc2626; color: white; padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer;">${i}</button>`;
            } else {
                html += `<button onclick="loadComments(${i})" style="background-color: #2a2a2a; color: white; padding: 8px 16px; border-radius: 6px; border: 1px solid #3a3a3a; cursor: pointer;">${i}</button>`;
            }
        }

        if (pagination.has_next) {
            html += `<button onclick="loadComments(${pagination.current_page + 1})" style="background-color: #2a2a2a; color: white; padding: 8px 16px; border-radius: 6px; border: 1px solid #3a3a3a; cursor: pointer;">Next</button>`;
        }

        html += '</div>';
        document.getElementById('pagination').innerHTML = html;
    }

    // Apply filters
    function applyFilters() {
        loadComments(1);
    }

    // Update comment status
    function updateStatus(commentId, status) {
        fetch(`${API_BASE}/comments/${commentId}`, {
            method: 'PATCH',
            headers: {
                'Authorization': `Bearer ${getAdminToken()}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadComments(currentPage);
            } else {
                alert('Error: ' + (data.message || 'Failed to update status'));
            }
        })
        .catch(error => {
            console.error('Error updating status:', error);
            alert('Error updating comment status');
        });
    }

    // Delete comment
    function deleteComment(commentId) {
        if (!confirm('Are you sure you want to delete this comment?')) {
            return;
        }

        fetch(`${API_BASE}/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${getAdminToken()}`,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadComments(currentPage);
            } else {
                alert('Error: ' + (data.message || 'Failed to delete comment'));
            }
        })
        .catch(error => {
            console.error('Error deleting comment:', error);
            alert('Error deleting comment');
        });
    }

    // Open reply modal
    function openReplyModal(commentId) {
        currentReplyCommentId = commentId;
        
        // Get comment details
        fetch(`${API_BASE}/comments/${commentId}`, {
            headers: {
                'Authorization': `Bearer ${getAdminToken()}`,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modal-original-comment').textContent = data.data.comment.comment;
                document.getElementById('modal-reply-text').value = '';
                document.getElementById('reply-modal').style.display = 'flex';
            }
        })
        .catch(error => {
            console.error('Error loading comment:', error);
            alert('Error loading comment details');
        });
    }

    // Close reply modal
    function closeReplyModal() {
        document.getElementById('reply-modal').style.display = 'none';
        currentReplyCommentId = null;
    }

    // Submit admin reply
    function submitAdminReply() {
        const replyText = document.getElementById('modal-reply-text').value.trim();
        
        if (!replyText) {
            alert('Please enter a reply');
            return;
        }

        fetch(`${API_BASE}/comments/${currentReplyCommentId}`, {
            method: 'PATCH',
            headers: {
                'Authorization': `Bearer ${getAdminToken()}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ comment: replyText })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeReplyModal();
                loadComments(currentPage);
                alert('Reply sent successfully!');
            } else {
                alert('Error: ' + (data.message || 'Failed to send reply'));
            }
        })
        .catch(error => {
            console.error('Error sending reply:', error);
            alert('Error sending reply');
        });
    }

    // Toggle selection
    function toggleSelection(commentId) {
        const checkbox = document.querySelector(`.comment-checkbox[value="${commentId}"]`);
        if (checkbox.checked) {
            selectedComments.add(commentId);
        } else {
            selectedComments.delete(commentId);
        }
        updateBulkActions();
    }

    // Update bulk actions
    function updateBulkActions() {
        const count = selectedComments.size;
        document.getElementById('selected-count').textContent = `${count} selected`;
        document.getElementById('bulk-actions').style.display = count > 0 ? 'block' : 'none';
    }

    // Clear selection
    function clearSelection() {
        selectedComments.clear();
        document.querySelectorAll('.comment-checkbox').forEach(cb => cb.checked = false);
        updateBulkActions();
    }

    // Bulk update
    function bulkUpdate() {
        if (selectedComments.size === 0) {
            return;
        }

        const status = document.getElementById('bulk-status').value;
        
        fetch(`${API_BASE}/comments/bulk-update`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${getAdminToken()}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                comment_ids: Array.from(selectedComments),
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                clearSelection();
                loadComments(currentPage);
                alert(`Successfully updated ${data.data.updated_count} comment(s)`);
            } else {
                alert('Error: ' + (data.message || 'Failed to update comments'));
            }
        })
        .catch(error => {
            console.error('Error bulk updating:', error);
            alert('Error updating comments');
        });
    }

    // Export emails
    function exportEmails() {
        const status = document.getElementById('filter-status').value;
        const params = new URLSearchParams();
        if (status) params.append('status', status);

        const url = `${API_BASE}/comments/export/emails${params.toString() ? '?' + params.toString() : ''}`;
        
        fetch(url, {
            headers: {
                'Authorization': `Bearer ${getAdminToken()}`,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Create download link
                const blob = new Blob([JSON.stringify(data.data.emails, null, 2)], { type: 'application/json' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `user_emails_${new Date().toISOString().split('T')[0]}.json`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            } else {
                alert('Error: ' + (data.message || 'Failed to export emails'));
            }
        })
        .catch(error => {
            console.error('Error exporting emails:', error);
            alert('Error exporting emails');
        });
    }

    // Helper functions
    function getAdminToken() {
        return localStorage.getItem('admin_token') || '';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }
</script>
@endpush

