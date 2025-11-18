import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/movie.dart';
import '../models/tvshow.dart';
import 'movie_card.dart';
import 'tvshow_card.dart';

class ContentRow extends StatefulWidget {
  final String title;
  final String endpoint;
  final String type; // 'movies', 'tvshows', or 'leaderboard'
  final String? contentType; // For leaderboard: 'movies' or 'tvShows'
  final String? viewMoreLink;

  const ContentRow({
    Key? key,
    required this.title,
    required this.endpoint,
    required this.type,
    this.contentType,
    this.viewMoreLink,
  }) : super(key: key);

  @override
  State<ContentRow> createState() => _ContentRowState();
}

class _ContentRowState extends State<ContentRow> {
  List<dynamic> items = [];
  bool isLoading = true;
  bool hasError = false;

  @override
  void initState() {
    super.initState();
    _fetchData();
  }

  Future<void> _fetchData() async {
    try {
      setState(() {
        isLoading = true;
        hasError = false;
      });

      if (widget.type == 'leaderboard' && widget.contentType == 'all') {
        // For mixed content (trending with all), use getTrendingContent
        // Parse period and limit from endpoint (e.g., /leaderboard/trending?period=week&limit=16)
        String period = 'week';
        int limit = 16;
        
        // Handle relative paths by parsing query string manually
        try {
          if (widget.endpoint.contains('?')) {
            final parts = widget.endpoint.split('?');
            if (parts.length > 1) {
              final queryString = parts[1];
              final params = Uri.splitQueryString(queryString);
              if (params.containsKey('period')) {
                period = params['period'] ?? 'week';
              }
              if (params.containsKey('limit')) {
                limit = int.tryParse(params['limit'] ?? '16') ?? 16;
              }
            }
          }
        } catch (e) {
          print('ContentRow: Error parsing endpoint parameters: $e');
        }
        
        print('ContentRow: Fetching trending content - period: $period, limit: $limit');
        
        try {
          final trendingData = await ApiService.getTrendingContent(
            period: period,
            limit: limit,
          );
          
          print('ContentRow: Trending data received - movies: ${(trendingData['movies'] as List?)?.length ?? 0}, tvShows: ${(trendingData['tvShows'] as List?)?.length ?? 0}');
          
          final moviesList = trendingData['movies'] as List<Movie>? ?? [];
          final tvShowsList = trendingData['tvShows'] as List<TVShow>? ?? [];
          
          final combined = <dynamic>[
            ...moviesList,
            ...tvShowsList,
          ];
          
          // Remove duplicates based on ID and type
          final seen = <String>{};
          final uniqueContent = <dynamic>[];
          
          for (final item in combined) {
            final key = item is Movie 
                ? 'movie_${item.id}' 
                : 'tvshow_${(item as TVShow).id}';
            
            if (!seen.contains(key)) {
              seen.add(key);
              uniqueContent.add(item);
            }
          }
          
          // Randomize the content for variety each time
          uniqueContent.shuffle();
          
          // Sort by rating and popularity for quality, then shuffle again for randomness
          uniqueContent.sort((a, b) {
            final aVote = a is Movie ? a.voteAverage : (a as TVShow).voteAverage;
            final bVote = b is Movie ? b.voteAverage : (b as TVShow).voteAverage;
            final aPopularity = a is Movie ? a.popularity : (a as TVShow).popularity;
            final bPopularity = b is Movie ? b.popularity : (b as TVShow).popularity;
            
            // First sort by rating (higher is better)
            if (aVote != null && bVote != null) {
              final ratingDiff = bVote.compareTo(aVote);
              if (ratingDiff != 0) return ratingDiff;
            }
            
            // Then by popularity (higher is better)
            if (aPopularity != null && bPopularity != null) {
              final popularityDiff = bPopularity.compareTo(aPopularity);
              if (popularityDiff != 0) return popularityDiff;
            }
            
            return 0;
          });
          
          // Final shuffle to add randomness while keeping quality
          uniqueContent.shuffle();
          
          print('ContentRow: Combined items: ${uniqueContent.length} (after deduplication and randomization)');
          
          setState(() {
            items = uniqueContent;
            isLoading = false;
            hasError = false; // Empty list is not an error
          });
        } catch (e, stackTrace) {
          print('ContentRow: Error fetching trending content: $e');
          print('ContentRow: Stack trace: $stackTrace');
          setState(() {
            hasError = true;
            isLoading = false;
            items = [];
          });
        }
      } else if (widget.type == 'movies' || (widget.type == 'leaderboard' && widget.contentType == 'movies')) {
        print('[ContentRow] Fetching movies for endpoint: ${widget.endpoint}');
        
        // Check if this is a top-rated movies request and use the same method as top_rated_movies_page
        if (widget.endpoint.contains('sort_by=rating') && widget.endpoint.contains('min_rating')) {
          // Parse query parameters
          final uri = Uri.parse(widget.endpoint);
          final params = uri.queryParameters;
          
          final result = await ApiService.getMovies(params: {
            'sort_by': params['sort_by'] ?? 'rating',
            'min_rating': params['min_rating'] ?? '7.0',
            'min_votes': params['min_votes'] ?? '100',
            'limit': params['limit'] ?? '8',
            'order': params['order'] ?? 'desc',
          });
          
          final movies = result['movies'] ?? [];
          print('[ContentRow] Received ${movies.length} movies using getMovies method');
          
          setState(() {
            items = movies;
            isLoading = false;
            hasError = false;
          });
        } else {
          // Use endpoint method for other cases
          final movies = await ApiService.getMoviesByEndpoint(widget.endpoint);
          print('[ContentRow] Received ${movies.length} movies using getMoviesByEndpoint');
          setState(() {
            items = movies;
            isLoading = false;
            hasError = false;
          });
        }
      } else if (widget.type == 'tvshows' || (widget.type == 'leaderboard' && widget.contentType == 'tvShows')) {
        final tvShows = await ApiService.getTVShows(widget.endpoint);
        setState(() {
          items = tvShows;
          isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        hasError = true;
        isLoading = false;
      });
      print('Error fetching content: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  widget.title,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                if (widget.viewMoreLink != null)
                  TextButton(
                    onPressed: () {
                      Navigator.pushNamed(context, widget.viewMoreLink!);
                    },
                    child: Row(
                      children: [
                        const Text(
                          'View More',
                          style: TextStyle(
                            color: Colors.red,
                            fontSize: 14,
                          ),
                        ),
                        const SizedBox(width: 4),
                        const Icon(
                          Icons.arrow_forward_ios,
                          color: Colors.red,
                          size: 14,
                        ),
                      ],
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          // Content
          SizedBox(
            height: 220,
            child: isLoading
                ? _buildLoadingState()
                : hasError
                    ? _buildErrorState()
                    : items.isEmpty
                        ? _buildEmptyState()
                        : _buildContentList(),
          ),
        ],
      ),
    );
  }

  Widget _buildLoadingState() {
    return ListView.builder(
      scrollDirection: Axis.horizontal,
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: 5,
      itemBuilder: (context, index) {
        return Container(
          width: 130,
          margin: const EdgeInsets.only(right: 12),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                height: 195,
                decoration: BoxDecoration(
                  color: Colors.grey[900],
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              const SizedBox(height: 8),
              Container(
                height: 12,
                width: 100,
                color: Colors.grey[900],
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.error_outline, color: Colors.grey[700], size: 48),
          const SizedBox(height: 8),
          Text(
            'Failed to load content',
            style: TextStyle(color: Colors.grey[700], fontSize: 14),
          ),
          const SizedBox(height: 8),
          TextButton(
            onPressed: _fetchData,
            child: const Text('Retry'),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Text(
        'No content available',
        style: TextStyle(color: Colors.grey[700], fontSize: 14),
      ),
    );
  }

  Widget _buildContentList() {
    return ListView.builder(
      scrollDirection: Axis.horizontal,
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: items.length,
      itemBuilder: (context, index) {
        final item = items[index];
        if (item is Movie) {
          return MovieCard(movie: item);
        } else if (item is TVShow) {
          return TVShowCard(tvShow: item);
        }
        return const SizedBox.shrink();
      },
    );
  }
}

