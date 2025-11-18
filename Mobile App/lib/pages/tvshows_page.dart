import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../services/api_service.dart';
import '../models/tvshow.dart';

class TVShowsPage extends StatefulWidget {
  final Map<String, String>? initialFilters;
  
  const TVShowsPage({super.key, this.initialFilters});

  @override
  State<TVShowsPage> createState() => _TVShowsPageState();
}

class _TVShowsPageState extends State<TVShowsPage> {
  // State variables
  final ScrollController _scrollController = ScrollController();
  List<TVShow> _tvShows = [];
  bool _isLoading = true;
  bool _isLoadingMore = false;
  bool _hasMorePages = true;
  bool _showFilters = false;
  Map<String, dynamic> _pagination = {};
  
  // Filter data
  List<Map<String, dynamic>> _genres = [];
  List<Map<String, dynamic>> _countries = [];
  List<Map<String, dynamic>> _categories = [];
  List<Map<String, dynamic>> _languages = [];
  
  // Selected filters
  String _selectedGenre = 'All';
  String _selectedCountry = 'All';
  String _selectedCategory = 'All';
  String _selectedYear = 'All';
  String _selectedLanguage = 'All';
  String _selectedRating = 'All';
  String _sortBy = 'foryou';
  int _currentPage = 1;

  final List<String> _yearOptions = ['All', '2025', '2024', '2023', '2022', '2021', '2020', '2010s', '2000s', '1990s', '1980s', 'Other'];
  final List<String> _ratingOptions = ['All', '9+', '8+', '7+', '6+', '5+'];
  final List<Map<String, String>> _sortOptions = [
    {'value': 'foryou', 'label': 'For You'},
    {'value': 'hottest', 'label': 'Hottest'},
    {'value': 'latest', 'label': 'Latest'},
    {'value': 'rating', 'label': 'Top Rated'},
    {'value': 'name', 'label': 'A-Z'},
  ];

  @override
  void initState() {
    super.initState();
    _applyInitialFilters();
    _setupScrollListener();
    // Fetch utility data first, then apply filters and fetch TV shows
    _fetchUtilityData().then((_) {
      // Re-apply category filter after categories are loaded to ensure proper matching
      if (widget.initialFilters != null && widget.initialFilters!.containsKey('category')) {
        final categoryValue = widget.initialFilters!['category'] ?? 'All';
        final categoryName = _findCategoryName(categoryValue);
        if (categoryName != null && categoryName != _selectedCategory) {
          setState(() {
            _selectedCategory = categoryName;
          });
          // Re-fetch TV shows with the correct category filter
          _fetchTVShows();
        } else if (categoryName == null && categoryValue != 'All') {
          // Category value was provided but not found in list - still use it for API call
          setState(() {
            _selectedCategory = categoryValue;
          });
          _fetchTVShows();
        } else {
          // No category filter or already set correctly, just fetch
          _fetchTVShows();
        }
      } else {
        // No category filter, fetch normally
        _fetchTVShows();
      }
    });
  }

