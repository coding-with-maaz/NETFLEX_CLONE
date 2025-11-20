import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../services/api_service.dart';
import '../models/movie.dart';
import '../widgets/layout/home_header.dart';

class MoviesPage extends StatefulWidget {
  final Map<String, String>? initialFilters;
  
  const MoviesPage({Key? key, this.initialFilters}) : super(key: key);

  @override
  State<MoviesPage> createState() => _MoviesPageState();
}

class _MoviesPageState extends State<MoviesPage> {
  final ScrollController _scrollController = ScrollController();
  
  bool _isLoading = true;
  bool _isLoadingMore = false;
  bool _hasMorePages = true;
  List<Movie> _movies = [];
  Map<String, dynamic> _pagination = {};
  bool _showFilters = false;
  
  // Filter data
  List<Map<String, dynamic>> _genres = [];
  List<Map<String, dynamic>> _countries = [];
  List<Map<String, dynamic>> _categories = [];
  List<Map<String, dynamic>> _languages = [];
  
  // Selected filters
  String _selectedGenre = '';
  String _selectedCountry = '';
  String _selectedCategory = '';
  String _selectedYear = '';
  String _selectedLanguage = '';
  String _selectedRating = '';
  String _sortBy = 'foryou';
  int _currentPage = 1;

  final List<String> _yearOptions = [
    'All',
    '2025',
    '2024',
    '2023',
    '2022',
    '2021',
    '2020',
    '2010s',
    '2000s',
    '1990s',
    '1980s',
    'Other'
  ];

  final List<String> _ratingOptions = ['All', '9+', '8+', '7+', '6+', '5+'];

  final List<Map<String, String>> _sortOptions = [
    {'value': 'foryou', 'label': 'For You'},
    {'value': 'hottest', 'label': 'Hottest'},
    {'value': 'latest', 'label': 'Latest'},
    {'value': 'rating', 'label': 'Top Rated'},
    {'value': 'title', 'label': 'A-Z'},
  ];

  @override
  void initState() {
    super.initState();
    _applyInitialFilters();
    _fetchUtilityData().then((_) {
      // After utility data is loaded, re-apply filters to match genre properly
      if (widget.initialFilters != null && widget.initialFilters!.containsKey('genre')) {
        _applyInitialFilters();
      }
      _fetchMovies();
    });
    _setupScrollListener();
  }

  void _setupScrollListener() {
    _scrollController.addListener(() {
      if (!_scrollController.hasClients) return;
      
      final maxScroll = _scrollController.position.maxScrollExtent;
      final currentScroll = _scrollController.position.pixels;
      
      // Load more when user is 200 pixels from bottom
      if (currentScroll >= maxScroll - 200) {
        _loadMoreMovies();
      }
    });
  }

