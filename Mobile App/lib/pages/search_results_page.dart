import 'package:flutter/material.dart';
import '../models/movie.dart';
import '../models/tvshow.dart';
import '../services/api_service.dart';
import '../widgets/movie_card.dart';
import '../widgets/tvshow_card.dart';

class SearchResultsPage extends StatefulWidget {
  final String? initialQuery;

  const SearchResultsPage({
    Key? key,
    this.initialQuery,
  }) : super(key: key);

  @override
  State<SearchResultsPage> createState() => _SearchResultsPageState();
}

class _SearchResultsPageState extends State<SearchResultsPage> {
  final TextEditingController _searchController = TextEditingController();
  
  List<Movie> _movies = [];
  List<TVShow> _tvShows = [];
  int _totalMovies = 0;
  int _totalTVShows = 0;
  
  bool _loading = false;
  String _error = '';
  int _currentPage = 1;
  int _totalPages = 1;
  String _searchType = 'all'; // 'all', 'movies', 'tvshows'
  bool _showFilters = false;
  
  // Filters
  List<Map<String, dynamic>> _genres = [];
  List<Map<String, dynamic>> _countries = [];
  List<Map<String, dynamic>> _languages = [];
  List<int> _years = [];
  
  Map<String, String> _appliedFilters = {
    'genre': '',
    'country': '',
    'year': '',
    'language': '',
    'sort_by': 'popularity',
    'order': 'desc',
  };