  void _setupScrollListener() {
    _scrollController.addListener(() {
      if (!_scrollController.hasClients) return;
      
      final maxScroll = _scrollController.position.maxScrollExtent;
      final currentScroll = _scrollController.position.pixels;
      
      // Load more when user is 200 pixels from bottom
      if (currentScroll >= maxScroll - 200) {
        _loadMoreTVShows();
      }
    });
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _applyInitialFilters() {
    if (widget.initialFilters != null) {
      final filters = widget.initialFilters!;
      
      // Apply genre filter
      if (filters.containsKey('genre')) {
        _selectedGenre = filters['genre'] ?? 'All';
        _showFilters = true; // Show filters panel
      }
      
      // Apply sort_by - validate against available options
      if (filters.containsKey('sort_by')) {
        final sortValue = filters['sort_by'] ?? 'foryou';
        // Map popularity to hottest, or use valid value
        if (sortValue == 'popularity') {
          _sortBy = 'hottest';
        } else {
          // Validate the sort value exists in options
          final validValues = _sortOptions.map((opt) => opt['value']).toList();
          _sortBy = validValues.contains(sortValue) ? sortValue : 'foryou';
        }
      }
      
      // Apply other filters
      if (filters.containsKey('country')) {
        _selectedCountry = filters['country'] ?? 'All';
        _showFilters = true;
      }
      
      if (filters.containsKey('category')) {
        final categoryValue = filters['category'] ?? 'All';
        // Store the category value - will be matched properly after categories are loaded
        _selectedCategory = categoryValue;
        _showFilters = true;
        print('[TVShowsPage] Applied category filter (initial): $categoryValue');
      }
      
      if (filters.containsKey('year')) {
        _selectedYear = filters['year'] ?? 'All';
        _showFilters = true;
      }
      
      if (filters.containsKey('language')) {
        _selectedLanguage = filters['language'] ?? 'All';
        _showFilters = true;
      }
      
      if (filters.containsKey('min_rating')) {
        _selectedRating = filters['min_rating'] ?? 'All';
        _showFilters = true;
      }
      
      print('Applied initial filters to TV Shows: genre=$_selectedGenre, sortBy=$_sortBy');
    }
  }

  Future<void> _fetchUtilityData() async {
    try {
      final data = await ApiService.getUtilityData();
      
      setState(() {
        _genres = (data['genres'] as List?)?.cast<Map<String, dynamic>>() ?? [];
        _countries = (data['countries'] as List?)?.cast<Map<String, dynamic>>() ?? [];
        _categories = (data['categories'] as List?)?.cast<Map<String, dynamic>>() ?? [];
        _languages = (data['languages'] as List?)?.cast<Map<String, dynamic>>() ?? [];
      });
      
      print('Utility data loaded: ${_genres.length} genres, ${_countries.length} countries');
    } catch (e) {
      print('Error fetching utility data: $e');
    }
  }

  Future<void> _fetchTVShows({bool loadMore = false}) async {
    if (loadMore) {
      if (_isLoadingMore || !_hasMorePages) return;
      setState(() => _isLoadingMore = true);
    } else {
      setState(() {
        _isLoading = true;
        _currentPage = 1;
        _hasMorePages = true;
      });
    }

    try {
      // Map Flutter sort options to backend sort_by values
      String backendSortBy = _sortBy;
      String? order = 'desc';
      
      if (_sortBy == 'foryou') {
        backendSortBy = 'created_at';
        order = 'desc';
      } else if (_sortBy == 'hottest') {
        backendSortBy = 'view_count';
        order = 'desc';
      } else if (_sortBy == 'latest') {
        backendSortBy = 'created_at';
        order = 'desc';
      } else if (_sortBy == 'rating') {
        backendSortBy = 'vote_average';
        order = 'desc';
      } else if (_sortBy == 'name') {
        backendSortBy = 'name';
        order = 'asc';
      } else {
        // Default fallback
        backendSortBy = 'created_at';
        order = 'desc';
      }
      
      final params = <String, dynamic>{
        'page': _currentPage.toString(),
        'limit': '20',
        'sort_by': backendSortBy,
        'order': order,
      };

      if (_selectedGenre != 'All') params['genre'] = _selectedGenre;
      if (_selectedCountry != 'All') params['country'] = _selectedCountry;
      if (_selectedCategory != 'All' && _selectedCategory.isNotEmpty) {
        params['category'] = _selectedCategory;
        print('[TVShowsPage] Adding category filter to params: $_selectedCategory');
      }
      if (_selectedYear != 'All') params['year'] = _selectedYear;
      if (_selectedLanguage != 'All') params['language'] = _selectedLanguage;
      if (_selectedRating != 'All') {
        params['min_rating'] = _selectedRating.replaceAll('+', '');
      }

      final result = await ApiService.getTVShowsWithParams(params: params);
      
      final newTVShows = result['tvShows'] ?? [];
      final pagination = result['pagination'] ?? {};
      // Backend returns: current_page, last_page, per_page, total, from, to
      final lastPage = pagination['last_page'] ?? pagination['lastPage'] ?? pagination['total_pages'] ?? pagination['totalPages'] ?? 1;
      final currentPageNum = pagination['current_page'] ?? pagination['currentPage'] ?? _currentPage;
      final total = pagination['total'] ?? 0;
      
      // Calculate if there are more pages
      final hasMore = currentPageNum < lastPage;
      
      setState(() {
        if (loadMore) {
          // Append new TV shows when loading more
          _tvShows.addAll(newTVShows);
        } else {
          // Replace TV shows when starting fresh
          _tvShows = newTVShows;
        }
        _pagination = pagination;
        // Sync _currentPage with API response
        _currentPage = currentPageNum;
        _isLoading = false;
        _isLoadingMore = false;
        // Set hasMorePages based on pagination
        _hasMorePages = hasMore;
      });
      
      print('TVShowsPage: Fetched ${newTVShows.length} TV shows. Total: ${_tvShows.length}. Current Page: $_currentPage, Last Page: $lastPage, Total Items: $total, Has More: $_hasMorePages');
      print('TVShowsPage: Pagination: $_pagination');
    } catch (e) {
      print('Error fetching TV shows: $e');
      setState(() {
        if (!loadMore) {
          _tvShows = [];
          _pagination = {};
        }
        _isLoading = false;
        _isLoadingMore = false;
      });
    }
  }

  Future<void> _loadMoreTVShows() async {
    if (_isLoadingMore || !_hasMorePages || _isLoading) return;
    
    // Backend returns last_page, not total_pages
    final lastPage = _pagination['last_page'] ?? _pagination['lastPage'] ?? _pagination['total_pages'] ?? _pagination['totalPages'] ?? 1;
    if (_currentPage >= lastPage) {
      setState(() {
        _hasMorePages = false;
      });
      return;
    }
    
    setState(() {
      _currentPage++;
    });
    
    await _fetchTVShows(loadMore: true);
  }

  void _clearFilters() {
    setState(() {
      _selectedGenre = 'All';
      _selectedCountry = 'All';
      _selectedCategory = 'All';
      _selectedYear = 'All';
      _selectedLanguage = 'All';
      _selectedRating = 'All';
      _sortBy = 'foryou';
      _currentPage = 1;
      _hasMorePages = true;
    });
    _fetchTVShows();
  }

  bool get _hasActiveFilters {
    return _selectedGenre != 'All' ||
        _selectedCountry != 'All' ||
        _selectedCategory != 'All' ||
        _selectedYear != 'All' ||
        _selectedLanguage != 'All' ||
        _selectedRating != 'All';
  }

  int _getActiveFilterCount() {
    int count = 0;
    if (_selectedGenre != 'All') count++;
    if (_selectedCountry != 'All') count++;
    if (_selectedCategory != 'All') count++;
    if (_selectedYear != 'All') count++;
    if (_selectedLanguage != 'All') count++;
    if (_selectedRating != 'All') count++;
    return count;
  }

  List<String> _getOptionsFromList(List<Map<String, dynamic>> list) {
    return list
        .map((item) => item['name']?.toString() ?? '')
        .where((name) => name.isNotEmpty)
        .toList();
  }

  String? _findCategoryName(String value) {
    // Try to find exact match by name
    for (final cat in _categories) {
      final name = cat['name']?.toString() ?? '';
      final slug = cat['slug']?.toString() ?? '';
      if (name.toLowerCase() == value.toLowerCase() || 
          slug.toLowerCase() == value.toLowerCase()) {
        return name;
      }
    }
    return null;
  }

  Widget _buildFilterDropdown({
    required String label,
    required String value,
    required List<String> options,
    required Function(String?) onChanged,
    IconData? icon,
  }) {
    // Ensure we always have at least 'All' option
    final safeOptions = options.isEmpty ? ['All'] : ['All', ...options];
    
    // Validate current value, fall back to 'All' if not in list
    final safeValue = safeOptions.contains(value) ? value : 'All';
    
    final isActive = safeValue != 'All';
    final isDisabled = safeOptions.length == 1; // Only 'All' available
    
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            if (icon != null) ...[
              Icon(
                icon,
                size: 14,
                color: isActive ? Colors.red : Colors.grey[400],
              ),
              const SizedBox(width: 6),
            ],
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w500,
                color: isActive ? Colors.white : Colors.grey[400],
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        Container(
          decoration: BoxDecoration(
            color: isActive ? Colors.red.withOpacity(0.1) : Colors.grey[850],
            border: Border.all(
              color: isActive ? Colors.red.withOpacity(0.5) : Colors.grey[700]!,
              width: isActive ? 1.5 : 1,
            ),
            borderRadius: BorderRadius.circular(8),
            boxShadow: isActive
                ? [
                    BoxShadow(
                      color: Colors.red.withOpacity(0.2),
                      blurRadius: 8,
                      spreadRadius: 1,
                    ),
                  ]
                : null,
          ),
          child: DropdownButtonHideUnderline(
            child: DropdownButton<String>(
              value: safeValue,
              isExpanded: true,
              dropdownColor: Colors.grey[850],
              icon: Icon(
                Icons.arrow_drop_down,
                color: isActive ? Colors.red : Colors.grey[400],
              ),
              style: TextStyle(
                color: isActive ? Colors.white : Colors.grey[300],
                fontSize: 14,
              ),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              items: safeOptions.map((option) {
                return DropdownMenuItem<String>(
                  value: option,
                  child: Text(
                    option == 'All' ? 'All ${label}s' : option,
                    style: TextStyle(
                      color: option == safeValue ? Colors.red : Colors.white,
                      fontWeight: option == safeValue ? FontWeight.w600 : FontWeight.normal,
                    ),
                  ),
                );
              }).toList(),
              onChanged: isDisabled ? null : (value) {
                if (value != null) {
                  onChanged(value);
                  setState(() {
                    _currentPage = 1;
                    _hasMorePages = true;
                  });
                  _fetchTVShows();
                }
              },
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildFiltersPanel() {
    return Container(
      margin: const EdgeInsets.only(bottom: 24),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            Colors.grey[900]!,
            Colors.grey[850]!,
          ],
        ),
        border: Border.all(
          color: _hasActiveFilters ? Colors.red.withOpacity(0.3) : Colors.grey[800]!,
          width: 1,
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: _hasActiveFilters
            ? [
                BoxShadow(
                  color: Colors.red.withOpacity(0.2),
                  blurRadius: 20,
                  spreadRadius: 2,
                ),
              ]
            : [
                BoxShadow(
                  color: Colors.black.withOpacity(0.3),
                  blurRadius: 10,
                  spreadRadius: 1,
                ),
              ],
      ),
      child: Column(
        children: [
          // Header
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              border: Border(
                bottom: BorderSide(
                  color: Colors.grey[800]!,
                  width: 1,
                ),
              ),
            ),
            child: Row(
              children: [
                Icon(
                  Icons.tune,
                  color: _hasActiveFilters ? Colors.red : Colors.grey[400],
                  size: 24,
                ),
                const SizedBox(width: 12),
                Text(
                  'Filters',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                if (_hasActiveFilters) ...[
                  const SizedBox(width: 12),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.red,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      '${_getActiveFilterCount()} active',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
                const Spacer(),
                if (_hasActiveFilters)
                  TextButton.icon(
                    onPressed: _clearFilters,
                    icon: const Icon(Icons.clear_all, size: 18, color: Colors.red),
                    label: const Text(
                      'Clear All',
                      style: TextStyle(
                        color: Colors.red,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                IconButton(
                  onPressed: () {
                    setState(() {
                      _showFilters = false;
                    });
                  },
                  icon: Icon(Icons.close, color: Colors.grey[400]),
                  tooltip: 'Close filters',
                ),
              ],
            ),
          ),
          
          // Filter options
          Padding(
            padding: const EdgeInsets.all(20),
            child: LayoutBuilder(
              builder: (context, constraints) {
                final crossAxisCount = constraints.maxWidth > 1200
                    ? 3
                    : constraints.maxWidth > 800
                        ? 2
                        : 1;

                return GridView.count(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  crossAxisCount: crossAxisCount,
                  crossAxisSpacing: 20,
                  mainAxisSpacing: 20,
                  childAspectRatio: 3.5,
                  children: [
                    _buildFilterDropdown(
                      label: 'Genre',
                      value: _selectedGenre,
                      options: _getOptionsFromList(_genres),
                      onChanged: (value) {
                        setState(() {
                          _selectedGenre = value!;
                          _currentPage = 1;
                          _hasMorePages = true;
                        });
                        _fetchTVShows();
                      },
                      icon: Icons.movie_filter,
                    ),
                    _buildFilterDropdown(
                      label: 'Category',
                      value: _selectedCategory,
                      options: _getOptionsFromList(_categories),
                      onChanged: (value) {
                        setState(() {
                          _selectedCategory = value!;
                          _currentPage = 1;
                          _hasMorePages = true;
                        });
                        _fetchTVShows();
                      },
                      icon: Icons.category,
                    ),
                    _buildFilterDropdown(
                      label: 'Country',
                      value: _selectedCountry,
                      options: _getOptionsFromList(_countries),
                      onChanged: (value) {
                        setState(() {
                          _selectedCountry = value!;
                          _currentPage = 1;
                          _hasMorePages = true;
                        });
                        _fetchTVShows();
                      },
                      icon: Icons.public,
                    ),
                    _buildFilterDropdown(
                      label: 'Year',
                      value: _selectedYear,
                      options: _yearOptions.where((y) => y != 'All').toList(),
                      onChanged: (value) {
                        setState(() {
                          _selectedYear = value!;
                          _currentPage = 1;
                          _hasMorePages = true;
                        });
                        _fetchTVShows();
                      },
                      icon: Icons.calendar_today,
                    ),
                    _buildFilterDropdown(
                      label: 'Language',
                      value: _selectedLanguage,
                      options: _getOptionsFromList(_languages),
                      onChanged: (value) {
                        setState(() {
                          _selectedLanguage = value!;
                          _currentPage = 1;
                          _hasMorePages = true;
                        });
                        _fetchTVShows();
                      },
                      icon: Icons.language,
                    ),
                    _buildFilterDropdown(
                      label: 'Rating',
                      value: _selectedRating,
                      options: _ratingOptions.where((r) => r != 'All').toList(),
                      onChanged: (value) {
                        setState(() {
                          _selectedRating = value!;
                          _currentPage = 1;
                          _hasMorePages = true;
                        });
                        _fetchTVShows();
                      },
                      icon: Icons.star,
                    ),
                  ],
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: RefreshIndicator(
        onRefresh: _fetchTVShows,
        color: Colors.red,
        backgroundColor: Colors.grey[900],
        child: CustomScrollView(
          controller: _scrollController,
          slivers: [
            // App Bar
            SliverAppBar(
              floating: true,
              backgroundColor: Colors.black,
              title: const Text(
                'TV Shows',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 28,
                  fontWeight: FontWeight.bold,
                ),
              ),
              actions: [
                // Sort dropdown
                Container(
                  margin: const EdgeInsets.symmetric(horizontal: 8),
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  decoration: BoxDecoration(
                    color: Colors.grey[850],
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.grey[700]!),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<String>(
                      value: _sortBy,
                      dropdownColor: Colors.grey[850],
                      icon: const Icon(Icons.arrow_drop_down, color: Colors.white),
                      style: const TextStyle(color: Colors.white, fontSize: 14),
                      items: _sortOptions.map((option) {
                        return DropdownMenuItem<String>(
                          value: option['value'],
                          child: Text(option['label']!),
                        );
                      }).toList(),
                      onChanged: (value) {
                        if (value != null) {
                          setState(() {
                            _sortBy = value;
                            _currentPage = 1;
                            _hasMorePages = true;
                          });
                          _fetchTVShows();
                        }
                      },
                    ),
                  ),
                ),
                
                // Filter button
                Container(
                  margin: const EdgeInsets.only(right: 16, left: 8),
                  child: Stack(
                    children: [
                      IconButton(
                        onPressed: () {
                          setState(() {
                            _showFilters = !_showFilters;
                          });
                        },
                        icon: Icon(
                          Icons.tune,
                          color: _showFilters ? Colors.red : Colors.white,
                        ),
                        style: IconButton.styleFrom(
                          backgroundColor: _showFilters ? Colors.red.withOpacity(0.2) : Colors.grey[850],
                        ),
                      ),
                      if (_hasActiveFilters)
                        Positioned(
                          right: 8,
                          top: 8,
                          child: Container(
                            padding: const EdgeInsets.all(4),
                            decoration: const BoxDecoration(
                              color: Colors.red,
                              shape: BoxShape.circle,
                            ),
                            child: Text(
                              '${_getActiveFilterCount()}',
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ],
            ),

            // Content
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Filters panel
                    if (_showFilters) _buildFiltersPanel(),

                    // Results info
                    if (_pagination['total'] != null && _pagination['total'] > 0)
                      Padding(
                        padding: const EdgeInsets.only(bottom: 16),
                        child: Text(
                          () {
                            // Backend returns: total, from, to, current_page, last_page
                            final total = _pagination['total'] ?? 0;
                            final from = _pagination['from'] ?? 0;
                            final to = _pagination['to'] ?? 0;
                            final currentPage = _pagination['current_page'] ?? _currentPage;
                            
                            if (from > 0 && to > 0) {
                              return 'Showing $from - $to of $total TV shows';
                            } else {
                              // Fallback calculation
                              final perPage = _pagination['per_page'] ?? 20;
                              final start = ((currentPage - 1) * perPage) + 1;
                              final end = (currentPage * perPage) < total ? (currentPage * perPage) : total;
                              return 'Showing $start - $end of $total TV shows';
                            }
                          }(),
                          style: TextStyle(
                            color: Colors.grey[400],
                            fontSize: 14,
                          ),
                        ),
                      ),

                    // Loading state
                    if (_isLoading)
                      const Center(
                        child: Padding(
                          padding: EdgeInsets.all(40),
                          child: CircularProgressIndicator(
                            color: Colors.red,
                          ),
                        ),
                      ),

                    // Empty state
                    if (!_isLoading && _tvShows.isEmpty)
                      Center(
                        child: Padding(
                          padding: const EdgeInsets.all(40),
                          child: Column(
                            children: [
                              Icon(
                                Icons.tv_off,
                                size: 64,
                                color: Colors.grey[600],
                              ),
                              const SizedBox(height: 16),
                              Text(
                                'No TV shows found',
                                style: TextStyle(
                                  color: Colors.grey[400],
                                  fontSize: 18,
                                ),
                              ),
                              if (_hasActiveFilters) ...[
                                const SizedBox(height: 16),
                                TextButton(
                                  onPressed: _clearFilters,
                                  child: const Text(
                                    'Clear all filters',
                                    style: TextStyle(
                                      color: Colors.red,
                                      decoration: TextDecoration.underline,
                                    ),
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                      ),

                    // TV shows grid
                    if (!_isLoading && _tvShows.isNotEmpty) ...[
                      GridView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: 2,
                          childAspectRatio: 0.75,
                          crossAxisSpacing: 16,
                          mainAxisSpacing: 20,
                        ),
                        itemCount: _tvShows.length,
                        itemBuilder: (context, index) {
                          return _buildStyledTVShowCard(_tvShows[index]);
                        },
                      ),

                      // Loading More Indicator
                      if (_isLoadingMore)
                        const Padding(
                          padding: EdgeInsets.all(20.0),
                          child: Center(
                            child: CircularProgressIndicator(color: Colors.red),
                          ),
                        ),
                      
                      // End of Results Message
                      if (!_hasMorePages && _tvShows.isNotEmpty)
                        Padding(
                          padding: const EdgeInsets.all(20.0),
                          child: Text(
                            'You\'ve reached the end',
                            style: TextStyle(
                              color: Colors.grey[400],
                              fontSize: 14,
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ),
                    ],
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStyledTVShowCard(TVShow tvShow) {
    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.red.withOpacity(0.3),
            blurRadius: 20,
            spreadRadius: 2,
            offset: const Offset(0, 8),
          ),
          BoxShadow(
            color: Colors.black.withOpacity(0.6),
            blurRadius: 15,
            spreadRadius: 1,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: Material(
          color: Colors.transparent,
          child: InkWell(
            onTap: () {
              Navigator.pushNamed(
                context,
                '/tvshow/${tvShow.id}?name=${Uri.encodeComponent(tvShow.name)}',
              );
            },
            borderRadius: BorderRadius.circular(16),
            splashColor: Colors.red.withOpacity(0.3),
            highlightColor: Colors.red.withOpacity(0.1),
            child: Stack(
              children: [
                // Background Image with Gradient Overlay
                Positioned.fill(
                  child: Container(
                    decoration: BoxDecoration(
                      color: Colors.grey[900],
                    ),
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        // TV Show Backdrop
                        Builder(
                          builder: (context) {
                            final imageUrl = tvShow.getBackdropUrl('w1280');
                            final isValidUrl = imageUrl.isNotEmpty && 
                                (imageUrl.startsWith('http://') || imageUrl.startsWith('https://')) &&
                                !imageUrl.contains('placeholder');
                            
                            if (isValidUrl)
                              return CachedNetworkImage(
                                imageUrl: imageUrl,
                                fit: BoxFit.cover,
                                errorWidget: (context, url, error) {
                                  return Container(
                                    color: Colors.grey[900],
                                    child: Icon(
                                      Icons.tv,
                                      size: 60,
                                      color: Colors.grey[700],
                                    ),
                                  );
                                },
                                placeholder: (context, url) {
                                  return Container(
                                    color: Colors.grey[900],
                                    child: Center(
                                      child: CircularProgressIndicator(
                                        color: Colors.red,
                                        strokeWidth: 2,
                                      ),
                                    ),
                                  );
                                },
                              );
                            else
                              return Container(
                                color: Colors.grey[900],
                                child: Icon(
                                  Icons.tv,
                                  size: 60,
                                  color: Colors.grey[700],
                                ),
                              );
                          },
                        ),
                        
                        // Gradient Overlay
                        Container(
                          decoration: BoxDecoration(
                            gradient: LinearGradient(
                              begin: Alignment.topCenter,
                              end: Alignment.bottomCenter,
                              colors: [
                                Colors.transparent,
                                Colors.black.withOpacity(0.3),
                                Colors.black.withOpacity(0.8),
                                Colors.black.withOpacity(0.95),
                              ],
                              stops: const [0.0, 0.4, 0.7, 1.0],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),

                // Content Overlay
                Positioned(
                  bottom: 0,
                  left: 0,
                  right: 0,
                  child: Container(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        // TV Show Title
                        Text(
                          tvShow.name,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        
                        const SizedBox(height: 8),
                        
                        // View Count Badge
                        if (tvShow.viewCount != null && tvShow.viewCount! > 0)
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
                            decoration: BoxDecoration(
                              color: Colors.orange.withOpacity(0.2),
                              borderRadius: BorderRadius.circular(6),
                              border: Border.all(
                                color: Colors.orange.withOpacity(0.5),
                                width: 1,
                              ),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(
                                  Icons.visibility,
                                  size: 12,
                                  color: Colors.orange[300],
                                ),
                                const SizedBox(width: 4),
                                Text(
                                  _formatViewCount(tvShow.viewCount!),
                                  style: TextStyle(
                                    color: Colors.orange[300],
                                    fontSize: 11,
                                    fontWeight: FontWeight.w500,
                                  ),
                                ),
                              ],
                            ),
                          ),
                      ],
                    ),
                  ),
                ),

                // Bottom Gradient Border
                Positioned(
                  bottom: 0,
                  left: 0,
                  right: 0,
                  height: 4,
                  child: Container(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          Colors.red,
                          Colors.red.withOpacity(0.7),
                          Colors.transparent,
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  String _formatViewCount(int count) {
    if (count >= 1000000) {
      return '${(count / 1000000).toStringAsFixed(1)}M';
    } else if (count >= 1000) {
      return '${(count / 1000).toStringAsFixed(1)}K';
    }
    return count.toString();
  }
}

