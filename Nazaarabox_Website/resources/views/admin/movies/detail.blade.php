@extends('layouts.admin')

@section('title', 'Movie Details - Admin Panel')

@push('styles')
<style>
    .stat-card {
        background-color: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 8px;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #2a2a2a;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .status-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }
    .status-active { background-color: #166534; color: #86efac; }
    .status-inactive { background-color: #991b1b; color: #fca5a5; }
    .status-pending { background-color: #854d0e; color: #fde047; }
    
    /* Drag and Drop Styles */
    .embed-item {
        cursor: move;
        transition: all 0.2s ease;
        position: relative;
    }
    .embed-item.dragging {
        opacity: 0.5;
        transform: scale(0.95);
    }
    .embed-item.drag-over {
        border-top: 3px solid #dc2626;
        margin-top: 8px;
    }
    .drag-handle {
        cursor: grab;
        color: #9ca3af;
        padding: 4px 8px;
        display: inline-flex;
        align-items: center;
    }
    .drag-handle:active {
        cursor: grabbing;
    }
    .drag-handle:hover {
        color: #dc2626;
        background-color: rgba(220, 38, 38, 0.1);
    }
    
    .embed-item:active {
        cursor: grabbing;
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
                    <a href="/admin/movies" style="color: #9ca3af; text-decoration: none;">← Back to Movies</a>
                    <h1 style="font-size: 20px; font-weight: bold; color: white; margin: 0;">Movie Details</h1>
                </div>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <button id="edit-btn" onclick="toggleEditMode()" class="flex items-center gap-2 bg-transparent border border-gray-600 text-white px-4 py-2 rounded hover:bg-dark-700 transition-colors text-sm">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span id="edit-btn-text">Edit Info</span>
                    </button>
                    <button id="save-btn" onclick="saveMovie()" class="flex items-center gap-2 bg-primary-600 hover:bg-red-700 text-white px-4 py-2 rounded transition-colors text-sm" style="display: none;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Save Changes</span>
                    </button>
                    <button id="cancel-btn" onclick="cancelEdit()" class="flex items-center gap-2 bg-transparent border border-gray-600 text-white px-4 py-2 rounded hover:bg-dark-700 transition-colors text-sm" style="display: none;">
                        <span>Cancel</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main style="max-width: 1280px; margin: 0 auto; padding: 32px 16px;">
        <!-- Loading State -->
        <div id="loading-state" style="display: flex; justify-content: center; align-items: center; min-height: 400px;">
            <div class="spinner"></div>
        </div>

        <!-- Movie Content -->
        <div id="movie-content" style="display: none;">
            <!-- Hero Section -->
            <div class="stat-card p-6 mb-6">
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 24px;">
                    <div>
                        <img 
                            id="movie-poster" 
                            src="/images/placeholder.svg" 
                            alt="Movie Poster"
                            style="width: 100%; aspect-ratio: 2/3; object-fit: cover; border-radius: 8px;"
                        >
                    </div>
                    <div>
                        <h2 id="movie-title" style="font-size: 28px; font-weight: bold; color: white; margin-bottom: 8px;">Loading...</h2>
                        <div style="display: flex; gap: 16px; margin-bottom: 16px; flex-wrap: wrap;">
                            <span id="movie-year" style="color: #9ca3af;">-</span>
                            <span id="movie-runtime" style="color: #9ca3af;">-</span>
                            <span id="movie-rating" style="color: #fbbf24;">⭐ -</span>
                        </div>
                        <p id="movie-overview" style="color: #d1d5db; line-height: 1.6; margin-bottom: 16px;">Loading description...</p>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <span id="movie-status-badge" class="status-badge">-</span>
                            <span id="movie-featured-badge" style="background-color: #854d0e; color: #fde047; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; display: none;">Featured</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="stat-card p-6">
                    <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Views</p>
                    <p style="font-size: 24px; font-weight: bold; color: white;" id="stat-views">0</p>
                </div>
                <div class="stat-card p-6">
                    <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Vote Average</p>
                    <p style="font-size: 24px; font-weight: bold; color: #fbbf24;" id="stat-vote">0</p>
                </div>
                <div class="stat-card p-6">
                    <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Vote Count</p>
                    <p style="font-size: 24px; font-weight: bold; color: white;" id="stat-vote-count">0</p>
                </div>
                <div class="stat-card p-6">
                    <p style="font-size: 14px; color: #9ca3af; margin-bottom: 8px;">Popularity</p>
                    <p style="font-size: 24px; font-weight: bold; color: white;" id="stat-popularity">0</p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="stat-card mb-6">
                <div style="display: flex; border-bottom: 1px solid #2a2a2a;">
                    <button 
                        onclick="showTab('info')" 
                        id="tab-info-btn"
                        class="px-6 py-3 font-medium transition-colors"
                        style="background-color: #2a2a2a; color: white; border-bottom: 2px solid #dc2626;"
                    >
                        Information
                    </button>
                    <button 
                        onclick="showTab('embeds')" 
                        id="tab-embeds-btn"
                        class="px-6 py-3 font-medium transition-colors"
                        style="background-color: transparent; color: #9ca3af; border-bottom: 2px solid transparent;"
                    >
                        Embeds
                    </button>
                    <button 
                        onclick="showTab('downloads')" 
                        id="tab-downloads-btn"
                        class="px-6 py-3 font-medium transition-colors"
                        style="background-color: transparent; color: #9ca3af; border-bottom: 2px solid transparent;"
                    >
                        Downloads
                    </button>
                </div>

                <!-- Info Tab -->
                <div id="tab-info" style="padding: 24px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                        <div>
                            <h3 style="font-size: 18px; font-weight: 600; color: white; margin-bottom: 16px;">Basic Information</h3>
                            <div class="info-row">
                                <span style="color: #9ca3af; min-width: 120px;">Title</span>
                                <div style="flex: 1; display: flex; align-items: center;">
                                    <input type="text" id="edit-title" style="background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 6px 12px; border-radius: 4px; width: 100%; max-width: 400px; display: none; box-sizing: border-box; pointer-events: auto;">
                                    <span id="display-title" style="color: white; font-weight: 500;"></span>
                                </div>
                            </div>
                            <div class="info-row">
                                <span style="color: #9ca3af; min-width: 120px;">Slug</span>
                                <div style="flex: 1; display: flex; align-items: center;">
                                    <input type="text" id="edit-slug" style="background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 6px 12px; border-radius: 4px; width: 100%; max-width: 400px; display: none; box-sizing: border-box; pointer-events: auto;">
                                    <span id="display-slug" style="color: white; font-weight: 500;"></span>
                                </div>
                            </div>
                            <div class="info-row">
                                <span style="color: #9ca3af; min-width: 120px;">Status</span>
                                <div style="flex: 1; display: flex; align-items: center;">
                                    <select id="edit-status" style="background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 6px 12px; border-radius: 4px; width: 100%; max-width: 400px; display: none; box-sizing: border-box; pointer-events: auto;">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                    <span id="display-status" style="color: white; font-weight: 500;"></span>
                                </div>
                            </div>
                            <div class="info-row">
                                <span style="color: #9ca3af; min-width: 120px;">Featured</span>
                                <div style="flex: 1; display: flex; align-items: center;">
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; pointer-events: auto;" id="edit-featured-label" style="display: none;">
                                        <input type="checkbox" id="edit-featured" style="width: 18px; height: 18px; cursor: pointer; pointer-events: auto;">
                                        <span style="color: white;">Featured</span>
                                    </label>
                                    <span id="display-featured" style="color: white; font-weight: 500;"></span>
                                </div>
                            </div>
                            <div class="info-row">
                                <span style="color: #9ca3af; min-width: 120px;">Release Date</span>
                                <div style="flex: 1; display: flex; align-items: center;">
                                    <input type="date" id="edit-release-date" style="background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 6px 12px; border-radius: 4px; width: 100%; max-width: 400px; display: none; box-sizing: border-box; pointer-events: auto;">
                                    <span id="display-release-date" style="color: white; font-weight: 500;"></span>
                                </div>
                            </div>
                            <div class="info-row">
                                <span style="color: #9ca3af; min-width: 120px;">Runtime (minutes)</span>
                                <div style="flex: 1; display: flex; align-items: center;">
                                    <input type="number" id="edit-runtime" style="background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 6px 12px; border-radius: 4px; width: 100%; max-width: 400px; display: none; box-sizing: border-box; pointer-events: auto;">
                                    <span id="display-runtime" style="color: white; font-weight: 500;"></span>
                                </div>
                            </div>
                            <div class="info-row" style="align-items: start;">
                                <span style="color: #9ca3af; min-width: 120px; padding-top: 6px;">Overview</span>
                                <div style="flex: 1; display: flex; align-items: start;">
                                    <textarea id="edit-overview" rows="4" style="background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 6px 12px; border-radius: 4px; width: 100%; max-width: 400px; display: none; resize: vertical; box-sizing: border-box; pointer-events: auto; font-family: inherit;"></textarea>
                                    <span id="display-overview" style="color: white; font-weight: 500; white-space: pre-wrap;"></span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h3 style="font-size: 18px; font-weight: 600; color: white; margin-bottom: 16px;">Additional Information</h3>
                            <div class="info-row">
                                <span style="color: #9ca3af; min-width: 120px;">Original Language</span>
                                <div style="flex: 1; display: flex; align-items: center;">
                                    <input type="text" id="edit-original-language" placeholder="e.g., en, fr, hi" maxlength="5" style="background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 6px 12px; border-radius: 4px; width: 100%; max-width: 400px; display: none; box-sizing: border-box; pointer-events: auto;">
                                    <span id="display-language" style="color: white; font-weight: 500;"></span>
                                </div>
                            </div>
                            <div class="info-row">
                                <span style="color: #9ca3af; min-width: 120px;">Dubbing Language</span>
                                <div style="flex: 1; display: flex; align-items: center;">
                                    <select id="edit-dubbing-language" style="background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 6px 12px; border-radius: 4px; width: 100%; max-width: 400px; display: none; box-sizing: border-box; pointer-events: auto;">
                                        <option value="">None</option>
                                    </select>
                                    <span id="display-dubbing-language" style="color: white; font-weight: 500;"></span>
                                </div>
                            </div>
                            <div class="info-row" style="align-items: start;">
                                <span style="color: #9ca3af; min-width: 120px; padding-top: 6px;">Genres</span>
                                <div style="flex: 1; display: flex; align-items: start;">
                                    <div id="edit-genres-wrapper" style="display: none; flex-wrap: wrap; gap: 8px; width: 100%; max-width: 400px; pointer-events: auto;">
                                        <!-- Genres checkboxes will be inserted here -->
                                    </div>
                                    <span id="display-genres" style="color: white; font-weight: 500;"></span>
                                </div>
                            </div>
                            <div class="info-row">
                                <span style="color: #9ca3af; min-width: 120px;">Category</span>
                                <div style="flex: 1; display: flex; align-items: center;">
                                    <select id="edit-category" style="background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 6px 12px; border-radius: 4px; width: 100%; max-width: 400px; display: none; box-sizing: border-box; pointer-events: auto;">
                                        <option value="">None</option>
                                    </select>
                                    <span id="display-category" style="color: white; font-weight: 500;"></span>
                                </div>
                            </div>
                            <div class="info-row">
                                <span style="color: #9ca3af;">TMDB ID</span>
                                <span id="display-tmdb-id" style="color: white; font-weight: 500;"></span>
                            </div>
                            <div class="info-row">
                                <span style="color: #9ca3af;">Created At</span>
                                <span id="display-created" style="color: white; font-weight: 500;"></span>
                            </div>
                            <div class="info-row">
                                <span style="color: #9ca3af;">Updated At</span>
                                <span id="display-updated" style="color: white; font-weight: 500;"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Embeds Tab -->
                <div id="tab-embeds" style="padding: 24px; display: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <div>
                            <h3 style="font-size: 18px; font-weight: 600; color: white; margin-bottom: 4px;">Embeds</h3>
                            <p style="color: #9ca3af; font-size: 12px; margin: 0;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                </svg>
                                Drag and drop to reorder embeds
                            </p>
                        </div>
                        <div style="display: flex; gap: 12px;">
                            <button onclick="autoAddDefaultEmbeds()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition-colors text-sm flex items-center gap-2">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <span>Auto-Add Default Embeds</span>
                            </button>
                            <button onclick="openEmbedModal()" class="bg-primary-600 hover:bg-red-700 text-white px-4 py-2 rounded transition-colors text-sm">
                                Add Embed
                            </button>
                        </div>
                    </div>
                    <div id="embeds-list" style="position: relative;"></div>
                </div>

                <!-- Downloads Tab -->
                <div id="tab-downloads" style="padding: 24px; display: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 style="font-size: 18px; font-weight: 600; color: white;">Downloads</h3>
                        <button onclick="openDownloadModal()" class="bg-primary-600 hover:bg-red-700 text-white px-4 py-2 rounded transition-colors text-sm">
                            Add Download
                        </button>
                    </div>
                    <div id="downloads-list"></div>
                </div>
            </div>
        </div>
    </main>

    <!-- Auto-Add Embeds Modal -->
    <div id="auto-add-embeds-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center; overflow-y: auto;">
        <div style="background-color: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 8px; padding: 24px; max-width: 500px; width: 90%; margin: 40px auto; position: relative; z-index: 1001;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 20px; font-weight: 600; color: white;">Auto-Add Default Embeds</h3>
                <button onclick="closeAutoAddEmbedsModal()" style="background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 24px;">&times;</button>
            </div>
            <p style="color: #9ca3af; margin-bottom: 20px; font-size: 14px;">
                Select which embeds to automatically add. The TMDB ID (or movie ID if TMDB ID is not available) will be automatically inserted into the embed URLs.
            </p>
            <div style="margin-bottom: 24px;">
                <label style="display: flex; items-center; gap: 12px; padding: 12px; background-color: #2a2a2a; border: 1px solid #3a3a3a; border-radius: 6px; margin-bottom: 12px; cursor: pointer; transition: all 0.2s;">
                    <input type="checkbox" id="auto-add-vidlink" value="vidlink" 
                           style="width: 18px; height: 18px; cursor: pointer; accent-color: #dc2626;">
                    <div style="flex: 1;">
                        <div style="color: white; font-weight: 500; margin-bottom: 4px;">VidLink</div>
                                        <code style="color: #9ca3af; font-size: 12px; background-color: #1a1a1a; padding: 4px 8px; border-radius: 4px; display: block;">https://vidlink.pro/movie/{tmdb_id}</code>
                    </div>
                </label>
                <label style="display: flex; items-center; gap: 12px; padding: 12px; background-color: #2a2a2a; border: 1px solid #3a3a3a; border-radius: 6px; margin-bottom: 12px; cursor: pointer; transition: all 0.2s;">
                    <input type="checkbox" id="auto-add-vidsrc" value="vidsrc" 
                           style="width: 18px; height: 18px; cursor: pointer; accent-color: #dc2626;">
                    <div style="flex: 1;">
                        <div style="color: white; font-weight: 500; margin-bottom: 4px;">VidSrc</div>
                        <code style="color: #9ca3af; font-size: 12px; background-color: #1a1a1a; padding: 4px 8px; border-radius: 4px; display: block;">https://vidsrc.icu/embed/movie/{tmdb_id}</code>
                    </div>
                </label>
                <label style="display: flex; items-center; gap: 12px; padding: 12px; background-color: #2a2a2a; border: 1px solid #3a3a3a; border-radius: 6px; margin-bottom: 12px; cursor: pointer; transition: all 0.2s;">
                    <input type="checkbox" id="auto-add-vidfast" value="vidfast" 
                           style="width: 18px; height: 18px; cursor: pointer; accent-color: #dc2626;">
                    <div style="flex: 1;">
                        <div style="color: white; font-weight: 500; margin-bottom: 4px;">VidFast</div>
                        <code style="color: #9ca3af; font-size: 12px; background-color: #1a1a1a; padding: 4px 8px; border-radius: 4px; display: block;">https://vidfast.pro/movie/{tmdb_id}</code>
                    </div>
                </label>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button onclick="closeAutoAddEmbedsModal()" 
                        style="background-color: #3a3a3a; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                    Cancel
                </button>
                <button onclick="confirmAutoAddEmbeds()" 
                        style="background-color: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                    Add Selected
                </button>
            </div>
        </div>
    </div>

    <!-- Embed Modal -->
    <div id="embed-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center; overflow-y: auto;">
        <div style="background-color: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 8px; padding: 24px; max-width: 500px; width: 90%; margin: 40px auto; position: relative; z-index: 1001;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 20px; font-weight: 600; color: white;" id="embed-modal-title">Add Embed</h3>
                <button onclick="closeEmbedModal()" style="background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 24px;">&times;</button>
            </div>
            <form id="embed-form" onsubmit="saveEmbed(event)">
                <input type="hidden" id="embed-id" value="">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Server Name *</label>
                    <input type="text" id="embed-server-name" required style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 8px 12px; border-radius: 4px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Language</label>
                    <select id="embed-language" style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 8px 12px; border-radius: 4px; box-sizing: border-box; pointer-events: auto;">
                        <option value="">None</option>
                    </select>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Embed URL/ID/Iframe *</label>
                    <textarea id="embed-url" required rows="4" placeholder="Enter embed URL, YouTube/Dailymotion ID, or iframe HTML" style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 8px 12px; border-radius: 4px; resize: vertical; box-sizing: border-box; pointer-events: auto; font-family: inherit;"></textarea>
                    <p style="color: #6b7280; font-size: 12px; margin-top: 4px;">Supports: YouTube URLs/IDs, Dailymotion URLs/IDs, iframe HTML, or direct embed URLs</p>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Priority</label>
                    <input type="number" id="embed-priority" value="0" min="0" max="999" style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 8px 12px; border-radius: 4px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: flex; align-items: center; gap: 8px; color: #9ca3af; cursor: pointer; pointer-events: auto;">
                        <input type="checkbox" id="embed-is-active" checked style="width: 18px; height: 18px; cursor: pointer; pointer-events: auto;">
                        <span>Active</span>
                    </label>
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 8px; color: #9ca3af; cursor: pointer; pointer-events: auto;">
                        <input type="checkbox" id="embed-requires-ad" style="width: 18px; height: 18px; cursor: pointer; pointer-events: auto;">
                        <span>Requires Interstitial Ad</span>
                    </label>
                    <p style="color: #6b7280; font-size: 12px; margin-top: 4px; margin-left: 26px;">If checked, users will see an interstitial ad before watching this embed</p>
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end; pointer-events: auto;">
                    <button type="button" onclick="closeEmbedModal()" style="background-color: #3a3a3a; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; pointer-events: auto;">Cancel</button>
                    <button type="submit" style="background-color: #dc2626; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; pointer-events: auto;">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Download Modal -->
    <div id="download-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center; overflow-y: auto;">
        <div style="background-color: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 8px; padding: 24px; max-width: 500px; width: 90%; margin: 40px auto; position: relative; z-index: 1001;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 20px; font-weight: 600; color: white;" id="download-modal-title">Add Download</h3>
                <button onclick="closeDownloadModal()" style="background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 24px;">&times;</button>
            </div>
            <form id="download-form" onsubmit="saveDownload(event)">
                <input type="hidden" id="download-id" value="">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Server Name *</label>
                    <input type="text" id="download-server-name" required style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 8px 12px; border-radius: 4px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Download URL *</label>
                    <input type="url" id="download-url" required style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 8px 12px; border-radius: 4px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Quality</label>
                    <input type="text" id="download-quality" placeholder="e.g., 1080p, 720p" style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 8px 12px; border-radius: 4px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Size</label>
                    <input type="text" id="download-size" placeholder="e.g., 2.5 GB, 1.2 GB" style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 8px 12px; border-radius: 4px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Priority</label>
                    <input type="number" id="download-priority" value="0" min="0" max="999" style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 8px 12px; border-radius: 4px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 8px; color: #9ca3af; cursor: pointer; pointer-events: auto;">
                        <input type="checkbox" id="download-is-active" checked style="width: 18px; height: 18px; cursor: pointer; pointer-events: auto;">
                        <span>Active</span>
                    </label>
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end; pointer-events: auto;">
                    <button type="button" onclick="closeDownloadModal()" style="background-color: #3a3a3a; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; pointer-events: auto;">Cancel</button>
                    <button type="submit" style="background-color: #dc2626; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; pointer-events: auto;">Save</button>
                </div>
            </form>
        </div>
    </div>
            </div>
        </div>
    </main>
</div>

@push('scripts')
<script>
    let movie = null;
    let currentTab = 'info';

    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Helper function to unescape HTML (for displaying in textarea)
    function unescapeHtml(html) {
        if (!html) return '';
        const div = document.createElement('div');
        div.innerHTML = html;
        return div.textContent || div.innerText || html;
    }

    async function fetchMovie() {
        const token = localStorage.getItem('adminAccessToken');
        if (!token) {
            window.location.href = '/admin/login';
            return;
        }

        const movieId = {{ $id }};

        try {
            document.getElementById('loading-state').style.display = 'flex';
            document.getElementById('movie-content').style.display = 'none';

            const response = await fetch(`${API_BASE_URL}/admin/movies/${movieId}`, {
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
                throw new Error('Failed to fetch movie');
            }

            const data = await response.json();
            if (data.success) {
                movie = data.data.movie;
                renderMovie();
            }
        } catch (error) {
            console.error('Error fetching movie:', error);
        } finally {
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('movie-content').style.display = 'block';
        }
    }

    function renderMovie() {
        if (!movie) return;

        // Hero Section
        const posterPath = movie.poster_path 
            ? (movie.poster_path.startsWith('http') ? movie.poster_path : `https://image.tmdb.org/t/p/w500${movie.poster_path}`)
            : '/images/placeholder.svg';
        document.getElementById('movie-poster').src = posterPath;
        document.getElementById('movie-title').textContent = movie.title || 'Untitled';
        document.getElementById('movie-year').textContent = movie.release_date ? new Date(movie.release_date).getFullYear() : 'N/A';
        document.getElementById('movie-runtime').textContent = movie.runtime ? `${movie.runtime} min` : 'N/A';
        document.getElementById('movie-rating').textContent = `⭐ ${(movie.vote_average || 0).toFixed(1)}`;
        document.getElementById('movie-overview').textContent = movie.overview || 'No description available.';
        
        // Status badges
        const status = movie.status || 'pending';
        const statusBadge = document.getElementById('movie-status-badge');
        statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        statusBadge.className = `status-badge status-${status}`;

        if (movie.is_featured) {
            document.getElementById('movie-featured-badge').style.display = 'inline-block';
        }

        // Stats
        document.getElementById('stat-views').textContent = (movie.view_count || 0).toLocaleString();
        document.getElementById('stat-vote').textContent = (movie.vote_average || 0).toFixed(1);
        document.getElementById('stat-vote-count').textContent = (movie.vote_count || 0).toLocaleString();
        document.getElementById('stat-popularity').textContent = (movie.popularity || 0).toFixed(1);

        // Info Tab
        document.getElementById('display-title').textContent = movie.title || 'N/A';
        document.getElementById('display-slug').textContent = movie.slug || 'N/A';
        document.getElementById('display-status').textContent = status.charAt(0).toUpperCase() + status.slice(1);
        document.getElementById('display-featured').textContent = movie.is_featured ? 'Yes' : 'No';
        document.getElementById('display-release-date').textContent = movie.release_date ? new Date(movie.release_date).toLocaleDateString() : 'N/A';
        document.getElementById('display-runtime').textContent = movie.runtime ? `${movie.runtime} min` : 'N/A';
        document.getElementById('display-overview').textContent = movie.overview || 'N/A';

        // Edit fields
        document.getElementById('edit-title').value = movie.title || '';
        document.getElementById('edit-slug').value = movie.slug || '';
        document.getElementById('edit-status').value = status;
        document.getElementById('edit-featured').checked = movie.is_featured || false;
        document.getElementById('edit-release-date').value = movie.release_date || '';
        document.getElementById('edit-runtime').value = movie.runtime || '';
        document.getElementById('edit-overview').value = movie.overview || '';

        // Additional Info
        document.getElementById('display-language').textContent = movie.original_language || 'N/A';
        document.getElementById('display-dubbing-language').textContent = movie.dubbing_language ? movie.dubbing_language.name : 'N/A';
        document.getElementById('display-genres').textContent = movie.genres && movie.genres.length > 0 
            ? movie.genres.map(g => g.name).join(', ') 
            : 'N/A';
        document.getElementById('display-category').textContent = movie.category ? movie.category.name : 'N/A';
        document.getElementById('display-tmdb-id').textContent = movie.tmdb_id || 'N/A';
        document.getElementById('display-created').textContent = movie.created_at ? new Date(movie.created_at).toLocaleString() : 'N/A';
        document.getElementById('display-updated').textContent = movie.updated_at ? new Date(movie.updated_at).toLocaleString() : 'N/A';

        // Populate edit fields for additional info
        document.getElementById('edit-original-language').value = movie.original_language || '';
        if (movie.dubbing_language) {
            document.getElementById('edit-dubbing-language').value = movie.dubbing_language.id;
        } else {
            document.getElementById('edit-dubbing-language').value = '';
        }
        if (movie.category) {
            document.getElementById('edit-category').value = movie.category.id;
        } else {
            document.getElementById('edit-category').value = '';
        }
        
        // Check selected genres
        if (utilsData.genres && movie.genres) {
            utilsData.genres.forEach(genre => {
                const checkbox = document.getElementById(`genre-${genre.id}`);
                if (checkbox) {
                    checkbox.checked = movie.genres.some(mg => mg.id === genre.id);
                }
            });
        }

        // Load embeds and downloads
        loadEmbeds();
        loadDownloads();
    }

    function showTab(tab) {
        currentTab = tab;
        
        // Hide all tabs
        document.getElementById('tab-info').style.display = 'none';
        document.getElementById('tab-embeds').style.display = 'none';
        document.getElementById('tab-downloads').style.display = 'none';
        
        // Reset all tab buttons
        document.getElementById('tab-info-btn').style.backgroundColor = 'transparent';
        document.getElementById('tab-info-btn').style.color = '#9ca3af';
        document.getElementById('tab-info-btn').style.borderBottomColor = 'transparent';
        
        document.getElementById('tab-embeds-btn').style.backgroundColor = 'transparent';
        document.getElementById('tab-embeds-btn').style.color = '#9ca3af';
        document.getElementById('tab-embeds-btn').style.borderBottomColor = 'transparent';
        
        document.getElementById('tab-downloads-btn').style.backgroundColor = 'transparent';
        document.getElementById('tab-downloads-btn').style.color = '#9ca3af';
        document.getElementById('tab-downloads-btn').style.borderBottomColor = 'transparent';
        
        // Show selected tab
        if (tab === 'info') {
            document.getElementById('tab-info').style.display = 'block';
            document.getElementById('tab-info-btn').style.backgroundColor = '#2a2a2a';
            document.getElementById('tab-info-btn').style.color = 'white';
            document.getElementById('tab-info-btn').style.borderBottomColor = '#dc2626';
        } else if (tab === 'embeds') {
            document.getElementById('tab-embeds').style.display = 'block';
            document.getElementById('tab-embeds-btn').style.backgroundColor = '#2a2a2a';
            document.getElementById('tab-embeds-btn').style.color = 'white';
            document.getElementById('tab-embeds-btn').style.borderBottomColor = '#dc2626';
        } else if (tab === 'downloads') {
            document.getElementById('tab-downloads').style.display = 'block';
            document.getElementById('tab-downloads-btn').style.backgroundColor = '#2a2a2a';
            document.getElementById('tab-downloads-btn').style.color = 'white';
            document.getElementById('tab-downloads-btn').style.borderBottomColor = '#dc2626';
        }
    }

    async function loadEmbeds() {
        const token = localStorage.getItem('adminAccessToken');
        const movieId = {{ $id }};

        try {
            const response = await fetch(`${API_BASE_URL}/embeds/movies/${movieId}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'X-API-Key': API_KEY,
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            let embeds = data.success ? (data.data || []) : [];
            
            // Sort embeds by priority (lower number = higher priority)
            embeds = embeds.sort((a, b) => (a.priority || 0) - (b.priority || 0));

            const embedsList = document.getElementById('embeds-list');
            if (embeds.length === 0) {
                embedsList.innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 32px;">No embeds available</p>';
            } else {
                embedsList.innerHTML = '';
                embeds.forEach((embed, index) => {
                    const embedDiv = document.createElement('div');
                    embedDiv.className = 'embed-item';
                    embedDiv.draggable = true;
                    embedDiv.dataset.embedId = embed.id;
                    embedDiv.dataset.priority = embed.priority || index;
                    embedDiv.style.cssText = `background-color: #2a2a2a; border: 1px solid #3a3a3a; border-radius: 8px; padding: 16px; margin-bottom: 12px; ${embed.is_active === false ? 'opacity: 0.6;' : ''}`;
                    
                    embedDiv.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                    <span class="drag-handle" style="cursor: grab; user-select: none; display: inline-flex; align-items: center; padding: 4px; margin-right: 4px; border-radius: 4px;">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                        </svg>
                                    </span>
                                    <h4 style="color: white; font-weight: 600; margin: 0;">${embed.server_name || 'Server'}</h4>
                                    ${embed.is_active === false ? '<span style="background-color: #991b1b; color: #fca5a5; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">Inactive</span>' : ''}
                                    ${embed.requires_ad === true ? '<span style="background-color: #dc2626; color: #fca5a5; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">Requires Ad</span>' : ''}
                                    <span style="background-color: #3a3a3a; color: #9ca3af; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; margin-left: auto;">Priority: ${embed.priority || index}</span>
                                </div>
                                <div style="display: flex; gap: 16px; margin-top: 4px; margin-left: 24px;">
                                    ${embed.language ? `<p style="color: #9ca3af; font-size: 14px; margin: 0;">Language: ${embed.language.name || 'N/A'}</p>` : ''}
                                </div>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                    <button onclick="event.stopPropagation(); editEmbed(${embed.id})" class="bg-dark-600 hover:bg-dark-500 text-white px-3 py-1 rounded text-sm transition-colors" style="background-color: #3a3a3a; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer;">
                                    Edit
                                </button>
                                <button onclick="event.stopPropagation(); deleteEmbed(${embed.id})" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition-colors" style="padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer;">
                                    Delete
                                </button>
                            </div>
                        </div>
                        <div style="background-color: #1a1a1a; padding: 8px; border-radius: 4px; margin-top: 8px;">
                            <p style="color: #9ca3af; font-size: 12px; margin-bottom: 4px;">Embed URL/Iframe:</p>
                            ${embed.embed_url && (embed.embed_url.includes('<iframe') || embed.embed_url.includes('<IFRAME')) ? 
                                `<div style="color: #d1d5db; font-size: 12px; white-space: pre-wrap; word-break: break-all; margin: 0; max-height: 150px; overflow-y: auto; font-family: monospace;">${escapeHtml(embed.embed_url)}</div>` :
                                `<p style="color: #d1d5db; font-size: 14px; word-break: break-all; margin: 0;">${embed.embed_url || 'N/A'}</p>`
                            }
                        </div>
                    `;
                    
                    // Add drag event listeners
                    embedDiv.addEventListener('dragstart', handleDragStart);
                    embedDiv.addEventListener('dragover', handleDragOver);
                    embedDiv.addEventListener('drop', handleDrop);
                    embedDiv.addEventListener('dragend', handleDragEnd);
                    embedDiv.addEventListener('dragleave', handleDragLeave);
                    
                    // Prevent drag when clicking on buttons
                    embedDiv.querySelectorAll('button').forEach(btn => {
                        btn.addEventListener('mousedown', (e) => {
                            e.stopPropagation();
                        });
                    });
                    
                    embedsList.appendChild(embedDiv);
                });
            }
            
            // Store embeds for priority updates
            window.currentEmbeds = embeds;
        } catch (error) {
            console.error('Error loading embeds:', error);
            document.getElementById('embeds-list').innerHTML = '<p style="color: #f87171; text-align: center; padding: 32px;">Error loading embeds</p>';
        }
    }

    // Drag and Drop Functions
    let draggedElement = null;
    let draggedEmbedId = null;

    function handleDragStart(e) {
        // Don't start drag if clicking on buttons or interactive elements
        if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
            e.preventDefault();
            return false;
        }
        
        draggedElement = this;
        draggedEmbedId = parseInt(this.dataset.embedId);
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
    }

    function handleDragOver(e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
        e.dataTransfer.dropEffect = 'move';
        
        if (this !== draggedElement && this.classList.contains('embed-item')) {
            this.classList.add('drag-over');
        }
        return false;
    }

    function handleDragLeave(e) {
        this.classList.remove('drag-over');
    }

    async function handleDrop(e) {
        if (e.stopPropagation) {
            e.stopPropagation();
        }
        
        this.classList.remove('drag-over');
        
        if (draggedElement !== this && this.classList.contains('embed-item')) {
            const embedsList = document.getElementById('embeds-list');
            const allItems = Array.from(embedsList.querySelectorAll('.embed-item'));
            const draggedIndex = allItems.indexOf(draggedElement);
            const targetIndex = allItems.indexOf(this);
            
            // Reorder DOM elements
            if (draggedIndex < targetIndex) {
                embedsList.insertBefore(draggedElement, this.nextSibling);
            } else {
                embedsList.insertBefore(draggedElement, this);
            }
            
            // Update priorities based on new order
            await updateEmbedPriorities();
        }
        
        return false;
    }

    function handleDragEnd(e) {
        this.classList.remove('dragging');
        
        // Remove drag-over class from all items
        document.querySelectorAll('.embed-item').forEach(item => {
            item.classList.remove('drag-over');
        });
        
        draggedElement = null;
        draggedEmbedId = null;
    }

    async function updateEmbedPriorities() {
        const token = localStorage.getItem('adminAccessToken');
        const movieId = {{ $id }};
        const embedsList = document.getElementById('embeds-list');
        const allItems = Array.from(embedsList.querySelectorAll('.embed-item'));
        
        try {
            // Update priorities based on new order (0 = first, 1 = second, etc.)
            const updatePromises = allItems.map(async (item, index) => {
                const embedId = parseInt(item.dataset.embedId);
                const newPriority = index;
                
                // Only update if priority changed
                if (parseInt(item.dataset.priority) !== newPriority) {
                    try {
                        const response = await fetch(`${API_BASE_URL}/embeds/movies/${movieId}/${embedId}`, {
                            method: 'PATCH',
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'X-API-Key': API_KEY,
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                priority: newPriority
                            })
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            // Update dataset priority
                            item.dataset.priority = newPriority;
                            // Update priority display in the item
                            const prioritySpan = item.querySelector('span[style*="Priority:"]');
                            if (prioritySpan) {
                                prioritySpan.textContent = `Priority: ${newPriority}`;
                            }
                        }
                        return result.success;
                    } catch (error) {
                        console.error(`Error updating priority for embed ${embedId}:`, error);
                        return false;
                    }
                }
                return true;
            });
            
            await Promise.all(updatePromises);
            console.log('Embed priorities updated successfully');
        } catch (error) {
            console.error('Error updating embed priorities:', error);
            alert('Error updating embed order. Please refresh the page.');
        }
    }

    async function loadDownloads() {
        const token = localStorage.getItem('adminAccessToken');
        const movieId = {{ $id }};

        try {
            const response = await fetch(`${API_BASE_URL}/downloads/movies/${movieId}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            const downloads = data.success ? (data.data || []) : [];

            const downloadsList = document.getElementById('downloads-list');
            if (downloads.length === 0) {
                downloadsList.innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 32px;">No downloads available</p>';
            } else {
                downloadsList.innerHTML = downloads.map(download => `
                    <div style="background-color: #2a2a2a; border: 1px solid #3a3a3a; border-radius: 8px; padding: 16px; margin-bottom: 12px; ${download.is_active === false ? 'opacity: 0.6;' : ''}">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                    <h4 style="color: white; font-weight: 600; margin: 0;">${download.server_name || 'Server'}</h4>
                                    ${download.is_active === false ? '<span style="background-color: #991b1b; color: #fca5a5; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">Inactive</span>' : ''}
                                </div>
                                <div style="display: flex; gap: 16px; margin-top: 4px;">
                                    <p style="color: #9ca3af; font-size: 14px; margin: 0;">Quality: ${download.quality || 'N/A'}</p>
                                    <p style="color: #9ca3af; font-size: 14px; margin: 0;">Size: ${download.size || 'N/A'}</p>
                                    <p style="color: #9ca3af; font-size: 14px; margin: 0;">Priority: ${download.priority || 0}</p>
                                </div>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <button onclick="editDownload(${download.id})" class="bg-dark-600 hover:bg-dark-500 text-white px-3 py-1 rounded text-sm transition-colors" style="background-color: #3a3a3a; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer;">
                                    Edit
                                </button>
                                <button onclick="deleteDownload(${download.id})" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition-colors" style="padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer;">
                                    Delete
                                </button>
                            </div>
                        </div>
                        <div style="background-color: #1a1a1a; padding: 8px; border-radius: 4px; margin-top: 8px;">
                            <p style="color: #9ca3af; font-size: 12px; margin-bottom: 4px;">Download URL:</p>
                            <p style="color: #d1d5db; font-size: 14px; word-break: break-all; margin: 0;">${download.download_url || 'N/A'}</p>
                        </div>
                    </div>
                `).join('');
            }
        } catch (error) {
            console.error('Error loading downloads:', error);
            document.getElementById('downloads-list').innerHTML = '<p style="color: #f87171; text-align: center; padding: 32px;">Error loading downloads</p>';
        }
    }

    // Embed Modal Functions
    function openEmbedModal(embedId = null) {
        const modal = document.getElementById('embed-modal');
        const form = document.getElementById('embed-form');
        const title = document.getElementById('embed-modal-title');
        
        if (embedId) {
            // Edit mode - find embed
            const embeds = Array.from(document.querySelectorAll('#embeds-list > div'));
            const embedElement = embeds.find(el => {
                const editBtn = el.querySelector('button[onclick*="editEmbed"]');
                if (editBtn) {
                    const match = editBtn.getAttribute('onclick').match(/editEmbed\((\d+)\)/);
                    return match && parseInt(match[1]) === embedId;
                }
                return false;
            });
            
            if (embedElement) {
                // Fetch current embed data
                fetch(`${API_BASE_URL}/embeds/movies/{{ $id }}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('adminAccessToken')}`,
                        'X-API-Key': API_KEY
                    }
                })
                .then(res => res.json())
                .then(data => {
                    const embed = data.data.find(e => e.id === embedId);
                    if (embed) {
                        document.getElementById('embed-id').value = embed.id;
                        document.getElementById('embed-server-name').value = embed.server_name || '';
                        // Preserve iframe HTML or show URL as-is
                        document.getElementById('embed-url').value = embed.embed_url || '';
                        document.getElementById('embed-language').value = embed.language_id || '';
                        document.getElementById('embed-priority').value = embed.priority || 0;
                        document.getElementById('embed-is-active').checked = embed.is_active !== false;
                        document.getElementById('embed-requires-ad').checked = embed.requires_ad === true;
                        title.textContent = 'Edit Embed';
                    }
                });
            }
        } else {
            // Add mode
            form.reset();
            document.getElementById('embed-id').value = '';
            document.getElementById('embed-priority').value = 0;
            document.getElementById('embed-is-active').checked = true;
            document.getElementById('embed-requires-ad').checked = false;
            title.textContent = 'Add Embed';
        }
        
        modal.style.display = 'flex';
    }

    function closeEmbedModal() {
        document.getElementById('embed-modal').style.display = 'none';
        document.getElementById('embed-form').reset();
        document.getElementById('embed-id').value = '';
    }

    async function saveEmbed(event) {
        event.preventDefault();
        
        const token = localStorage.getItem('adminAccessToken');
        const movieId = {{ $id }};
        const embedId = document.getElementById('embed-id').value;
        
        const data = {
            server_name: document.getElementById('embed-server-name').value,
            embed_url: document.getElementById('embed-url').value,
            language_id: document.getElementById('embed-language').value || null,
            priority: parseInt(document.getElementById('embed-priority').value) || 0,
            is_active: document.getElementById('embed-is-active').checked,
            requires_ad: document.getElementById('embed-requires-ad').checked
        };

        try {
            let response;
            if (embedId) {
                // Update
                response = await fetch(`${API_BASE_URL}/embeds/movies/${movieId}/${embedId}`, {
                    method: 'PATCH',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'X-API-Key': API_KEY,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
            } else {
                // Create
                response = await fetch(`${API_BASE_URL}/embeds/movies/${movieId}`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'X-API-Key': API_KEY,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
            }

            const result = await response.json();
            if (result.success) {
                closeEmbedModal();
                loadEmbeds();
                alert(result.message || 'Embed saved successfully');
            } else {
                alert(result.message || 'Error saving embed');
            }
        } catch (error) {
            console.error('Error saving embed:', error);
            alert('Error saving embed. Please try again.');
        }
    }

    function editEmbed(id) {
        openEmbedModal(id);
    }

    async function deleteEmbed(id) {
        if (!confirm('Are you sure you want to delete this embed?')) {
            return;
        }

        const token = localStorage.getItem('adminAccessToken');
        const movieId = {{ $id }};

        try {
            const response = await fetch(`${API_BASE_URL}/embeds/movies/${movieId}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'X-API-Key': API_KEY,
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();
            if (result.success) {
                loadEmbeds();
                alert('Embed deleted successfully');
            } else {
                alert(result.message || 'Error deleting embed');
            }
        } catch (error) {
            console.error('Error deleting embed:', error);
            alert('Error deleting embed. Please try again.');
        }
    }

    function autoAddDefaultEmbeds() {
        // Reset checkboxes
        document.getElementById('auto-add-vidlink').checked = false;
        document.getElementById('auto-add-vidsrc').checked = false;
        document.getElementById('auto-add-vidfast').checked = false;
        
        // Show modal
        document.getElementById('auto-add-embeds-modal').style.display = 'flex';
    }

    function closeAutoAddEmbedsModal() {
        document.getElementById('auto-add-embeds-modal').style.display = 'none';
    }

    async function confirmAutoAddEmbeds() {
        // Get selected embed types
        const selectedEmbeds = [];
        if (document.getElementById('auto-add-vidlink').checked) {
            selectedEmbeds.push('vidlink');
        }
        if (document.getElementById('auto-add-vidsrc').checked) {
            selectedEmbeds.push('vidsrc');
        }
        if (document.getElementById('auto-add-vidfast').checked) {
            selectedEmbeds.push('vidfast');
        }

        if (selectedEmbeds.length === 0) {
            alert('Please select at least one embed to add.');
            return;
        }

        // Close modal
        closeAutoAddEmbedsModal();

        const token = localStorage.getItem('adminAccessToken');
        const movieId = {{ $id }};
        
        // Use TMDB ID if available, otherwise fallback to movie ID
        const embedId = (movie && movie.tmdb_id) ? movie.tmdb_id : movieId;

        const embedConfigs = {
            'vidlink': {
                server_name: 'VidLink',
                embed_url: `https://vidlink.pro/movie/${embedId}`,
                priority: 1,
            },
            'vidsrc': {
                server_name: 'VidSrc',
                embed_url: `https://vidsrc.icu/embed/movie/${embedId}`,
                priority: 2,
            },
            'vidfast': {
                server_name: 'VidFast',
                embed_url: `https://vidfast.pro/movie/${embedId}`,
                priority: 3,
            },
        };

        // Find and disable button
        const btn = document.querySelector('button[onclick="autoAddDefaultEmbeds()"]');
        const originalText = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span>Adding...</span>';
        }

        try {
            let successCount = 0;
            let errorCount = 0;
            const errors = [];

            for (const embedType of selectedEmbeds) {
                if (embedConfigs[embedType]) {
                    const embed = {
                        ...embedConfigs[embedType],
                        is_active: true,
                    };

                    try {
                        const response = await fetch(`${API_BASE_URL}/embeds/movies/${movieId}`, {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'X-API-Key': API_KEY,
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(embed)
                        });

                        const result = await response.json();
                        if (result.success) {
                            successCount++;
                        } else {
                            errorCount++;
                            errors.push(`${embed.server_name}: ${result.message || 'Error'}`);
                        }
                    } catch (error) {
                        errorCount++;
                        errors.push(`${embed.server_name}: ${error.message}`);
                    }
                }
            }

            // Re-enable button
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }

            // Reload embeds list
            await loadEmbeds();

            // Show result message
            if (successCount > 0 && errorCount === 0) {
                alert(`Successfully added ${successCount} embed(s)!`);
            } else if (successCount > 0 && errorCount > 0) {
                alert(`Added ${successCount} embed(s), but ${errorCount} failed:\n\n${errors.join('\n')}`);
            } else {
                alert(`Failed to add embeds:\n\n${errors.join('\n')}`);
            }
        } catch (error) {
            console.error('Error adding default embeds:', error);
            alert('Error adding default embeds. Please try again.');
            // Re-enable button
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    }

    // Download Modal Functions
    function openDownloadModal(downloadId = null) {
        const modal = document.getElementById('download-modal');
        const form = document.getElementById('download-form');
        const title = document.getElementById('download-modal-title');
        
        if (downloadId) {
            // Edit mode - fetch download data
            fetch(`${API_BASE_URL}/downloads/movies/{{ $id }}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('adminAccessToken')}`
                }
            })
            .then(res => res.json())
            .then(data => {
                const download = data.data.find(d => d.id === downloadId);
                if (download) {
                    document.getElementById('download-id').value = download.id;
                    document.getElementById('download-server-name').value = download.server_name || '';
                    document.getElementById('download-url').value = download.download_url || '';
                    document.getElementById('download-quality').value = download.quality || '';
                    document.getElementById('download-size').value = download.size || '';
                    document.getElementById('download-priority').value = download.priority || 0;
                    document.getElementById('download-is-active').checked = download.is_active !== false;
                    title.textContent = 'Edit Download';
                }
            });
        } else {
            // Add mode
            form.reset();
            document.getElementById('download-id').value = '';
            document.getElementById('download-priority').value = 0;
            document.getElementById('download-is-active').checked = true;
            title.textContent = 'Add Download';
        }
        
        modal.style.display = 'flex';
    }

    function closeDownloadModal() {
        const modal = document.getElementById('download-modal');
        modal.style.display = 'none';
        document.getElementById('download-form').reset();
        document.getElementById('download-id').value = '';
        // Stop event propagation
        event?.stopPropagation();
    }

    async function saveDownload(event) {
        event.preventDefault();
        
        const token = localStorage.getItem('adminAccessToken');
        const movieId = {{ $id }};
        const downloadId = document.getElementById('download-id').value;
        
        const data = {
            server_name: document.getElementById('download-server-name').value,
            download_url: document.getElementById('download-url').value,
            quality: document.getElementById('download-quality').value,
            size: document.getElementById('download-size').value,
            priority: parseInt(document.getElementById('download-priority').value) || 0,
            is_active: document.getElementById('download-is-active').checked
        };

        try {
            let response;
            if (downloadId) {
                // Update
                response = await fetch(`${API_BASE_URL}/downloads/movies/${movieId}/${downloadId}`, {
                    method: 'PATCH',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
            } else {
                // Create
                response = await fetch(`${API_BASE_URL}/downloads/movies/${movieId}`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
            }

            const result = await response.json();
            if (result.success) {
                closeDownloadModal();
                loadDownloads();
                alert(result.message || 'Download saved successfully');
            } else {
                alert(result.message || 'Error saving download');
            }
        } catch (error) {
            console.error('Error saving download:', error);
            alert('Error saving download. Please try again.');
        }
    }

    function editDownload(id) {
        openDownloadModal(id);
    }

    async function deleteDownload(id) {
        if (!confirm('Are you sure you want to delete this download?')) {
            return;
        }

        const token = localStorage.getItem('adminAccessToken');
        const movieId = {{ $id }};

        try {
            const response = await fetch(`${API_BASE_URL}/downloads/movies/${movieId}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();
            if (result.success) {
                loadDownloads();
                alert('Download deleted successfully');
            } else {
                alert(result.message || 'Error deleting download');
            }
        } catch (error) {
            console.error('Error deleting download:', error);
            alert('Error deleting download. Please try again.');
        }
    }

    let isEditMode = false;
    let utilsData = { genres: [], categories: [], languages: [] };

    // Fetch utility data (genres, categories, languages)
    async function fetchUtilsData() {
        try {
            const response = await fetch(`${API_BASE_URL}/utils/all`);
            const data = await response.json();
            if (data.success) {
                utilsData = data.data;
                populateEditDropdowns();
            }
        } catch (error) {
            console.error('Error fetching utility data:', error);
        }
    }

    function populateEditDropdowns() {
        // Populate categories
        const categorySelect = document.getElementById('edit-category');
        if (categorySelect && utilsData.categories) {
            categorySelect.innerHTML = '<option value="">None</option>';
            utilsData.categories.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.id;
                option.textContent = cat.name;
                categorySelect.appendChild(option);
            });
        }

        // Populate dubbing languages
        const dubbingLangSelect = document.getElementById('edit-dubbing-language');
        if (dubbingLangSelect && utilsData.languages) {
            dubbingLangSelect.innerHTML = '<option value="">None</option>';
            utilsData.languages.forEach(lang => {
                const option = document.createElement('option');
                option.value = lang.id;
                option.textContent = lang.name;
                dubbingLangSelect.appendChild(option);
            });
        }

        // Populate genres checkboxes
        const genresWrapper = document.getElementById('edit-genres-wrapper');
        if (genresWrapper && utilsData.genres) {
            genresWrapper.innerHTML = '';
            utilsData.genres.forEach(genre => {
                const label = document.createElement('label');
                label.style.display = 'flex';
                label.style.alignItems = 'center';
                label.style.gap = '8px';
                label.style.marginBottom = '8px';
                label.style.pointerEvents = 'auto';
                label.style.cursor = 'pointer';
                
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.value = genre.id;
                checkbox.id = `genre-${genre.id}`;
                checkbox.style.pointerEvents = 'auto';
                checkbox.style.cursor = 'pointer';
                
                const span = document.createElement('span');
                span.textContent = genre.name;
                span.style.color = 'white';
                
                label.appendChild(checkbox);
                label.appendChild(span);
                genresWrapper.appendChild(label);
            });
        }

        // Populate embed language dropdown
        const embedLangSelect = document.getElementById('embed-language');
        if (embedLangSelect && utilsData.languages) {
            embedLangSelect.innerHTML = '<option value="">None</option>';
            utilsData.languages.forEach(lang => {
                const option = document.createElement('option');
                option.value = lang.id;
                option.textContent = lang.name;
                embedLangSelect.appendChild(option);
            });
        }
    }

    function toggleEditMode() {
        isEditMode = !isEditMode;
        
        if (isEditMode) {
            // Show edit inputs, hide display
            document.querySelectorAll('[id^="edit-"]').forEach(el => {
                if (el.id !== 'edit-featured' && el.id !== 'edit-featured-label' && el.id !== 'edit-genres-wrapper') {
                    el.style.display = 'block';
                }
            });
            document.getElementById('edit-featured-label').style.display = 'flex';
            document.getElementById('edit-genres-wrapper').style.display = 'flex';
            
            document.querySelectorAll('[id^="display-"]').forEach(el => {
                el.style.display = 'none';
            });
            
            document.getElementById('edit-btn').style.display = 'none';
            document.getElementById('save-btn').style.display = 'flex';
            document.getElementById('cancel-btn').style.display = 'flex';
        } else {
            cancelEdit();
        }
    }

    function cancelEdit() {
        isEditMode = false;
        
        // Hide edit inputs, show display
        document.querySelectorAll('[id^="edit-"]').forEach(el => {
            if (el.id !== 'edit-featured' && el.id !== 'edit-featured-label' && el.id !== 'edit-genres-wrapper') {
                el.style.display = 'none';
            }
        });
        document.getElementById('edit-featured-label').style.display = 'none';
        document.getElementById('edit-genres-wrapper').style.display = 'none';
        
        document.querySelectorAll('[id^="display-"]').forEach(el => {
            el.style.display = 'inline';
        });
        
        document.getElementById('edit-btn').style.display = 'flex';
        document.getElementById('save-btn').style.display = 'none';
        document.getElementById('cancel-btn').style.display = 'none';
        
        // Reload original values
        if (movie) {
            renderMovie();
        }
    }

    async function saveMovie() {
        if (!movie) return;

        const token = localStorage.getItem('adminAccessToken');
        const movieId = {{ $id }};

        // Get selected genres
        const selectedGenres = Array.from(document.querySelectorAll('#edit-genres-wrapper input[type="checkbox"]:checked'))
            .map(cb => parseInt(cb.value));

        const data = {
            title: document.getElementById('edit-title').value,
            slug: document.getElementById('edit-slug').value,
            overview: document.getElementById('edit-overview').value,
            release_date: document.getElementById('edit-release-date').value || null,
            runtime: document.getElementById('edit-runtime').value || null,
            status: document.getElementById('edit-status').value,
            is_featured: document.getElementById('edit-featured').checked,
            original_language: document.getElementById('edit-original-language').value || null,
            dubbing_language_id: document.getElementById('edit-dubbing-language').value || null,
            category_id: document.getElementById('edit-category').value || null,
            genres: selectedGenres,
        };

        try {
            const saveBtn = document.getElementById('save-btn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span>Saving...</span>';

            const response = await fetch(`${API_BASE_URL}/admin/movies/${movieId}`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                movie = result.data.movie;
                renderMovie();
                cancelEdit();
                alert('Movie updated successfully');
            } else {
                const errorMsg = result.errors ? Object.values(result.errors).flat().join(', ') : result.message || 'Error updating movie';
                alert(errorMsg);
            }
        } catch (error) {
            console.error('Error saving movie:', error);
            alert('Error saving movie. Please try again.');
        } finally {
            const saveBtn = document.getElementById('save-btn');
            saveBtn.disabled = false;
            saveBtn.innerHTML = `
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Save Changes</span>
            `;
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', async () => {
        await fetchUtilsData();
        fetchMovie();
    });
</script>
@endpush
@endsection

