@extends('layouts.admin')

@section('title', 'Create Movie - Admin Panel')

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
    .section-title::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 80px;
        height: 2px;
        background: linear-gradient(90deg, #dc2626 0%, transparent 100%);
    }
    .form-help-text::before {
        content: '💡 ';
    }
    .form-select-custom {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 14 14'%3E%3Cpath fill='%23374151' d='M7 10L2 5h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 14px;
        appearance: none;
    }
    .checkbox-item input[type="checkbox"]:checked ~ span {
        color: #dc2626;
    }
    .featured-checkbox input[type="checkbox"]:checked ~ span {
        color: #dc2626;
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
                    <a href="/admin/movies" class="text-gray-400 hover:text-white transition-colors">← Back to Movies</a>
                    <h1 class="text-xl font-bold text-white">Create New Movie</h1>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <!-- TMDB Search Card -->
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 rounded-xl shadow-lg hover:border-gray-600 hover:shadow-xl transition-all duration-300 p-6 mb-6">
            <div class="flex justify-between items-center mb-6 pb-5 border-b border-gray-700">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <h2 class="text-xl font-bold text-white">Import from TMDB</h2>
                </div>
                <div class="flex gap-3 items-center">
                    <input type="text" id="tmdb-search" placeholder="Search movie by title..." 
                           class="bg-gray-700 border-2 border-gray-600 text-white px-4 py-2.5 rounded-lg w-80 text-sm transition-all duration-200 focus:border-red-600 focus:ring-4 focus:ring-red-600/20 focus:outline-none placeholder:text-gray-500">
                    <button type="button" onclick="searchTMDB()" 
                            class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-semibold text-sm transition-all duration-200 flex items-center gap-2 shadow-lg shadow-red-600/30 hover:shadow-xl hover:shadow-red-600/40 hover:-translate-y-0.5">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <span>Search</span>
                    </button>
                </div>
            </div>
            <div id="tmdb-results" class="hidden">
                <!-- TMDB search results will appear here -->
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 rounded-xl shadow-lg hover:border-gray-600 hover:shadow-xl transition-all duration-300 p-8">
            <form id="create-movie-form" onsubmit="createMovie(event)">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                    <!-- Left Column -->
                    <div>
                        <h3 class="section-title text-2xl font-bold text-white mb-7 pb-4 border-b-2 border-gray-700 relative flex items-center gap-3">
                            <span class="w-1 h-6 bg-gradient-to-b from-red-600 to-red-700 rounded"></span>
                            Basic Information
                        </h3>
                        
                        <div class="mb-7 relative">
                            <label class="form-label block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider opacity-90 required">Title</label>
                            <input type="text" id="title" name="title" 
                                   class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 shadow-inner hover:border-gray-500 hover:bg-gradient-to-br hover:from-gray-800 hover:to-gray-900 hover:shadow-[inset_0_2px_4px_rgba(0,0,0,0.3),0_0_0_2px_rgba(220,38,38,0.1)] focus:border-red-600 focus:bg-gradient-to-br focus:from-gray-700 focus:to-gray-800 focus:ring-4 focus:ring-red-600/15 focus:outline-none focus:-translate-y-0.5 placeholder:text-gray-500 placeholder:italic" 
                                   required placeholder="Enter movie title">
                        </div>

                        <div class="mb-7 relative">
                            <label class="form-label block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider opacity-90 required">Slug</label>
                            <input type="text" id="slug" name="slug" 
                                   class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 shadow-inner hover:border-gray-500 hover:bg-gradient-to-br hover:from-gray-800 hover:to-gray-900 hover:shadow-[inset_0_2px_4px_rgba(0,0,0,0.3),0_0_0_2px_rgba(220,38,38,0.1)] focus:border-red-600 focus:bg-gradient-to-br focus:from-gray-700 focus:to-gray-800 focus:ring-4 focus:ring-red-600/15 focus:outline-none focus:-translate-y-0.5 placeholder:text-gray-500 placeholder:italic" 
                                   required placeholder="movie-slug-url">
                            <p class="text-gray-500 text-xs mt-2 italic flex items-center gap-1.5">💡 Leave empty to auto-generate from title</p>
                        </div>

                        <div class="mb-7 relative">
                            <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider opacity-90">Overview</label>
                            <textarea id="overview" name="overview" rows="6" 
                                      class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 shadow-inner min-h-[140px] resize-y leading-relaxed hover:border-gray-500 hover:bg-gradient-to-br hover:from-gray-800 hover:to-gray-900 hover:shadow-[inset_0_2px_4px_rgba(0,0,0,0.3),0_0_0_2px_rgba(220,38,38,0.1)] focus:border-red-600 focus:bg-gradient-to-br focus:from-gray-700 focus:to-gray-800 focus:ring-4 focus:ring-red-600/15 focus:outline-none focus:-translate-y-0.5 placeholder:text-gray-500 placeholder:italic" 
                                      placeholder="Enter movie description or overview..."></textarea>
                        </div>

                        <div class="mb-7 relative">
                            <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider opacity-90">Status</label>
                            <select id="status" name="status" 
                                    class="w-full bg-white border-2 border-gray-300 text-gray-900 px-4 py-3.5 pr-11 rounded-lg text-base transition-all duration-300 shadow-sm form-select-custom cursor-pointer hover:border-gray-400 focus:border-red-600 focus:ring-4 focus:ring-red-600/15 focus:outline-none focus:-translate-y-0.5">
                                <option value="pending" selected>Pending</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="mb-7 relative">
                            <label class="flex items-center gap-3 p-4 bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 rounded-lg cursor-pointer transition-all duration-300 shadow-inner hover:border-red-600 hover:bg-gradient-to-br hover:from-gray-800 hover:to-gray-900 hover:shadow-[0_0_0_4px_rgba(220,38,38,0.1),inset_0_2px_4px_rgba(0,0,0,0.2)] featured-checkbox">
                                <input type="checkbox" id="is_featured" name="is_featured" value="1" class="w-5 h-5 cursor-pointer accent-red-600">
                                <span class="text-gray-200 font-semibold text-base select-none">Mark as Featured</span>
                            </label>
                        </div>

                        <div class="mb-7 relative">
                            <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider opacity-90">Release Date</label>
                            <input type="date" id="release_date" name="release_date" 
                                   class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 shadow-inner hover:border-gray-500 hover:bg-gradient-to-br hover:from-gray-800 hover:to-gray-900 hover:shadow-[inset_0_2px_4px_rgba(0,0,0,0.3),0_0_0_2px_rgba(220,38,38,0.1)] focus:border-red-600 focus:bg-gradient-to-br focus:from-gray-700 focus:to-gray-800 focus:ring-4 focus:ring-red-600/15 focus:outline-none focus:-translate-y-0.5">
                        </div>

                        <div class="mb-7 relative">
                            <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider opacity-90">Runtime (minutes)</label>
                            <input type="number" id="runtime" name="runtime" min="0" 
                                   class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 shadow-inner hover:border-gray-500 hover:bg-gradient-to-br hover:from-gray-800 hover:to-gray-900 hover:shadow-[inset_0_2px_4px_rgba(0,0,0,0.3),0_0_0_2px_rgba(220,38,38,0.1)] focus:border-red-600 focus:bg-gradient-to-br focus:from-gray-700 focus:to-gray-800 focus:ring-4 focus:ring-red-600/15 focus:outline-none focus:-translate-y-0.5 placeholder:text-gray-500 placeholder:italic" 
                                   placeholder="120">
                        </div>

                        <div class="mb-7 relative">
                            <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider opacity-90">Original Language</label>
                            <input type="text" id="original_language" name="original_language" maxlength="5" 
                                   class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 shadow-inner hover:border-gray-500 hover:bg-gradient-to-br hover:from-gray-800 hover:to-gray-900 hover:shadow-[inset_0_2px_4px_rgba(0,0,0,0.3),0_0_0_2px_rgba(220,38,38,0.1)] focus:border-red-600 focus:bg-gradient-to-br focus:from-gray-700 focus:to-gray-800 focus:ring-4 focus:ring-red-600/15 focus:outline-none focus:-translate-y-0.5 placeholder:text-gray-500 placeholder:italic" 
                                   placeholder="e.g., en, fr, hi">
                        </div>

                        <div class="mb-7 relative">
                            <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider opacity-90">Dubbing Language</label>
                            <select id="dubbing_language_id" name="dubbing_language_id" 
                                    class="w-full bg-white border-2 border-gray-300 text-gray-900 px-4 py-3.5 pr-11 rounded-lg text-base transition-all duration-300 shadow-sm form-select-custom cursor-pointer hover:border-gray-400 focus:border-red-600 focus:ring-4 focus:ring-red-600/15 focus:outline-none focus:-translate-y-0.5">
                                <option value="">Select dubbing language...</option>
                            </select>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div>
                        <h3 class="section-title text-2xl font-bold text-white mb-7 pb-4 border-b-2 border-gray-700 relative flex items-center gap-3">
                            <span class="w-1 h-6 bg-gradient-to-b from-red-600 to-red-700 rounded"></span>
                            Additional Information
                        </h3>

                        <div class="mb-7 relative">
                            <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider opacity-90">Category</label>
                            <select id="category_id" name="category_id" 
                                    class="w-full bg-white border-2 border-gray-300 text-gray-900 px-4 py-3.5 pr-11 rounded-lg text-base transition-all duration-300 shadow-sm form-select-custom cursor-pointer hover:border-gray-400 focus:border-red-600 focus:ring-4 focus:ring-red-600/15 focus:outline-none focus:-translate-y-0.5">
                                <option value="">Select category...</option>
                            </select>
                        </div>

                        <div class="mb-7 relative">
                            <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider opacity-90">Genres</label>
                            <div id="genres-wrapper" class="flex flex-wrap gap-3 mt-3 p-5 bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 rounded-lg min-h-20 transition-all duration-300 shadow-inner focus-within:border-red-600 focus-within:ring-4 focus-within:ring-red-600/15">
                                <!-- Genres will be populated here -->
                            </div>
                        </div>

                        <div class="mb-7 relative">
                            <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider opacity-90">TMDB ID</label>
                            <input type="number" id="tmdb_id" name="tmdb_id" 
                                   class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 shadow-inner hover:border-gray-500 hover:bg-gradient-to-br hover:from-gray-800 hover:to-gray-900 hover:shadow-[inset_0_2px_4px_rgba(0,0,0,0.3),0_0_0_2px_rgba(220,38,38,0.1)] focus:border-red-600 focus:bg-gradient-to-br focus:from-gray-700 focus:to-gray-800 focus:ring-4 focus:ring-red-600/15 focus:outline-none focus:-translate-y-0.5 placeholder:text-gray-500 placeholder:italic" 
                                   placeholder="e.g., 550">
                        </div>

                        <div class="mb-7 relative">
                            <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider opacity-90">IMDB ID</label>
                            <input type="text" id="imdb_id" name="imdb_id" 
                                   class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 shadow-inner hover:border-gray-500 hover:bg-gradient-to-br hover:from-gray-800 hover:to-gray-900 hover:shadow-[inset_0_2px_4px_rgba(0,0,0,0.3),0_0_0_2px_rgba(220,38,38,0.1)] focus:border-red-600 focus:bg-gradient-to-br focus:from-gray-700 focus:to-gray-800 focus:ring-4 focus:ring-red-600/15 focus:outline-none focus:-translate-y-0.5 placeholder:text-gray-500 placeholder:italic" 
                                   placeholder="e.g., tt0133093">
                        </div>

                        <div class="mb-7 relative">
                            <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider opacity-90">Poster Path</label>
                            <input type="text" id="poster_path" name="poster_path" 
                                   class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 shadow-inner hover:border-gray-500 hover:bg-gradient-to-br hover:from-gray-800 hover:to-gray-900 hover:shadow-[inset_0_2px_4px_rgba(0,0,0,0.3),0_0_0_2px_rgba(220,38,38,0.1)] focus:border-red-600 focus:bg-gradient-to-br focus:from-gray-700 focus:to-gray-800 focus:ring-4 focus:ring-red-600/15 focus:outline-none focus:-translate-y-0.5 placeholder:text-gray-500 placeholder:italic" 
                                   placeholder="/path/to/poster.jpg or full URL">
                            <p class="text-gray-500 text-xs mt-2 italic flex items-center gap-1.5">💡 TMDB relative path (e.g., /w500/poster.jpg) or full URL</p>
                        </div>

                        <div class="mb-7 relative">
                            <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider opacity-90">Backdrop Path</label>
                            <input type="text" id="backdrop_path" name="backdrop_path" 
                                   class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 shadow-inner hover:border-gray-500 hover:bg-gradient-to-br hover:from-gray-800 hover:to-gray-900 hover:shadow-[inset_0_2px_4px_rgba(0,0,0,0.3),0_0_0_2px_rgba(220,38,38,0.1)] focus:border-red-600 focus:bg-gradient-to-br focus:from-gray-700 focus:to-gray-800 focus:ring-4 focus:ring-red-600/15 focus:outline-none focus:-translate-y-0.5 placeholder:text-gray-500 placeholder:italic" 
                                   placeholder="/path/to/backdrop.jpg or full URL">
                            <p class="text-gray-500 text-xs mt-2 italic flex items-center gap-1.5">💡 TMDB relative path (e.g., /original/backdrop.jpg) or full URL</p>
                        </div>

                        <div class="mb-7 relative">
                            <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider opacity-90">Vote Average</label>
                            <input type="number" id="vote_average" name="vote_average" step="0.1" min="0" max="10" value="0" 
                                   class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 shadow-inner hover:border-gray-500 hover:bg-gradient-to-br hover:from-gray-800 hover:to-gray-900 hover:shadow-[inset_0_2px_4px_rgba(0,0,0,0.3),0_0_0_2px_rgba(220,38,38,0.1)] focus:border-red-600 focus:bg-gradient-to-br focus:from-gray-700 focus:to-gray-800 focus:ring-4 focus:ring-red-600/15 focus:outline-none focus:-translate-y-0.5 placeholder:text-gray-500 placeholder:italic" 
                                   placeholder="7.5">
                        </div>

                        <div class="mb-7 relative">
                            <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider opacity-90">Vote Count</label>
                            <input type="number" id="vote_count" name="vote_count" min="0" value="0" 
                                   class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 shadow-inner hover:border-gray-500 hover:bg-gradient-to-br hover:from-gray-800 hover:to-gray-900 hover:shadow-[inset_0_2px_4px_rgba(0,0,0,0.3),0_0_0_2px_rgba(220,38,38,0.1)] focus:border-red-600 focus:bg-gradient-to-br focus:from-gray-700 focus:to-gray-800 focus:ring-4 focus:ring-red-600/15 focus:outline-none focus:-translate-y-0.5 placeholder:text-gray-500 placeholder:italic" 
                                   placeholder="1500">
                        </div>

                        <div class="mb-7 relative">
                            <label class="block text-gray-300 mb-3 text-xs font-semibold uppercase tracking-wider opacity-90">Popularity</label>
                            <input type="number" id="popularity" name="popularity" step="0.01" min="0" value="0" 
                                   class="w-full bg-gradient-to-br from-gray-700 to-gray-800 border-2 border-gray-600 text-white px-4 py-3.5 rounded-lg text-base transition-all duration-300 shadow-inner hover:border-gray-500 hover:bg-gradient-to-br hover:from-gray-800 hover:to-gray-900 hover:shadow-[inset_0_2px_4px_rgba(0,0,0,0.3),0_0_0_2px_rgba(220,38,38,0.1)] focus:border-red-600 focus:bg-gradient-to-br focus:from-gray-700 focus:to-gray-800 focus:ring-4 focus:ring-red-600/15 focus:outline-none focus:-translate-y-0.5 placeholder:text-gray-500 placeholder:italic" 
                                   placeholder="45.678">
                        </div>
                    </div>
                </div>

                <!-- Auto-Add Embeds Option -->
                <div class="mt-8 mb-6 p-6 bg-gradient-to-br from-blue-900/20 to-purple-900/20 border-2 border-blue-700/30 rounded-xl">
                    <div class="flex items-start gap-4">
                        <div class="flex-1">
                            <label class="block text-white font-semibold text-base mb-3 cursor-pointer">
                                Auto-Add Default Embeds
                            </label>
                            <p class="text-gray-400 text-sm leading-relaxed mb-4">
                                Select which embeds to automatically add when creating this movie. The TMDB ID (or movie ID if TMDB ID is not available) will be automatically inserted into the embed URLs:
                            </p>
                            <div class="space-y-3">
                                <label class="flex items-start gap-3 cursor-pointer group hover:bg-gray-800/30 p-3 rounded-lg transition-colors">
                                    <input type="checkbox" id="auto_add_vidlink" name="auto_add_embeds[]" value="vidlink"
                                           class="mt-1 w-5 h-5 cursor-pointer accent-red-600 flex-shrink-0">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-white font-medium">VidLink</span>
                                        </div>
                                        <code class="bg-gray-800/50 px-2 py-1 rounded text-gray-300 text-xs block">https://vidlink.pro/movie/{tmdb_id}</code>
                                    </div>
                                </label>
                                <label class="flex items-start gap-3 cursor-pointer group hover:bg-gray-800/30 p-3 rounded-lg transition-colors">
                                    <input type="checkbox" id="auto_add_vidsrc" name="auto_add_embeds[]" value="vidsrc"
                                           class="mt-1 w-5 h-5 cursor-pointer accent-red-600 flex-shrink-0">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-white font-medium">VidSrc</span>
                                        </div>
                                        <code class="bg-gray-800/50 px-2 py-1 rounded text-gray-300 text-xs block">https://vidsrc.icu/embed/movie/{tmdb_id}</code>
                                    </div>
                                </label>
                                <label class="flex items-start gap-3 cursor-pointer group hover:bg-gray-800/30 p-3 rounded-lg transition-colors">
                                    <input type="checkbox" id="auto_add_vidfast" name="auto_add_embeds[]" value="vidfast"
                                           class="mt-1 w-5 h-5 cursor-pointer accent-red-600 flex-shrink-0">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-white font-medium">VidFast</span>
                                        </div>
                                        <code class="bg-gray-800/50 px-2 py-1 rounded text-gray-300 text-xs block">https://vidfast.pro/movie/{tmdb_id}</code>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-4 justify-end mt-12 pt-8 border-t-2 border-gray-700">
                    <button type="button" onclick="window.location.href='/admin/movies'" 
                            class="bg-gradient-to-br from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-8 py-3.5 rounded-lg font-semibold text-base transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-0.5 flex items-center gap-2.5 border-2 border-gray-600 hover:border-gray-500">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span>Cancel</span>
                    </button>
                    <button type="submit" 
                            class="bg-gradient-to-br from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-8 py-3.5 rounded-lg font-semibold text-base uppercase tracking-wide transition-all duration-300 shadow-lg shadow-red-600/30 hover:shadow-xl hover:shadow-red-600/40 hover:-translate-y-0.5 active:translate-y-0 active:shadow-md flex items-center gap-2.5">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Create Movie</span>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Server Selection Modal -->
    <div id="server-selection-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 max-w-2xl w-full mx-4">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-white">Include VidSrc Servers</h3>
                <button onclick="closeServerSelectionModal()" class="text-gray-400 hover:text-white text-2xl">&times;</button>
            </div>
            <div>
                <p class="text-gray-300 mb-6">Would you like to automatically add VidSrc servers to the movie(s)?</p>
                <div class="space-y-3 mb-6">
                    <label class="flex items-start gap-3 p-3 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer hover:bg-gray-600 transition-colors">
                        <input type="checkbox" id="server-vidsrc-pro" value="vidsrc-pro"
                               class="mt-1 w-5 h-5 cursor-pointer accent-red-600">
                        <div class="flex-1">
                            <div class="text-white font-medium mb-1">VidSrc Pro</div>
                            <code class="text-gray-400 text-xs bg-gray-800 px-2 py-1 rounded block">https://vidlink.pro/movie/{tmdb_id}</code>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 p-3 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer hover:bg-gray-600 transition-colors">
                        <input type="checkbox" id="server-vidsrc-icu" value="vidsrc-icu"
                               class="mt-1 w-5 h-5 cursor-pointer accent-red-600">
                        <div class="flex-1">
                            <div class="text-white font-medium mb-1">VidSrc ICU</div>
                            <code class="text-gray-400 text-xs bg-gray-800 px-2 py-1 rounded block">https://vidsrc.icu/embed/movie/{tmdb_id}</code>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 p-3 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer hover:bg-gray-600 transition-colors">
                        <input type="checkbox" id="server-vidsrc-fast" value="vidsrc-fast"
                               class="mt-1 w-5 h-5 cursor-pointer accent-red-600">
                        <div class="flex-1">
                            <div class="text-white font-medium mb-1">VidSrc Fast</div>
                            <code class="text-gray-400 text-xs bg-gray-800 px-2 py-1 rounded block">https://vidfast.pro/movie/{tmdb_id}</code>
                        </div>
                    </label>
                </div>
                <div class="flex justify-end gap-3">
                    <button onclick="skipServerSelection()" 
                            class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-2.5 rounded-lg font-semibold transition-colors">
                        Skip
                    </button>
                    <button onclick="confirmServerSelection()" 
                            class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-semibold transition-colors">
                        Continue
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('scripts')
<script>
    (function() {
        'use strict';
        
        // API_BASE_URL is already declared in layouts/admin.blade.php
        const TMDB_API_KEY = '{{ config('services.tmdb.api_key') }}';
        const TMDB_ACCESS_TOKEN = '{{ config('services.tmdb.access_token') }}';
        const TMDB_BASE_URL = '{{ config('services.tmdb.base_url') }}';
        const TMDB_IMAGE_URL = '{{ config('services.tmdb.image_url') }}';
        let utilsData = { genres: [], categories: [], languages: [] };

        async function searchTMDB() {
            const query = document.getElementById('tmdb-search').value.trim();
            if (!query) {
                alert('Please enter a movie title to search');
                return;
            }

            const resultsDiv = document.getElementById('tmdb-results');
            resultsDiv.innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 20px;">Searching...</p>';
            resultsDiv.style.display = 'block';

            try {
                // Fetch first page to get total results
                const firstResponse = await fetch(`${TMDB_BASE_URL}/search/movie?query=${encodeURIComponent(query)}&page=1`, {
                    headers: {
                        'Authorization': `Bearer ${TMDB_ACCESS_TOKEN}`,
                        'accept': 'application/json'
                    }
                });

                const firstData = await firstResponse.json();

                if (!firstData.results || firstData.results.length === 0) {
                    resultsDiv.innerHTML = '<p style="color: #f87171; text-align: center; padding: 20px;">No movies found. Please try a different search term.</p>';
                    return;
                }

                // Collect all results from multiple pages (up to 5 pages = 100 results max)
                let allResults = [...firstData.results];
                const totalPages = Math.min(firstData.total_pages || 1, 5); // Limit to 5 pages max

                // Fetch additional pages if available
                if (totalPages > 1) {
                    const pagePromises = [];
                    for (let page = 2; page <= totalPages; page++) {
                        pagePromises.push(
                            fetch(`${TMDB_BASE_URL}/search/movie?query=${encodeURIComponent(query)}&page=${page}`, {
                                headers: {
                                    'Authorization': `Bearer ${TMDB_ACCESS_TOKEN}`,
                                    'accept': 'application/json'
                                }
                            }).then(res => res.json())
                        );
                    }

                    const additionalPages = await Promise.all(pagePromises);
                    additionalPages.forEach(pageData => {
                        if (pageData.results) {
                            allResults = allResults.concat(pageData.results);
                        }
                    });
                }

                // Merge with previously selected movies (avoid duplicates)
                const newResultsMap = new Map();
                allResults.forEach(movie => {
                    newResultsMap.set(movie.id, movie);
                });
                
                // Add previously selected movies that aren't in current results
                selectedMoviesMap.forEach((movie, id) => {
                    if (!newResultsMap.has(id)) {
                        newResultsMap.set(id, movie);
                    }
                });
                
                // Update selectedMoviesMap with current results
                allResults.forEach(movie => {
                    if (selectedMoviesMap.has(movie.id)) {
                        // Keep the selected state
                        selectedMoviesMap.set(movie.id, movie);
                    }
                });
                
                // Convert back to array
                const allMovies = Array.from(newResultsMap.values());
                const totalResults = allMovies.length;
                const previouslySelectedCount = Array.from(selectedMoviesMap.values()).filter(m => !allResults.find(r => r.id === m.id)).length;
                
                resultsDiv.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 style="color: white; margin: 0;">Select movie(s):</h3>
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <span style="color: #9ca3af; font-size: 14px;">${totalResults} movie${totalResults !== 1 ? 's' : ''} (${allResults.length} from search${previouslySelectedCount > 0 ? `, ${previouslySelectedCount} previously selected` : ''})</span>
                            <button onclick="selectAllMovies()" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                                Select All
                            </button>
                            <button onclick="deselectAllMovies()" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                                Deselect All
                            </button>
                            <button onclick="createBulkMovies()" id="bulk-create-btn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                Create Selected (<span id="selected-count">0</span>)
                            </button>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; max-height: 600px; overflow-y: auto; padding: 4px;">
                        ${allMovies.map(movie => {
                            const isPreviouslySelected = !allResults.find(r => r.id === movie.id);
                            const isChecked = selectedMoviesMap.has(movie.id);
                            return `
                            <div style="background-color: #2a2a2a; border: 1px solid ${isPreviouslySelected ? '#dc2626' : '#3a3a3a'}; border-radius: 6px; padding: 8px; cursor: pointer; transition: all 0.2s ease; position: relative; ${isPreviouslySelected ? 'opacity: 0.9;' : ''}" 
                                 onmouseover="this.style.borderColor='#dc2626'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.3)'" 
                                 onmouseout="this.style.borderColor='${isPreviouslySelected ? '#dc2626' : '#3a3a3a'}'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                ${isPreviouslySelected ? '<div style="position: absolute; top: 4px; left: 4px; background-color: #dc2626; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; z-index: 5;">Selected</div>' : ''}
                                <label style="position: absolute; top: 8px; right: 8px; z-index: 10; cursor: pointer;" onclick="event.stopPropagation();">
                                    <input type="checkbox" class="movie-checkbox" value="${movie.id}" data-movie='${JSON.stringify(movie).replace(/'/g, "&#39;")}' 
                                           ${isChecked ? 'checked' : ''}
                                           style="width: 20px; height: 20px; cursor: pointer; accent-color: #dc2626;" 
                                           onchange="handleMovieCheckboxChange(${movie.id}, this);">
                                </label>
                                <div onclick="selectTMDBMovie(${movie.id})">
                                <img src="${movie.poster_path ? `${TMDB_IMAGE_URL}/w154${movie.poster_path}` : '/images/placeholder.svg'}" 
                                     alt="${movie.title}" 
                                     style="width: 100%; aspect-ratio: 2/3; object-fit: cover; border-radius: 4px; margin-bottom: 6px; display: block;">
                                <h4 style="color: white; font-size: 12px; font-weight: 600; margin: 0 0 3px 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; line-height: 1.3;">${movie.title}</h4>
                                <p style="color: #9ca3af; font-size: 11px; margin: 0;">${movie.release_date ? new Date(movie.release_date).getFullYear() : 'N/A'}</p>
                            </div>
                    </div>
                `;
                        }).join('')}
                    </div>
                `;
                
                // Update selected count after rendering
                setTimeout(() => updateSelectedCount(), 100);
            } catch (error) {
                console.error('Error searching TMDB:', error);
                resultsDiv.innerHTML = '<p style="color: #f87171; text-align: center; padding: 20px;">Error searching TMDB. Please try again.</p>';
            }
        }

        async function selectTMDBMovie(tmdbId) {
            try {
                const response = await fetch(`${TMDB_BASE_URL}/movie/${tmdbId}?append_to_response=credits`, {
                    headers: {
                        'Authorization': `Bearer ${TMDB_ACCESS_TOKEN}`,
                        'accept': 'application/json'
                    }
                });

                const movie = await response.json();

                // Fill ALL form fields with TMDB data
                // Basic Information
                document.getElementById('title').value = movie.title || '';
                
                // Auto-generate slug from title
                const slugValue = movie.title ? movie.title.toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '') : '';
                document.getElementById('slug').value = slugValue;
                document.getElementById('slug').dataset.autoGenerated = 'true';
                
                document.getElementById('overview').value = movie.overview || '';
                
                // Set status based on release date (if released, set to active, otherwise pending)
                const releaseDate = movie.release_date ? new Date(movie.release_date) : null;
                const today = new Date();
                if (releaseDate && releaseDate <= today) {
                    document.getElementById('status').value = 'active';
                } else {
                    document.getElementById('status').value = 'pending';
                }
                
                // Set featured based on popularity and vote average
                const isFeatured = (movie.popularity && movie.popularity > 50) || (movie.vote_average && movie.vote_average >= 7.5);
                document.getElementById('is_featured').checked = isFeatured;
                
                document.getElementById('release_date').value = movie.release_date || '';
                document.getElementById('runtime').value = movie.runtime || '';
                document.getElementById('original_language').value = movie.original_language || '';
                
                // Additional Information
                document.getElementById('tmdb_id').value = movie.id || '';
                document.getElementById('imdb_id').value = movie.imdb_id || '';
                
                // Poster and backdrop paths (use relative paths for TMDB images)
                document.getElementById('poster_path').value = movie.poster_path ? movie.poster_path : '';
                document.getElementById('backdrop_path').value = movie.backdrop_path ? movie.backdrop_path : '';
                
                // Ratings and popularity
                document.getElementById('vote_average').value = movie.vote_average ? parseFloat(movie.vote_average).toFixed(1) : '0';
                document.getElementById('vote_count').value = movie.vote_count || '0';
                document.getElementById('popularity').value = movie.popularity ? parseFloat(movie.popularity).toFixed(2) : '0';

                // Map TMDB genres to our genres (uncheck all first, then check matching ones)
                if (utilsData.genres) {
                    utilsData.genres.forEach(genre => {
                        const checkbox = document.getElementById(`genre-${genre.id}`);
                        if (checkbox) checkbox.checked = false;
                    });
                }
                
                if (movie.genres && utilsData.genres) {
                    movie.genres.forEach(tmdbGenre => {
                        const ourGenre = utilsData.genres.find(g => g.tmdb_id === tmdbGenre.id);
                        if (ourGenre) {
                            const checkbox = document.getElementById(`genre-${ourGenre.id}`);
                            if (checkbox) checkbox.checked = true;
                        }
                    });
                }

                // Scroll to form after import
                document.querySelector('#create-movie-form').scrollIntoView({ behavior: 'smooth', block: 'start' });

                // Hide search results
                document.getElementById('tmdb-results').style.display = 'none';
                document.getElementById('tmdb-search').value = '';

                alert('All movie data has been imported from TMDB! Please review the fields before submitting.');
            } catch (error) {
                console.error('Error fetching TMDB movie details:', error);
                alert('Error importing movie data from TMDB. Please try again.');
            }
        }

        // Allow Enter key to trigger search
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('tmdb-search');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        searchTMDB();
                    }
                });
            }
        });

        // Fetch utility data
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
            // Populate categories
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

            // Populate genres checkboxes
            const genresWrapper = document.getElementById('genres-wrapper');
            if (genresWrapper && utilsData.genres) {
                utilsData.genres.forEach(genre => {
                    const label = document.createElement('label');
                    label.className = 'flex items-center gap-2.5 px-4 py-2.5 bg-gradient-to-br from-gray-800 to-gray-900 border-2 border-gray-600 rounded-lg transition-all duration-300 cursor-pointer shadow-md hover:bg-gradient-to-br hover:from-gray-900 hover:to-gray-950 hover:border-red-600 hover:-translate-y-0.5 hover:shadow-lg checkbox-item';
                    
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = 'genres[]';
                    checkbox.value = genre.id;
                    checkbox.id = `genre-${genre.id}`;
                    checkbox.className = 'w-5 h-5 cursor-pointer accent-red-600';
                    
                    const span = document.createElement('span');
                    span.textContent = genre.name;
                    span.className = 'text-gray-300 cursor-pointer text-sm transition-all duration-200 select-none font-medium hover:text-white';
                    
                    label.appendChild(checkbox);
                    label.appendChild(span);
                    genresWrapper.appendChild(label);
                });
            }
        }

        // Auto-generate slug from title
        document.getElementById('title').addEventListener('input', function() {
            const slugInput = document.getElementById('slug');
            if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
                const title = this.value;
                const slug = title.toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                slugInput.value = slug;
                slugInput.dataset.autoGenerated = 'true';
            }
        });

        document.getElementById('slug').addEventListener('input', function() {
            this.dataset.autoGenerated = 'false';
        });


        // Server selection state
        let selectedServers = [];
        let pendingCreateOperation = null;
        
        // Store selected movies across searches (key: tmdb_id, value: movie object)
        let selectedMoviesMap = new Map();

        // Handle checkbox change
        function handleMovieCheckboxChange(movieId, checkbox) {
            const movieJson = checkbox.getAttribute('data-movie');
            if (!movieJson) return;
            
            const movie = JSON.parse(movieJson);
            
            if (checkbox.checked) {
                selectedMoviesMap.set(movieId, movie);
            } else {
                selectedMoviesMap.delete(movieId);
            }
            
            updateSelectedCount();
        }

        // Toggle movie selection (for programmatic use)
        function toggleMovieSelection(movieId, movieJson) {
            const checkbox = document.querySelector(`.movie-checkbox[value="${movieId}"]`);
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                handleMovieCheckboxChange(movieId, checkbox);
            } else {
                // If checkbox doesn't exist yet, just update the map
                const movie = JSON.parse(movieJson);
                if (selectedMoviesMap.has(movieId)) {
                    selectedMoviesMap.delete(movieId);
                } else {
                    selectedMoviesMap.set(movieId, movie);
                }
                updateSelectedCount();
            }
        }

        // Update selected count
        function updateSelectedCount() {
            // Use the map as source of truth for total selected count
            const totalSelected = selectedMoviesMap.size;
            const countEl = document.getElementById('selected-count');
            const btn = document.getElementById('bulk-create-btn');
            if (countEl) {
                countEl.textContent = totalSelected;
            }
            if (btn) {
                btn.disabled = totalSelected === 0;
            }
        }

        // Select all movies
        function selectAllMovies() {
            document.querySelectorAll('.movie-checkbox').forEach(cb => {
                cb.checked = true;
                const movieId = parseInt(cb.value);
                const movieJson = cb.getAttribute('data-movie');
                if (movieJson) {
                    const movie = JSON.parse(movieJson);
                    selectedMoviesMap.set(movieId, movie);
                }
            });
            updateSelectedCount();
        }

        // Deselect all movies
        function deselectAllMovies() {
            document.querySelectorAll('.movie-checkbox').forEach(cb => {
                cb.checked = false;
                const movieId = parseInt(cb.value);
                selectedMoviesMap.delete(movieId);
            });
            updateSelectedCount();
        }

        // Server selection modal functions
        function openServerSelectionModal(createFunction) {
            pendingCreateOperation = createFunction;
            selectedServers = [];
            document.getElementById('server-vidsrc-pro').checked = false;
            document.getElementById('server-vidsrc-icu').checked = false;
            document.getElementById('server-vidsrc-fast').checked = false;
            document.getElementById('server-selection-modal').classList.remove('hidden');
        }

        function closeServerSelectionModal() {
            document.getElementById('server-selection-modal').classList.add('hidden');
        }

        function confirmServerSelection() {
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
            
            if (pendingCreateOperation) {
                const createFn = pendingCreateOperation;
                pendingCreateOperation = null;
                createFn();
            }
        }

        function skipServerSelection() {
            selectedServers = [];
            document.getElementById('server-selection-modal').classList.add('hidden');
            
            if (pendingCreateOperation) {
                const createFn = pendingCreateOperation;
                pendingCreateOperation = null;
                createFn();
            }
        }

        // Add VidSrc embeds to a movie after creation
        async function addVidsrcEmbedsToMovie(movieId, tmdbId) {
            if (selectedServers.length === 0 || !tmdbId) {
                return;
            }

            const token = localStorage.getItem('adminAccessToken');
            const serverConfigs = {
                'vidsrc-pro': {
                    server_name: 'VidSrc Pro',
                    base_url: 'https://vidlink.pro/movie',
                    priority: 1
                },
                'vidsrc-icu': {
                    server_name: 'VidSrc ICU',
                    base_url: 'https://vidsrc.icu/embed/movie',
                    priority: 2
                },
                'vidsrc-fast': {
                    server_name: 'VidSrc Fast',
                    base_url: 'https://vidfast.pro/movie',
                    priority: 3
                }
            };

            for (const serverType of selectedServers) {
                const config = serverConfigs[serverType];
                if (config) {
                    const embedUrl = `${config.base_url}/${tmdbId}`;
                    
                    try {
                        const response = await fetch(`${API_BASE_URL}/embeds/movies/${movieId}`, {
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

                        if (!response.ok) {
                            console.warn(`[Add Embeds] Failed to add ${config.server_name}:`, response.status);
                        }
                    } catch (error) {
                        console.error(`[Add Embeds] Error adding ${config.server_name}:`, error);
                    }
                }
            }
        }

        // Bulk create movies
        async function createBulkMovies() {
            // Get selected movies from the map
            const selectedMovieIds = Array.from(selectedMoviesMap.keys());
            if (selectedMovieIds.length === 0) {
                alert('Please select at least one movie to create.');
                return;
            }

            const movies = Array.from(selectedMoviesMap.values());
            
            if (!confirm(`Create ${movies.length} movie(s) with selected VidSrc servers?\n\nThis will create all selected movies.`)) {
                return;
            }

            // Open server selection modal
            openServerSelectionModal(async () => {
                const token = localStorage.getItem('adminAccessToken');
                let successCount = 0;
                let errorCount = 0;

                // Show loading indicator
                const loadingMsg = document.createElement('div');
                loadingMsg.id = 'bulk-movies-loading';
                loadingMsg.style.cssText = 'position: fixed; top: 20px; right: 20px; background-color: #1a1a1a; color: white; padding: 16px 24px; border-radius: 8px; border: 1px solid #2a2a2a; z-index: 2000; box-shadow: 0 4px 12px rgba(0,0,0,0.5);';
                loadingMsg.textContent = `Creating movies... (0/${movies.length})`;
                document.body.appendChild(loadingMsg);

                for (let i = 0; i < movies.length; i++) {
                    const movie = movies[i];
                    loadingMsg.textContent = `Creating movies... (${i + 1}/${movies.length})`;

                    try {
                        // Fetch full movie details
                        const tmdbResponse = await fetch(`${TMDB_BASE_URL}/movie/${movie.id}?append_to_response=credits`, {
                            headers: {
                                'Authorization': `Bearer ${TMDB_ACCESS_TOKEN}`,
                                'accept': 'application/json'
                            }
                        });
                        const fullMovie = await tmdbResponse.json();

                        // Prepare movie data
                        const slugValue = fullMovie.title ? fullMovie.title.toLowerCase()
                            .trim()
                            .replace(/[^\w\s-]/g, '')
                            .replace(/[\s_-]+/g, '-')
                            .replace(/^-+|-+$/g, '') : '';
                        
                        const releaseDate = fullMovie.release_date ? new Date(fullMovie.release_date) : null;
                        const today = new Date();
                        const status = (releaseDate && releaseDate <= today) ? 'active' : 'pending';
                        const isFeatured = (fullMovie.popularity && fullMovie.popularity > 50) || (fullMovie.vote_average && fullMovie.vote_average >= 7.5);

                        // Map genres
                        const genreIds = [];
                        if (fullMovie.genres && utilsData.genres) {
                            fullMovie.genres.forEach(tmdbGenre => {
                                const localGenre = utilsData.genres.find(g => 
                                    g.name.toLowerCase() === tmdbGenre.name.toLowerCase()
                                );
                                if (localGenre) {
                                    genreIds.push(localGenre.id);
                                }
                            });
                        }

                        const formData = {
                            title: fullMovie.title || '',
                            slug: slugValue,
                            overview: fullMovie.overview || null,
                            status: status,
                            is_featured: isFeatured ? 1 : 0,
                            release_date: fullMovie.release_date || null,
                            runtime: fullMovie.runtime || null,
                            original_language: fullMovie.original_language || null,
                            tmdb_id: fullMovie.id || null,
                            imdb_id: fullMovie.imdb_id || null,
                            poster_path: fullMovie.poster_path || null,
                            backdrop_path: fullMovie.backdrop_path || null,
                            vote_average: fullMovie.vote_average ? parseFloat(fullMovie.vote_average) : 0,
                            vote_count: fullMovie.vote_count || 0,
                            popularity: fullMovie.popularity ? parseFloat(fullMovie.popularity) : 0,
                            genres: genreIds.length > 0 ? genreIds : null,
                            auto_add_embeds: [] // We'll add VidSrc servers separately
                        };

                        // Create movie
                        const response = await fetch(`${API_BASE_URL}/admin/movies`, {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(formData)
                        });

                        const result = await response.json();
                        if (result.success && result.data && result.data.movie) {
                            // Add VidSrc embeds if servers were selected
                            if (selectedServers.length > 0 && result.data.movie.tmdb_id) {
                                await addVidsrcEmbedsToMovie(result.data.movie.id, result.data.movie.tmdb_id);
                            }
                            successCount++;
                        } else {
                            errorCount++;
                        }
                    } catch (error) {
                        console.error(`Error creating movie ${movie.title}:`, error);
                        errorCount++;
                    }
                }

                // Show result
                loadingMsg.textContent = `Created ${successCount} movie(s)${errorCount > 0 ? ` (${errorCount} errors)` : ''}`;
                loadingMsg.style.backgroundColor = errorCount > 0 ? '#f59e0b' : '#16a34a';

                setTimeout(() => {
                    if (loadingMsg.parentNode) {
                        loadingMsg.parentNode.removeChild(loadingMsg);
                    }
                    // Clear selected movies after successful creation
                    selectedMoviesMap.clear();
                    if (successCount > 0) {
                        window.location.href = '/admin/movies';
                    }
                }, 3000);
            });
        }

        // Create movie with embeds support
        async function createMovieWithEmbeds(event) {
            event.preventDefault();

            const token = localStorage.getItem('adminAccessToken');
            if (!token) {
                window.location.href = '/admin/login';
                return;
            }

            const title = document.getElementById('title').value.trim();
            if (!title) {
                alert('Title is required');
                return;
            }

            const selectedGenres = Array.from(document.querySelectorAll('#genres-wrapper input[type="checkbox"]:checked'))
                .map(cb => parseInt(cb.value));

            const formData = {
                title: title,
                slug: document.getElementById('slug').value.trim() || null,
                overview: document.getElementById('overview').value.trim() || null,
                status: document.getElementById('status').value,
                is_featured: document.getElementById('is_featured').checked ? 1 : 0,
                release_date: document.getElementById('release_date').value || null,
                runtime: document.getElementById('runtime').value ? parseInt(document.getElementById('runtime').value) : null,
                original_language: document.getElementById('original_language').value.trim() || null,
                dubbing_language_id: document.getElementById('dubbing_language_id').value || null,
                category_id: document.getElementById('category_id').value || null,
                tmdb_id: document.getElementById('tmdb_id').value ? parseInt(document.getElementById('tmdb_id').value) : null,
                imdb_id: document.getElementById('imdb_id').value.trim() || null,
                poster_path: document.getElementById('poster_path').value.trim() || null,
                backdrop_path: document.getElementById('backdrop_path').value.trim() || null,
                vote_average: document.getElementById('vote_average').value ? parseFloat(document.getElementById('vote_average').value) : 0,
                vote_count: document.getElementById('vote_count').value ? parseInt(document.getElementById('vote_count').value) : 0,
                popularity: document.getElementById('popularity').value ? parseFloat(document.getElementById('popularity').value) : 0,
                genres: selectedGenres.length > 0 ? selectedGenres : null,
                auto_add_embeds: Array.from(document.querySelectorAll('input[name="auto_add_embeds[]"]:checked')).map(cb => cb.value),
            };

            try {
                const submitBtn = document.querySelector('#create-movie-form button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <svg class="animate-spin w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>Creating...</span>
                    `;
                }

                const url = `${API_BASE_URL}/admin/movies`;
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                let result;
                const responseText = await response.text();
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error('[Create Movie] Failed to parse JSON:', e);
                    alert('Server returned invalid response. Please check console for details.');
                    return;
                }

                if (result.success) {
                    // Add VidSrc embeds if servers were selected
                    if (selectedServers.length > 0 && result.data && result.data.movie && result.data.movie.tmdb_id) {
                        await addVidsrcEmbedsToMovie(result.data.movie.id, result.data.movie.tmdb_id);
                    }
                    
                    alert('Movie created successfully!');
                    window.location.href = `/admin/movies/${result.data.movie.id}`;
                } else {
                    let errorMsg = 'Error creating movie';
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
                console.error('[Create Movie] Exception:', error);
                alert(`Error creating movie: ${error.message}\n\nPlease check the browser console for more details.`);
            } finally {
                const submitBtn = document.querySelector('#create-movie-form button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = `
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Create Movie</span>
                    `;
                }
            }
        }

        // Wrap createMovieWithEmbeds to show server selection modal first
        async function createMovieWrapper(event) {
            event.preventDefault();
            
            const tmdbId = document.getElementById('tmdb_id').value;
            if (tmdbId) {
                // Open server selection modal if TMDB ID exists
                openServerSelectionModal(async () => {
                    await createMovieWithEmbeds(event);
                });
            } else {
                // Create without server selection if no TMDB ID
                await createMovieWithEmbeds(event);
            }
        }

        // Make functions globally accessible for inline handlers
        window.searchTMDB = searchTMDB;
        window.selectTMDBMovie = selectTMDBMovie;
        window.createMovie = createMovieWrapper;
        window.updateSelectedCount = updateSelectedCount;
        window.toggleMovieSelection = toggleMovieSelection;
        window.handleMovieCheckboxChange = handleMovieCheckboxChange;
        window.selectAllMovies = selectAllMovies;
        window.deselectAllMovies = deselectAllMovies;
        window.createBulkMovies = createBulkMovies;
        window.openServerSelectionModal = openServerSelectionModal;
        window.closeServerSelectionModal = closeServerSelectionModal;
        window.confirmServerSelection = confirmServerSelection;
        window.skipServerSelection = skipServerSelection;

        // Initialize
        document.addEventListener('DOMContentLoaded', async () => {
            await fetchUtilsData();
        });
    })();
</script>
@endpush