  @override
  void initState() {
    super.initState();
    if (widget.initialQuery != null && widget.initialQuery!.isNotEmpty) {
      _searchController.text = widget.initialQuery!;
      _performSearch();
    }
    _fetchUtilityData();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchUtilityData() async {
    final data = await ApiService.getUtilityData();
    setState(() {
      _genres = (data['genres'] as List? ?? []).cast<Map<String, dynamic>>();
      _countries = (data['countries'] as List? ?? []).cast<Map<String, dynamic>>();
      _languages = (data['languages'] as List? ?? []).cast<Map<String, dynamic>>();
      
      // Generate years
      final currentYear = DateTime.now().year;
      _years = List.generate(50, (index) => currentYear - index);
    });
  }

  Future<void> _performSearch() async {
    final query = _searchController.text.trim();
    if (query.isEmpty) return;

    setState(() {
      _loading = true;
      _error = '';
    });

    try {
      // Build query params
      final params = {
        'page': _currentPage.toString(),
        'limit': '20',
        ..._appliedFilters,
      };

      // Remove empty filters
      params.removeWhere((key, value) => value.isEmpty || value == 'All');

      List<Movie> movies = [];
      List<TVShow> tvShows = [];
      int totalMovies = 0;
      int totalTVShows = 0;

      // Search movies if type is 'all' or 'movies'
      if (_searchType == 'all' || _searchType == 'movies') {
        try {
          final movieResult = await ApiService.searchMovies(
            query: query,
            params: params,
          );
          movies = movieResult['movies'] ?? [];
          totalMovies = movieResult['pagination']?['totalItems'] ?? 0;
        } catch (e) {
          print('Movie search error: $e');
        }
      }

      // Search TV shows if type is 'all' or 'tvshows'
      if (_searchType == 'all' || _searchType == 'tvshows') {
        try {
          final tvShowResult = await ApiService.searchTVShows(
            query: query,
            params: params,
          );
          tvShows = tvShowResult['tvShows'] ?? [];
          totalTVShows = tvShowResult['pagination']?['totalItems'] ?? 0;
        } catch (e) {
          print('TV show search error: $e');
        }
      }

      setState(() {
        _movies = movies;
        _tvShows = tvShows;
        _totalMovies = totalMovies;
        _totalTVShows = totalTVShows;
        
        // Calculate total pages
        final totalResults = totalMovies + totalTVShows;
        _totalPages = (totalResults / 20).ceil();
        _loading = false;
      });
    } catch (e) {
      setState(() {
        _error = 'Failed to search. Please try again.';
        _loading = false;
      });
    }
  }

  void _handleFilterChange(String key, String value) {
    setState(() {
      _appliedFilters[key] = value;
      _currentPage = 1;
    });
    _performSearch();
  }

  void _resetFilters() {
    setState(() {
      _appliedFilters = {
        'genre': '',
        'country': '',
        'year': '',
        'language': '',
        'sort_by': 'popularity',
        'order': 'desc',
      };
      _currentPage = 1;
    });
    _performSearch();
  }

  @override
  Widget build(BuildContext context) {
    final totalResults = _totalMovies + _totalTVShows;
    final width = MediaQuery.of(context).size.width;

    return Scaffold(
      backgroundColor: const Color(0xFF0A0A0A),
      appBar: AppBar(
        backgroundColor: Colors.black,
        elevation: 0,
        title: const Text(
          'Search',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
        ),
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Search Header
              Row(
                children: [
                  const Icon(Icons.search, color: Colors.red, size: 32),
                  const SizedBox(width: 12),
                  const Text(
                    'Search Results',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 28,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),

              // Search Bar
              Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _searchController,
                      onSubmitted: (_) => _performSearch(),
                      style: const TextStyle(color: Colors.white),
                      decoration: InputDecoration(
                        hintText: 'Search movies, TV shows...',
                        hintStyle: TextStyle(color: Colors.grey[600]),
                        prefixIcon: const Icon(Icons.search, color: Colors.grey),
                        filled: true,
                        fillColor: const Color(0xFF1A1A1A),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: BorderSide.none,
                        ),
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 14,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  ElevatedButton(
                    onPressed: _performSearch,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.red,
                      padding: const EdgeInsets.symmetric(
                        horizontal: 24,
                        vertical: 14,
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: const Text(
                      'Search',
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  OutlinedButton.icon(
                    onPressed: () {
                      setState(() => _showFilters = !_showFilters);
                    },
                    icon: const Icon(Icons.filter_list, color: Colors.white),
                    label: const Text(
                      'Filters',
                      style: TextStyle(color: Colors.white),
                    ),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: Colors.grey),
                      padding: const EdgeInsets.symmetric(
                        horizontal: 24,
                        vertical: 14,
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),

              // Search Type Tabs
              Row(
                children: [
                  _buildTab('All', 'all', totalResults),
                  const SizedBox(width: 8),
                  _buildTab('Movies', 'movies', _totalMovies),
                  const SizedBox(width: 8),
                  _buildTab('TV Shows', 'tvshows', _totalTVShows),
                ],
              ),
              const SizedBox(height: 24),

              // Filters Panel
              if (_showFilters) ...[
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: const Color(0xFF1A1A1A),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Filters',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 16),
                      Wrap(
                        spacing: 12,
                        runSpacing: 12,
                        children: [
                          _buildFilterDropdown(
                            'Genre',
                            _appliedFilters['genre']!,
                            ['', ..._genres.map((g) => g['id'].toString())],
                            ['All Genres', ..._genres.map((g) => g['name'] as String)],
                            (value) => _handleFilterChange('genre', value),
                          ),
                          _buildFilterDropdown(
                            'Country',
                            _appliedFilters['country']!,
                            ['', ..._countries.map((c) => c['id'].toString())],
                            ['All Countries', ..._countries.map((c) => c['name'] as String)],
                            (value) => _handleFilterChange('country', value),
                          ),
                          _buildFilterDropdown(
                            'Year',
                            _appliedFilters['year']!,
                            ['', ..._years.map((y) => y.toString())],
                            ['All Years', ..._years.map((y) => y.toString())],
                            (value) => _handleFilterChange('year', value),
                          ),
                          _buildFilterDropdown(
                            'Language',
                            _appliedFilters['language']!,
                            ['', ..._languages.map((l) => l['name'] as String)],
                            ['All Languages', ..._languages.map((l) => l['name'] as String)],
                            (value) => _handleFilterChange('language', value),
                          ),
                          _buildFilterDropdown(
                            'Sort By',
                            _appliedFilters['sort_by']!,
                            ['popularity', 'title', 'release_date', 'vote_average'],
                            ['Popularity', 'Title', 'Release Date', 'Rating'],
                            (value) => _handleFilterChange('sort_by', value),
                          ),
                          _buildFilterDropdown(
                            'Order',
                            _appliedFilters['order']!,
                            ['desc', 'asc'],
                            ['Descending', 'Ascending'],
                            (value) => _handleFilterChange('order', value),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Align(
                        alignment: Alignment.centerRight,
                        child: OutlinedButton(
                          onPressed: _resetFilters,
                          style: OutlinedButton.styleFrom(
                            side: const BorderSide(color: Colors.grey),
                            padding: const EdgeInsets.symmetric(
                              horizontal: 24,
                              vertical: 12,
                            ),
                          ),
                          child: const Text(
                            'Reset Filters',
                            style: TextStyle(color: Colors.white),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 24),
              ],

              // Loading
              if (_loading)
                const Center(
                  child: Padding(
                    padding: EdgeInsets.all(48.0),
                    child: CircularProgressIndicator(
                      valueColor: AlwaysStoppedAnimation<Color>(Colors.red),
                    ),
                  ),
                ),

              // Error
              if (_error.isNotEmpty)
                Center(
                  child: Padding(
                    padding: const EdgeInsets.all(48.0),
                    child: Column(
                      children: [
                        Text(
                          _error,
                          style: const TextStyle(color: Colors.red),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _performSearch,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.red,
                          ),
                          child: const Text('Try Again'),
                        ),
                      ],
                    ),
                  ),
                ),

              // Results
              if (!_loading && _error.isEmpty && _searchController.text.isNotEmpty) ...[
                // Results Summary
                Text(
                  'Found $totalResults result${totalResults != 1 ? 's' : ''} for "${_searchController.text}"',
                  style: TextStyle(color: Colors.grey[400], fontSize: 14),
                ),
                const SizedBox(height: 24),

                // Movies Results
                if ((_searchType == 'all' || _searchType == 'movies') && _movies.isNotEmpty) ...[
                  Row(
                    children: [
                      const Icon(Icons.movie, color: Colors.white, size: 24),
                      const SizedBox(width: 8),
                      Text(
                        'Movies ($_totalMovies)',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  LayoutBuilder(
                    builder: (context, constraints) {
                      final crossAxisCount = width < 600 ? 2 : width < 900 ? 3 : width < 1200 ? 4 : 5;
                      return GridView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: crossAxisCount,
                          childAspectRatio: 0.65,
                          crossAxisSpacing: 16,
                          mainAxisSpacing: 16,
                        ),
                        itemCount: _movies.length,
                        itemBuilder: (context, index) {
                          return MovieCard(movie: _movies[index]);
                        },
                      );
                    },
                  ),
                  const SizedBox(height: 32),
                ],

                // TV Shows Results
                if ((_searchType == 'all' || _searchType == 'tvshows') && _tvShows.isNotEmpty) ...[
                  Row(
                    children: [
                      const Icon(Icons.tv, color: Colors.white, size: 24),
                      const SizedBox(width: 8),
                      Text(
                        'TV Shows ($_totalTVShows)',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  LayoutBuilder(
                    builder: (context, constraints) {
                      final crossAxisCount = width < 600 ? 2 : width < 900 ? 3 : width < 1200 ? 4 : 5;
                      return GridView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: crossAxisCount,
                          childAspectRatio: 0.65,
                          crossAxisSpacing: 16,
                          mainAxisSpacing: 16,
                        ),
                        itemCount: _tvShows.length,
                        itemBuilder: (context, index) {
                          return TVShowCard(tvShow: _tvShows[index]);
                        },
                      );
                    },
                  ),
                  const SizedBox(height: 32),
                ],

                // No Results
                if (totalResults == 0) ...[
                  Center(
                    child: Padding(
                      padding: const EdgeInsets.all(48.0),
                      child: Column(
                        children: [
                          const Icon(
                            Icons.search_off,
                            size: 64,
                            color: Colors.grey,
                          ),
                          const SizedBox(height: 16),
                          const Text(
                            'No results found',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Try adjusting your search terms or filters',
                            style: TextStyle(color: Colors.grey[600]),
                            textAlign: TextAlign.center,
                          ),
                          const SizedBox(height: 16),
                          OutlinedButton(
                            onPressed: _resetFilters,
                            style: OutlinedButton.styleFrom(
                              side: const BorderSide(color: Colors.grey),
                            ),
                            child: const Text(
                              'Clear Filters',
                              style: TextStyle(color: Colors.white),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],

                // Pagination
                if (_totalPages > 1) ...[
                  const SizedBox(height: 32),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      OutlinedButton(
                        onPressed: _currentPage > 1
                            ? () {
                                setState(() => _currentPage--);
                                _performSearch();
                              }
                            : null,
                        style: OutlinedButton.styleFrom(
                          side: const BorderSide(color: Colors.grey),
                        ),
                        child: const Text(
                          'Previous',
                          style: TextStyle(color: Colors.white),
                        ),
                      ),
                      const SizedBox(width: 16),
                      Text(
                        'Page $_currentPage of $_totalPages',
                        style: const TextStyle(color: Colors.white),
                      ),
                      const SizedBox(width: 16),
                      OutlinedButton(
                        onPressed: _currentPage < _totalPages
                            ? () {
                                setState(() => _currentPage++);
                                _performSearch();
                              }
                            : null,
                        style: OutlinedButton.styleFrom(
                          side: const BorderSide(color: Colors.grey),
                        ),
                        child: const Text(
                          'Next',
                          style: TextStyle(color: Colors.white),
                        ),
                      ),
                    ],
                  ),
                ],
              ],

              // Empty State
              if (!_loading && _error.isEmpty && _searchController.text.isEmpty) ...[
                Center(
                  child: Padding(
                    padding: const EdgeInsets.all(48.0),
                    child: Column(
                      children: [
                        const Icon(
                          Icons.search,
                          size: 64,
                          color: Colors.grey,
                        ),
                        const SizedBox(height: 16),
                        const Text(
                          'Start your search',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Enter a movie or TV show title to get started',
                          style: TextStyle(color: Colors.grey[600]),
                          textAlign: TextAlign.center,
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTab(String label, String type, int count) {
    final isActive = _searchType == type;
    return GestureDetector(
      onTap: () {
        setState(() {
          _searchType = type;
          _currentPage = 1;
        });
        if (_searchController.text.isNotEmpty) {
          _performSearch();
        }
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: isActive ? Colors.red : const Color(0xFF1A1A1A),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Text(
          '$label ($count)',
          style: TextStyle(
            color: isActive ? Colors.white : Colors.grey[400],
            fontWeight: isActive ? FontWeight.bold : FontWeight.normal,
          ),
        ),
      ),
    );
  }

  Widget _buildFilterDropdown(
    String label,
    String value,
    List<String> values,
    List<String> labels,
    Function(String) onChanged,
  ) {
    return Container(
      width: 180,
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
      decoration: BoxDecoration(
        color: const Color(0xFF0A0A0A),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.grey[800]!),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: TextStyle(
              color: Colors.grey[600],
              fontSize: 12,
              fontWeight: FontWeight.bold,
            ),
          ),
          DropdownButton<String>(
            value: value.isEmpty ? values[0] : value,
            isExpanded: true,
            underline: const SizedBox(),
            dropdownColor: const Color(0xFF1A1A1A),
            style: const TextStyle(color: Colors.white, fontSize: 14),
            items: List.generate(values.length, (index) {
              return DropdownMenuItem(
                value: values[index],
                child: Text(
                  labels[index],
                  overflow: TextOverflow.ellipsis,
                ),
              );
            }),
            onChanged: (newValue) {
              if (newValue != null) {
                onChanged(newValue);
              }
            },
          ),
        ],
      ),
    );
  }
}

