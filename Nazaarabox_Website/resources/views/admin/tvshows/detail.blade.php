@extends('layouts.admin')

@section('title', 'TV Show Details - Admin Panel')

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
    .episode-card {
        background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
        border: 2px solid #3a3a3a;
        border-radius: 8px;
        padding: 12px;
        transition: all 0.2s ease;
    }
    .episode-card:hover {
        border-color: #dc2626;
    }
    
    /* Custom Scrollbar Styles */
    #episode-embeds-list::-webkit-scrollbar,
    #episode-downloads-list::-webkit-scrollbar,
    #episode-embed-modal::-webkit-scrollbar,
    #episode-download-modal::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    #episode-embeds-list::-webkit-scrollbar-track,
    #episode-downloads-list::-webkit-scrollbar-track,
    #episode-embed-modal::-webkit-scrollbar-track,
    #episode-download-modal::-webkit-scrollbar-track {
        background: #1a1a1a;
        border-radius: 4px;
    }
    #episode-embeds-list::-webkit-scrollbar-thumb,
    #episode-downloads-list::-webkit-scrollbar-thumb,
    #episode-embed-modal::-webkit-scrollbar-thumb,
    #episode-download-modal::-webkit-scrollbar-thumb {
        background: #dc2626;
        border-radius: 4px;
    }
    #episode-embeds-list::-webkit-scrollbar-thumb:hover,
    #episode-downloads-list::-webkit-scrollbar-thumb:hover,
    #episode-embed-modal::-webkit-scrollbar-thumb:hover,
    #episode-download-modal::-webkit-scrollbar-thumb:hover {
        background: #b91c1c;
    }
    /* Firefox Scrollbar */
    #episode-embeds-list,
    #episode-downloads-list,
    #episode-embed-modal,
    #episode-download-modal {
        scrollbar-width: thin;
        scrollbar-color: #dc2626 #1a1a1a;
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
                    <a href="/admin/tvshows" style="color: #9ca3af; text-decoration: none;">← Back to TV Shows</a>
                    <h1 style="font-size: 20px; font-weight: bold; color: white; margin: 0;">TV Show Details</h1>
                </div>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <button id="edit-btn" onclick="toggleEditMode()" class="flex items-center gap-2 bg-transparent border border-gray-600 text-white px-4 py-2 rounded hover:bg-dark-700 transition-colors text-sm">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span id="edit-btn-text">Edit Info</span>
                    </button>
                    <button id="save-btn" onclick="saveTVShow()" class="flex items-center gap-2 bg-primary-600 hover:bg-red-700 text-white px-4 py-2 rounded transition-colors text-sm" style="display: none;">
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

        <!-- TV Show Content -->
        <div id="tvshow-content" style="display: none;">
            <!-- Hero Section -->
            <div class="stat-card p-6 mb-6">
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 24px;">
                    <div>
                        <img 
                            id="tvshow-poster" 
                            src="/images/placeholder.svg" 
                            alt="TV Show Poster"
                            style="width: 100%; aspect-ratio: 2/3; object-fit: cover; border-radius: 8px;"
                        >
                    </div>
                    <div>
                        <h2 id="tvshow-name" style="font-size: 28px; font-weight: bold; color: white; margin-bottom: 8px;">Loading...</h2>
                        <div style="display: flex; gap: 16px; margin-bottom: 16px; flex-wrap: wrap;">
                            <span id="tvshow-year" style="color: #9ca3af;">-</span>
                            <span id="tvshow-seasons" style="color: #9ca3af;">-</span>
                            <span id="tvshow-rating" style="color: #fbbf24;">⭐ -</span>
                        </div>
                        <p id="tvshow-overview" style="color: #d1d5db; line-height: 1.6; margin-bottom: 16px;">Loading description...</p>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <span id="tvshow-status-badge" class="status-badge">-</span>
                            <span id="tvshow-featured-badge" style="background-color: #854d0e; color: #fde047; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; display: none;">Featured</span>
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
                        onclick="showTab('seasons')" 
                        id="tab-seasons-btn"
                        class="px-6 py-3 font-medium transition-colors"
                        style="background-color: transparent; color: #9ca3af; border-bottom: 2px solid transparent;"
                    >
                        Seasons & Episodes
                    </button>
                </div>

                <!-- Info Tab -->
                <div id="tab-info" style="padding: 24px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                        <div>
                            <h3 style="font-size: 18px; font-weight: 600; color: white; margin-bottom: 16px;">Basic Information</h3>
                            <div class="info-row">
                                <span style="color: #9ca3af; min-width: 120px;">Name</span>
                                <div style="flex: 1; display: flex; align-items: center;">
                                    <input type="text" id="edit-name" style="background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 6px 12px; border-radius: 4px; width: 100%; max-width: 400px; display: none; box-sizing: border-box; pointer-events: auto;">
                                    <span id="display-name" style="color: white; font-weight: 500;"></span>
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
                                <span style="color: #9ca3af; min-width: 120px;">First Air Date</span>
                                <div style="flex: 1; display: flex; align-items: center;">
                                    <input type="date" id="edit-first-air-date" style="background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 6px 12px; border-radius: 4px; width: 100%; max-width: 400px; display: none; box-sizing: border-box; pointer-events: auto;">
                                    <span id="display-first-air-date" style="color: white; font-weight: 500;"></span>
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
                                    <span id="display-dubbing-language" style="color: white; font-weight: 500;">N/A</span>
                                </div>
                            </div>
                            <div class="info-row" style="align-items: start;">
                                <span style="color: #9ca3af; min-width: 120px; padding-top: 6px;">Genres</span>
                                <div style="flex: 1; display: flex; align-items: start;">
                                    <div id="edit-genres-wrapper" style="display: none; flex-wrap: wrap; gap: 8px; width: 100%; max-width: 400px; pointer-events: auto;">
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
                                <span style="color: #9ca3af; min-width: 120px;">TMDB ID</span>
                                <div style="flex: 1; display: flex; align-items: center;">
                                    <input type="number" id="edit-tmdb-id" placeholder="Enter TMDB ID" style="background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 6px 12px; border-radius: 4px; width: 100%; max-width: 400px; display: none; box-sizing: border-box; pointer-events: auto;">
                                    <span id="display-tmdb-id" style="color: white; font-weight: 500;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seasons & Episodes Tab -->
                <div id="tab-seasons" style="padding: 24px; display: none;">
                    <div style="margin-bottom: 24px; padding: 16px; background-color: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <h3 style="color: white; font-size: 16px; font-weight: 600;">Season Management</h3>
                            <a href="/admin/tvshows/{{ $id }}/seasons" 
                               style="background-color: #dc2626; color: white; padding: 8px 16px; border-radius: 6px; font-size: 14px; cursor: pointer; border: none; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                Full Seasons Management
                            </a>
                        </div>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <button onclick="openCreateSeasonModal()" 
                                    style="background-color: #16a34a; color: white; padding: 8px 16px; border-radius: 6px; font-size: 14px; cursor: pointer; border: none; font-weight: 500;">
                                ➕ Create Season
                            </button>
                            <button onclick="openFetchSeasonModal()" 
                                    style="background-color: #2563eb; color: white; padding: 8px 16px; border-radius: 6px; font-size: 14px; cursor: pointer; border: none; font-weight: 500;">
                                📥 Fetch Season (Bulk)
                            </button>
                            <button onclick="openFetchEpisodeModal()" 
                                    style="background-color: #16a34a; color: white; padding: 8px 16px; border-radius: 6px; font-size: 14px; cursor: pointer; border: none; font-weight: 500;">
                                📥 Fetch Single Episode
                            </button>
                        </div>
                    </div>
                    <div id="seasons-container">
                        <div style="text-align: center; padding: 40px; color: #9ca3af;">
                            <p>Loading seasons...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Episode Edit Modal -->
    <div id="episode-edit-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background-color: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 12px; padding: 24px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; position: relative; z-index: 1001;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="color: white; font-size: 20px; font-weight: 600;" id="episode-edit-modal-title">Edit Episode</h3>
                <button onclick="closeEpisodeEditModal()" style="background: none; border: none; color: #9ca3af; font-size: 24px; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">&times;</button>
            </div>
            <form id="episode-edit-form" onsubmit="event.preventDefault(); saveEpisodeEdit();">
                <input type="hidden" id="episode-edit-id">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Episode Number</label>
                    <input type="number" id="episode-edit-number" disabled 
                           style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: #9ca3af; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Name</label>
                    <input type="text" id="episode-edit-name" 
                           style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Overview</label>
                    <textarea id="episode-edit-overview" rows="4" 
                              style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; resize: vertical; pointer-events: auto;"></textarea>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Still Path</label>
                    <input type="text" id="episode-edit-still-path" 
                           style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;">
                    <p style="color: #6b7280; font-size: 12px; margin-top: 4px;">TMDB relative path (e.g., /w500/still.jpg)</p>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Air Date</label>
                    <input type="date" id="episode-edit-air-date" 
                           style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Runtime (minutes)</label>
                    <input type="number" id="episode-edit-runtime" min="0" 
                           style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Vote Average</label>
                    <input type="number" id="episode-edit-vote-average" step="0.1" min="0" max="10" 
                           style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Vote Count</label>
                    <input type="number" id="episode-edit-vote-count" min="0" 
                           style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end; pointer-events: auto;">
                    <button type="button" onclick="closeEpisodeEditModal()" 
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

    <!-- Add Episode Modal -->
    <div id="episode-add-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background-color: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 12px; padding: 24px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; position: relative; z-index: 1001;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="color: white; font-size: 20px; font-weight: 600;" id="episode-add-modal-title">Add New Episode</h3>
                <button onclick="closeAddEpisodeModal()" style="background: none; border: none; color: #9ca3af; font-size: 24px; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">&times;</button>
            </div>
            <form id="episode-add-form" onsubmit="event.preventDefault(); saveNewEpisode();">
                <input type="hidden" id="episode-add-season-id">
                <input type="hidden" id="episode-add-season-number">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Episode Number <span style="color: #dc2626;">*</span></label>
                    <input type="number" id="episode-add-number" min="1" required
                           style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Name</label>
                    <input type="text" id="episode-add-name" 
                           style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;"
                           placeholder="Episode name (optional)">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Overview</label>
                    <textarea id="episode-add-overview" rows="4" 
                              style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; resize: vertical; pointer-events: auto;"
                              placeholder="Episode description (optional)"></textarea>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Still Path</label>
                    <input type="text" id="episode-add-still-path" 
                           style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;"
                           placeholder="/path/to/still.jpg or full URL (optional)">
                    <p style="color: #6b7280; font-size: 12px; margin-top: 4px;">TMDB relative path (e.g., /w500/still.jpg) or full URL</p>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Air Date</label>
                    <input type="date" id="episode-add-air-date" 
                           style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Runtime (minutes)</label>
                    <input type="number" id="episode-add-runtime" min="0" 
                           style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;"
                           placeholder="Optional">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Vote Average</label>
                    <input type="number" id="episode-add-vote-average" step="0.1" min="0" max="10" value="0"
                           style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Vote Count</label>
                    <input type="number" id="episode-add-vote-count" min="0" value="0"
                           style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 10px; border-radius: 6px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end; pointer-events: auto;">
                    <button type="button" onclick="closeAddEpisodeModal()" 
                            style="background-color: #2a2a2a; color: white; padding: 10px 20px; border-radius: 6px; border: 1px solid #3a3a3a; cursor: pointer; pointer-events: auto;">
                        Cancel
                    </button>
                    <button type="submit" 
                            style="background-color: #2563eb; color: white; padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; pointer-events: auto;">
                        Create Episode
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Episode Embed Modal -->
    <div id="episode-embed-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center; overflow-y: auto;">
        <div style="background-color: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 8px; padding: 24px; max-width: 700px; width: 90%; margin: 40px auto; position: relative; z-index: 1001;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 20px; font-weight: 600; color: white;" id="episode-embed-modal-title">Manage Embeds</h3>
                <button onclick="closeEpisodeEmbedModal()" style="background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 24px;">&times;</button>
            </div>
            
            <!-- Existing Embeds List -->
            <div id="episode-embeds-list" style="margin-bottom: 24px; max-height: 300px; overflow-y: auto;">
                <p style="color: #9ca3af; text-align: center; padding: 20px;">Loading embeds...</p>
            </div>
            
            <!-- Form Section -->
            <div style="border-top: 1px solid #2a2a2a; padding-top: 20px;">
                <h4 style="font-size: 16px; font-weight: 600; color: white; margin-bottom: 16px;" id="episode-embed-form-title">Add New Embed</h4>
                <form id="episode-embed-form" onsubmit="saveEpisodeEmbed(event)">
                <input type="hidden" id="episode-embed-id" value="">
                <input type="hidden" id="episode-embed-episode-id" value="">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Server Name *</label>
                    <input type="text" id="episode-embed-server-name" required style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 8px 12px; border-radius: 4px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Embed URL/ID/Iframe *</label>
                    <textarea id="episode-embed-url" required rows="4" placeholder="Enter embed URL, YouTube/Dailymotion ID, or iframe HTML" style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 8px 12px; border-radius: 4px; resize: vertical; box-sizing: border-box; pointer-events: auto; font-family: inherit;"></textarea>
                    <p style="color: #6b7280; font-size: 12px; margin-top: 4px;">Supports: YouTube URLs/IDs, Dailymotion URLs/IDs, iframe HTML, or direct embed URLs</p>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Priority</label>
                    <input type="number" id="episode-embed-priority" value="0" min="0" max="999" style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 8px 12px; border-radius: 4px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: flex; align-items: center; gap: 8px; color: #9ca3af; cursor: pointer; pointer-events: auto;">
                        <input type="checkbox" id="episode-embed-is-active" checked style="width: 18px; height: 18px; cursor: pointer; pointer-events: auto;">
                        <span>Active</span>
                    </label>
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 8px; color: #9ca3af; cursor: pointer; pointer-events: auto;">
                        <input type="checkbox" id="episode-embed-requires-ad" style="width: 18px; height: 18px; cursor: pointer; pointer-events: auto;">
                        <span>Requires Interstitial Ad</span>
                    </label>
                    <p style="color: #6b7280; font-size: 12px; margin-top: 4px; margin-left: 26px;">If checked, users will see an interstitial ad before watching this embed</p>
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end; pointer-events: auto;">
                    <button type="button" onclick="closeEpisodeEmbedModal()" style="background-color: #3a3a3a; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; pointer-events: auto;">Cancel</button>
                    <button type="submit" style="background-color: #dc2626; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; pointer-events: auto;">Save</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Server Selection Modal for Episodes -->
    <div id="server-selection-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center; overflow-y: auto;">
        <div style="background-color: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 8px; padding: 24px; max-width: 600px; width: 90%; margin: 40px auto; position: relative; z-index: 1001;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 20px; font-weight: 600; color: white;">Include VidSrc Servers</h3>
                <button onclick="closeServerSelectionModal()" style="background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 24px;">&times;</button>
            </div>
            <div>
                <p style="color: #d1d5db; margin-bottom: 24px;">Would you like to automatically add VidSrc servers to the episodes?</p>
                <div style="margin-bottom: 24px;">
                    <label style="display: flex; align-items: start; gap: 12px; padding: 12px; background-color: #2a2a2a; border: 1px solid #3a3a3a; border-radius: 6px; margin-bottom: 12px; cursor: pointer; transition: all 0.2s;">
                        <input type="checkbox" id="server-vidsrc-pro" value="vidsrc-pro"
                               style="width: 18px; height: 18px; cursor: pointer; accent-color: #dc2626; margin-top: 2px;">
                        <div style="flex: 1;">
                            <div style="color: white; font-weight: 500; margin-bottom: 4px;">VidSrc Pro</div>
                            <code style="color: #9ca3af; font-size: 12px; background-color: #1a1a1a; padding: 4px 8px; border-radius: 4px; display: block;">https://vidlink.pro/tv/{tmdb_id}/{season}/{episode}</code>
                        </div>
                    </label>
                    <label style="display: flex; align-items: start; gap: 12px; padding: 12px; background-color: #2a2a2a; border: 1px solid #3a3a3a; border-radius: 6px; margin-bottom: 12px; cursor: pointer; transition: all 0.2s;">
                        <input type="checkbox" id="server-vidsrc-icu" value="vidsrc-icu"
                               style="width: 18px; height: 18px; cursor: pointer; accent-color: #dc2626; margin-top: 2px;">
                        <div style="flex: 1;">
                            <div style="color: white; font-weight: 500; margin-bottom: 4px;">VidSrc ICU</div>
                            <code style="color: #9ca3af; font-size: 12px; background-color: #1a1a1a; padding: 4px 8px; border-radius: 4px; display: block;">https://vidsrc.icu/embed/tv/{tmdb_id}/{season}/{episode}</code>
                        </div>
                    </label>
                    <label style="display: flex; align-items: start; gap: 12px; padding: 12px; background-color: #2a2a2a; border: 1px solid #3a3a3a; border-radius: 6px; margin-bottom: 12px; cursor: pointer; transition: all 0.2s;">
                        <input type="checkbox" id="server-vidsrc-fast" value="vidsrc-fast"
                               style="width: 18px; height: 18px; cursor: pointer; accent-color: #dc2626; margin-top: 2px;">
                        <div style="flex: 1;">
                            <div style="color: white; font-weight: 500; margin-bottom: 4px;">VidSrc Fast</div>
                            <code style="color: #9ca3af; font-size: 12px; background-color: #1a1a1a; padding: 4px 8px; border-radius: 4px; display: block;">https://vidfast.pro/tv/{tmdb_id}/{season}/{episode}</code>
                        </div>
                    </label>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <button onclick="skipServerSelection()" 
                            style="background-color: #3a3a3a; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
                        Skip
                    </button>
                    <button onclick="confirmServerSelection()" 
                            style="background-color: #dc2626; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
                        Continue
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Episode Download Modal -->
    <div id="episode-download-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center; overflow-y: auto;">
        <div style="background-color: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 8px; padding: 24px; max-width: 700px; width: 90%; margin: 40px auto; position: relative; z-index: 1001;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 20px; font-weight: 600; color: white;" id="episode-download-modal-title">Manage Downloads</h3>
                <button onclick="closeEpisodeDownloadModal()" style="background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 24px;">&times;</button>
            </div>
            
            <!-- Existing Downloads List -->
            <div id="episode-downloads-list" style="margin-bottom: 24px; max-height: 300px; overflow-y: auto;">
                <p style="color: #9ca3af; text-align: center; padding: 20px;">Loading downloads...</p>
            </div>
            
            <!-- Form Section -->
            <div style="border-top: 1px solid #2a2a2a; padding-top: 20px;">
                <h4 style="font-size: 16px; font-weight: 600; color: white; margin-bottom: 16px;" id="episode-download-form-title">Add New Download</h4>
                <form id="episode-download-form" onsubmit="saveEpisodeDownload(event)">
                <input type="hidden" id="episode-download-id" value="">
                <input type="hidden" id="episode-download-episode-id" value="">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Server Name *</label>
                    <input type="text" id="episode-download-server-name" required style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 8px 12px; border-radius: 4px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Download URL *</label>
                    <input type="url" id="episode-download-url" required style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 8px 12px; border-radius: 4px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Quality</label>
                    <input type="text" id="episode-download-quality" placeholder="e.g., 1080p, 720p" style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 8px 12px; border-radius: 4px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Size</label>
                    <input type="text" id="episode-download-size" placeholder="e.g., 2.5 GB, 1.2 GB" style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 8px 12px; border-radius: 4px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #9ca3af; margin-bottom: 8px; font-size: 14px;">Priority</label>
                    <input type="number" id="episode-download-priority" value="0" min="0" max="999" style="width: 100%; background-color: #2a2a2a; border: 1px solid #3a3a3a; color: white; padding: 8px 12px; border-radius: 4px; box-sizing: border-box; pointer-events: auto;">
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 8px; color: #9ca3af; cursor: pointer; pointer-events: auto;">
                        <input type="checkbox" id="episode-download-is-active" checked style="width: 18px; height: 18px; cursor: pointer; pointer-events: auto;">
                        <span>Active</span>
                    </label>
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end; pointer-events: auto;">
                    <button type="button" onclick="closeEpisodeDownloadModal()" style="background-color: #3a3a3a; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; pointer-events: auto;">Cancel</button>
                    <button type="submit" style="background-color: #dc2626; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; pointer-events: auto;">Save</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const tvshowId = {{ $id }};
    let tvShow = null;
    let utilsData = {};
    let isEditMode = false;
    
    // Server selection state
    let pendingFetchOperation = null; // Stores the fetch function to execute after server selection
    let selectedServers = []; // Stores selected server types
    let pendingSeasonData = null; // Stores season data after fetch for adding embeds
    
    // TMDB Configuration
    const TMDB_BASE_URL = '{{ config("services.tmdb.base_url") }}';
    const TMDB_ACCESS_TOKEN = '{{ config("services.tmdb.access_token") }}';
    const TMDB_IMAGE_URL = '{{ config("services.tmdb.image_url") }}';

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

    async function loadTVShow(loadEpisodes = false) {
        const token = localStorage.getItem('adminAccessToken');
        if (!token) {
            window.location.href = '/admin/login';
            return;
        }

        try {
            // Only load episodes if explicitly requested (for performance)
            const url = `${API_BASE_URL}/admin/tvshows/${tvshowId}${loadEpisodes ? '?load_episodes=1' : ''}`;
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
                renderTVShow();
                document.getElementById('loading-state').style.display = 'none';
                document.getElementById('tvshow-content').style.display = 'block';
            }
            return tvShow;
        } catch (error) {
            console.error('Error loading TV show:', error);
            throw error;
        }
    }

    function renderTVShow() {
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
        document.getElementById('tvshow-year').textContent = tvShow.first_air_date ? new Date(tvShow.first_air_date).getFullYear() : 'N/A';
        document.getElementById('tvshow-seasons').textContent = `${tvShow.number_of_seasons || 0} Seasons`;
        document.getElementById('tvshow-rating').textContent = `⭐ ${(tvShow.vote_average || 0).toFixed(1)}`;
        document.getElementById('tvshow-overview').textContent = tvShow.overview || 'No overview available.';
        
        const statusBadge = document.getElementById('tvshow-status-badge');
        statusBadge.textContent = (tvShow.status || 'pending').toUpperCase();
        statusBadge.className = `status-badge status-${tvShow.status || 'pending'}`;

        if (tvShow.is_featured) {
            document.getElementById('tvshow-featured-badge').style.display = 'inline-block';
        }

        document.getElementById('stat-views').textContent = tvShow.view_count || 0;
        document.getElementById('stat-vote').textContent = (tvShow.vote_average || 0).toFixed(1);
        document.getElementById('stat-vote-count').textContent = tvShow.vote_count || 0;
        document.getElementById('stat-popularity').textContent = (tvShow.popularity || 0).toFixed(2);

        // Display mode
        document.getElementById('display-name').textContent = tvShow.name || 'Untitled';
        document.getElementById('display-slug').textContent = tvShow.slug || '-';
        document.getElementById('display-status').textContent = (tvShow.status || 'pending').toUpperCase();
        document.getElementById('display-featured').textContent = tvShow.is_featured ? 'Yes' : 'No';
        document.getElementById('display-first-air-date').textContent = tvShow.first_air_date || 'N/A';
        document.getElementById('display-overview').textContent = tvShow.overview || 'No overview available.';
        document.getElementById('display-language').textContent = tvShow.original_language || 'N/A';
        document.getElementById('display-dubbing-language').textContent = tvShow.dubbing_language?.name || 'N/A';
        document.getElementById('display-category').textContent = tvShow.category?.name || 'N/A';
        document.getElementById('display-genres').textContent = tvShow.genres?.map(g => g.name).join(', ') || 'N/A';
        document.getElementById('display-tmdb-id').textContent = tvShow.tmdb_id || 'N/A';

        // Render seasons and episodes only if seasons container exists
        const seasonsContainer = document.getElementById('seasons-container');
        if (seasonsContainer) {
            renderSeasons();
        }
    }

    function renderSeasons() {
        const container = document.getElementById('seasons-container');
        if (!tvShow.seasons || tvShow.seasons.length === 0) {
            container.innerHTML = '<p style="color: #9ca3af;">No seasons available.</p>';
            return;
        }

        container.innerHTML = tvShow.seasons.map(season => `
            <div style="margin-bottom: 32px;" data-season-id="${season.id}">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 2px solid #2a2a2a;">
                    <h3 style="font-size: 20px; font-weight: 600; color: white;">${season.name || `Season ${season.season_number}`}</h3>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #9ca3af; font-size: 14px;">${season.episode_count || 0} episodes</span>
                        <button class="edit-season-btn" 
                                data-season-id="${season.id}"
                                data-season-number="${season.season_number}"
                                style="background-color: #2563eb; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; border: none; font-weight: 500;">
                            ✏️ Edit
                        </button>
                        <button onclick="event.stopPropagation(); fetchSeasonFromTMDB(${season.season_number})" 
                                style="background-color: #2563eb; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; border: none; font-weight: 500;">
                            🔄 Refresh from TMDB
                        </button>
                        ${(season.episodes || []).length > 0 ? `
                        <button onclick="event.stopPropagation(); addVidsrcServersToSeason(${season.id}, ${season.season_number})" 
                                style="background-color: #16a34a; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; border: none; font-weight: 500;">
                            ➕ Add VidSrc Servers
                        </button>
                        <button onclick="event.stopPropagation(); bulkToggleSeasonAds(${season.id}, ${season.season_number})" 
                                style="background-color: #dc2626; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; border: none; font-weight: 500;">
                            📢 Manage Ads
                        </button>
                        ` : ''}
                        <button class="delete-season-btn"
                                data-season-id="${season.id}"
                                data-season-name="${(season.name || `Season ${season.season_number}`).replace(/"/g, '&quot;')}"
                                style="background-color: #dc2626; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; border: none; font-weight: 500;">
                            🗑️ Delete
                        </button>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                    ${(season.episodes || []).length === 0 ? `
                        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background-color: #1a1a1a; border: 2px dashed #3a3a3a; border-radius: 8px;">
                            <p style="color: #9ca3af; font-size: 14px; margin-bottom: 16px;">No episodes yet</p>
                            <button onclick="openAddEpisodeModal(${season.id}, ${season.season_number})" 
                                    style="background-color: #2563eb; color: white; padding: 10px 20px; border-radius: 6px; font-size: 14px; cursor: pointer; border: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Episode
                            </button>
                        </div>
                    ` : ''}
                    ${(season.episodes || []).map(episode => {
                        let stillPath = '/images/placeholder.svg';
                        if (episode.still_path) {
                            if (episode.still_path.startsWith('http')) {
                                stillPath = episode.still_path;
                            } else if (episode.still_path.startsWith('/')) {
                                stillPath = `https://image.tmdb.org/t/p/w154${episode.still_path}`;
                            } else {
                                stillPath = `https://image.tmdb.org/t/p/w154/${episode.still_path}`;
                            }
                        }
                        return `
                        <div class="episode-card" data-episode-id="${episode.id}">
                            <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                                <img src="${stillPath}" 
                                     alt="${episode.name || `Episode ${episode.episode_number}`}" 
                                     style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px;"
                                     onerror="this.src='/images/placeholder.svg'">
                                <div style="flex: 1;">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 4px;">
                                        <h4 style="color: white; font-weight: 600; font-size: 14px; margin: 0;">${episode.name || `Episode ${episode.episode_number}`}</h4>
                                        <button class="edit-episode-btn" 
                                                data-episode-id="${episode.id}"
                                                data-episode-number="${episode.episode_number}"
                                                style="background-color: #3a3a3a; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; cursor: pointer; border: none; pointer-events: auto;">
                                            ✏️
                                        </button>
                                    </div>
                                    <p style="color: #9ca3af; font-size: 12px;">Ep. ${episode.episode_number}</p>
                                    ${episode.air_date ? `<p style="color: #6b7280; font-size: 11px;">${new Date(episode.air_date).toLocaleDateString()}</p>` : ''}
                                </div>
                            </div>
                            ${episode.overview ? `<p style="color: #9ca3af; font-size: 12px; line-height: 1.4; margin-bottom: 12px;">${episode.overview.substring(0, 100)}...</p>` : ''}
                            <div style="display: flex; gap: 8px; padding-top: 12px; border-top: 1px solid #2a2a2a; flex-wrap: wrap;">
                                <button onclick="openEpisodeEmbeds(${episode.id})" 
                                        style="flex: 1; min-width: 80px; background-color: #2563eb; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; border: none;">
                                    Embeds
                                </button>
                                <button onclick="openEpisodeDownloads(${episode.id})" 
                                        style="flex: 1; min-width: 80px; background-color: #16a34a; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; border: none;">
                                    Downloads
                                </button>
                                <button class="delete-episode-btn"
                                        data-episode-id="${episode.id}"
                                        data-episode-number="${episode.episode_number}"
                                        data-episode-name="${(episode.name || `Episode ${episode.episode_number}`).replace(/"/g, '&quot;')}"
                                        style="background-color: #dc2626; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; border: none; pointer-events: auto;">
                                    🗑️
                                </button>
                            </div>
                        </div>
                    `;
                    }).join('')}
                    ${(season.episodes || []).length > 0 ? `
                        <div class="episode-card" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 200px; border: 2px dashed #3a3a3a; background-color: #1a1a1a; cursor: pointer;" onclick="openAddEpisodeModal(${season.id}, ${season.season_number})">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 48px; height: 48px; color: #6b7280; margin-bottom: 12px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <p style="color: #9ca3af; font-size: 14px; font-weight: 500;">Add Episode</p>
                        </div>
                    ` : ''}
                </div>
            </div>
        `).join('');
        
        // Attach event listeners for edit and delete buttons
        document.querySelectorAll('.edit-season-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const seasonId = parseInt(this.getAttribute('data-season-id'));
                const seasonNumber = parseInt(this.getAttribute('data-season-number'));
                const season = tvShow.seasons.find(s => s.id === seasonId);
                if (season) {
                    console.log('[Season Edit] Found season data:', season);
                    openEditSeasonModal(
                        seasonId, 
                        seasonNumber, 
                        season.name || '', 
                        season.overview || '', 
                        season.poster_path || '', 
                        season.air_date || ''
                    );
                } else {
                    console.error('[Season Edit] Season not found in tvShow.seasons:', seasonId);
                }
            });
        });
        
        document.querySelectorAll('.delete-season-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const seasonId = parseInt(this.getAttribute('data-season-id'));
                const seasonName = this.getAttribute('data-season-name');
                deleteSeason(seasonId, seasonName);
            });
        });
        
        // Attach event listeners for episode edit and delete buttons
        document.querySelectorAll('.edit-episode-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const episodeId = parseInt(this.getAttribute('data-episode-id'));
                const episodeNumber = parseInt(this.getAttribute('data-episode-number'));
                const episode = tvShow.seasons.flatMap(s => s.episodes || []).find(e => e.id === episodeId);
                if (episode) {
                    console.log('[Episode Edit] Found episode data:', episode);
                    openEditEpisodeModal(episode);
                } else {
                    console.error('[Episode Edit] Episode not found:', episodeId);
                }
            });
        });
        
        document.querySelectorAll('.delete-episode-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const episodeId = parseInt(this.getAttribute('data-episode-id'));
                const episodeNumber = parseInt(this.getAttribute('data-episode-number'));
                const episodeName = this.getAttribute('data-episode-name');
                deleteEpisode(episodeId, episodeNumber, episodeName);
            });
        });
    }

    function showTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('[id^="tab-"]').forEach(tab => {
            if (tab.id !== `tab-${tabName}-btn`) {
                tab.style.display = 'none';
            }
        });
        
        // Reset all tab buttons
        const tabButtons = ['info', 'seasons'];
        tabButtons.forEach(tab => {
            const btn = document.getElementById(`tab-${tab}-btn`);
            if (btn) {
                btn.style.backgroundColor = 'transparent';
                btn.style.color = '#9ca3af';
                btn.style.borderBottom = '2px solid transparent';
            }
        });

        // Show selected tab
        const selectedTab = document.getElementById(`tab-${tabName}`);
        if (selectedTab) {
            selectedTab.style.display = 'block';
        }
        
        // Activate selected tab button
        const btn = document.getElementById(`tab-${tabName}-btn`);
        if (btn) {
            btn.style.backgroundColor = '#2a2a2a';
            btn.style.color = 'white';
            btn.style.borderBottom = '2px solid #dc2626';
        }

        // Lazy load seasons when tab is opened for the first time
        if (tabName === 'seasons') {
            // Check if we need to load episodes
            const needsEpisodes = tvShow && tvShow.seasons && tvShow.seasons.length > 0 && 
                                tvShow.seasons.some(season => !season.episodes || season.episodes.length === 0);
            
            if (needsEpisodes) {
                console.log('[Detail] Loading episodes for seasons tab');
                loadTVShow(true).then(() => {
                    // Re-render seasons after loading episodes
                    if (tvShow && tvShow.seasons) {
                        renderSeasons();
                    }
                });
            } else if (tvShow && tvShow.seasons) {
                // Episodes already loaded, just render
                renderSeasons();
            }
        }
    }

    function toggleEditMode() {
        isEditMode = !isEditMode;
        
        // Toggle visibility
        document.querySelectorAll('[id^="edit-"]').forEach(el => {
            if (el.id !== 'edit-btn' && el.id !== 'edit-btn-text') {
                el.style.display = isEditMode ? 'block' : 'none';
            }
        });
        document.querySelectorAll('[id^="display-"]').forEach(el => {
            el.style.display = isEditMode ? 'none' : 'block';
        });

        document.getElementById('edit-btn').style.display = isEditMode ? 'none' : 'flex';
        document.getElementById('save-btn').style.display = isEditMode ? 'flex' : 'none';
        document.getElementById('cancel-btn').style.display = isEditMode ? 'flex' : 'none';

        if (isEditMode) {
            populateEditFields();
        }
    }

    function populateEditFields() {
        document.getElementById('edit-name').value = tvShow.name || '';
        document.getElementById('edit-slug').value = tvShow.slug || '';
        document.getElementById('edit-status').value = tvShow.status || 'pending';
        document.getElementById('edit-featured').checked = tvShow.is_featured || false;
        document.getElementById('edit-first-air-date').value = tvShow.first_air_date || '';
        document.getElementById('edit-overview').value = tvShow.overview || '';
        document.getElementById('edit-original-language').value = tvShow.original_language || '';
        document.getElementById('edit-dubbing-language').value = tvShow.dubbing_language?.id || '';
        document.getElementById('edit-category').value = tvShow.category?.id || '';
        document.getElementById('edit-tmdb-id').value = tvShow.tmdb_id || '';

        // Populate genres checkboxes
        const genresWrapper = document.getElementById('edit-genres-wrapper');
        genresWrapper.innerHTML = '';
        if (utilsData.genres) {
            utilsData.genres.forEach(genre => {
                const label = document.createElement('label');
                label.style.cssText = 'display: flex; align-items: center; gap: 8px; cursor: pointer;';
                label.innerHTML = `
                    <input type="checkbox" value="${genre.id}" ${tvShow.genres?.some(g => g.id === genre.id) ? 'checked' : ''}>
                    <span style="color: white; font-size: 14px;">${genre.name}</span>
                `;
                genresWrapper.appendChild(label);
            });
        }
    }

    async function saveTVShow() {
        const token = localStorage.getItem('adminAccessToken');
        const selectedGenres = Array.from(document.querySelectorAll('#edit-genres-wrapper input[type="checkbox"]:checked'))
            .map(cb => parseInt(cb.value));

        const data = {
            name: document.getElementById('edit-name').value,
            slug: document.getElementById('edit-slug').value,
            overview: document.getElementById('edit-overview').value,
            status: document.getElementById('edit-status').value,
            is_featured: document.getElementById('edit-featured').checked,
            first_air_date: document.getElementById('edit-first-air-date').value || null,
            original_language: document.getElementById('edit-original-language').value || null,
            dubbing_language_id: document.getElementById('edit-dubbing-language').value || null,
            category_id: document.getElementById('edit-category').value || null,
            tmdb_id: document.getElementById('edit-tmdb-id').value ? parseInt(document.getElementById('edit-tmdb-id').value) : null,
            genres: selectedGenres,
        };

        try {
            const response = await fetch(`${API_BASE_URL}/admin/tvshows/${tvshowId}`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (result.success) {
                tvShow = result.data.tvShow;
                renderTVShow();
                toggleEditMode();
                alert('TV Show updated successfully!');
            } else {
                alert(result.message || 'Error updating TV show');
            }
        } catch (error) {
            console.error('Error saving TV show:', error);
            alert('Error saving TV show');
        }
    }

    function cancelEdit() {
        toggleEditMode();
    }

    function openEpisodeEmbeds(episodeId) {
        console.log('[Episode Embeds] Opening embed modal for episode:', episodeId);
        loadEpisodeEmbeds(episodeId);
    }

    function openEpisodeDownloads(episodeId) {
        console.log('[Episode Downloads] Opening download modal for episode:', episodeId);
        loadEpisodeDownloads(episodeId);
    }

    // Load and display episode embeds in a list modal
    async function loadEpisodeEmbeds(episodeId) {
        const token = localStorage.getItem('adminAccessToken');
        if (!token) {
            window.location.href = '/admin/login';
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}/embeds/episodes/${episodeId}`, {
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

            const result = await response.json();
            const embeds = result.success ? (result.data || []) : [];

            // Show modal with embeds list
            showEpisodeEmbedsModal(episodeId, embeds);
        } catch (error) {
            console.error('Error loading episode embeds:', error);
            alert('Error loading embeds: ' + error.message);
        }
    }

    // Load and display episode downloads in a list modal
    async function loadEpisodeDownloads(episodeId) {
        const token = localStorage.getItem('adminAccessToken');
        if (!token) {
            window.location.href = '/admin/login';
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}/downloads/episodes/${episodeId}`, {
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
            const downloads = result.success ? (result.data || []) : [];

            // Show modal with downloads list
            showEpisodeDownloadsModal(episodeId, downloads);
        } catch (error) {
            console.error('Error loading episode downloads:', error);
            alert('Error loading downloads: ' + error.message);
        }
    }

    // Show embeds modal with list
    function showEpisodeEmbedsModal(episodeId, embeds) {
        const modal = document.getElementById('episode-embed-modal');
        const embedsList = document.getElementById('episode-embeds-list');
        const episode = tvShow.seasons.flatMap(s => s.episodes || []).find(e => e.id === episodeId);
        const episodeName = episode ? `Episode ${episode.episode_number}: ${episode.name || 'Untitled'}` : `Episode ID: ${episodeId}`;
        
        // Store current episode ID for later use
        window.currentEpisodeId = episodeId;
        
        // Update modal title
        document.getElementById('episode-embed-modal-title').textContent = `Manage Embeds - ${episodeName}`;
        
        // Render embeds list
        if (embeds.length === 0) {
            embedsList.innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 32px;">No embeds available</p>';
        } else {
            embedsList.innerHTML = embeds.map(embed => `
                <div style="background-color: #2a2a2a; border: 1px solid #3a3a3a; border-radius: 8px; padding: 16px; margin-bottom: 12px; ${embed.is_active === false ? 'opacity: 0.6;' : ''}">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                <h4 style="color: white; font-weight: 600; margin: 0;">${embed.server_name || 'Server'}</h4>
                                ${embed.is_active === false ? '<span style="background-color: #991b1b; color: #fca5a5; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">Inactive</span>' : ''}
                                ${embed.requires_ad === true ? '<span style="background-color: #dc2626; color: #fca5a5; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">Requires Ad</span>' : ''}
                            </div>
                            <div style="display: flex; gap: 16px; margin-top: 4px;">
                                <p style="color: #9ca3af; font-size: 14px; margin: 0;">Priority: ${embed.priority || 0}</p>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button onclick="toggleEpisodeEmbedAd(${episodeId}, ${embed.id}, ${embed.requires_ad ? 'true' : 'false'})" 
                                    style="background-color: ${embed.requires_ad ? '#dc2626' : '#3a3a3a'}; color: white; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; pointer-events: auto; font-size: 11px; white-space: nowrap;"
                                    title="${embed.requires_ad ? 'Disable' : 'Enable'} Interstitial Ad">
                                ${embed.requires_ad ? '🔴 Ad ON' : '⚪ Ad OFF'}
                            </button>
                            <button onclick="editEpisodeEmbed(${embed.id})" class="bg-dark-600 hover:bg-dark-500 text-white px-3 py-1 rounded text-sm transition-colors" style="background-color: #3a3a3a; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; pointer-events: auto;">
                                Edit
                            </button>
                            <button onclick="deleteEpisodeEmbed(${embed.id})" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition-colors" style="padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; pointer-events: auto;">
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
                </div>
            `).join('');
        }
        
        // Show modal
        modal.style.display = 'flex';
    }

    // Show downloads modal with list
    function showEpisodeDownloadsModal(episodeId, downloads) {
        const modal = document.getElementById('episode-download-modal');
        const downloadsList = document.getElementById('episode-downloads-list');
        const episode = tvShow.seasons.flatMap(s => s.episodes || []).find(e => e.id === episodeId);
        const episodeName = episode ? `Episode ${episode.episode_number}: ${episode.name || 'Untitled'}` : `Episode ID: ${episodeId}`;
        
        // Store current episode ID for later use
        window.currentEpisodeId = episodeId;
        
        // Update modal title
        document.getElementById('episode-download-modal-title').textContent = `Manage Downloads - ${episodeName}`;
        
        // Render downloads list
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
                                ${download.quality ? `<p style="color: #9ca3af; font-size: 14px; margin: 0;">Quality: ${download.quality}</p>` : ''}
                                ${download.size ? `<p style="color: #9ca3af; font-size: 14px; margin: 0;">Size: ${download.size}</p>` : ''}
                                <p style="color: #9ca3af; font-size: 14px; margin: 0;">Priority: ${download.priority || 0}</p>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button onclick="editEpisodeDownload(${download.id})" class="bg-dark-600 hover:bg-dark-500 text-white px-3 py-1 rounded text-sm transition-colors" style="background-color: #3a3a3a; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; pointer-events: auto;">
                                Edit
                            </button>
                            <button onclick="deleteEpisodeDownload(${download.id})" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition-colors" style="padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; pointer-events: auto;">
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
        
        // Show modal
        modal.style.display = 'flex';
    }

    // Modal control functions
    function closeEpisodeEmbedModal() {
        document.getElementById('episode-embed-modal').style.display = 'none';
        document.getElementById('episode-embed-form').reset();
        document.getElementById('episode-embed-id').value = '';
        window.currentEpisodeId = null;
    }

    function closeEpisodeDownloadModal() {
        document.getElementById('episode-download-modal').style.display = 'none';
        document.getElementById('episode-download-form').reset();
        document.getElementById('episode-download-id').value = '';
        window.currentEpisodeId = null;
    }

    async function saveEpisodeEmbed(event) {
        event.preventDefault();
        
        if (!window.currentEpisodeId) {
            alert('Episode ID not set');
            return;
        }

        const token = localStorage.getItem('adminAccessToken');
        const episodeId = window.currentEpisodeId;
        const embedId = document.getElementById('episode-embed-id').value;
        
        const data = {
            server_name: document.getElementById('episode-embed-server-name').value,
            embed_url: document.getElementById('episode-embed-url').value,
            priority: parseInt(document.getElementById('episode-embed-priority').value) || 0,
            is_active: document.getElementById('episode-embed-is-active').checked,
            requires_ad: document.getElementById('episode-embed-requires-ad').checked
        };

        try {
            let response;
            if (embedId) {
                // Update
                response = await fetch(`${API_BASE_URL}/embeds/episodes/${episodeId}/${embedId}`, {
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
                response = await fetch(`${API_BASE_URL}/embeds/episodes/${episodeId}`, {
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
                // Reset form for next entry
                document.getElementById('episode-embed-form').reset();
                document.getElementById('episode-embed-id').value = '';
                document.getElementById('episode-embed-priority').value = 0;
                document.getElementById('episode-embed-is-active').checked = true;
                document.getElementById('episode-embed-requires-ad').checked = false;
                
                // Reload embeds
                loadEpisodeEmbeds(episodeId);
                alert(result.message || 'Embed saved successfully');
            } else {
                alert(result.message || 'Error saving embed');
            }
        } catch (error) {
            console.error('Error saving episode embed:', error);
            alert('Error saving embed. Please try again.');
        }
    }

    function editEpisodeEmbed(embedId) {
        console.log('[Episode Embeds] Edit embed:', embedId);
        const embeds = Array.from(document.querySelectorAll('#episode-embeds-list > div'));
        const embedElement = embeds.find(el => {
            const editBtn = el.querySelector('button[onclick*="editEpisodeEmbed"]');
            if (editBtn) {
                const match = editBtn.getAttribute('onclick').match(/editEpisodeEmbed\((\d+)\)/);
                return match && parseInt(match[1]) === embedId;
            }
            return false;
        });
        
        if (embedElement) {
            // Fetch current embed data
            fetch(`${API_BASE_URL}/embeds/episodes/${window.currentEpisodeId}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('adminAccessToken')}`,
                    'X-API-Key': API_KEY
                }
            })
            .then(res => res.json())
            .then(data => {
                const embed = data.data.find(e => e.id === embedId);
                if (embed) {
                    document.getElementById('episode-embed-id').value = embed.id;
                    document.getElementById('episode-embed-server-name').value = embed.server_name || '';
                    document.getElementById('episode-embed-url').value = embed.embed_url ? unescapeHtml(embed.embed_url) : '';
                    document.getElementById('episode-embed-priority').value = embed.priority || 0;
                    document.getElementById('episode-embed-is-active').checked = embed.is_active !== false;
                    document.getElementById('episode-embed-requires-ad').checked = embed.requires_ad === true;
                    document.getElementById('episode-embed-form-title').textContent = 'Edit Embed';
                    
                    // Scroll to form
                    document.querySelector('#episode-embed-form').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        }
    }

    async function deleteEpisodeEmbed(embedId) {
        if (!confirm('Are you sure you want to delete this embed?')) {
            return;
        }

        const token = localStorage.getItem('adminAccessToken');
        if (!window.currentEpisodeId) return;

        try {
            const response = await fetch(`${API_BASE_URL}/embeds/episodes/${window.currentEpisodeId}/${embedId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'X-API-Key': API_KEY,
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();
            if (result.success) {
                loadEpisodeEmbeds(window.currentEpisodeId);
                alert('Embed deleted successfully');
            } else {
                alert(result.message || 'Error deleting embed');
            }
        } catch (error) {
            console.error('Error deleting episode embed:', error);
            alert('Error deleting embed. Please try again.');
        }
    }

    // Toggle interstitial ad for a single embed
    async function toggleEpisodeEmbedAd(episodeId, embedId, currentState) {
        const token = localStorage.getItem('adminAccessToken');
        const action = currentState === 'true' || currentState === true ? 'disable' : 'enable';

        try {
            const response = await fetch(`${API_BASE_URL}/admin/ads/episodes/${episodeId}/embeds/${embedId}/toggle`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'X-API-Key': API_KEY,
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            if (result.success) {
                // Reload embeds to show updated state
                if (window.currentEpisodeId === episodeId) {
                    loadEpisodeEmbeds(episodeId);
                }
                // Also reload TV show data to update the UI
                await loadTVShow(true);
            } else {
                alert(result.message || 'Error toggling ad requirement');
            }
        } catch (error) {
            console.error('Error toggling episode embed ad:', error);
            alert('Error toggling ad requirement. Please try again.');
        }
    }

    // Bulk toggle interstitial ads for all embeds in a season
    window.bulkToggleSeasonAds = async function(seasonId, seasonNumber) {
        const season = tvShow.seasons.find(s => s.id === seasonId);
        if (!season || !season.episodes || season.episodes.length === 0) {
            alert('This season has no episodes.');
            return;
        }

        // Show loading indicator
        let loadingMsg = document.getElementById('bulk-ads-loading-msg');
        if (!loadingMsg) {
            loadingMsg = document.createElement('div');
            loadingMsg.id = 'bulk-ads-loading-msg';
            loadingMsg.style.cssText = 'position: fixed; top: 20px; right: 20px; background-color: #1a1a1a; color: white; padding: 16px 24px; border-radius: 8px; border: 1px solid #2a2a2a; z-index: 2000; box-shadow: 0 4px 12px rgba(0,0,0,0.5);';
            document.body.appendChild(loadingMsg);
        }
        loadingMsg.textContent = 'Loading embeds...';

        const token = localStorage.getItem('adminAccessToken');
        
        // Collect all embeds from all episodes in the season
        // If embeds are not loaded, fetch them for each episode
        const allEmbeds = [];
        
        for (const episode of season.episodes) {
            let embeds = episode.embeds;
            
            // If embeds are not loaded, fetch them
            if (!embeds || embeds.length === 0) {
                try {
                    const response = await fetch(`${API_BASE_URL}/embeds/episodes/${episode.id}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'X-API-Key': API_KEY,
                            'Content-Type': 'application/json'
                        }
                    });
                    
                    if (response.ok) {
                        const result = await response.json();
                        embeds = result.data || [];
                    }
                } catch (error) {
                    console.error(`Error fetching embeds for episode ${episode.id}:`, error);
                }
            }
            
            if (embeds && embeds.length > 0) {
                embeds.forEach(embed => {
                    allEmbeds.push({
                        episodeId: episode.id,
                        embedId: embed.id,
                        serverName: embed.server_name,
                        currentState: embed.requires_ad
                    });
                });
            }
        }

        if (allEmbeds.length === 0) {
            alert('This season has no embeds to manage.');
            return;
        }

        // Count current state
        const adsEnabled = allEmbeds.filter(e => e.currentState).length;
        const adsDisabled = allEmbeds.length - adsEnabled;

        // Ask user what they want to do
        const action = confirm(
            `Manage Interstitial Ads for Season ${seasonNumber}\n\n` +
            `Total Embeds: ${allEmbeds.length}\n` +
            `Currently Enabled: ${adsEnabled}\n` +
            `Currently Disabled: ${adsDisabled}\n\n` +
            `Click OK to ENABLE ads for all embeds\n` +
            `Click Cancel to DISABLE ads for all embeds`
        );

        const targetState = action; // true = enable, false = disable
        const actionText = targetState ? 'enable' : 'disable';

        if (!confirm(`Are you sure you want to ${actionText} interstitial ads for all ${allEmbeds.length} embeds in this season?`)) {
            return;
        }

        // Update loading indicator text
        loadingMsg.textContent = `${actionText === 'enable' ? 'Enabling' : 'Disabling'} ads for ${allEmbeds.length} embeds...`;
        let successCount = 0;
        let errorCount = 0;

        // Toggle each embed
        for (const embed of allEmbeds) {
            // Only toggle if current state is different from target state
            if (embed.currentState !== targetState) {
                try {
                    const response = await fetch(`${API_BASE_URL}/admin/ads/episodes/${embed.episodeId}/embeds/${embed.embedId}/toggle`, {
                        method: 'PATCH',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'X-API-Key': API_KEY,
                            'Content-Type': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();
                    if (result.success) {
                        successCount++;
                    } else {
                        errorCount++;
                    }
                } catch (error) {
                    console.error(`Error toggling embed ${embed.embedId}:`, error);
                    errorCount++;
                }
            } else {
                // Already in target state, count as success
                successCount++;
            }
        }

        // Reload TV show data
        await loadTVShow(true);

        // Show result
        loadingMsg.textContent = `${actionText === 'enable' ? 'Enabled' : 'Disabled'} ads for ${successCount} embeds${errorCount > 0 ? ` (${errorCount} errors)` : ''}`;
        loadingMsg.style.backgroundColor = errorCount > 0 ? '#f59e0b' : '#16a34a';

        setTimeout(() => {
            if (loadingMsg.parentNode) {
                loadingMsg.parentNode.removeChild(loadingMsg);
            }
        }, 3000);
    };

    async function saveEpisodeDownload(event) {
        event.preventDefault();
        
        if (!window.currentEpisodeId) {
            alert('Episode ID not set');
            return;
        }

        const token = localStorage.getItem('adminAccessToken');
        const episodeId = window.currentEpisodeId;
        const downloadId = document.getElementById('episode-download-id').value;
        
        const data = {
            server_name: document.getElementById('episode-download-server-name').value,
            download_url: document.getElementById('episode-download-url').value,
            quality: document.getElementById('episode-download-quality').value,
            size: document.getElementById('episode-download-size').value,
            priority: parseInt(document.getElementById('episode-download-priority').value) || 0,
            is_active: document.getElementById('episode-download-is-active').checked
        };

        try {
            let response;
            if (downloadId) {
                // Update
                response = await fetch(`${API_BASE_URL}/downloads/episodes/${episodeId}/${downloadId}`, {
                    method: 'PATCH',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
            } else {
                // Create
                response = await fetch(`${API_BASE_URL}/downloads/episodes/${episodeId}`, {
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
                // Reset form for next entry
                document.getElementById('episode-download-form').reset();
                document.getElementById('episode-download-id').value = '';
                document.getElementById('episode-download-priority').value = 0;
                document.getElementById('episode-download-is-active').checked = true;
                
                // Reload downloads
                loadEpisodeDownloads(episodeId);
                alert(result.message || 'Download saved successfully');
            } else {
                alert(result.message || 'Error saving download');
            }
        } catch (error) {
            console.error('Error saving episode download:', error);
            alert('Error saving download. Please try again.');
        }
    }

    function editEpisodeDownload(downloadId) {
        console.log('[Episode Downloads] Edit download:', downloadId);
        const downloads = Array.from(document.querySelectorAll('#episode-downloads-list > div'));
        const downloadElement = downloads.find(el => {
            const editBtn = el.querySelector('button[onclick*="editEpisodeDownload"]');
            if (editBtn) {
                const match = editBtn.getAttribute('onclick').match(/editEpisodeDownload\((\d+)\)/);
                return match && parseInt(match[1]) === downloadId;
            }
            return false;
        });
        
        if (downloadElement) {
            // Fetch current download data
            fetch(`${API_BASE_URL}/downloads/episodes/${window.currentEpisodeId}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('adminAccessToken')}`
                }
            })
            .then(res => res.json())
            .then(data => {
                const download = data.data.find(d => d.id === downloadId);
                if (download) {
                    document.getElementById('episode-download-id').value = download.id;
                    document.getElementById('episode-download-server-name').value = download.server_name || '';
                    document.getElementById('episode-download-url').value = download.download_url || '';
                    document.getElementById('episode-download-quality').value = download.quality || '';
                    document.getElementById('episode-download-size').value = download.size || '';
                    document.getElementById('episode-download-priority').value = download.priority || 0;
                    document.getElementById('episode-download-is-active').checked = download.is_active !== false;
                    document.getElementById('episode-download-form-title').textContent = 'Edit Download';
                    
                    // Scroll to form
                    document.querySelector('#episode-download-form').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        }
    }

    async function deleteEpisodeDownload(downloadId) {
        if (!confirm('Are you sure you want to delete this download?')) {
            return;
        }

        const token = localStorage.getItem('adminAccessToken');
        if (!window.currentEpisodeId) return;

        try {
            const response = await fetch(`${API_BASE_URL}/downloads/episodes/${window.currentEpisodeId}/${downloadId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();
            if (result.success) {
                loadEpisodeDownloads(window.currentEpisodeId);
                alert('Download deleted successfully');
            } else {
                alert(result.message || 'Error deleting download');
            }
        } catch (error) {
            console.error('Error deleting episode download:', error);
            alert('Error deleting download. Please try again.');
        }
    }

    // Episode Edit and Delete Functions
    function openEditEpisodeModal(episode) {
        console.log('[Episode Edit] Opening modal for episode:', episode);
        document.getElementById('episode-edit-id').value = episode.id;
        document.getElementById('episode-edit-number').value = episode.episode_number;
        document.getElementById('episode-edit-name').value = episode.name || '';
        document.getElementById('episode-edit-overview').value = episode.overview || '';
        document.getElementById('episode-edit-still-path').value = episode.still_path || '';
        document.getElementById('episode-edit-air-date').value = episode.air_date || '';
        document.getElementById('episode-edit-runtime').value = episode.runtime || '';
        document.getElementById('episode-edit-vote-average').value = episode.vote_average || 0;
        document.getElementById('episode-edit-vote-count').value = episode.vote_count || 0;
        
        document.getElementById('episode-edit-modal').style.display = 'flex';
    }

    function closeEpisodeEditModal() {
        document.getElementById('episode-edit-modal').style.display = 'none';
        document.getElementById('episode-edit-form').reset();
        document.getElementById('episode-edit-id').value = '';
    }

    // Add Episode Functions
    function openAddEpisodeModal(seasonId, seasonNumber) {
        console.log('[Add Episode] Opening modal for season:', seasonId, seasonNumber);
        
        // Get the season to find the next episode number
        const season = tvShow?.seasons?.find(s => s.id === seasonId);
        const existingEpisodes = season?.episodes || [];
        const nextEpisodeNumber = existingEpisodes.length > 0 
            ? Math.max(...existingEpisodes.map(e => e.episode_number || 0)) + 1
            : 1;
        
        document.getElementById('episode-add-season-id').value = seasonId;
        document.getElementById('episode-add-season-number').value = seasonNumber;
        document.getElementById('episode-add-number').value = nextEpisodeNumber;
        document.getElementById('episode-add-name').value = '';
        document.getElementById('episode-add-overview').value = '';
        document.getElementById('episode-add-still-path').value = '';
        document.getElementById('episode-add-air-date').value = '';
        document.getElementById('episode-add-runtime').value = '';
        document.getElementById('episode-add-vote-average').value = '0';
        document.getElementById('episode-add-vote-count').value = '0';
        
        document.getElementById('episode-add-modal-title').textContent = `Add New Episode - Season ${seasonNumber}`;
        document.getElementById('episode-add-modal').style.display = 'flex';
    }

    function closeAddEpisodeModal() {
        document.getElementById('episode-add-modal').style.display = 'none';
        document.getElementById('episode-add-form').reset();
        document.getElementById('episode-add-season-id').value = '';
        document.getElementById('episode-add-season-number').value = '';
    }

    async function saveNewEpisode() {
        const token = localStorage.getItem('adminAccessToken');
        const seasonId = parseInt(document.getElementById('episode-add-season-id').value);
        const seasonNumber = parseInt(document.getElementById('episode-add-season-number').value);
        
        if (!seasonId || !seasonNumber) {
            alert('Season information not set');
            return;
        }

        // Get the season to preserve existing data
        const season = tvShow?.seasons?.find(s => s.id === seasonId);
        if (!season) {
            alert('Season not found');
            return;
        }

        const episodeNumber = parseInt(document.getElementById('episode-add-number').value);
        
        // Check if episode number already exists
        const existingEpisode = (season.episodes || []).find(e => e.episode_number === episodeNumber);
        if (existingEpisode) {
            alert(`Episode ${episodeNumber} already exists in this season!`);
            return;
        }

        // Prepare episode data
        const episodeData = {
            episode_number: episodeNumber,
            name: document.getElementById('episode-add-name').value.trim() || null,
            overview: document.getElementById('episode-add-overview').value.trim() || null,
            still_path: document.getElementById('episode-add-still-path').value.trim() || null,
            air_date: document.getElementById('episode-add-air-date').value || null,
            runtime: document.getElementById('episode-add-runtime').value ? parseInt(document.getElementById('episode-add-runtime').value) : null,
            vote_average: document.getElementById('episode-add-vote-average').value ? parseFloat(document.getElementById('episode-add-vote-average').value) : 0,
            vote_count: document.getElementById('episode-add-vote-count').value ? parseInt(document.getElementById('episode-add-vote-count').value) : 0,
            embeds: [],
            downloads: []
        };

        // Prepare season data with the new episode
        const seasonData = {
            season_number: seasonNumber,
            name: season.name || `Season ${seasonNumber}`,
            overview: season.overview || null,
            poster_path: season.poster_path || null,
            air_date: season.air_date || null,
            episodes: [...(season.episodes || []), episodeData]
        };

        try {
            const submitBtn = document.querySelector('#episode-add-form button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';

            const response = await fetch(`${API_BASE_URL}/admin/tvshows/${tvshowId}/seasons`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    seasons: [seasonData]
                })
            });

            const result = await response.json();

            if (result.success) {
                closeAddEpisodeModal();
                // Reload TV show to get updated episode data
                await loadTVShow(true);
                alert(`Episode ${episodeNumber} created successfully!`);
            } else {
                let errorMsg = result.message || 'Error creating episode';
                if (result.errors) {
                    errorMsg = Object.entries(result.errors)
                        .map(([field, messages]) => `${field}: ${Array.isArray(messages) ? messages.join(', ') : messages}`)
                        .join('\n');
                }
                alert(errorMsg);
            }
        } catch (error) {
            console.error('Error creating episode:', error);
            alert('Error creating episode. Please try again.');
        } finally {
            const submitBtn = document.querySelector('#episode-add-form button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Episode';
            }
        }
    }

    async function saveEpisodeEdit() {
        const token = localStorage.getItem('adminAccessToken');
        const episodeId = document.getElementById('episode-edit-id').value;
        
        if (!episodeId) {
            alert('Episode ID not set');
            return;
        }

        const data = {
            name: document.getElementById('episode-edit-name').value,
            overview: document.getElementById('episode-edit-overview').value,
            still_path: document.getElementById('episode-edit-still-path').value,
            air_date: document.getElementById('episode-edit-air-date').value || null,
            runtime: document.getElementById('episode-edit-runtime').value ? parseInt(document.getElementById('episode-edit-runtime').value) : null,
            vote_average: document.getElementById('episode-edit-vote-average').value ? parseFloat(document.getElementById('episode-edit-vote-average').value) : 0,
            vote_count: document.getElementById('episode-edit-vote-count').value ? parseInt(document.getElementById('episode-edit-vote-count').value) : 0,
        };

              try {
          const response = await fetch(`${API_BASE_URL}/episodes/${episodeId}`, {
              method: 'PATCH',
              headers: {
                  'Authorization': `Bearer ${token}`,
                  'X-API-Key': API_KEY,
                  'Content-Type': 'application/json'
              },
              body: JSON.stringify(data)
          });

            const result = await response.json();
            if (result.success) {
                closeEpisodeEditModal();
                // Reload TV show to get updated episode data
                await loadTVShow(true);
                alert('Episode updated successfully');
            } else {
                alert(result.message || 'Error updating episode');
            }
        } catch (error) {
            console.error('Error updating episode:', error);
            alert('Error updating episode. Please try again.');
        }
    }

    async function deleteEpisode(episodeId, episodeNumber, episodeName) {
        if (!confirm(`Are you sure you want to delete Episode ${episodeNumber}: ${episodeName}?`)) {
            return;
        }

        const token = localStorage.getItem('adminAccessToken');

        try {
            const response = await fetch(`${API_BASE_URL}/episodes/${episodeId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();
            if (result.success) {
                // Reload TV show to refresh episode list
                await loadTVShow(true);
                alert('Episode deleted successfully');
            } else {
                alert(result.message || 'Error deleting episode');
            }
        } catch (error) {
            console.error('Error deleting episode:', error);
            alert('Error deleting episode. Please try again.');
        }
    }

    async function fetchUtilsData() {
        try {
            const response = await fetch(`${API_BASE_URL}/utils/all`);
            const data = await response.json();
            if (data.success) {
                utilsData = data.data;
                
                // Populate dropdowns
                const dubbingLangSelect = document.getElementById('edit-dubbing-language');
                if (dubbingLangSelect && utilsData.languages) {
                    utilsData.languages.forEach(lang => {
                        const option = document.createElement('option');
                        option.value = lang.id;
                        option.textContent = lang.name;
                        dubbingLangSelect.appendChild(option);
                    });
                }

                const categorySelect = document.getElementById('edit-category');
                if (categorySelect && utilsData.categories) {
                    utilsData.categories.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.id;
                        option.textContent = cat.name;
                        categorySelect.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.error('Error fetching utility data:', error);
        }
    }

    // Server selection modal functions
    window.openServerSelectionModal = function(fetchFunction) {
        pendingFetchOperation = fetchFunction;
        selectedServers = [];
        // Reset checkboxes
        document.getElementById('server-vidsrc-pro').checked = false;
        document.getElementById('server-vidsrc-icu').checked = false;
        document.getElementById('server-vidsrc-fast').checked = false;
        document.getElementById('server-selection-modal').style.display = 'flex';
    };

    window.closeServerSelectionModal = function() {
        document.getElementById('server-selection-modal').style.display = 'none';
    };

    window.confirmServerSelection = function() {
        // Get selected servers
        selectedServers = [];
        if (document.getElementById('server-vidsrc-pro').checked) {
            selectedServers.push('vidsrc-pro');
        }
        if (document.getElementById('server-vidsrc-icu').checked) {
            selectedServers.push('vidsrc-icu');
        }
        if (document.getElementById('server-vidsrc-fast').checked) {
            selectedServers.push('vidsrc-fast');
        }

        document.getElementById('server-selection-modal').style.display = 'none';
        
        // Execute the pending fetch operation
        if (pendingFetchOperation) {
            const fetchFn = pendingFetchOperation;
            pendingFetchOperation = null;
            fetchFn();
        }
    };

    // Handle skip button - fetch without servers
    window.skipServerSelection = function() {
        selectedServers = [];
        document.getElementById('server-selection-modal').style.display = 'none';
        
        // Execute the pending fetch operation without servers
        if (pendingFetchOperation) {
            const fetchFn = pendingFetchOperation;
            pendingFetchOperation = null;
            fetchFn();
        }
    };

    // Function to add vidsrc embeds to episodes after they're saved
    async function addVidsrcEmbedsToEpisodes(episodes, seasonNumber) {
        if (!tvShow || !tvShow.tmdb_id || selectedServers.length === 0 || !episodes || episodes.length === 0) {
            return;
        }

        const tmdbId = tvShow.tmdb_id;
        const token = localStorage.getItem('adminAccessToken');
        
        // Server configurations
        const serverConfigs = {
            'vidsrc-pro': {
                server_name: 'VidSrc Pro',
                base_url: 'https://vidlink.pro/tv',
                priority: 1
            },
            'vidsrc-icu': {
                server_name: 'VidSrc ICU',
                base_url: 'https://vidsrc.icu/embed/tv',
                priority: 2
            },
            'vidsrc-fast': {
                server_name: 'VidSrc Fast',
                base_url: 'https://vidfast.pro/tv',
                priority: 3
            }
        };

        // Add embeds to each episode via API
        for (const episode of episodes) {
            if (!episode.id) {
                console.warn('[Add Embeds] Episode missing ID, skipping:', episode);
                continue;
            }

            for (const serverType of selectedServers) {
                const config = serverConfigs[serverType];
                if (config) {
                    const embedUrl = `${config.base_url}/${tmdbId}/${seasonNumber}/${episode.episode_number}`;
                    
                    try {
                        const response = await fetch(`${API_BASE_URL}/embeds/episodes/${episode.id}`, {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'X-API-Key': API_KEY,
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                server_name: config.server_name,
                                embed_url: embedUrl,
                                priority: config.priority,
                                is_active: true,
                                requires_ad: true
                            })
                        });

                        if (response.ok) {
                            const result = await response.json();
                            if (!result.success) {
                                console.warn(`[Add Embeds] Failed to add ${config.server_name} to episode ${episode.episode_number}:`, result.message);
                            }
                        } else {
                            console.warn(`[Add Embeds] HTTP error adding ${config.server_name} to episode ${episode.episode_number}:`, response.status);
                        }
                    } catch (error) {
                        console.error(`[Add Embeds] Error adding ${config.server_name} to episode ${episode.episode_number}:`, error);
                    }
                }
            }
        }
    }

    // Add VidSrc servers to an existing season
    window.addVidsrcServersToSeason = function(seasonId, seasonNumber) {
        console.log('[Add VidSrc Servers] Called for season:', seasonId, seasonNumber);
        
        if (!tvShow || !tvShow.tmdb_id) {
            alert('TV show must have a TMDB ID to add VidSrc servers. Please add TMDB ID first.');
            return;
        }

        // Find the season
        const season = tvShow.seasons.find(s => s.id === seasonId);
        if (!season) {
            alert('Season not found.');
            return;
        }

        // Check if season has episodes
        if (!season.episodes || season.episodes.length === 0) {
            alert('This season has no episodes. Please add episodes first.');
            return;
        }

        // Store season info for the callback
        const seasonEpisodes = season.episodes;
        
        // Open server selection modal with callback
        openServerSelectionModal(async function() {
            // This callback runs after server selection is confirmed
            if (selectedServers.length > 0) {
                console.log('[Add VidSrc Servers] Adding embeds to', seasonEpisodes.length, 'episodes...');
                
                // Show loading indicator
                const loadingMsg = document.createElement('div');
                loadingMsg.id = 'vidsrc-loading-msg';
                loadingMsg.style.cssText = 'position: fixed; top: 20px; right: 20px; background-color: #1a1a1a; color: white; padding: 16px 24px; border-radius: 8px; border: 1px solid #2a2a2a; z-index: 2000; box-shadow: 0 4px 12px rgba(0,0,0,0.5);';
                loadingMsg.textContent = 'Adding VidSrc servers to episodes...';
                document.body.appendChild(loadingMsg);

                try {
                    // Add embeds to episodes
                    await addVidsrcEmbedsToEpisodes(seasonEpisodes, seasonNumber);
                    
                    // Reload TV show data to show the new embeds
                    await loadTVShow(true);
                    
                    // Show success message
                    loadingMsg.textContent = `Successfully added VidSrc servers to ${seasonEpisodes.length} episodes!`;
                    loadingMsg.style.backgroundColor = '#16a34a';
                    
                    setTimeout(() => {
                        if (loadingMsg.parentNode) {
                            loadingMsg.parentNode.removeChild(loadingMsg);
                        }
                    }, 3000);
                } catch (error) {
                    console.error('[Add VidSrc Servers] Error:', error);
                    loadingMsg.textContent = 'Error adding VidSrc servers. Please try again.';
                    loadingMsg.style.backgroundColor = '#dc2626';
                    
                    setTimeout(() => {
                        if (loadingMsg.parentNode) {
                            loadingMsg.parentNode.removeChild(loadingMsg);
                        }
                    }, 3000);
                }
            } else {
                console.log('[Add VidSrc Servers] No servers selected, skipping...');
            }
        });
    };

    // Open modal to fetch season from TMDB
    window.openFetchSeasonModal = function() {
        console.log('[Fetch Season] Opening fetch season modal');
        
        if (!tvShow || !tvShow.tmdb_id) {
            alert('TV show must have a TMDB ID to fetch seasons. Please add TMDB ID first.');
            return;
        }

        console.log('[Fetch Season] TV Show TMDB ID:', tvShow.tmdb_id);

        const seasonNumber = prompt(`Enter Season Number to fetch from TMDB:\n\nTV Show: ${tvShow.name || 'Untitled'}\nTMDB ID: ${tvShow.tmdb_id}`);
        if (seasonNumber === null || seasonNumber === '') {
            console.log('[Fetch Season] User cancelled');
            return;
        }

        const seasonNum = parseInt(seasonNumber);
        if (isNaN(seasonNum) || seasonNum < 0) {
            alert('Please enter a valid season number.');
            return;
        }

        console.log('[Fetch Season] User entered season number:', seasonNum);

        if (confirm(`Fetch Season ${seasonNum} with ALL episodes from TMDB?\n\nThis will fetch the complete season data including all episodes.`)) {
            console.log('[Fetch Season] User confirmed, showing server selection...');
            // Show server selection modal first
            openServerSelectionModal(function() {
                fetchSeasonFromTMDB(seasonNum);
            });
        } else {
            console.log('[Fetch Season] User cancelled confirmation');
        }
    };

    // Open modal to fetch single episode from TMDB
    window.openFetchEpisodeModal = function() {
        console.log('[Fetch Episode] Opening fetch episode modal');
        
        if (!tvShow || !tvShow.tmdb_id) {
            alert('TV show must have a TMDB ID to fetch episodes. Please add TMDB ID first.');
            return;
        }

        console.log('[Fetch Episode] TV Show TMDB ID:', tvShow.tmdb_id);

        const seasonNumber = prompt(`Enter Season Number:\n\nTV Show: ${tvShow.name || 'Untitled'}\nTMDB ID: ${tvShow.tmdb_id}`);
        if (seasonNumber === null || seasonNumber === '') {
            console.log('[Fetch Episode] User cancelled season input');
            return;
        }
        
        const seasonNum = parseInt(seasonNumber);
        if (isNaN(seasonNum) || seasonNum < 0) {
            alert('Please enter a valid season number.');
            return;
        }

        console.log('[Fetch Episode] User entered season number:', seasonNum);

        const episodeNumber = prompt(`Enter Episode Number:\n\nSeason: ${seasonNum}`);
        if (episodeNumber === null || episodeNumber === '') {
            console.log('[Fetch Episode] User cancelled episode input');
            return;
        }
        
        const episodeNum = parseInt(episodeNumber);
        if (isNaN(episodeNum) || episodeNum < 1) {
            alert('Please enter a valid episode number.');
            return;
        }

        console.log('[Fetch Episode] User entered episode number:', episodeNum);

        if (confirm(`Fetch Season ${seasonNum}, Episode ${episodeNum} from TMDB?\n\nThis will fetch the episode data from TMDB.`)) {
            console.log('[Fetch Episode] User confirmed, fetching...');
            fetchEpisodeFromTMDB(seasonNum, episodeNum);
        } else {
            console.log('[Fetch Episode] User cancelled confirmation');
        }
    };

    // Fetch season from TMDB and save
    window.fetchSeasonFromTMDB = async function(seasonNumber) {
        console.log('[Fetch Season] fetchSeasonFromTMDB called with season:', seasonNumber);
        
        if (!tvShow || !tvShow.tmdb_id) {
            alert('TV show must have a TMDB ID.');
            return;
        }

        try {
            const tmdbUrl = `${TMDB_BASE_URL}/tv/${tvShow.tmdb_id}/season/${seasonNumber}`;
            console.log('[Fetch Season] Fetching from TMDB:', tmdbUrl);
            console.log('[Fetch Season] TMDB_BASE_URL:', TMDB_BASE_URL);
            console.log('[Fetch Season] TMDB_ACCESS_TOKEN exists:', !!TMDB_ACCESS_TOKEN);
            
            const response = await fetch(tmdbUrl, {
                headers: {
                    'Authorization': `Bearer ${TMDB_ACCESS_TOKEN}`,
                    'accept': 'application/json'
                }
            });

            console.log('[Fetch Season] TMDB response status:', response.status);
            console.log('[Fetch Season] TMDB response ok:', response.ok);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('[Fetch Season] TMDB error response:', errorText);
                throw new Error(`TMDB API error: ${response.status} - ${errorText.substring(0, 100)}`);
            }

            const seasonData = await response.json();
            console.log('[Fetch Season] TMDB season data received, episodes:', seasonData.episodes?.length || 0);
            
            // Format season data for our API
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

            // Save to backend
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
            console.log('[Fetch Season] Save result:', saveResult);
            
            if (saveResult.success) {
                // Reload TV show data first to get episode IDs
                await loadTVShow(true);
                
                // If servers were selected, add embeds to episodes
                if (selectedServers.length > 0 && tvShow && tvShow.seasons) {
                    const savedSeason = tvShow.seasons.find(s => s.season_number === seasonNumber);
                    if (savedSeason && savedSeason.episodes && savedSeason.episodes.length > 0) {
                        console.log('[Fetch Season] Adding embeds to episodes...');
                        await addVidsrcEmbedsToEpisodes(savedSeason.episodes, seasonNumber);
                        // Reload again to show the new embeds
                        await loadTVShow(true);
                    }
                }
                
                alert(`Successfully added Season ${seasonNumber} with ${formattedSeason.episodes.length} episodes!`);
            } else {
                console.error('[Fetch Season] Error saving season:', saveResult);
                alert('Error saving season: ' + (saveResult.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('[Fetch Season] Exception:', error);
            console.error('[Fetch Season] Error stack:', error.stack);
            alert('Error fetching season from TMDB: ' + error.message);
        }
    };

    // Fetch single episode from TMDB and save
    window.fetchEpisodeFromTMDB = async function(seasonNumber, episodeNumber) {
        console.log('[Fetch Episode] fetchEpisodeFromTMDB called with season:', seasonNumber, 'episode:', episodeNumber);
        
        if (!tvShow || !tvShow.tmdb_id) {
            alert('TV show must have a TMDB ID.');
            return;
        }

        try {
            const episodeUrl = `${TMDB_BASE_URL}/tv/${tvShow.tmdb_id}/season/${seasonNumber}/episode/${episodeNumber}`;
            console.log('[Fetch Episode] Fetching episode from TMDB:', episodeUrl);
            
            const response = await fetch(episodeUrl, {
                headers: {
                    'Authorization': `Bearer ${TMDB_ACCESS_TOKEN}`,
                    'accept': 'application/json'
                }
            });

            console.log('[Fetch Episode] Episode response status:', response.status);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('[Fetch Episode] TMDB episode error:', errorText);
                throw new Error(`TMDB API error: ${response.status} - ${errorText.substring(0, 100)}`);
            }

            const episodeData = await response.json();
            console.log('[Fetch Episode] Episode data received:', episodeData.name || `Episode ${episodeNumber}`);
            
            // Get season info first (we need it to create/update the season)
            const seasonUrl = `${TMDB_BASE_URL}/tv/${tvShow.tmdb_id}/season/${seasonNumber}`;
            console.log('[Fetch Episode] Fetching season info from TMDB:', seasonUrl);
            
            const seasonResponse = await fetch(seasonUrl, {
                headers: {
                    'Authorization': `Bearer ${TMDB_ACCESS_TOKEN}`,
                    'accept': 'application/json'
                }
            });
            
            console.log('[Fetch Episode] Season response status:', seasonResponse.status);
            
            const seasonData = await seasonResponse.json();
            console.log('[Fetch Episode] Season data received');

            // Format season with single episode
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

            // Save to backend
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
            console.log('[Fetch Episode] Save result:', saveResult);
            
            if (saveResult.success) {
                alert(`Successfully added Season ${seasonNumber}, Episode ${episodeNumber}!`);
                // Reload TV show data
                await loadTVShow();
            } else {
                console.error('[Fetch Episode] Error saving episode:', saveResult);
                alert('Error saving episode: ' + (saveResult.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('[Fetch Episode] Exception:', error);
            console.error('[Fetch Episode] Error stack:', error.stack);
            alert('Error fetching episode from TMDB: ' + error.message);
        }
    };

    // Open modal to create new season
    window.openCreateSeasonModal = function() {
        const seasonNumber = prompt('Enter Season Number:');
        if (seasonNumber === null || seasonNumber === '') return;

        const seasonNum = parseInt(seasonNumber);
        if (isNaN(seasonNum) || seasonNum < 0) {
            alert('Please enter a valid season number.');
            return;
        }

        const seasonName = prompt('Enter Season Name (optional):') || `Season ${seasonNum}`;
        const seasonOverview = prompt('Enter Season Overview (optional):') || '';
        const seasonPosterPath = prompt('Enter Poster Path (optional, TMDB format like /path/to/poster.jpg):') || '';
        const seasonAirDate = prompt('Enter Air Date (optional, YYYY-MM-DD):') || '';

        if (confirm(`Create Season ${seasonNum}?\nName: ${seasonName}`)) {
            createSeason({
                season_number: seasonNum,
                name: seasonName,
                overview: seasonOverview || null,
                poster_path: seasonPosterPath || null,
                air_date: seasonAirDate || null,
                episodes: [] // Empty season, episodes can be added later
            });
        }
    };

    // Open modal to edit season
    window.openEditSeasonModal = function(seasonId, seasonNumber, seasonName, seasonOverview, posterPath, airDate) {
        console.log('[Season Edit] Opening edit modal for season:', seasonId, seasonNumber);
        
        const newName = prompt('Enter Season Name:', seasonName || `Season ${seasonNumber}`);
        if (newName === null) {
            console.log('[Season Edit] User cancelled name input');
            return;
        }

        const newOverview = prompt('Enter Season Overview:', seasonOverview || '');
        if (newOverview === null) {
            console.log('[Season Edit] User cancelled overview input');
            return;
        }

        const newPosterPath = prompt('Enter Poster Path (TMDB format like /path/to/poster.jpg):', posterPath || '');
        if (newPosterPath === null) {
            console.log('[Season Edit] User cancelled poster path input');
            return;
        }

        const newAirDate = prompt('Enter Air Date (YYYY-MM-DD):', airDate || '');
        if (newAirDate === null) {
            console.log('[Season Edit] User cancelled air date input');
            return;
        }

        if (confirm(`Update Season ${seasonNumber}?`)) {
            console.log('[Season Edit] Updating season with data:', {
                season_number: seasonNumber,
                name: newName,
                overview: newOverview || null,
                poster_path: newPosterPath || null,
                air_date: newAirDate || null,
            });
            updateSeason(seasonId, {
                season_number: seasonNumber,
                name: newName,
                overview: newOverview || null,
                poster_path: newPosterPath || null,
                air_date: newAirDate || null,
            });
        } else {
            console.log('[Season Edit] User cancelled update confirmation');
        }
    };

    // Create new season
    async function createSeason(seasonData) {
        const token = localStorage.getItem('adminAccessToken');
        if (!token) {
            window.location.href = '/admin/login';
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}/admin/tvshows/${tvshowId}/seasons/create`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(seasonData)
            });

            if (response.status === 401) {
                window.location.href = '/admin/login';
                return;
            }

            const result = await response.json();

            if (result.success) {
                alert(`Season ${seasonData.season_number} created successfully!`);
                await loadTVShow();
            } else {
                alert('Error creating season: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error creating season:', error);
            alert('Error creating season: ' + error.message);
        }
    }

    // Update season
    async function updateSeason(seasonId, seasonData) {
        console.log('[Season Edit] updateSeason called with:', seasonId, seasonData);
        
        const token = localStorage.getItem('adminAccessToken');
        if (!token) {
            console.error('[Season Edit] No admin token found');
            window.location.href = '/admin/login';
            return;
        }

        try {
            const url = `${API_BASE_URL}/admin/tvshows/${tvshowId}/seasons/${seasonId}`;
            console.log('[Season Edit] Making PATCH request to:', url);
            console.log('[Season Edit] Request body:', JSON.stringify(seasonData));
            
            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(seasonData)
            });

            console.log('[Season Edit] Response status:', response.status);
            console.log('[Season Edit] Response ok:', response.ok);

            if (response.status === 401) {
                console.error('[Season Edit] Unauthorized');
                window.location.href = '/admin/login';
                return;
            }

            const result = await response.json();
            console.log('[Season Edit] Response data:', result);

            if (result.success) {
                alert(`Season ${seasonData.season_number} updated successfully!`);
                await loadTVShow();
            } else {
                const errorMsg = result.message || 'Unknown error';
                console.error('[Season Edit] Error from API:', errorMsg);
                if (result.errors) {
                    console.error('[Season Edit] Validation errors:', result.errors);
                    alert('Error updating season: ' + JSON.stringify(result.errors));
                } else {
                    alert('Error updating season: ' + errorMsg);
                }
            }
        } catch (error) {
            console.error('[Season Edit] Exception:', error);
            alert('Error updating season: ' + error.message);
        }
    }

    // Delete season
    window.deleteSeason = async function(seasonId, seasonName) {
        if (!confirm(`Are you sure you want to delete "${seasonName}"?\n\nThis action cannot be undone and will delete all associated episodes.`)) {
            return;
        }

        const token = localStorage.getItem('adminAccessToken');
        if (!token) {
            window.location.href = '/admin/login';
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}/admin/tvshows/${tvshowId}/seasons/${seasonId}`, {
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
                alert(`Season "${seasonName}" deleted successfully!`);
                await loadTVShow();
            } else {
                alert('Error deleting season: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error deleting season:', error);
            alert('Error deleting season: ' + error.message);
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        fetchUtilsData();
        loadTVShow();
    });
</script>
@endpush
@endsection