  void _applyInitialFilters() {
    if (widget.initialFilters != null) {
      final filters = widget.initialFilters!;
      
      // Apply genre filter - handle URL decoding for special characters like "+"
      if (filters.containsKey('genre')) {
        String genreValue = filters['genre'] ?? '';
        // Decode URL encoding (e.g., "18%2B" becomes "18+")
        genreValue = Uri.decodeComponent(genreValue);
        _selectedGenre = genreValue;
        _showFilters = true; // Show filters panel
        print('MoviesPage: Applied genre filter from URL: "$genreValue"');
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
        _selectedCountry = filters['country'] ?? '';
        _showFilters = true;
      }
      
      if (filters.containsKey('category')) {
        _selectedCategory = filters['category'] ?? '';
        _showFilters = true;
      }
      
      if (filters.containsKey('year')) {
        _selectedYear = filters['year'] ?? '';
        _showFilters = true;
      }
      
      if (filters.containsKey('language')) {
        _selectedLanguage = filters['language'] ?? '';
        _showFilters = true;
      }
      
      if (filters.containsKey('min_rating')) {
        _selectedRating = filters['min_rating'] ?? '';
        _showFilters = true;
      }
      
      print('Applied initial filters: genre=$_selectedGenre, sortBy=$_sortBy');
    }
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _fetchUtilityData() async {
    try {
    final data = await ApiService.getUtilityData();
      print('Utility Data Received: $data');
      
      setState(() {
        _genres = List<Map<String, dynamic>>.from(data['genres'] ?? []);
        _countries = List<Map<String, dynamic>>.from(data['countries'] ?? []);
        _categories = List<Map<String, dynamic>>.from(data['categories'] ?? []);
        _languages = List<Map<String, dynamic>>.from(data['languages'] ?? []);
      });
      
      print('Genres count: ${_genres.length}');
      print('Countries count: ${_countries.length}');
      print('Categories count: ${_categories.length}');
      print('Languages count: ${_languages.length}');
    } catch (e) {
      print('Error fetching utility data: $e');
      // Set empty lists on error
    setState(() {
        _genres = [];
        _countries = [];
        _categories = [];
        _languages = [];
      });
    }
  }

  Future<void> _fetchMovies({bool loadMore = false}) async {
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
      } else if (_sortBy == 'title') {
        backendSortBy = 'title';
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

      if (_selectedGenre.isNotEmpty && _selectedGenre != 'All') {
        // For 18+ genre, try to find the actual genre ID or slug from the genres list
        // Otherwise, pass the value as-is (backend should handle name/slug matching)
        String genreParam = _selectedGenre;
        
        // Try to find matching genre in the list to get proper slug/ID
        if (_genres.isNotEmpty) {
          // First, try exact match
          var matchingGenre = _genres.firstWhere(
            (genre) {
              final name = (genre['name'] ?? '').toString();
              final slug = (genre['slug'] ?? '').toString();
              return name.toLowerCase() == _selectedGenre.toLowerCase() || 
                     slug.toLowerCase() == _selectedGenre.toLowerCase();
            },
            orElse: () => <String, dynamic>{},
          );
          
          // If no exact match, try partial match for 18+
          if (matchingGenre.isEmpty && (_selectedGenre.toLowerCase().contains('18+') || _selectedGenre.toLowerCase().contains('18'))) {
            matchingGenre = _genres.firstWhere(
              (genre) {
                final name = (genre['name'] ?? '').toString().toLowerCase();
                final slug = (genre['slug'] ?? '').toString().toLowerCase();
                return name.contains('18+') || name.contains('18') || 
                       slug.contains('18+') || slug.contains('18');
              },
              orElse: () => <String, dynamic>{},
            );
          }
          
          if (matchingGenre.isNotEmpty) {
            // Prefer slug if available, otherwise use name, or ID if available
            if (matchingGenre.containsKey('slug') && matchingGenre['slug'] != null && (matchingGenre['slug'] as String).isNotEmpty) {
              genreParam = matchingGenre['slug'] as String;
            } else if (matchingGenre.containsKey('name') && matchingGenre['name'] != null) {
              genreParam = matchingGenre['name'] as String;
            } else if (matchingGenre.containsKey('id') && matchingGenre['id'] != null) {
              genreParam = matchingGenre['id'].toString();
            }
            print('MoviesPage: Found matching genre in list: ${matchingGenre['name']} (slug: ${matchingGenre['slug']}, id: ${matchingGenre['id']}), using: $genreParam');
          } else {
            print('MoviesPage: Genre "$_selectedGenre" not found in genres list, using as-is');
            print('MoviesPage: Available genres: ${_genres.map((g) => '${g['name']} (slug: ${g['slug']})').take(10).join(', ')}');
          }
        } else {
          print('MoviesPage: Genres list is empty, using genre as-is: "$_selectedGenre"');
        }
        
        params['genre'] = genreParam;
        print('MoviesPage: Setting genre param to: "$genreParam"');
      }
      if (_selectedCountry.isNotEmpty && _selectedCountry != 'All') {
        params['country'] = _selectedCountry;
      }
      if (_selectedCategory.isNotEmpty && _selectedCategory != 'All') {
        params['category'] = _selectedCategory;
      }
      if (_selectedYear.isNotEmpty && _selectedYear != 'All') {
        params['year'] = _selectedYear;
      }
      if (_selectedLanguage.isNotEmpty && _selectedLanguage != 'All') {
        params['language'] = _selectedLanguage;
      }
      if (_selectedRating.isNotEmpty && _selectedRating != 'All') {
        params['min_rating'] = _selectedRating.replaceAll('+', '');
      }

      print('MoviesPage: Fetching movies with params: $params');
      print('MoviesPage: Active filters - Genre: $_selectedGenre, Category: $_selectedCategory, Country: $_selectedCountry, Year: $_selectedYear, Language: $_selectedLanguage, Rating: $_selectedRating, Sort: $_sortBy');
      print('MoviesPage: Genres list has ${_genres.length} items');

      final result = await ApiService.getMovies(params: params);
      
      final newMovies = result['movies'] ?? [];
      final pagination = result['pagination'] ?? {};
      
      print('MoviesPage: API returned ${newMovies.length} movies');
      if (newMovies.isEmpty && _selectedGenre.isNotEmpty) {
        print('MoviesPage: WARNING - No movies found with genre filter "$_selectedGenre"');
        print('MoviesPage: Available genres: ${_genres.map((g) => '${g['name']} (slug: ${g['slug']})').join(', ')}');
      }
      // Backend returns: current_page, last_page, per_page, total, from, to
      final lastPage = pagination['last_page'] ?? pagination['lastPage'] ?? pagination['total_pages'] ?? pagination['totalPages'] ?? 1;
      final currentPageNum = pagination['current_page'] ?? pagination['currentPage'] ?? _currentPage;
      final total = pagination['total'] ?? 0;
      
      // Calculate if there are more pages
      final hasMore = currentPageNum < lastPage;
      
      setState(() {
        if (loadMore) {
          // Append new movies when loading more
          _movies.addAll(newMovies);
        } else {
          // Replace movies when starting fresh
          _movies = newMovies;
        }
        _pagination = pagination;
        // Sync _currentPage with API response
        _currentPage = currentPageNum;
        _isLoading = false;
        _isLoadingMore = false;
        // Set hasMorePages based on pagination
        _hasMorePages = hasMore;
      });
      
      print('MoviesPage: Fetched ${newMovies.length} movies. Total: ${_movies.length}. Current Page: $_currentPage, Last Page: $lastPage, Total Items: $total, Has More: $_hasMorePages');
      print('MoviesPage: Pagination: $_pagination');
    } catch (e) {
      print('MoviesPage: Error fetching movies: $e');
      setState(() {
        if (!loadMore) {
          _movies = [];
          _pagination = {};
        }
        _isLoading = false;
        _isLoadingMore = false;
      });
    }
  }

