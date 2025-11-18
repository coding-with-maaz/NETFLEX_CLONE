import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../services/api_service.dart';
import '../models/movie.dart';
import '../models/tvshow.dart';
import '../widgets/layout/home_header.dart';

class TrendingPage extends StatefulWidget {
  const TrendingPage({Key? key}) : super(key: key);

  @override
  State<TrendingPage> createState() => _TrendingPageState();
}

class _TrendingPageState extends State<TrendingPage> {
  final ScrollController _scrollController = ScrollController();
  
  bool _isLoading = true;
  List<Map<String, dynamic>> _trendingContent = [];
  String _period = 'today';
  String _contentType = 'all';

  final List<Map<String, String>> _periods = [
    {'value': 'today', 'label': 'Today'},
    {'value': 'week', 'label': 'This Week'},
    {'value': 'month', 'label': 'This Month'},
    {'value': 'overall', 'label': 'All Time'},
  ];

  final List<Map<String, dynamic>> _contentTypes = [
    {'value': 'all', 'label': 'All', 'icon': Icons.trending_up},
    {'value': 'movies', 'label': 'Movies', 'icon': Icons.movie},
    {'value': 'tvshows', 'label': 'TV Shows', 'icon': Icons.tv},
  ];

  @override
  void initState() {
    super.initState();
    _fetchTrendingContent();
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _fetchTrendingContent() async {
    setState(() => _isLoading = true);

    try {
      List<dynamic> items = [];

      if (_contentType == 'movies') {
        final movies = await ApiService.getMoviesLeaderboard(
          period: _period,
          limit: 50,
        );
        items = movies.asMap().entries.map((entry) {
          return {
            'type': 'movie',
            'rank': entry.key + 1,
            'content': entry.value,
          };
        }).toList();
      } else if (_contentType == 'tvshows') {
        final tvShows = await ApiService.getTVShowsLeaderboard(
          period: _period,
          limit: 50,
        );
        items = tvShows.asMap().entries.map((entry) {
          return {
            'type': 'tvshow',
            'rank': entry.key + 1,
            'content': entry.value,
          };
        }).toList();
      } else {
        // Use unified trending API
        final result = await ApiService.getTrendingContent(
          period: _period,
          limit: 50,
        );
        
        final movies = result['movies'] as List<Movie>;
        final tvShows = result['tvShows'] as List<TVShow>;

        // Combine and interleave
        final movieItems = movies.asMap().entries.map((entry) {
          return {
            'type': 'movie',
            'rank': entry.key + 1,
            'content': entry.value,
          };
        }).toList();

        final tvShowItems = tvShows.asMap().entries.map((entry) {
          return {
            'type': 'tvshow',
            'rank': entry.key + 1,
            'content': entry.value,
          };
        }).toList();

        // Sort by rank and combine
        items = [...movieItems, ...tvShowItems]
          ..sort((a, b) => (a['rank'] as int).compareTo(b['rank'] as int));
      }

      setState(() {
        _trendingContent = items.cast<Map<String, dynamic>>();
        _isLoading = false;
      });
    } catch (e) {
      print('Error fetching trending content: $e');
      setState(() {
        _trendingContent = [];
        _isLoading = false;
      });
    }
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
                const SizedBox(height: 80),
                
                Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Header
                      Row(
                        children: [
                          Icon(Icons.trending_up, color: Colors.red[600], size: 32),
                          const SizedBox(width: 12),
                          const Text(
                            'Trending Now',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 32,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(height: 8),

                      Text(
                        'Discover what\'s hot right now based on what people are watching',
                        style: TextStyle(
                          color: Colors.grey[400],
                          fontSize: 14,
                        ),
                      ),

                      const SizedBox(height: 24),

                      // Content Type Filter
                      Wrap(
                        spacing: 12,
                        runSpacing: 12,
                        children: _contentTypes.map((type) {
                          final isActive = _contentType == type['value'];
                          return GestureDetector(
                            onTap: () {
                              setState(() => _contentType = type['value'] as String);
                              _fetchTrendingContent();
                            },
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                              decoration: BoxDecoration(
                                color: isActive ? Colors.red[600] : Colors.grey[800],
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    type['icon'] as IconData,
                                    color: Colors.white,
                                    size: 16,
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    type['label'] as String,
                                    style: TextStyle(
                                      color: isActive ? Colors.white : Colors.grey[300],
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          );
                        }).toList(),
                      ),

                      const SizedBox(height: 16),

                      // Period Filter
                      Row(
                        children: [
                          Text(
                            'Time Period:',
                            style: TextStyle(
                              color: Colors.grey[400],
                              fontSize: 13,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Wrap(
                              spacing: 12,
                              runSpacing: 12,
                              children: _periods.map((p) {
                                final isActive = _period == p['value'];
                                return GestureDetector(
                                  onTap: () {
                                    setState(() => _period = p['value']!);
                                    _fetchTrendingContent();
                                  },
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 16,
                                      vertical: 8,
                                    ),
                                    decoration: BoxDecoration(
                                      color: isActive ? Colors.red[600] : Colors.grey[800],
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Text(
                                      p['label']!,
                                      style: TextStyle(
                                        color: isActive ? Colors.white : Colors.grey[300],
                                        fontSize: 13,
                                      ),
                                    ),
                                  ),
                                );
                              }).toList(),
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(height: 24),

                      // Loading or Content
                      if (_isLoading)
                        const Center(
                          child: Padding(
                            padding: EdgeInsets.all(80.0),
                            child: CircularProgressIndicator(color: Colors.red),
                          ),
                        )
                      else if (_trendingContent.isEmpty)
                        Center(
                          child: Padding(
                            padding: const EdgeInsets.all(80.0),
                            child: Column(
                              children: [
                                const Icon(
                                  Icons.trending_up,
                                  size: 64,
                                  color: Colors.grey,
                                ),
                                const SizedBox(height: 24),
                                Text(
                                  'No trending content found for this period.',
                                  style: TextStyle(
                                    color: Colors.grey[400],
                                    fontSize: 18,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        )
                      else
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              '${_trendingContent.length} trending ${_contentType == 'all' ? 'items' : _contentType == 'movies' ? 'movies' : 'TV shows'}',
                              style: TextStyle(
                                color: Colors.grey[400],
                                fontSize: 14,
                              ),
                            ),
                            const SizedBox(height: 16),
                            GridView.builder(
                              shrinkWrap: true,
                              physics: const NeverScrollableScrollPhysics(),
                              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                                crossAxisCount: 2,
                                childAspectRatio: 0.75,
                                crossAxisSpacing: 16,
                                mainAxisSpacing: 20,
                              ),
                              itemCount: _trendingContent.length,
                              itemBuilder: (context, index) {
                                final item = _trendingContent[index];
                                final isMovie = item['type'] == 'movie';
                                final content = item['content'];
                                final rank = index + 1;

                                return isMovie
                                    ? _buildStyledMovieCard(content as Movie, rank)
                                    : _buildStyledTVShowCard(content as TVShow, rank);
                              },
                            ),
                          ],
                        ),
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

  Color _getRankBadgeColor(int rank) {
    if (rank == 1) {
      return const Color(0xFFFBBF24); // Yellow/Gold
    } else if (rank == 2) {
      return const Color(0xFFD1D5DB); // Silver/Gray
    } else if (rank == 3) {
      return const Color(0xFFFB923C); // Bronze/Orange
    } else if (rank <= 10) {
      return const Color(0xFFFF4444); // Red
    } else {
      return const Color(0xFF6366F1); // Indigo
    }
  }

  Widget _buildStyledMovieCard(Movie movie, int rank) {
    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: _getRankBadgeColor(rank).withOpacity(0.4),
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

                // Rank Badge (Top Left)
                Positioned(
                  top: 12,
                  left: 12,
                  child: Container(
                    width: 56,
                    height: 56,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                        colors: [
                          _getRankBadgeColor(rank),
                          _getRankBadgeColor(rank).withOpacity(0.8),
                        ],
                      ),
                      shape: BoxShape.circle,
                      border: Border.all(
                        color: Colors.black,
                        width: 3,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: _getRankBadgeColor(rank).withOpacity(0.6),
                          blurRadius: 12,
                          spreadRadius: 2,
                        ),
                        BoxShadow(
                          color: Colors.black.withOpacity(0.5),
                          blurRadius: 8,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Center(
                      child: Text(
                        '#$rank',
                        style: TextStyle(
                          color: rank <= 3 ? Colors.black : Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 16,
                          letterSpacing: 0.5,
                        ),
                      ),
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

  Widget _buildStyledTVShowCard(TVShow tvShow, int rank) {
    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: _getRankBadgeColor(rank).withOpacity(0.4),
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
                '/tvshow/${tvShow.id}',
              );
            },
            borderRadius: BorderRadius.circular(16),
            splashColor: Colors.blue.withOpacity(0.3),
            highlightColor: Colors.blue.withOpacity(0.1),
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
                                        color: Colors.blue,
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
                        // TV Show Name
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

                // Rank Badge (Top Left)
                Positioned(
                  top: 12,
                  left: 12,
                  child: Container(
                    width: 56,
                    height: 56,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                        colors: [
                          _getRankBadgeColor(rank),
                          _getRankBadgeColor(rank).withOpacity(0.8),
                        ],
                      ),
                      shape: BoxShape.circle,
                      border: Border.all(
                        color: Colors.black,
                        width: 3,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: _getRankBadgeColor(rank).withOpacity(0.6),
                          blurRadius: 12,
                          spreadRadius: 2,
                        ),
                        BoxShadow(
                          color: Colors.black.withOpacity(0.5),
                          blurRadius: 8,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Center(
                      child: Text(
                        '#$rank',
                        style: TextStyle(
                          color: rank <= 3 ? Colors.black : Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 16,
                          letterSpacing: 0.5,
                        ),
                      ),
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
                          Colors.blue,
                          Colors.blue.withOpacity(0.7),
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

