@extends('layouts.admin')

@section('title', 'Create TV Show - Admin Panel')

@push('styles')
<style>
    .form-label.required::after {
        content: ' *';
        color: #dc2626;
        font-weight: 700;
        margin-left: 4px;
    }
    .section-title::before {
        content: '';
        width: 4px;
        height: 24px;
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        border-radius: 2px;
    }
    .episode-card {
        background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
        border: 2px solid #3a3a3a;
        border-radius: 12px;
        padding: 16px;
        transition: all 0.3s ease;
        position: relative;
    }
    .episode-card:hover {
        border-color: #dc2626;
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(220, 38, 38, 0.3);
    }
    .episode-card.selected {
        border-color: #dc2626;
        background: linear-gradient(135deg, #2a1a1a 0%, #3a2a2a 100%);
    }
    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    .action-btn:hover {
        transform: scale(1.1);
    }
    .season-selector {
        background: linear-gradient(135deg, #2a2a2a 0%, #1a1a1a 100%);
        border: 2px solid #3a3a3a;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
    }
    .season-checkbox {
        accent-color: #dc2626;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-900">
    <!-- Header -->
    <header class="bg-gray-800 border-b border-gray-700">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-6">
                    <a href="/admin/tvshows" class="text-gray-400 hover:text-white transition-colors">← Back to TV Shows</a>
                    <h1 class="text-xl font-bold text-white">Create New TV Show</h1>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <!-- TMDB Search Card (Optional Import) -->
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 rounded-xl shadow-lg hover:border-gray-600 hover:shadow-xl transition-all duration-300 p-6 mb-6">
            <div class="flex justify-between items-center mb-6 pb-5 border-b border-gray-700">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <div>
                        <h2 class="text-xl font-bold text-white">Import from TMDB / IMDB (Optional)</h2>
                        <p class="text-gray-400 text-sm mt-1">You can import TV show data from TMDB/IMDB or create a custom TV show manually below</p>
                    </div>
                </div>
                <div class="flex gap-3 items-center">
                    <input type="text" id="tmdb-search" placeholder="Search by title, TMDB ID, or IMDB ID (e.g., tt3896198)..." 
                           class="bg-gray-700 border-2 border-gray-600 text-white px-4 py-2.5 rounded-lg w-80 text-sm transition-all duration-200 focus:border-red-600 focus:ring-4 focus:ring-red-600/20 focus:outline-none placeholder:text-gray-500">
                    <button type="button" onclick="searchTMDBTVShow()" 
                            class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-semibold text-sm transition-all duration-200 flex items-center gap-2 shadow-lg shadow-red-600/30 hover:shadow-xl hover:shadow-red-600/40 hover:-translate-y-0.5">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <span>Search</span>
                    </button>
                </div>
            </div>
            <div id="tmdb-results" class="hidden"></div>
        </div>

        <!-- TV Show Form -->
        <div id="tvshow-form-section">
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 rounded-xl shadow-lg p-8 mb-6">
                <div class="mb-6 pb-4 border-b border-gray-700">
                    <h2 class="text-2xl font-bold text-white mb-2 flex items-center gap-3">
                        <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create Custom TV Show
                    </h2>
                    <p class="text-gray-400 text-sm">Fill in the form below to create a custom TV show. All fields marked with * are required. You can optionally import data from TMDB/IMDB above, or enter all information manually.</p>
                </div>
                <form id="create-tvshow-form" onsubmit="createTVShow(event)">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                        <!-- Left Column -->
                        <div>
                            <h3 class="text-2xl font-bold text-white mb-7 pb-4 border-b-2 border-gray-700 flex items-center gap-3">
                                <span class="w-1 h-6 bg-gradient-to-b from-red-600 to-red-700 rounded"></span>
                                Basic Information
                            </h3>
                            
                            <div class="mb-7">
                                <label class="form-label block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider required">Name</label>
                                <input type="text" id="name" name="name" required
                                       class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 focus:border-red-600 focus:ring-4 focus:ring-red-600/15 focus:outline-none">
                            </div>

                            <div class="mb-7">
                                <label class="form-label block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider">Slug</label>
                                <input type="text" id="slug" name="slug"
                                       class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 focus:border-red-600 focus:ring-4 focus:ring-red-600/15 focus:outline-none">
                                <p class="text-gray-500 text-xs mt-2 italic">💡 Leave empty to auto-generate from name</p>
                            </div>

                            <div class="mb-7">
                                <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider">Overview</label>
                                <textarea id="overview" name="overview" rows="6"
                                          class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 focus:border-red-600 focus:ring-4 focus:ring-red-600/15 focus:outline-none"></textarea>
                            </div>

                            <div class="mb-7">
                                <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider">Status</label>
                                <select id="status" name="status"
                                        class="w-full bg-white border-2 border-gray-300 text-gray-900 px-4 py-3.5 rounded-lg text-base">
                                    <option value="pending" selected>Pending</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="mb-7">
                                <label class="flex items-center gap-3 p-4 bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 rounded-lg cursor-pointer">
                                    <input type="checkbox" id="is_featured" name="is_featured" value="1" class="w-5 h-5 accent-red-600">
                                    <span class="text-gray-200 font-semibold">Mark as Featured</span>
                                </label>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div>
                            <h3 class="text-2xl font-bold text-white mb-7 pb-4 border-b-2 border-gray-700 flex items-center gap-3">
                                <span class="w-1 h-6 bg-gradient-to-b from-red-600 to-red-700 rounded"></span>
                                Additional Information
                            </h3>

                            <div class="mb-7">
                                <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider">Category</label>
                                <select id="category_id" name="category_id"
                                        class="w-full bg-white border-2 border-gray-300 text-gray-900 px-4 py-3.5 rounded-lg text-base">
                                    <option value="">Select category...</option>
                                </select>
                            </div>

                            <div class="mb-7">
                                <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider">Genres</label>
                                <div id="genres-wrapper" class="flex flex-wrap gap-3 mt-3 p-5 bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 rounded-lg min-h-20">
                                </div>
                            </div>

                            <div class="mb-7">
                                <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider">First Air Date</label>
                                <input type="date" id="first_air_date" name="first_air_date"
                                       class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base">
                            </div>

                            <div class="mb-7">
                                <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider">Original Language</label>
                                <input type="text" id="original_language" name="original_language" maxlength="5"
                                       class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base">
                            </div>

                            <div class="mb-7">
                                <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider">Dubbing Language</label>
                                <select id="dubbing_language_id" name="dubbing_language_id"
                                        class="w-full bg-white border-2 border-gray-300 text-gray-900 px-4 py-3.5 rounded-lg text-base">
                                    <option value="">Select dubbing language...</option>
                                </select>
                            </div>

                            <div class="mb-7">
                                <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider">TMDB ID</label>
                                <input type="number" id="tmdb_id" name="tmdb_id"
                                       class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 focus:border-red-600 focus:ring-4 focus:ring-red-600/15 focus:outline-none"
                                       placeholder="Optional: Enter TMDB ID manually">
                                <p class="text-gray-500 text-xs mt-2 italic">💡 Leave empty if creating a custom TV show</p>
                            </div>

                            <div class="mb-7">
                                <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider">IMDB ID</label>
                                <input type="text" id="imdb_id" name="imdb_id"
                                       class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 focus:border-red-600 focus:ring-4 focus:ring-red-600/15 focus:outline-none"
                                       placeholder="e.g., tt3896198 (Optional)">
                                <p class="text-gray-500 text-xs mt-2 italic">💡 Leave empty if creating a custom TV show</p>
                            </div>

                            <div class="mb-7">
                                <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider">Poster Path</label>
                                <input type="text" id="poster_path" name="poster_path"
                                       class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 focus:border-red-600 focus:ring-4 focus:ring-red-600/15 focus:outline-none"
                                       placeholder="/path/to/poster.jpg or full URL">
                                <p class="text-gray-500 text-xs mt-2 italic">💡 TMDB relative path (e.g., /w500/poster.jpg) or full URL</p>
                            </div>

                            <div class="mb-7">
                                <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider">Backdrop Path</label>
                                <input type="text" id="backdrop_path" name="backdrop_path"
                                       class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 focus:border-red-600 focus:ring-4 focus:ring-red-600/15 focus:outline-none"
                                       placeholder="/path/to/backdrop.jpg or full URL">
                                <p class="text-gray-500 text-xs mt-2 italic">💡 TMDB relative path (e.g., /original/backdrop.jpg) or full URL</p>
                            </div>

                            <div class="mb-7">
                                <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider">Vote Average</label>
                                <input type="number" id="vote_average" name="vote_average" step="0.1" min="0" max="10" value="0"
                                       class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 focus:border-red-600 focus:ring-4 focus:ring-red-600/15 focus:outline-none">
                            </div>

                            <div class="mb-7">
                                <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider">Vote Count</label>
                                <input type="number" id="vote_count" name="vote_count" min="0" value="0"
                                       class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 focus:border-red-600 focus:ring-4 focus:ring-red-600/15 focus:outline-none">
                            </div>

                            <div class="mb-7">
                                <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider">Popularity</label>
                                <input type="number" id="popularity" name="popularity" step="0.01" min="0" value="0"
                                       class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 focus:border-red-600 focus:ring-4 focus:ring-red-600/15 focus:outline-none">
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Season Selection Section (Only shown when importing from TMDB) -->
            <div id="seasons-section" class="hidden bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 rounded-xl shadow-lg p-8 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white flex items-center gap-3">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Select Seasons to Import (Optional)
                    </h3>
                    <button type="button" onclick="fetchSelectedSeasons()" 
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-lg font-semibold transition-all duration-200">
                        Fetch Selected Seasons
                    </button>
                </div>
                <div id="seasons-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"></div>
            </div>

            <!-- Custom Seasons & Episodes Management Section (Always visible) -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 rounded-xl shadow-lg p-8 mb-6">
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-700">
                    <div>
                        <h3 class="text-xl font-bold text-white flex items-center gap-3 mb-2">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Custom Seasons & Episodes (Optional)
                        </h3>
                        <p class="text-gray-400 text-sm">Manually add seasons and episodes, or import them from TMDB above. You can also add them after creating the TV show.</p>
                    </div>
                    <button type="button" onclick="addCustomSeason()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-semibold transition-all duration-200 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Add Season</span>
                    </button>
                </div>
                
                <!-- Custom Seasons Container -->
                <div id="custom-seasons-container" class="space-y-6">
                    <!-- Custom seasons will be added here -->
                </div>
            </div>

            <!-- Create TV Show Button (Always visible) -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 rounded-xl shadow-lg p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-white mb-1">Create TV Show</h3>
                        <p class="text-gray-400 text-sm">Fill in the required fields above and click the button to create your TV show. Seasons and episodes added above will be included.</p>
                    </div>
                    <button type="button" onclick="saveTVShow()" 
                            class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold text-lg transition-all duration-200 shadow-lg shadow-green-600/30 hover:shadow-xl hover:shadow-green-600/40 hover:-translate-y-0.5 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Create TV Show</span>
                    </button>
                </div>
            </div>

            <!-- Episodes Management Section -->
            <div id="episodes-section" class="hidden">
                <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 rounded-xl shadow-lg p-8 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-white flex items-center gap-3">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Episodes Management
                        </h3>
                        <div class="flex gap-3">
                            <button type="button" onclick="fetchBulkEpisodes()" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all">
                                Fetch All Episodes (Bulk)
                            </button>
                            <button type="button" onclick="saveTVShow()" 
                                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold transition-all">
                                Create TV Show
                            </button>
                        </div>
                    </div>
                    <div id="episodes-container" class="space-y-6"></div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Server Selection Modal for Episodes -->
<div id="server-selection-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
    <div class="bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4" style="pointer-events: auto; position: relative; z-index: 1001;">
        <div class="p-6 border-b border-gray-700 flex justify-between items-center">
            <h3 class="text-xl font-bold text-white">Include VidSrc Servers</h3>
            <button onclick="closeServerSelectionModal()" class="text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-300 mb-6">Would you like to automatically add VidSrc servers to all fetched episodes?</p>
            <div class="space-y-3 mb-6">
                <label class="flex items-start gap-3 cursor-pointer group hover:bg-gray-700/50 p-3 rounded-lg transition-colors">
                    <input type="checkbox" id="server-vidsrc-pro" value="vidsrc-pro"
                           class="mt-1 w-5 h-5 cursor-pointer accent-red-600 flex-shrink-0">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-white font-medium">VidSrc Pro</span>
                        </div>
                        <code class="bg-gray-900/50 px-2 py-1 rounded text-gray-300 text-xs block">https://vidlink.pro/tv/{tmdb_id}/{season}/{episode}</code>
                    </div>
                </label>
                <label class="flex items-start gap-3 cursor-pointer group hover:bg-gray-700/50 p-3 rounded-lg transition-colors">
                    <input type="checkbox" id="server-vidsrc-icu" value="vidsrc-icu"
                           class="mt-1 w-5 h-5 cursor-pointer accent-red-600 flex-shrink-0">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-white font-medium">VidSrc ICU</span>
                        </div>
                        <code class="bg-gray-900/50 px-2 py-1 rounded text-gray-300 text-xs block">https://vidsrc.icu/embed/tv/{tmdb_id}/{season}/{episode}</code>
                    </div>
                </label>
                <label class="flex items-start gap-3 cursor-pointer group hover:bg-gray-700/50 p-3 rounded-lg transition-colors">
                    <input type="checkbox" id="server-vidsrc-fast" value="vidsrc-fast"
                           class="mt-1 w-5 h-5 cursor-pointer accent-red-600 flex-shrink-0">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-white font-medium">VidSrc Fast</span>
                        </div>
                        <code class="bg-gray-900/50 px-2 py-1 rounded text-gray-300 text-xs block">https://vidfast.pro/tv/{tmdb_id}/{season}/{episode}</code>
                    </div>
                </label>
            </div>
            <div class="flex justify-end gap-3">
                <button onclick="skipServerSelection()" 
                        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2.5 rounded-lg font-semibold transition-all">
                    Skip
                </button>
                <button onclick="confirmServerSelection()" 
                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-lg font-semibold transition-all">
                    Continue
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Episode Embed/Download Modal -->
<div id="episode-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
    <div class="bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto" style="pointer-events: auto; position: relative; z-index: 1001;">
        <div class="p-6 border-b border-gray-700 flex justify-between items-center">
            <h3 class="text-xl font-bold text-white" id="modal-title">Manage Episode</h3>
            <button onclick="closeEpisodeModal()" class="text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="p-6" id="modal-content"></div>
    </div>
</div>

@push('scripts')
<script>
    (function() {
        const TMDB_BASE_URL = '{{ config("services.tmdb.base_url") }}';
        const TMDB_ACCESS_TOKEN = '{{ config("services.tmdb.access_token") }}';
        const TMDB_IMAGE_URL = '{{ config("services.tmdb.image_url") }}';
        const OMDB_BASE_URL = '{{ config("services.omdb.base_url") }}';
        const OMDB_API_KEY = '{{ config("services.omdb.api_key") }}';
        
        let selectedTVShow = null;
        let seasonsData = [];
        let selectedSeasons = [];
        let episodesData = {}; // { seasonNumber: [episodes] }
        let utilsData = {};
        let customSeasons = {}; // { seasonNumber: { name, overview, poster_path, air_date, episodes: [] } }
        let nextCustomSeasonNumber = 1;
        
        // Server selection state
        let pendingFetchOperation = null; // Stores the fetch function to execute after server selection
        let selectedServers = []; // Stores selected server types

        // Custom Seasons & Episodes Management Functions
        window.addCustomSeason = function() {
            // Find the next available season number
            const existingSeasonNumbers = Object.keys(customSeasons).map(Number).concat(Object.keys(episodesData).map(Number));
            let seasonNumber = nextCustomSeasonNumber;
            while (existingSeasonNumbers.includes(seasonNumber)) {
                seasonNumber++;
            }
            nextCustomSeasonNumber = seasonNumber + 1;

            // Initialize custom season
            customSeasons[seasonNumber] = {
                season_number: seasonNumber,
                name: `Season ${seasonNumber}`,
                overview: '',
                poster_path: '',
                air_date: '',
                episodes: []
            };

            renderCustomSeasons();
        };

        window.removeCustomSeason = function(seasonNumber) {
            if (confirm(`Are you sure you want to remove Season ${seasonNumber} and all its episodes?`)) {
                delete customSeasons[seasonNumber];
                renderCustomSeasons();
            }
        };

        window.addCustomEpisode = function(seasonNumber) {
            if (!customSeasons[seasonNumber]) {
                return;
            }

            const episodes = customSeasons[seasonNumber].episodes || [];
            const nextEpisodeNumber = episodes.length > 0 
                ? Math.max(...episodes.map(e => e.episode_number || 0)) + 1
                : 1;

            episodes.push({
                episode_number: nextEpisodeNumber,
                name: `Episode ${nextEpisodeNumber}`,
                overview: '',
                still_path: '',
                air_date: '',
                runtime: null,
                vote_average: 0,
                vote_count: 0
            });

            customSeasons[seasonNumber].episodes = episodes;
            renderCustomSeasons();
        };

        window.removeCustomEpisode = function(seasonNumber, episodeNumber) {
            if (!customSeasons[seasonNumber]) {
                return;
            }

            if (confirm(`Are you sure you want to remove Episode ${episodeNumber}?`)) {
                customSeasons[seasonNumber].episodes = customSeasons[seasonNumber].episodes.filter(
                    e => e.episode_number !== episodeNumber
                );
                renderCustomSeasons();
            }
        };

        window.updateCustomSeason = function(seasonNumber, field, value) {
            if (!customSeasons[seasonNumber]) {
                return;
            }
            customSeasons[seasonNumber][field] = value;
        };

        window.updateCustomEpisode = function(seasonNumber, episodeNumber, field, value) {
            if (!customSeasons[seasonNumber]) {
                return;
            }

            const episode = customSeasons[seasonNumber].episodes.find(e => e.episode_number === episodeNumber);
            if (episode) {
                if (field === 'episode_number' || field === 'runtime' || field === 'vote_average' || field === 'vote_count') {
                    episode[field] = value ? (field === 'vote_average' ? parseFloat(value) : parseInt(value)) : null;
                } else {
                    episode[field] = value;
                }
            }
        };

        function renderCustomSeasons() {
            const container = document.getElementById('custom-seasons-container');
            if (!container) return;

            const seasonNumbers = Object.keys(customSeasons).map(Number).sort((a, b) => a - b);

            if (seasonNumbers.length === 0) {
                container.innerHTML = '<p class="text-gray-400 text-center py-4">No custom seasons added yet. Click "Add Season" to create one.</p>';
                return;
            }

            container.innerHTML = seasonNumbers.map(seasonNum => {
                const season = customSeasons[seasonNum];
                const episodes = season.episodes || [];

                return `
                    <div class="bg-gray-700 rounded-lg p-6 border-2 border-gray-600">
                        <div class="flex justify-between items-start mb-4">
                            <h4 class="text-lg font-bold text-white">Season ${seasonNum}</h4>
                            <button onclick="removeCustomSeason(${seasonNum})" 
                                    class="text-red-500 hover:text-red-700 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-300 mb-2 text-sm font-semibold">Season Name</label>
                                <input type="text" value="${season.name || ''}" 
                                       onchange="updateCustomSeason(${seasonNum}, 'name', this.value)"
                                       class="w-full bg-gray-600 border border-gray-500 text-white px-3 py-2 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-gray-300 mb-2 text-sm font-semibold">Air Date</label>
                                <input type="date" value="${season.air_date || ''}" 
                                       onchange="updateCustomSeason(${seasonNum}, 'air_date', this.value)"
                                       class="w-full bg-gray-600 border border-gray-500 text-white px-3 py-2 rounded-lg text-sm">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-300 mb-2 text-sm font-semibold">Overview</label>
                            <textarea onchange="updateCustomSeason(${seasonNum}, 'overview', this.value)"
                                      class="w-full bg-gray-600 border border-gray-500 text-white px-3 py-2 rounded-lg text-sm" 
                                      rows="2">${season.overview || ''}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-300 mb-2 text-sm font-semibold">Poster Path</label>
                            <input type="text" value="${season.poster_path || ''}" 
                                   placeholder="Optional: /path/to/poster.jpg or full URL"
                                   onchange="updateCustomSeason(${seasonNum}, 'poster_path', this.value)"
                                   class="w-full bg-gray-600 border border-gray-500 text-white px-3 py-2 rounded-lg text-sm">
                        </div>

                        <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-600">
                            <h5 class="text-md font-semibold text-white">Episodes (${episodes.length})</h5>
                            <button onclick="addCustomEpisode(${seasonNum})" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Episode
                            </button>
                        </div>

                        <div class="space-y-4">
                            ${episodes.sort((a, b) => (a.episode_number || 0) - (b.episode_number || 0)).map(episode => `
                                <div class="bg-gray-600 rounded-lg p-4 border border-gray-500">
                                    <div class="flex justify-between items-start mb-3">
                                        <h6 class="text-sm font-bold text-white">Episode ${episode.episode_number}</h6>
                                        <button onclick="removeCustomEpisode(${seasonNum}, ${episode.episode_number})" 
                                                class="text-red-400 hover:text-red-600 text-sm">
                                            Remove
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-gray-300 mb-1 text-xs">Episode Name</label>
                                            <input type="text" value="${episode.name || ''}" 
                                                   onchange="updateCustomEpisode(${seasonNum}, ${episode.episode_number}, 'name', this.value)"
                                                   class="w-full bg-gray-500 border border-gray-400 text-white px-2 py-1.5 rounded text-xs">
                                        </div>
                                        <div>
                                            <label class="block text-gray-300 mb-1 text-xs">Air Date</label>
                                            <input type="date" value="${episode.air_date || ''}" 
                                                   onchange="updateCustomEpisode(${seasonNum}, ${episode.episode_number}, 'air_date', this.value)"
                                                   class="w-full bg-gray-500 border border-gray-400 text-white px-2 py-1.5 rounded text-xs">
                                        </div>
                                        <div>
                                            <label class="block text-gray-300 mb-1 text-xs">Runtime (minutes)</label>
                                            <input type="number" value="${episode.runtime || ''}" 
                                                   onchange="updateCustomEpisode(${seasonNum}, ${episode.episode_number}, 'runtime', this.value)"
                                                   class="w-full bg-gray-500 border border-gray-400 text-white px-2 py-1.5 rounded text-xs">
                                        </div>
                                        <div>
                                            <label class="block text-gray-300 mb-1 text-xs">Rating</label>
                                            <input type="number" step="0.1" min="0" max="10" value="${episode.vote_average || 0}" 
                                                   onchange="updateCustomEpisode(${seasonNum}, ${episode.episode_number}, 'vote_average', this.value)"
                                                   class="w-full bg-gray-500 border border-gray-400 text-white px-2 py-1.5 rounded text-xs">
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="block text-gray-300 mb-1 text-xs">Overview</label>
                                        <textarea onchange="updateCustomEpisode(${seasonNum}, ${episode.episode_number}, 'overview', this.value)"
                                                  class="w-full bg-gray-500 border border-gray-400 text-white px-2 py-1.5 rounded text-xs" 
                                                  rows="2">${episode.overview || ''}</textarea>
                                    </div>
                                    <div class="mt-3">
                                        <label class="block text-gray-300 mb-1 text-xs">Still Path</label>
                                        <input type="text" value="${episode.still_path || ''}" 
                                               placeholder="Optional: /path/to/still.jpg or full URL"
                                               onchange="updateCustomEpisode(${seasonNum}, ${episode.episode_number}, 'still_path', this.value)"
                                               class="w-full bg-gray-500 border border-gray-400 text-white px-2 py-1.5 rounded text-xs">
                                    </div>
                                    <div class="mt-3 flex gap-2">
                                        <button type="button" onclick="openEpisodeEmbeds(${seasonNum}, ${episode.episode_number})" 
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition-all flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                            Embeds
                                        </button>
                                        <button type="button" onclick="openEpisodeDownloads(${seasonNum}, ${episode.episode_number})" 
                                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition-all flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V8a9 9 0 10-18 0v4a3 3 0 003 3h8z"></path>
                                            </svg>
                                            Downloads
                                        </button>
                                    </div>
                                </div>
                            `).join('')}
                            ${episodes.length === 0 ? '<p class="text-gray-400 text-center text-sm py-2">No episodes added yet.</p>' : ''}
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Function to search by IMDB ID using OMDb API
        async function searchByIMDBID(imdbId) {
            try {
                const response = await fetch(`${OMDB_BASE_URL}/?i=${imdbId}&apikey=${OMDB_API_KEY}&type=series`);
                const data = await response.json();
                
                if (data.Response === 'True' && data.Type === 'series') {
                    // Try to find TMDB ID using external IDs
                    let tmdbId = null;
                    try {
                        // TMDB can find by IMDB ID using find API
                        const findResponse = await fetch(`${TMDB_BASE_URL}/find/${imdbId}?external_source=imdb_id`, {
                            headers: {
                                'Authorization': `Bearer ${TMDB_ACCESS_TOKEN}`,
                                'accept': 'application/json'
                            }
                        });
                        const findData = await findResponse.json();
                        if (findData.tv_results && findData.tv_results.length > 0) {
                            tmdbId = findData.tv_results[0].id;
                        }
                    } catch (error) {
                        console.warn('Could not find TMDB ID from IMDB ID:', error);
                    }
                    
                    // Map OMDb data to form
                    await selectOMDBTVShow(data, tmdbId);
                    return true;
                } else {
                    return false;
                }
            } catch (error) {
                console.error('Error fetching from OMDb:', error);
                return false;
            }
        }

        // Function to populate form from OMDb data
        async function selectOMDBTVShow(omdbData, tmdbId = null) {
            try {
                // Fill basic form fields from OMDb
                document.getElementById('name').value = omdbData.Title || '';
                
                // Auto-generate slug
                const slugValue = omdbData.Title ? omdbData.Title.toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '') : '';
                document.getElementById('slug').value = slugValue;
                
                document.getElementById('overview').value = omdbData.Plot || '';
                
                // Parse year from Year field (format: "2011–2019" or "2011")
                const yearMatch = omdbData.Year ? omdbData.Year.match(/^(\d{4})/) : null;
                if (yearMatch) {
                    document.getElementById('first_air_date').value = yearMatch[1] + '-01-01';
                }
                
                document.getElementById('original_language').value = omdbData.Language ? omdbData.Language.split(',')[0].trim().substring(0, 5) : '';
                
                // Set IMDB ID
                if (omdbData.imdbID) {
                    const imdbIdField = document.getElementById('imdb_id');
                    if (imdbIdField) {
                        imdbIdField.value = omdbData.imdbID;
                    }
                }
                
                // If we found TMDB ID, fetch full details from TMDB
                if (tmdbId) {
                    document.getElementById('tmdb_id').value = tmdbId;
                    // Fetch full details from TMDB for better data (but preserve IMDB ID)
                    await selectTMDBTVShow(tmdbId);
                    // Restore IMDB ID after TMDB import (TMDB may not have it)
                    if (omdbData.imdbID) {
                        const imdbIdField = document.getElementById('imdb_id');
                        if (imdbIdField) {
                            imdbIdField.value = omdbData.imdbID;
                        }
                    }
                    return;
                }
                
                // If no TMDB ID, fill what we can from OMDb
                document.getElementById('tmdb_id').value = '';
                
                // Poster path
                if (omdbData.Poster && omdbData.Poster !== 'N/A') {
                    document.getElementById('poster_path').value = omdbData.Poster;
                }
                
                // Backdrop path (OMDb doesn't provide this, so we'll leave it empty)
                document.getElementById('backdrop_path').value = '';
                
                // Ratings
                if (omdbData.imdbRating && omdbData.imdbRating !== 'N/A') {
                    document.getElementById('vote_average').value = parseFloat(omdbData.imdbRating).toFixed(1);
                }
                if (omdbData.imdbVotes && omdbData.imdbVotes !== 'N/A') {
                    document.getElementById('vote_count').value = parseInt(omdbData.imdbVotes.replace(/,/g, '')) || 0;
                }
                
                // Map genres
                if (omdbData.Genre && utilsData.genres) {
                    const omdbGenres = omdbData.Genre.split(',').map(g => g.trim());
                    utilsData.genres.forEach(genre => {
                        const checkbox = document.getElementById(`genre-${genre.id}`);
                        if (checkbox && omdbGenres.some(omdbGenre => 
                            genre.name.toLowerCase().includes(omdbGenre.toLowerCase()) || 
                            omdbGenre.toLowerCase().includes(genre.name.toLowerCase())
                        )) {
                            checkbox.checked = true;
                        }
                    });
                }
                
                // Show form (already visible for custom creation)
                document.getElementById('tvshow-form-section').classList.remove('hidden');
                document.getElementById('seasons-section').classList.add('hidden');
                document.getElementById('tmdb-results').style.display = 'none';
                
                // Scroll to form
                document.getElementById('tvshow-form-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
                
                // Show message
                alert('TV show data imported from IMDB! Note: Season/episode data requires TMDB ID. You can manually add seasons and episodes after creating the TV show.');
            } catch (error) {
                console.error('Error processing OMDb data:', error);
                alert('Error importing TV show data from IMDB.');
            }
        }

        window.searchTMDBTVShow = async function() {
            const query = document.getElementById('tmdb-search').value.trim();
            if (!query) {
                alert('Please enter a TV show title, TMDB ID, or IMDB ID to search');
                return;
            }

            const resultsDiv = document.getElementById('tmdb-results');
            resultsDiv.style.display = 'block';
            resultsDiv.innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 20px;">Searching...</p>';

            try {
                // Check if query is IMDB ID (format: tt followed by numbers)
                if (/^tt\d+$/i.test(query)) {
                    const found = await searchByIMDBID(query);
                    if (!found) {
                        resultsDiv.innerHTML = '<p style="color: #f87171; text-align: center; padding: 20px;">TV show with IMDB ID ' + query + ' not found.</p>';
                    }
                    return;
                }
                
                // Check if query is numeric (TMDB ID)
                if (/^\d+$/.test(query)) {
                    // Direct ID lookup - fetch TV show directly by ID
                    try {
                        const response = await fetch(`${TMDB_BASE_URL}/tv/${query}?append_to_response=seasons`, {
                            headers: {
                                'Authorization': `Bearer ${TMDB_ACCESS_TOKEN}`,
                                'accept': 'application/json'
                            }
                        });

                        if (response.ok) {
                            const tvShow = await response.json();
                            // Directly select the TV show
                            await selectTMDBTVShow(parseInt(query));
                            return;
                        } else {
                            // If ID not found, show error
                            resultsDiv.innerHTML = '<p style="color: #f87171; text-align: center; padding: 20px;">TV show with TMDB ID ' + query + ' not found.</p>';
                            return;
                        }
                    } catch (error) {
                        console.error('Error fetching TV show by ID:', error);
                        resultsDiv.innerHTML = '<p style="color: #f87171; text-align: center; padding: 20px;">Error fetching TV show by ID. Please try again.</p>';
                        return;
                    }
                }

                // If not numeric or IMDB ID, search by title (try both TMDB and OMDb)
                // First try TMDB
                try {
                    const response = await fetch(`${TMDB_BASE_URL}/search/tv?query=${encodeURIComponent(query)}`, {
                        headers: {
                            'Authorization': `Bearer ${TMDB_ACCESS_TOKEN}`,
                            'accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.results && data.results.length > 0) {
                        resultsDiv.innerHTML = `
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                                <h3 style="color: white; margin: 0;">Select a TV show:</h3>
                                <span style="color: #9ca3af; font-size: 14px;">${data.results.length} result${data.results.length !== 1 ? 's' : ''} found</span>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; max-height: 600px; overflow-y: auto; padding: 4px;">
                                ${data.results.map(tvshow => `
                                    <div onclick="selectTMDBTVShow(${tvshow.id})" style="background-color: #2a2a2a; border: 1px solid #3a3a3a; border-radius: 6px; padding: 8px; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.borderColor='#dc2626'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.3)'" onmouseout="this.style.borderColor='#3a3a3a'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                        <img src="${tvshow.poster_path ? `${TMDB_IMAGE_URL}/w154${tvshow.poster_path}` : '/images/placeholder.svg'}" 
                                             alt="${tvshow.name}" 
                                             style="width: 100%; aspect-ratio: 2/3; object-fit: cover; border-radius: 4px; margin-bottom: 6px; display: block;">
                                        <h4 style="color: white; font-size: 12px; font-weight: 600; margin: 0 0 3px 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; line-height: 1.3;">${tvshow.name}</h4>
                                        <p style="color: #9ca3af; font-size: 11px; margin: 0;">${tvshow.first_air_date ? new Date(tvshow.first_air_date).getFullYear() : 'N/A'}</p>
                                    </div>
                                `).join('')}
                            </div>
                        `;
                    } else {
                        // Try OMDb as fallback
                        try {
                            const omdbResponse = await fetch(`${OMDB_BASE_URL}/?t=${encodeURIComponent(query)}&apikey=${OMDB_API_KEY}&type=series`);
                            const omdbData = await omdbResponse.json();
                            if (omdbData.Response === 'True' && omdbData.Type === 'series') {
                                await selectOMDBTVShow(omdbData);
                                return;
                            }
                        } catch (omdbError) {
                            console.error('OMDb search error:', omdbError);
                        }
                        resultsDiv.innerHTML = '<p style="color: #f87171; text-align: center; padding: 20px;">No TV shows found.</p>';
                    }
                } catch (error) {
                    console.error('Error searching TMDB:', error);
                    resultsDiv.innerHTML = '<p style="color: #f87171; text-align: center; padding: 20px;">Error searching TMDB.</p>';
                }
            } catch (error) {
                console.error('Error searching:', error);
                resultsDiv.innerHTML = '<p style="color: #f87171; text-align: center; padding: 20px;">Error searching. Please try again.</p>';
            }
        };

        window.selectTMDBTVShow = async function(tmdbId) {
            try {
                const response = await fetch(`${TMDB_BASE_URL}/tv/${tmdbId}?append_to_response=seasons,external_ids`, {
                    headers: {
                        'Authorization': `Bearer ${TMDB_ACCESS_TOKEN}`,
                        'accept': 'application/json'
                    }
                });

                const tvShow = await response.json();
                selectedTVShow = tvShow;

                // Fill form
                document.getElementById('name').value = tvShow.name || '';
                document.getElementById('slug').value = tvShow.name ? tvShow.name.toLowerCase().replace(/[^\w\s-]/g, '').replace(/[\s_-]+/g, '-').replace(/^-+|-+$/g, '') : '';
                document.getElementById('overview').value = tvShow.overview || '';
                document.getElementById('first_air_date').value = tvShow.first_air_date || '';
                document.getElementById('original_language').value = tvShow.original_language || '';
                document.getElementById('tmdb_id').value = tvShow.id || '';
                
                // Get IMDB ID from external_ids if available
                if (tvShow.external_ids && tvShow.external_ids.imdb_id) {
                    const imdbIdField = document.getElementById('imdb_id');
                    if (imdbIdField) {
                        imdbIdField.value = tvShow.external_ids.imdb_id;
                    }
                }
                
                // Poster and backdrop paths (use relative paths for TMDB images)
                document.getElementById('poster_path').value = tvShow.poster_path ? tvShow.poster_path : '';
                document.getElementById('backdrop_path').value = tvShow.backdrop_path ? tvShow.backdrop_path : '';
                
                // Ratings and popularity
                document.getElementById('vote_average').value = tvShow.vote_average ? parseFloat(tvShow.vote_average).toFixed(1) : '0';
                document.getElementById('vote_count').value = tvShow.vote_count || '0';
                document.getElementById('popularity').value = tvShow.popularity ? parseFloat(tvShow.popularity).toFixed(2) : '0';

                // Map genres
                if (tvShow.genres && utilsData.genres) {
                    tvShow.genres.forEach(tmdbGenre => {
                        const ourGenre = utilsData.genres.find(g => g.tmdb_id === tmdbGenre.id);
                        if (ourGenre) {
                            const checkbox = document.getElementById(`genre-${ourGenre.id}`);
                            if (checkbox) checkbox.checked = true;
                        }
                    });
                }

                // Store seasons data
                seasonsData = tvShow.seasons || [];

                // Show form (already visible for custom creation)
                document.getElementById('tvshow-form-section').classList.remove('hidden');
                // Show seasons section only if there are seasons to import
                if (seasonsData && seasonsData.length > 0) {
                    document.getElementById('seasons-section').classList.remove('hidden');
                    renderSeasonsList();
                } else {
                    document.getElementById('seasons-section').classList.add('hidden');
                }
                document.getElementById('tmdb-results').style.display = 'none';

                // Scroll to form
                document.getElementById('tvshow-form-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (error) {
                console.error('Error fetching TV show:', error);
                alert('Error importing TV show data.');
            }
        };

        function renderSeasonsList() {
            const container = document.getElementById('seasons-list');
            container.innerHTML = seasonsData.map(season => `
                <div class="season-selector">
                    <label class="flex items-center gap-4 cursor-pointer">
                        <input type="checkbox" class="season-checkbox w-5 h-5" 
                               value="${season.season_number}" 
                               onchange="toggleSeason(${season.season_number})">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                ${season.poster_path ? `
                                    <img src="${TMDB_IMAGE_URL}/w154${season.poster_path}" 
                                         alt="${season.name}" 
                                         class="w-16 h-20 object-cover rounded">
                                ` : ''}
                                <div>
                                    <h4 class="text-white font-semibold">${season.name || `Season ${season.season_number}`}</h4>
                                    <p class="text-gray-400 text-sm">${season.episode_count || 0} episodes</p>
                                    <p class="text-gray-500 text-xs">${season.air_date ? new Date(season.air_date).getFullYear() : ''}</p>
                                </div>
                            </div>
                            ${season.overview ? `<p class="text-gray-400 text-xs mt-2">${season.overview.substring(0, 100)}...</p>` : ''}
                        </div>
                    </label>
                </div>
            `).join('');
        }

        window.toggleSeason = function(seasonNumber) {
            const index = selectedSeasons.indexOf(seasonNumber);
            if (index > -1) {
                selectedSeasons.splice(index, 1);
            } else {
                selectedSeasons.push(seasonNumber);
            }
        };

        // Server selection modal functions
        window.openServerSelectionModal = function(fetchFunction) {
            pendingFetchOperation = fetchFunction;
            selectedServers = [];
            // Reset checkboxes
            document.getElementById('server-vidsrc-pro').checked = false;
            document.getElementById('server-vidsrc-icu').checked = false;
            document.getElementById('server-vidsrc-fast').checked = false;
            document.getElementById('server-selection-modal').classList.remove('hidden');
        };

        window.closeServerSelectionModal = function() {
            document.getElementById('server-selection-modal').classList.add('hidden');
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

            document.getElementById('server-selection-modal').classList.add('hidden');
            
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
            document.getElementById('server-selection-modal').classList.add('hidden');
            
            // Execute the pending fetch operation without servers
            if (pendingFetchOperation) {
                const fetchFn = pendingFetchOperation;
                pendingFetchOperation = null;
                fetchFn();
            }
        };

        // Function to add vidsrc embeds to episodes
        window.addVidsrcEmbedsToEpisodes = function() {
            if (!selectedTVShow || !selectedTVShow.id || selectedServers.length === 0) {
                return;
            }

            const tmdbId = selectedTVShow.id;
            
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

            // Add embeds to all episodes
            Object.keys(episodesData).forEach(seasonNum => {
                const episodes = episodesData[seasonNum];
                episodes.forEach(episode => {
                    const key = `${seasonNum}-${episode.episode_number}`;
                    if (!episodeEmbeds[key]) {
                        episodeEmbeds[key] = [];
                    }

                    // Add selected servers
                    selectedServers.forEach(serverType => {
                        const config = serverConfigs[serverType];
                        if (config) {
                            const embedUrl = `${config.base_url}/${tmdbId}/${seasonNum}/${episode.episode_number}`;
                            
                            // Check if this server already exists for this episode
                            const existingEmbed = episodeEmbeds[key].find(
                                e => e.server_name === config.server_name
                            );
                            
                            if (!existingEmbed) {
                                episodeEmbeds[key].push({
                                    server_name: config.server_name,
                                    embed_url: embedUrl,
                                    priority: config.priority,
                                    is_active: true,
                                    requires_ad: true
                                });
                            }
                        }
                    });
                });
            });
        };

        window.fetchSelectedSeasons = async function() {
            if (selectedSeasons.length === 0) {
                alert('Please select at least one season');
                return;
            }

            // Show server selection modal first
            openServerSelectionModal(async function() {
                try {
                    const seasonPromises = selectedSeasons.map(seasonNum => 
                        fetch(`${TMDB_BASE_URL}/tv/${selectedTVShow.id}/season/${seasonNum}`, {
                            headers: {
                                'Authorization': `Bearer ${TMDB_ACCESS_TOKEN}`,
                                'accept': 'application/json'
                            }
                        }).then(res => res.json())
                    );

                    const seasonsWithEpisodes = await Promise.all(seasonPromises);
                    
                    seasonsWithEpisodes.forEach(seasonData => {
                        episodesData[seasonData.season_number] = seasonData.episodes || [];
                    });

                    // Add vidsrc embeds if servers were selected
                    addVidsrcEmbedsToEpisodes();

                    renderEpisodes();
                    document.getElementById('episodes-section').classList.remove('hidden');
                } catch (error) {
                    console.error('Error fetching seasons:', error);
                    alert('Error fetching seasons and episodes.');
                }
            });
        };

        window.fetchBulkEpisodes = async function() {
            if (!selectedTVShow) return;

            // Show server selection modal first
            openServerSelectionModal(async function() {
                try {
                    // Fetch all seasons
                    const allSeasons = selectedTVShow.seasons || [];
                    const seasonPromises = allSeasons
                        .filter(s => s.season_number > 0) // Exclude specials
                        .map(season => 
                            fetch(`${TMDB_BASE_URL}/tv/${selectedTVShow.id}/season/${season.season_number}`, {
                                headers: {
                                    'Authorization': `Bearer ${TMDB_ACCESS_TOKEN}`,
                                    'accept': 'application/json'
                                }
                            }).then(res => res.json())
                        );

                    const seasonsWithEpisodes = await Promise.all(seasonPromises);
                    
                    seasonsWithEpisodes.forEach(seasonData => {
                        episodesData[seasonData.season_number] = seasonData.episodes || [];
                        if (!selectedSeasons.includes(seasonData.season_number)) {
                            selectedSeasons.push(seasonData.season_number);
                        }
                    });

                    // Add vidsrc embeds if servers were selected
                    addVidsrcEmbedsToEpisodes();

                    renderEpisodes();
                    document.getElementById('episodes-section').classList.remove('hidden');
                    alert(`Fetched ${seasonsWithEpisodes.length} seasons with all episodes!`);
                } catch (error) {
                    console.error('Error fetching bulk episodes:', error);
                }
            });
        };

        window.renderEpisodes = function() {
            const container = document.getElementById('episodes-container');
            container.innerHTML = '';

            Object.keys(episodesData).sort((a, b) => parseInt(a) - parseInt(b)).forEach(seasonNum => {
                const episodes = episodesData[seasonNum];
                const seasonInfo = seasonsData.find(s => s.season_number == seasonNum);

                const seasonSection = document.createElement('div');
                seasonSection.className = 'mb-8';
                seasonSection.innerHTML = `
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-700">
                        <div class="flex items-center gap-3">
                            <h4 class="text-lg font-bold text-white">${seasonInfo?.name || `Season ${seasonNum}`}</h4>
                            <span class="text-gray-400 text-sm">${episodes.length} episodes</span>
                        </div>
                        <button onclick="addCustomEpisodeToSeason(${seasonNum})" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Custom Episode
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="season-${seasonNum}-episodes"></div>
                `;
                container.appendChild(seasonSection);

                const episodesGrid = seasonSection.querySelector(`#season-${seasonNum}-episodes`);
                // Sort episodes by episode number before rendering
                const sortedEpisodes = [...episodes].sort((a, b) => (a.episode_number || 0) - (b.episode_number || 0));
                sortedEpisodes.forEach(episode => {
                    const episodeCard = createEpisodeCard(episode, seasonNum);
                    episodesGrid.appendChild(episodeCard);
                });
            });
        };

        function createEpisodeCard(episode, seasonNumber) {
            const card = document.createElement('div');
            card.className = 'episode-card';
            card.dataset.seasonNumber = seasonNumber;
            card.dataset.episodeNumber = episode.episode_number;
            
            const stillPath = episode.still_path 
                ? `${TMDB_IMAGE_URL}/w300${episode.still_path}` 
                : '/images/placeholder.svg';

            card.innerHTML = `
                <div class="flex gap-4 mb-3">
                    <img src="${stillPath}" 
                         alt="${episode.name}" 
                         class="w-24 h-16 object-cover rounded"
                         onerror="this.src='/images/placeholder.svg'">
                    <div class="flex-1 min-w-0">
                        <h5 class="text-white font-semibold text-sm mb-1 truncate">${episode.name || `Episode ${episode.episode_number}`}</h5>
                        <p class="text-gray-400 text-xs">Ep. ${episode.episode_number}</p>
                        <p class="text-gray-500 text-xs">${episode.air_date ? new Date(episode.air_date).toLocaleDateString() : 'N/A'}</p>
                    </div>
                </div>
                ${episode.overview ? `<p class="text-gray-400 text-xs mb-3 line-clamp-2">${episode.overview}</p>` : ''}
                <div class="flex justify-between items-center pt-3 border-t border-gray-700">
                    <div class="flex gap-2">
                        <button onclick="openEpisodeEmbeds(${seasonNumber}, ${episode.episode_number})" 
                                class="action-btn bg-blue-600 hover:bg-blue-700 text-white" title="Manage Embeds">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </button>
                        <button onclick="openEpisodeDownloads(${seasonNumber}, ${episode.episode_number})" 
                                class="action-btn bg-green-600 hover:bg-green-700 text-white" title="Manage Downloads">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V8a9 9 0 10-18 0v4a3 3 0 003 3h8z"></path>
                            </svg>
                        </button>
                        <button onclick="editEpisode(${seasonNumber}, ${episode.episode_number})" 
                                class="action-btn bg-yellow-600 hover:bg-yellow-700 text-white" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button onclick="deleteEpisode(${seasonNumber}, ${episode.episode_number})" 
                                class="action-btn bg-red-600 hover:bg-red-700 text-white" title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            `;
            
            return card;
        }

        let currentEpisodeContext = { seasonNumber: null, episodeNumber: null };
        let episodeEmbeds = {}; // { "season-episode": [embeds] }
        let episodeDownloads = {}; // { "season-episode": [downloads] }

        window.openEpisodeEmbeds = function(seasonNumber, episodeNumber) {
            currentEpisodeContext = { seasonNumber, episodeNumber };
            const key = `${seasonNumber}-${episodeNumber}`;
            
            document.getElementById('modal-title').textContent = `Manage Embeds - S${seasonNumber}E${episodeNumber}`;
            document.getElementById('modal-content').innerHTML = `
                <div id="embeds-list-${key}" class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-semibold text-white">Embeds</h4>
                        <button onclick="openEmbedModal(${seasonNumber}, ${episodeNumber})" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">
                            + Add Embed
                        </button>
                    </div>
                    <div id="embeds-items-${key}"></div>
                </div>
            `;
            document.getElementById('episode-modal').classList.remove('hidden');
            loadEpisodeEmbeds(seasonNumber, episodeNumber);
        };

        window.openEpisodeDownloads = function(seasonNumber, episodeNumber) {
            currentEpisodeContext = { seasonNumber, episodeNumber };
            const key = `${seasonNumber}-${episodeNumber}`;
            
            document.getElementById('modal-title').textContent = `Manage Downloads - S${seasonNumber}E${episodeNumber}`;
            document.getElementById('modal-content').innerHTML = `
                <div id="downloads-list-${key}" class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-semibold text-white">Downloads</h4>
                        <button onclick="openDownloadModal(${seasonNumber}, ${episodeNumber})" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">
                            + Add Download
                        </button>
                    </div>
                    <div id="downloads-items-${key}"></div>
                </div>
            `;
            document.getElementById('episode-modal').classList.remove('hidden');
            loadEpisodeDownloads(seasonNumber, episodeNumber);
        };

        function loadEpisodeEmbeds(seasonNumber, episodeNumber) {
            const key = `${seasonNumber}-${episodeNumber}`;
            episodeEmbeds[key] = episodeEmbeds[key] || [];
            renderEpisodeEmbeds(seasonNumber, episodeNumber);
        }

        function loadEpisodeDownloads(seasonNumber, episodeNumber) {
            const key = `${seasonNumber}-${episodeNumber}`;
            episodeDownloads[key] = episodeDownloads[key] || [];
            renderEpisodeDownloads(seasonNumber, episodeNumber);
        }

        function renderEpisodeEmbeds(seasonNumber, episodeNumber) {
            const key = `${seasonNumber}-${episodeNumber}`;
            const container = document.getElementById(`embeds-items-${key}`);
            if (!container) return;

            const embeds = episodeEmbeds[key] || [];
            if (embeds.length === 0) {
                container.innerHTML = '<p class="text-gray-400 text-sm">No embeds added yet.</p>';
                return;
            }

            container.innerHTML = embeds.map((embed, index) => `
                <div class="bg-gray-700 p-4 rounded-lg mb-3 flex justify-between items-center">
                    <div class="flex-1">
                        <p class="text-white font-semibold">${embed.server_name || 'Unnamed Server'}</p>
                        <p class="text-gray-400 text-xs truncate">${embed.embed_url.substring(0, 50)}...</p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="editEmbedInMemory(${seasonNumber}, ${episodeNumber}, ${index})" 
                                class="action-btn bg-yellow-600 hover:bg-yellow-700 text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button onclick="deleteEmbedInMemory(${seasonNumber}, ${episodeNumber}, ${index})" 
                                class="action-btn bg-red-600 hover:bg-red-700 text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function renderEpisodeDownloads(seasonNumber, episodeNumber) {
            const key = `${seasonNumber}-${episodeNumber}`;
            const container = document.getElementById(`downloads-items-${key}`);
            if (!container) return;

            const downloads = episodeDownloads[key] || [];
            if (downloads.length === 0) {
                container.innerHTML = '<p class="text-gray-400 text-sm">No downloads added yet.</p>';
                return;
            }

            container.innerHTML = downloads.map((download, index) => `
                <div class="bg-gray-700 p-4 rounded-lg mb-3 flex justify-between items-center">
                    <div class="flex-1">
                        <p class="text-white font-semibold">${download.server_name || 'Unnamed Server'}</p>
                        <p class="text-gray-400 text-xs">Quality: ${download.quality || 'N/A'} | Size: ${download.size || 'N/A'}</p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="editDownloadInMemory(${seasonNumber}, ${episodeNumber}, ${index})" 
                                class="action-btn bg-yellow-600 hover:bg-yellow-700 text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button onclick="deleteDownloadInMemory(${seasonNumber}, ${episodeNumber}, ${index})" 
                                class="action-btn bg-red-600 hover:bg-red-700 text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        window.openEmbedModal = function(seasonNumber, episodeNumber, embedIndex = null) {
            const key = `${seasonNumber}-${episodeNumber}`;
            const embed = embedIndex !== null ? episodeEmbeds[key][embedIndex] : null;
            
            document.getElementById('modal-content').innerHTML = `
                <form id="embed-form" onsubmit="saveEmbedInMemory(event, ${seasonNumber}, ${episodeNumber}, ${embedIndex !== null ? embedIndex : 'null'})">
                    <input type="hidden" id="embed-index" value="${embedIndex !== null ? embedIndex : ''}">
                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2 text-sm font-semibold">Server Name</label>
                        <input type="text" id="embed-server-name" value="${embed ? embed.server_name : ''}" 
                               class="w-full bg-gray-700 border border-gray-600 text-white px-4 py-2 rounded-lg" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2 text-sm font-semibold">Embed URL / HTML</label>
                        <textarea id="embed-url" rows="4" 
                                  class="w-full bg-gray-700 border border-gray-600 text-white px-4 py-2 rounded-lg" 
                                  placeholder="Enter embed URL, YouTube ID, or iframe HTML" required>${embed ? embed.embed_url : ''}</textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2 text-sm font-semibold">Priority</label>
                        <input type="number" id="embed-priority" value="${embed ? embed.priority : 0}" 
                               class="w-full bg-gray-700 border border-gray-600 text-white px-4 py-2 rounded-lg" min="0">
                    </div>
                    <div class="flex items-center gap-3 mb-4">
                        <input type="checkbox" id="embed-is-active" ${embed && embed.is_active !== false ? 'checked' : 'checked'} 
                               class="w-4 h-4 accent-red-600">
                        <label class="text-gray-300 text-sm">Active</label>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeEpisodeModal()" 
                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                            Save
                        </button>
                    </div>
                </form>
            `;
        };

        window.openDownloadModal = function(seasonNumber, episodeNumber, downloadIndex = null) {
            const key = `${seasonNumber}-${episodeNumber}`;
            const download = downloadIndex !== null ? episodeDownloads[key][downloadIndex] : null;
            
            document.getElementById('modal-content').innerHTML = `
                <form id="download-form" onsubmit="saveDownloadInMemory(event, ${seasonNumber}, ${episodeNumber}, ${downloadIndex !== null ? downloadIndex : 'null'})">
                    <input type="hidden" id="download-index" value="${downloadIndex !== null ? downloadIndex : ''}">
                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2 text-sm font-semibold">Server Name</label>
                        <input type="text" id="download-server-name" value="${download ? download.server_name : ''}" 
                               class="w-full bg-gray-700 border border-gray-600 text-white px-4 py-2 rounded-lg" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2 text-sm font-semibold">Download URL</label>
                        <input type="url" id="download-url" value="${download ? download.download_url : ''}" 
                               class="w-full bg-gray-700 border border-gray-600 text-white px-4 py-2 rounded-lg" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-300 mb-2 text-sm font-semibold">Quality</label>
                            <input type="text" id="download-quality" value="${download ? download.quality : ''}" 
                                   class="w-full bg-gray-700 border border-gray-600 text-white px-4 py-2 rounded-lg" 
                                   placeholder="e.g., 720p, 1080p">
                        </div>
                        <div>
                            <label class="block text-gray-300 mb-2 text-sm font-semibold">Size</label>
                            <input type="text" id="download-size" value="${download ? download.size : ''}" 
                                   class="w-full bg-gray-700 border border-gray-600 text-white px-4 py-2 rounded-lg" 
                                   placeholder="e.g., 500MB, 1.2GB">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2 text-sm font-semibold">Priority</label>
                        <input type="number" id="download-priority" value="${download ? download.priority : 0}" 
                               class="w-full bg-gray-700 border border-gray-600 text-white px-4 py-2 rounded-lg" min="0">
                    </div>
                    <div class="flex items-center gap-3 mb-4">
                        <input type="checkbox" id="download-is-active" ${download && download.is_active !== false ? 'checked' : 'checked'} 
                               class="w-4 h-4 accent-red-600">
                        <label class="text-gray-300 text-sm">Active</label>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeEpisodeModal()" 
                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                            Save
                        </button>
                    </div>
                </form>
            `;
        };

        window.saveEmbedInMemory = function(event, seasonNumber, episodeNumber, embedIndex) {
            event.preventDefault();
            const key = `${seasonNumber}-${episodeNumber}`;
            if (!episodeEmbeds[key]) episodeEmbeds[key] = [];

            const embed = {
                server_name: document.getElementById('embed-server-name').value,
                embed_url: document.getElementById('embed-url').value,
                priority: parseInt(document.getElementById('embed-priority').value) || 0,
                is_active: document.getElementById('embed-is-active').checked
            };

            if (embedIndex !== null) {
                episodeEmbeds[key][embedIndex] = embed;
            } else {
                episodeEmbeds[key].push(embed);
            }

            closeEpisodeModal();
            openEpisodeEmbeds(seasonNumber, episodeNumber);
        };

        window.saveDownloadInMemory = function(event, seasonNumber, episodeNumber, downloadIndex) {
            event.preventDefault();
            const key = `${seasonNumber}-${episodeNumber}`;
            if (!episodeDownloads[key]) episodeDownloads[key] = [];

            const download = {
                server_name: document.getElementById('download-server-name').value,
                download_url: document.getElementById('download-url').value,
                quality: document.getElementById('download-quality').value || null,
                size: document.getElementById('download-size').value || null,
                priority: parseInt(document.getElementById('download-priority').value) || 0,
                is_active: document.getElementById('download-is-active').checked
            };

            if (downloadIndex !== null) {
                episodeDownloads[key][downloadIndex] = download;
            } else {
                episodeDownloads[key].push(download);
            }

            closeEpisodeModal();
            openEpisodeDownloads(seasonNumber, episodeNumber);
        };

        window.editEmbedInMemory = function(seasonNumber, episodeNumber, index) {
            openEmbedModal(seasonNumber, episodeNumber, index);
        };

        window.editDownloadInMemory = function(seasonNumber, episodeNumber, index) {
            openDownloadModal(seasonNumber, episodeNumber, index);
        };

        window.deleteEmbedInMemory = function(seasonNumber, episodeNumber, index) {
            if (confirm('Delete this embed?')) {
                const key = `${seasonNumber}-${episodeNumber}`;
                episodeEmbeds[key].splice(index, 1);
                renderEpisodeEmbeds(seasonNumber, episodeNumber);
            }
        };

        window.deleteDownloadInMemory = function(seasonNumber, episodeNumber, index) {
            if (confirm('Delete this download?')) {
                const key = `${seasonNumber}-${episodeNumber}`;
                episodeDownloads[key].splice(index, 1);
                renderEpisodeDownloads(seasonNumber, episodeNumber);
            }
        };

        // Add custom episode to imported season
        window.addCustomEpisodeToSeason = function(seasonNumber) {
            if (!episodesData[seasonNumber]) {
                episodesData[seasonNumber] = [];
            }

            const episodes = episodesData[seasonNumber];
            const nextEpisodeNumber = episodes.length > 0 
                ? Math.max(...episodes.map(e => e.episode_number || 0)) + 1
                : 1;

            const newEpisode = {
                episode_number: nextEpisodeNumber,
                name: `Episode ${nextEpisodeNumber}`,
                overview: '',
                still_path: '',
                air_date: '',
                runtime: null,
                vote_average: 0,
                vote_count: 0
            };

            episodes.push(newEpisode);
            renderEpisodes();
            
            // Automatically open edit modal for the new episode
            setTimeout(() => {
                editEpisode(seasonNumber, nextEpisodeNumber);
            }, 100);
        };

        window.editEpisode = function(seasonNumber, episodeNumber) {
            const episodes = episodesData[seasonNumber];
            if (!episodes) return;

            const episode = episodes.find(e => e.episode_number == episodeNumber);
            if (!episode) return;

            currentEpisodeContext = { seasonNumber, episodeNumber };

            document.getElementById('modal-title').textContent = `Edit Episode ${episodeNumber} - Season ${seasonNumber}`;
            document.getElementById('modal-content').innerHTML = `
                <form id="episode-edit-form" onsubmit="saveEpisodeEdit(event, ${seasonNumber}, ${episodeNumber})">
                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2 text-sm font-semibold">Episode Name</label>
                        <input type="text" id="edit-episode-name" value="${episode.name || ''}" 
                               class="w-full bg-gray-700 border border-gray-600 text-white px-4 py-2 rounded-lg" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-300 mb-2 text-sm font-semibold">Episode Number</label>
                            <input type="number" id="edit-episode-number" value="${episode.episode_number}" 
                                   class="w-full bg-gray-700 border border-gray-600 text-white px-4 py-2 rounded-lg" min="1" required>
                        </div>
                        <div>
                            <label class="block text-gray-300 mb-2 text-sm font-semibold">Air Date</label>
                            <input type="date" id="edit-episode-air-date" value="${episode.air_date || ''}" 
                                   class="w-full bg-gray-700 border border-gray-600 text-white px-4 py-2 rounded-lg">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-300 mb-2 text-sm font-semibold">Runtime (minutes)</label>
                            <input type="number" id="edit-episode-runtime" value="${episode.runtime || ''}" 
                                   class="w-full bg-gray-700 border border-gray-600 text-white px-4 py-2 rounded-lg" min="0">
                        </div>
                        <div>
                            <label class="block text-gray-300 mb-2 text-sm font-semibold">Rating</label>
                            <input type="number" id="edit-episode-rating" value="${episode.vote_average || 0}" 
                                   step="0.1" min="0" max="10"
                                   class="w-full bg-gray-700 border border-gray-600 text-white px-4 py-2 rounded-lg">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2 text-sm font-semibold">Overview</label>
                        <textarea id="edit-episode-overview" rows="4" 
                                  class="w-full bg-gray-700 border border-gray-600 text-white px-4 py-2 rounded-lg">${episode.overview || ''}</textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2 text-sm font-semibold">Still Path</label>
                        <input type="text" id="edit-episode-still-path" value="${episode.still_path || ''}" 
                               placeholder="/path/to/still.jpg or full URL"
                               class="w-full bg-gray-700 border border-gray-600 text-white px-4 py-2 rounded-lg">
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeEpisodeModal()" 
                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                            Save Changes
                        </button>
                    </div>
                </form>
            `;
            document.getElementById('episode-modal').classList.remove('hidden');
        };

        window.saveEpisodeEdit = function(event, seasonNumber, episodeNumber) {
            event.preventDefault();

            const episodes = episodesData[seasonNumber];
            if (!episodes) return;

            const episode = episodes.find(e => e.episode_number == episodeNumber);
            if (!episode) return;

            // Update episode data
            const newEpisodeNumber = parseInt(document.getElementById('edit-episode-number').value);
            
            // If episode number changed, check for conflicts
            if (newEpisodeNumber !== episodeNumber) {
                const existingEpisode = episodes.find(e => e.episode_number === newEpisodeNumber);
                if (existingEpisode) {
                    alert(`Episode ${newEpisodeNumber} already exists in this season!`);
                    return;
                }
            }

            // Update episode fields
            episode.episode_number = newEpisodeNumber;
            episode.name = document.getElementById('edit-episode-name').value || null;
            episode.air_date = document.getElementById('edit-episode-air-date').value || null;
            episode.runtime = document.getElementById('edit-episode-runtime').value ? parseInt(document.getElementById('edit-episode-runtime').value) : null;
            episode.vote_average = document.getElementById('edit-episode-rating').value ? parseFloat(document.getElementById('edit-episode-rating').value) : 0;
            episode.overview = document.getElementById('edit-episode-overview').value || null;
            episode.still_path = document.getElementById('edit-episode-still-path').value || null;

            // If episode number changed, update embeds/downloads keys
            if (newEpisodeNumber !== episodeNumber) {
                const oldKey = `${seasonNumber}-${episodeNumber}`;
                const newKey = `${seasonNumber}-${newEpisodeNumber}`;
                
                if (episodeEmbeds[oldKey]) {
                    episodeEmbeds[newKey] = episodeEmbeds[oldKey];
                    delete episodeEmbeds[oldKey];
                }
                if (episodeDownloads[oldKey]) {
                    episodeDownloads[newKey] = episodeDownloads[oldKey];
                    delete episodeDownloads[oldKey];
                }
            }

            closeEpisodeModal();
            renderEpisodes();
        };

        window.deleteEpisode = function(seasonNumber, episodeNumber) {
            if (confirm('Delete this episode? This will also remove all embeds and downloads associated with it.')) {
                const episodes = episodesData[seasonNumber];
                if (!episodes) return;

                const index = episodes.findIndex(e => e.episode_number == episodeNumber);
                if (index > -1) {
                    episodes.splice(index, 1);
                    
                    // Remove embeds and downloads for this episode
                    const key = `${seasonNumber}-${episodeNumber}`;
                    delete episodeEmbeds[key];
                    delete episodeDownloads[key];
                    
                    renderEpisodes();
                }
            }
        };

        window.closeEpisodeModal = function() {
            document.getElementById('episode-modal').classList.add('hidden');
        };

        window.saveTVShow = async function() {
            const token = localStorage.getItem('adminAccessToken');
            if (!token) {
                window.location.href = '/admin/login';
                return;
            }

            // Validate required fields
            const name = document.getElementById('name').value.trim();
            if (!name) {
                alert('TV Show name is required!');
                return;
            }

            // Get selected genres
            const selectedGenres = Array.from(document.querySelectorAll('#genres-wrapper input[type="checkbox"]:checked'))
                .map(cb => parseInt(cb.value));

            // Build seasons and episodes data
            // First, add seasons from TMDB imports (episodesData)
            const seasons = [];
            const allSeasonNumbers = new Set();
            
            // Add TMDB imported seasons
            Object.keys(episodesData).forEach(seasonNum => {
                allSeasonNumbers.add(parseInt(seasonNum));
                const seasonInfo = seasonsData.find(s => s.season_number == seasonNum);
                const episodes = episodesData[seasonNum] || [];

                const season = {
                    season_number: parseInt(seasonNum),
                    name: seasonInfo?.name || `Season ${seasonNum}`,
                    overview: seasonInfo?.overview || null,
                    poster_path: seasonInfo?.poster_path || null,
                    air_date: seasonInfo?.air_date || null,
                    episode_count: episodes.length,
                    episodes: episodes.map(episode => {
                        const key = `${seasonNum}-${episode.episode_number}`;
                        return {
                            episode_number: episode.episode_number,
                            name: episode.name || null,
                            overview: episode.overview || null,
                            still_path: episode.still_path || null,
                            air_date: episode.air_date || null,
                            runtime: episode.runtime || null,
                            vote_average: episode.vote_average || 0,
                            vote_count: episode.vote_count || 0,
                            embeds: episodeEmbeds[key] || [],
                            downloads: episodeDownloads[key] || []
                        };
                    })
                };
                seasons.push(season);
            });

            // Add custom seasons (merge if season number already exists from TMDB)
            Object.keys(customSeasons).forEach(seasonNum => {
                const customSeason = customSeasons[seasonNum];
                const seasonNumber = parseInt(seasonNum);
                allSeasonNumbers.add(seasonNumber);

                // Check if this season already exists from TMDB import
                const existingSeasonIndex = seasons.findIndex(s => s.season_number === seasonNumber);
                
                if (existingSeasonIndex >= 0) {
                    // Merge with existing season (TMDB import takes precedence for some fields)
                    const existingSeason = seasons[existingSeasonIndex];
                    // Update season info but keep TMDB episodes, add custom episodes if they don't conflict
                    if (customSeason.name && !existingSeason.name.includes('Season')) {
                        existingSeason.name = customSeason.name;
                    }
                    if (customSeason.overview) {
                        existingSeason.overview = customSeason.overview;
                    }
                    if (customSeason.poster_path) {
                        existingSeason.poster_path = customSeason.poster_path;
                    }
                    if (customSeason.air_date) {
                        existingSeason.air_date = customSeason.air_date;
                    }
                    // Add custom episodes that don't conflict
                    const existingEpisodeNumbers = new Set(existingSeason.episodes.map(e => e.episode_number));
                    customSeason.episodes.forEach(customEpisode => {
                        if (!existingEpisodeNumbers.has(customEpisode.episode_number)) {
                            const key = `${seasonNumber}-${customEpisode.episode_number}`;
                            existingSeason.episodes.push({
                                episode_number: customEpisode.episode_number,
                                name: customEpisode.name || null,
                                overview: customEpisode.overview || null,
                                still_path: customEpisode.still_path || null,
                                air_date: customEpisode.air_date || null,
                                runtime: customEpisode.runtime || null,
                                vote_average: customEpisode.vote_average || 0,
                                vote_count: customEpisode.vote_count || 0,
                                embeds: episodeEmbeds[key] || [],
                                downloads: episodeDownloads[key] || []
                            });
                        }
                    });
                    existingSeason.episode_count = existingSeason.episodes.length;
                } else {
                    // Add new custom season
                    const season = {
                        season_number: seasonNumber,
                        name: customSeason.name || `Season ${seasonNumber}`,
                        overview: customSeason.overview || null,
                        poster_path: customSeason.poster_path || null,
                        air_date: customSeason.air_date || null,
                        episode_count: (customSeason.episodes || []).length,
                        episodes: (customSeason.episodes || []).map(episode => {
                            const key = `${seasonNumber}-${episode.episode_number}`;
                            return {
                                episode_number: episode.episode_number,
                                name: episode.name || null,
                                overview: episode.overview || null,
                                still_path: episode.still_path || null,
                                air_date: episode.air_date || null,
                                runtime: episode.runtime || null,
                                vote_average: episode.vote_average || 0,
                                vote_count: episode.vote_count || 0,
                                embeds: episodeEmbeds[key] || [],
                                downloads: episodeDownloads[key] || []
                            };
                        })
                    };
                    seasons.push(season);
                }
            });

            // Sort seasons by season number
            seasons.sort((a, b) => a.season_number - b.season_number);

            const formData = {
                name: name,
                slug: document.getElementById('slug').value.trim() || null,
                overview: document.getElementById('overview').value.trim() || null,
                status: document.getElementById('status').value,
                is_featured: document.getElementById('is_featured').checked ? 1 : 0,
                first_air_date: document.getElementById('first_air_date').value || null,
                original_language: document.getElementById('original_language').value.trim() || null,
                dubbing_language_id: document.getElementById('dubbing_language_id').value || null,
                category_id: document.getElementById('category_id').value || null,
                tmdb_id: document.getElementById('tmdb_id').value ? parseInt(document.getElementById('tmdb_id').value) : null,
                imdb_id: document.getElementById('imdb_id') ? document.getElementById('imdb_id').value.trim() || null : null,
                poster_path: document.getElementById('poster_path').value.trim() || null,
                backdrop_path: document.getElementById('backdrop_path').value.trim() || null,
                vote_average: document.getElementById('vote_average').value ? parseFloat(document.getElementById('vote_average').value) : 0,
                vote_count: document.getElementById('vote_count').value ? parseInt(document.getElementById('vote_count').value) : 0,
                popularity: document.getElementById('popularity').value ? parseFloat(document.getElementById('popularity').value) : 0,
                genres: selectedGenres.length > 0 ? selectedGenres : null,
                seasons: seasons.length > 0 ? seasons : null
            };

            const submitBtns = document.querySelectorAll('button[onclick="saveTVShow()"]');
            const originalTexts = Array.from(submitBtns).map(btn => btn.innerHTML);
            
            try {
                submitBtns.forEach(btn => {
                    btn.disabled = true;
                    btn.innerHTML = 'Creating...';
                });

                const response = await fetch(`${API_BASE_URL}/admin/tvshows`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (result.success) {
                    alert('TV Show created successfully!');
                    window.location.href = `/admin/tvshows/${result.data?.tvShow?.id || ''}`;
                } else {
                    let errorMsg = 'Error creating TV show';
                    if (result.errors) {
                        errorMsg = Object.entries(result.errors)
                            .map(([field, messages]) => `${field}: ${Array.isArray(messages) ? messages.join(', ') : messages}`)
                            .join('\n');
                    } else if (result.message) {
                        errorMsg = result.message;
                    }
                    alert(errorMsg);
                }
            } catch (error) {
                console.error('Error creating TV show:', error);
                alert(`Error creating TV show: ${error.message}`);
            } finally {
                const submitBtns = document.querySelectorAll('button[onclick="saveTVShow()"]');
                submitBtns.forEach((btn, index) => {
                    btn.disabled = false;
                    if (originalTexts[index]) {
                        btn.innerHTML = originalTexts[index];
                    }
                });
            }
        };

        // Allow Enter key to trigger search
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('tmdb-search');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        searchTMDBTVShow();
                    }
                });
            }

            fetchUtilsData();
            renderCustomSeasons(); // Initialize custom seasons display
        });

        async function fetchUtilsData() {
            try {
                const response = await fetch(`${API_BASE_URL}/utils/all`);
                const data = await response.json();
                if (data.success) {
                    utilsData = data.data;
                    populateDropdowns();
                }
            } catch (error) {
                console.error('Error fetching utility data:', error);
            }
        }

        function populateDropdowns() {
            const categorySelect = document.getElementById('category_id');
            if (categorySelect && utilsData.categories) {
                utilsData.categories.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name;
                    categorySelect.appendChild(option);
                });
            }

            // Populate dubbing languages
            const dubbingLangSelect = document.getElementById('dubbing_language_id');
            if (dubbingLangSelect && utilsData.languages) {
                utilsData.languages.forEach(lang => {
                    const option = document.createElement('option');
                    option.value = lang.id;
                    option.textContent = lang.name;
                    dubbingLangSelect.appendChild(option);
                });
            }

            const genresWrapper = document.getElementById('genres-wrapper');
            if (genresWrapper && utilsData.genres) {
                utilsData.genres.forEach(genre => {
                    const label = document.createElement('label');
                    label.className = 'flex items-center gap-2.5 px-4 py-2.5 bg-gradient-to-br from-gray-800 to-gray-900 border-2 border-gray-600 rounded-lg cursor-pointer';
                    label.innerHTML = `
                        <input type="checkbox" id="genre-${genre.id}" value="${genre.id}" class="w-4 h-4 accent-red-600">
                        <span class="text-gray-200 text-sm">${genre.name}</span>
                    `;
                    genresWrapper.appendChild(label);
                });
            }
        }

        // Make functions globally accessible
        window.searchTMDBTVShow = searchTMDBTVShow;
        window.selectTMDBTVShow = selectTMDBTVShow;
    })();
</script>
@endpush
@endsection