  Future<void> _loadMoreMovies() async {
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
    
    await _fetchMovies(loadMore: true);
  }

  void _clearFilters() {
    setState(() {
      _selectedGenre = '';
      _selectedCountry = '';
      _selectedCategory = '';
      _selectedYear = '';
      _selectedLanguage = '';
      _selectedRating = '';
      _sortBy = 'foryou';
      _currentPage = 1;
      _hasMorePages = true;
    });
    _fetchMovies();
  }

  bool get _hasActiveFilters {
    return _selectedGenre.isNotEmpty ||
        _selectedCountry.isNotEmpty ||
        _selectedCategory.isNotEmpty ||
        _selectedYear.isNotEmpty ||
        _selectedLanguage.isNotEmpty ||
        _selectedRating.isNotEmpty;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        children: [
          SingleChildScrollView(
            controller: _scrollController,
            child: Column(
              children: [
                const SizedBox(height: 80), // Space for header
                
                Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Header with Sort and Filter
                      _buildHeader(),
                      
                      const SizedBox(height: 16),

                      // Filters Panel
                      if (_showFilters) _buildFiltersPanel(),

                      const SizedBox(height: 16),

                      // Loading State
                      if (_isLoading)
                        const Center(
                          child: Padding(
                            padding: EdgeInsets.all(80.0),
                            child: CircularProgressIndicator(color: Colors.red),
                          ),
                        )
                      else ...[
                        // Results Info
                        if (_pagination['total'] != null && _pagination['total'] > 0)
                          _buildResultsInfo(),

                        const SizedBox(height: 16),

                        // Movies Grid or Empty State
                        if (_movies.isEmpty && !_isLoading)
                          _buildEmptyState()
                        else if (_movies.isNotEmpty) ...[
                          _buildMoviesGrid(),
                          
                          // Loading More Indicator
                          if (_isLoadingMore)
                            const Padding(
                              padding: EdgeInsets.all(20.0),
                              child: Center(
                                child: CircularProgressIndicator(color: Colors.red),
                              ),
                            ),
                          
                          // End of Results Message
                          if (!_hasMorePages && _movies.isNotEmpty)
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
                          
                          const SizedBox(height: 32),

                          // Traditional Pagination (Optional - can be hidden if using infinite scroll)
                          if (_pagination['totalPages'] != null &&
                              _pagination['totalPages'] > 1)
                            _buildPagination(),
                        ],
                      ],
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Floating Header
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            child: HomeHeader(scrollController: _scrollController),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
        const Text(
                              'Movies',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 32,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
        const SizedBox(height: 16),
        Row(
          children: [
                          // Sort Dropdown
            Expanded(
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                            decoration: BoxDecoration(
                              color: Colors.grey[800],
                  border: Border.all(color: Colors.grey[700]!),
                              borderRadius: BorderRadius.circular(8),
                            ),
                child: DropdownButtonHideUnderline(
                            child: DropdownButton<String>(
                    value: _sortBy,
                    isExpanded: true,
                    dropdownColor: Colors.grey[800],
                    style: const TextStyle(color: Colors.white),
                    icon: const Icon(Icons.arrow_drop_down, color: Colors.white),
                              onChanged: (value) {
                                if (value != null) {
                                  setState(() {
                                    _sortBy = value;
                                    _currentPage = 1;
                                    _hasMorePages = true;
                                  });
                                  _fetchMovies();
                                }
                              },
                    items: _sortOptions.map((option) {
                                return DropdownMenuItem<String>(
                                  value: option['value'],
                                  child: Text(option['label']!),
                                );
                              }).toList(),
                            ),
                          ),
              ),
            ),

            const SizedBox(width: 16),

                          // Filter Button
                          ElevatedButton.icon(
                            onPressed: () {
                setState(() => _showFilters = !_showFilters);
                            },
              icon: const Icon(Icons.filter_list, color: Colors.white),
                            label: Row(
                              children: [
                  const Text('Filters', style: TextStyle(color: Colors.white)),
                  if (_hasActiveFilters) ...[
                                  const SizedBox(width: 8),
                                  Container(
                                    width: 20,
                                    height: 20,
                                    decoration: const BoxDecoration(
                                      color: Colors.white,
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Center(
                                      child: Text(
                                        '!',
                                        style: TextStyle(
                                          color: Colors.red,
                                          fontWeight: FontWeight.bold,
                                          fontSize: 12,
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ],
                            ),
                            style: ElevatedButton.styleFrom(
                backgroundColor: Colors.red[600],
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                            ),
                          ),
                        ],
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
                const Text(
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
                      options: _getOptionsFromList(_genres, isGenre: true),
                      onChanged: (value) {
                        setState(() {
                          _selectedGenre = value ?? '';
                          _currentPage = 1;
                          _hasMorePages = true;
                        });
                        _fetchMovies();
                      },
                      icon: Icons.movie_filter,
                    ),
                    _buildFilterDropdown(
                      label: 'Category',
                      value: _selectedCategory,
                      options: _getOptionsFromList(_categories),
                      onChanged: (value) {
                        setState(() {
                          _selectedCategory = value ?? '';
                          _currentPage = 1;
                          _hasMorePages = true;
                        });
                        _fetchMovies();
                      },
                      icon: Icons.category,
                    ),
                    _buildFilterDropdown(
                      label: 'Country',
                      value: _selectedCountry,
                      options: _getOptionsFromList(_countries),
                      onChanged: (value) {
                        setState(() {
                          _selectedCountry = value ?? '';
                          _currentPage = 1;
                          _hasMorePages = true;
                        });
                        _fetchMovies();
                      },
                      icon: Icons.public,
                    ),
                    _buildFilterDropdown(
                      label: 'Year',
                      value: _selectedYear,
                      options: _yearOptions,
                      onChanged: (value) {
                        setState(() {
                          _selectedYear = value ?? '';
                          _currentPage = 1;
                          _hasMorePages = true;
                        });
                        _fetchMovies();
                      },
                      icon: Icons.calendar_today,
                    ),
                    _buildFilterDropdown(
                      label: 'Language',
                      value: _selectedLanguage,
                      options: _getOptionsFromList(_languages),
                      onChanged: (value) {
                        setState(() {
                          _selectedLanguage = value ?? '';
                          _currentPage = 1;
                          _hasMorePages = true;
                        });
                        _fetchMovies();
                      },
                      icon: Icons.language,
                    ),
                    _buildFilterDropdown(
                      label: 'Rating',
                      value: _selectedRating,
                      options: _ratingOptions,
                      onChanged: (value) {
                        setState(() {
                          _selectedRating = value ?? '';
                          _currentPage = 1;
                          _hasMorePages = true;
                        });
                        _fetchMovies();
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

  int _getActiveFilterCount() {
    int count = 0;
    if (_selectedGenre.isNotEmpty && _selectedGenre != 'All') count++;
    if (_selectedCountry.isNotEmpty && _selectedCountry != 'All') count++;
    if (_selectedCategory.isNotEmpty && _selectedCategory != 'All') count++;
    if (_selectedYear.isNotEmpty && _selectedYear != 'All') count++;
    if (_selectedLanguage.isNotEmpty && _selectedLanguage != 'All') count++;
    if (_selectedRating.isNotEmpty && _selectedRating != 'All') count++;
    return count;
  }

  List<String> _getOptionsFromList(List<Map<String, dynamic>> list, {bool isGenre = false}) {
    try {
      // For genres, use slug if available (matching Laravel), otherwise use name
      return ['All', ...list
          .where((item) {
            // Filter out 18+ genre from dropdown (but allow it if set programmatically)
            if (isGenre) {
              final name = (item['name'] ?? '').toString().toLowerCase();
              final slug = (item['slug'] ?? '').toString().toLowerCase();
              return !name.contains('18+') && !name.contains('18') && 
                     !slug.contains('18+') && !slug.contains('18');
            }
            return true;
          })
          .map((item) {
            // Prefer slug for genres (for API compatibility), fallback to name
            if (item.containsKey('slug') && item['slug'] != null && (item['slug'] as String).isNotEmpty) {
              return item['slug'] as String;
            }
            return (item['name'] ?? '') as String;
          }).where((value) => value.isNotEmpty)];
    } catch (e) {
      print('Error extracting options: $e');
      return ['All'];
    }
  }
  
  // Helper to get display name for filter (show name in UI, use slug/name for API)
  String _getDisplayNameForFilter(String value, List<Map<String, dynamic>> list) {
    if (value.isEmpty || value == 'All') return 'All';
    
    try {
      final item = list.firstWhere(
        (item) => 
          (item['slug'] == value || item['name'] == value),
        orElse: () => {},
      );
      
      return item['name'] ?? value;
    } catch (e) {
      return value;
    }
  }

  Widget _buildFilterDropdown({
    required String label,
    required String value,
    required List<String> options,
    required Function(String?) onChanged,
    IconData? icon,
  }) {
    // Ensure we always have at least 'All' option
    final safeOptions = options.isEmpty ? ['All'] : options;
    
    // Validate current value, but allow "18+" even if not in dropdown (set programmatically)
    final isEighteenPlus = value.toLowerCase().contains('18+') || value.toLowerCase().contains('18');
    final safeValue = (safeOptions.contains(value) || isEighteenPlus) ? value : 'All';
    
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
              items: [
                // Add "18+" option if it's the selected value but not in safeOptions
                if (isEighteenPlus && !safeOptions.contains(safeValue))
                  DropdownMenuItem<String>(
                    value: safeValue,
                    child: Text(
                      '18+',
                      style: const TextStyle(
                        color: Colors.red,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ...safeOptions.map((option) {
                  // For genre/category/country, show display name but store slug/name
                  String displayText;
                  if (option == 'All') {
                    displayText = 'All ${label}s';
                  } else {
                    // Get display name from the original list
                    if (label == 'Genre' && _genres.isNotEmpty) {
                      displayText = _getDisplayNameForFilter(option, _genres);
                    } else if (label == 'Category' && _categories.isNotEmpty) {
                      displayText = _getDisplayNameForFilter(option, _categories);
                    } else if (label == 'Country' && _countries.isNotEmpty) {
                      displayText = _getDisplayNameForFilter(option, _countries);
                    } else if (label == 'Language' && _languages.isNotEmpty) {
                      displayText = _getDisplayNameForFilter(option, _languages);
                    } else {
                      displayText = option;
                    }
                  }
                  
                  return DropdownMenuItem<String>(
                    value: option,
                    child: Text(
                      displayText,
                      style: TextStyle(
                        color: option == safeValue ? Colors.red : Colors.white,
                        fontWeight: option == safeValue ? FontWeight.w600 : FontWeight.normal,
                      ),
                    ),
                  );
                }),
              ],
              onChanged: isDisabled ? null : (value) {
                if (value != null) {
                  onChanged(value);
                }
              },
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildResultsInfo() {
    // Backend returns: total, from, to, current_page, last_page
    final total = _pagination['total'] ?? 0;
    final from = _pagination['from'] ?? 0;
    final to = _pagination['to'] ?? 0;
    final currentPage = _pagination['current_page'] ?? _currentPage;
    
    if (from > 0 && to > 0) {
      return Text(
        'Showing $from - $to of $total movies',
        style: TextStyle(
          color: Colors.grey[400],
          fontSize: 14,
        ),
      );
    } else {
      // Fallback calculation
      final perPage = _pagination['per_page'] ?? 20;
      final start = ((currentPage - 1) * perPage) + 1;
      final end = (currentPage * perPage) < total ? (currentPage * perPage) : total;
      return Text(
        'Showing $start - $end of $total movies',
        style: TextStyle(
          color: Colors.grey[400],
          fontSize: 14,
        ),
      );
    }
  }

  Widget _buildMoviesGrid() {
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        childAspectRatio: 0.75,
        crossAxisSpacing: 16,
        mainAxisSpacing: 20,
      ),
      itemCount: _movies.length,
      itemBuilder: (context, index) {
        return _buildStyledMovieCard(_movies[index]);
      },
    );
  }

  Widget _buildEmptyState() {
    return Center(
                          child: Padding(
        padding: const EdgeInsets.all(80.0),
                            child: Column(
                              children: [
                                Text(
                                  'No movies found matching your filters.',
              style: TextStyle(
                color: Colors.grey[400],
                fontSize: 18,
              ),
              textAlign: TextAlign.center,
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
    );
  }

  Widget _buildPagination() {
    // Backend returns last_page, not total_pages
    final totalPages = _pagination['last_page'] ?? _pagination['lastPage'] ?? _pagination['total_pages'] ?? _pagination['totalPages'] ?? 1;

    return Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
        // Previous Button
                            ElevatedButton(
          onPressed: _currentPage == 1
              ? null
              : () {
                  setState(() {
                    _currentPage--;
                    _hasMorePages = true;
                  });
                  _fetchMovies();
                  _scrollController.animateTo(
                    0,
                    duration: const Duration(milliseconds: 300),
                    curve: Curves.easeOut,
                  );
                },
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.grey[800],
            disabledBackgroundColor: Colors.grey[800]?.withOpacity(0.5),
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                              ),
          child: const Text('Previous', style: TextStyle(color: Colors.white)),
                            ),

                            const SizedBox(width: 16),

        // Page Numbers
        ..._buildPageNumbers(totalPages),

                            const SizedBox(width: 16),

        // Next Button
                            ElevatedButton(
          onPressed: _currentPage == totalPages
              ? null
              : () {
                  setState(() => _currentPage++);
                  _fetchMovies();
                  _scrollController.animateTo(
                    0,
                    duration: const Duration(milliseconds: 300),
                    curve: Curves.easeOut,
                  );
                },
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.grey[800],
            disabledBackgroundColor: Colors.grey[800]?.withOpacity(0.5),
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          ),
          child: const Text('Next', style: TextStyle(color: Colors.white)),
        ),
      ],
    );
  }

  List<Widget> _buildPageNumbers(int totalPages) {
    final List<Widget> pageButtons = [];
    final int maxPages = totalPages < 5 ? totalPages : 5;

    for (int i = 0; i < maxPages; i++) {
      int pageNum;
    if (totalPages <= 5) {
        pageNum = i + 1;
      } else if (_currentPage <= 3) {
        pageNum = i + 1;
      } else if (_currentPage >= totalPages - 2) {
        pageNum = totalPages - 4 + i;
    } else {
        pageNum = _currentPage - 2 + i;
    }
    
      pageButtons.add(
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 4),
          child: ElevatedButton(
            onPressed: () {
              setState(() {
                _currentPage = pageNum;
                _hasMorePages = true;
              });
              _fetchMovies();
              _scrollController.animateTo(
                0,
                duration: const Duration(milliseconds: 300),
                curve: Curves.easeOut,
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor:
                  _currentPage == pageNum ? Colors.red[600] : Colors.grey[800],
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              minimumSize: const Size(48, 48),
            ),
            child: Text(
              pageNum.toString(),
              style: const TextStyle(color: Colors.white),
            ),
          ),
        ),
      );
    }
    
    return pageButtons;
  }

  Widget _buildStyledMovieCard(Movie movie) {
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
                '/movie/${movie.id}',
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
                        // Movie Backdrop
                        Builder(
                          builder: (context) {
                            final imageUrl = movie.getBackdropUrl('w1280');
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
                                      Icons.movie,
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
                                  Icons.movie,
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
                        // Movie Title
                        Text(
                          movie.title,
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
                        if (movie.viewCount != null && movie.viewCount! > 0)
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
                                  _formatViewCount(movie.viewCount!),
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

