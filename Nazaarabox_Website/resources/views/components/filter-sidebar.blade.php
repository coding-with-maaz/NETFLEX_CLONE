{{-- Filter Sidebar Component - Matching Frontend FilterSidebar.jsx --}}
<div id="filter-sidebar" class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
    <div class="absolute inset-0 bg-black/50" onclick="closeFilterSidebar()"></div>
    
    <div class="absolute right-0 top-0 h-full w-full max-w-sm bg-gray-900 border-l border-gray-800 shadow-xl">
        <div class="flex h-full flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-800 bg-gray-800">
                <div class="flex items-center space-x-2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5 text-red-600">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <h2 class="text-lg font-semibold text-white">Filter & Sort</h2>
                </div>
                <button onclick="closeFilterSidebar()" class="text-gray-400 hover:text-white transition-colors">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-4 space-y-6 bg-gray-900">
                <!-- Genre Filter -->
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Genre</label>
                    <select id="sidebar-genre-filter" class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 outline-none focus:border-red-600">
                        <option value="">All Genres</option>
                    </select>
                </div>

                <!-- Country Filter -->
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Country</label>
                    <select id="sidebar-country-filter" class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 outline-none focus:border-red-600">
                        <option value="">All Countries</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Category</label>
                    <select id="sidebar-category-filter" class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 outline-none focus:border-red-600">
                        <option value="">All Categories</option>
                    </select>
                </div>

                <!-- Year Filter -->
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Year</label>
                    <select id="sidebar-year-filter" class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 outline-none focus:border-red-600">
                        <option value="">All Years</option>
                    </select>
                </div>

                <!-- Language Filter -->
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Language / Dub / Sub</label>
                    <select id="sidebar-language-filter" class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 outline-none focus:border-red-600">
                        <option value="">All</option>
                    </select>
                </div>

                <!-- Dubbed Language Filter -->
                <div id="sidebar-dubbed-language-wrapper" style="display: none;">
                    <label class="block text-sm font-medium text-white mb-2">Additional Dubbed Languages</label>
                    <select id="sidebar-dubbed-language-filter" class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 outline-none focus:border-red-600">
                        <option value="">All</option>
                    </select>
                </div>

                <!-- Sort By -->
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Sort By</label>
                    <select id="sidebar-sort-filter" class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 outline-none focus:border-red-600">
                        <option value="popularity">Popularity</option>
                        <option value="hottest">🔥 Hottest</option>
                        <option value="latest">🆕 Latest</option>
                        <option value="vote_average">⭐ Rating</option>
                        <option value="release_date">Release Date</option>
                        <option value="title">Title</option>
                    </select>
                </div>

                <!-- Order -->
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Order</label>
                    <select id="sidebar-order-filter" class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 outline-none focus:border-red-600">
                        <option value="desc">Descending</option>
                        <option value="asc">Ascending</option>
                    </select>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-4 border-t border-gray-800 space-y-3 bg-gray-800">
                <button onclick="applySidebarFilters()" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded font-semibold transition-colors shadow-lg">
                    Apply Filters
                </button>
                <button onclick="resetSidebarFilters()" class="w-full bg-gray-700 hover:bg-gray-600 text-white py-2 px-4 rounded font-semibold transition-colors flex items-center justify-center gap-2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Reset All
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Filter Sidebar Functions - Matching Frontend FilterSidebar.jsx
    function openFilterSidebar() {
        document.getElementById('filter-sidebar').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeFilterSidebar() {
        document.getElementById('filter-sidebar').style.display = 'none';
        document.body.style.overflow = '';
    }

    function applySidebarFilters() {
        const filters = {
            genre: document.getElementById('sidebar-genre-filter').value,
            country: document.getElementById('sidebar-country-filter').value,
            category: document.getElementById('sidebar-category-filter').value,
            year: document.getElementById('sidebar-year-filter').value,
            language: document.getElementById('sidebar-language-filter').value,
            dubbed_language: document.getElementById('sidebar-dubbed-language-filter').value,
            sort_by: document.getElementById('sidebar-sort-filter').value,
            order: document.getElementById('sidebar-order-filter').value
        };

        // Apply filters to main filter inputs
        if (typeof applyFilters === 'function') {
            applyFilters(filters);
        }

        closeFilterSidebar();
    }

    function resetSidebarFilters() {
        document.getElementById('sidebar-genre-filter').value = '';
        document.getElementById('sidebar-country-filter').value = '';
        document.getElementById('sidebar-category-filter').value = '';
        document.getElementById('sidebar-year-filter').value = '';
        document.getElementById('sidebar-language-filter').value = '';
        document.getElementById('sidebar-dubbed-language-filter').value = '';
        document.getElementById('sidebar-sort-filter').value = 'popularity';
        document.getElementById('sidebar-order-filter').value = 'desc';

        if (typeof clearFilters === 'function') {
            clearFilters();
        }
    }

    // Populate sidebar filters from utils data
    function populateSidebarFilters(utilsData) {
        const genreSelect = document.getElementById('sidebar-genre-filter');
        const countrySelect = document.getElementById('sidebar-country-filter');
        const categorySelect = document.getElementById('sidebar-category-filter');
        const yearSelect = document.getElementById('sidebar-year-filter');
        const languageSelect = document.getElementById('sidebar-language-filter');
        const dubbedLanguageSelect = document.getElementById('sidebar-dubbed-language-filter');

        if (genreSelect && utilsData.genres) {
            utilsData.genres.forEach(genre => {
                const option = document.createElement('option');
                option.value = genre.id;
                option.textContent = genre.name;
                genreSelect.appendChild(option);
            });
        }

        if (countrySelect && utilsData.countries) {
            utilsData.countries.forEach(country => {
                const option = document.createElement('option');
                option.value = country.id;
                option.textContent = country.name;
                countrySelect.appendChild(option);
            });
        }

        if (categorySelect && utilsData.categories) {
            utilsData.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                categorySelect.appendChild(option);
            });
        }

        if (yearSelect && utilsData.years) {
            utilsData.years.forEach(year => {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                yearSelect.appendChild(option);
            });
        } else {
            // Default years
            [2025, 2024, 2023, 2022, 2021, 2020].forEach(year => {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                yearSelect.appendChild(option);
            });
            const separator = document.createElement('option');
            separator.disabled = true;
            separator.textContent = '──────────';
            yearSelect.appendChild(separator);
            ['2010s', '2000s', '1990s', '1980s', 'Other'].forEach(decade => {
                const option = document.createElement('option');
                option.value = decade;
                option.textContent = decade;
                yearSelect.appendChild(option);
            });
        }

        if (languageSelect && utilsData.languages) {
            utilsData.languages.forEach(lang => {
                const option = document.createElement('option');
                option.value = lang.name || lang;
                option.textContent = lang.name || lang;
                languageSelect.appendChild(option);
            });
        } else {
            // Default languages
            ['French dub', 'Hindi dub', 'Bengali dub', 'Urdu dub', 'Punjabi dub', 'Tamil dub', 'Telugu dub', 'Malayalam dub', 'Kannada dub', 'Arabic dub', 'Tagalog dub', 'Indonesian dub', 'Russian dub', 'Kurdish sub'].forEach(lang => {
                const option = document.createElement('option');
                option.value = lang;
                option.textContent = lang;
                languageSelect.appendChild(option);
            });
        }

        if (utilsData.dubbed_languages && utilsData.dubbed_languages.length > 0) {
            document.getElementById('sidebar-dubbed-language-wrapper').style.display = 'block';
            if (dubbedLanguageSelect) {
                utilsData.dubbed_languages.forEach(lang => {
                    const option = document.createElement('option');
                    option.value = lang;
                    option.textContent = lang;
                    dubbedLanguageSelect.appendChild(option);
                });
            }
        }
    }
</script>

