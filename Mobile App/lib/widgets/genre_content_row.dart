import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/movie.dart';
import '../models/tvshow.dart';
import 'movie_card.dart';
import 'tvshow_card.dart';

class GenreContentRow extends StatefulWidget {
  final String genreId;
  final String genreName;
  final String genreSlug;
  final String? viewMoreLink;
  final bool lazyLoad;

  const GenreContentRow({
    Key? key,
    required this.genreId,
    required this.genreName,
    required this.genreSlug,
    this.viewMoreLink,
    this.lazyLoad = false,
  }) : super(key: key);

  @override
  State<GenreContentRow> createState() => _GenreContentRowState();
}

class _GenreContentRowState extends State<GenreContentRow> {
  List<dynamic> items = [];
  bool isLoading = false;
  bool hasError = false;
  bool hasLoaded = false;

  @override
  void initState() {
    super.initState();
    if (widget.lazyLoad) {
      // For lazy loading, wait a bit longer to start loading
      // This simulates intersection observer behavior
      Future.delayed(const Duration(milliseconds: 800), () {
        if (mounted && !hasLoaded) {
          _fetchData();
        }
      });
    } else {
      // Load immediately (with small delay to stagger requests)
      Future.delayed(const Duration(milliseconds: 100), () {
        if (mounted && !hasLoaded) {
          _fetchData();
        }
      });
    }
  }

  Future<void> _fetchData() async {
    if (hasLoaded) return;

    print('GenreContentRow: Fetching ${widget.genreName} (${widget.genreSlug})');

    try {
      setState(() {
        isLoading = true;
        hasError = false;
      });

      // Fetch both movies and TV shows for this genre
      final moviesEndpoint = '/movies?genre=${Uri.encodeComponent(widget.genreSlug)}&limit=8&sort_by=popularity&order=desc';
      final tvShowsEndpoint = '/tvshows?genre=${Uri.encodeComponent(widget.genreSlug)}&limit=8&sort_by=popularity&order=desc';

      print('GenreContentRow: Fetching movies from: $moviesEndpoint');
      print('GenreContentRow: Fetching TV shows from: $tvShowsEndpoint');

      // Fetch both in parallel
      final results = await Future.wait([
        ApiService.getMoviesByEndpoint(moviesEndpoint).catchError((e) {
          print('GenreContentRow: Error fetching movies: $e');
          return <Movie>[];
        }),
        ApiService.getTVShows(tvShowsEndpoint).catchError((e) {
          print('GenreContentRow: Error fetching TV shows: $e');
          return <TVShow>[];
        }),
      ]);

      final movies = results[0] as List<Movie>;
      final tvShows = results[1] as List<TVShow>;

      print('GenreContentRow: ${widget.genreName} - Found ${movies.length} movies and ${tvShows.length} TV shows');

      // Combine and sort by popularity (similar to Blade template logic)
      final combined = <dynamic>[
        ...movies.map((m) => {'item': m, 'popularity': (m.popularity ?? 0) * 0.7 + (m.viewCount ?? 0) * 0.3}),
        ...tvShows.map((tv) => {'item': tv, 'popularity': (tv.popularity ?? 0) * 0.7 + (tv.viewCount ?? 0) * 0.3}),
      ];

      combined.sort((a, b) => (b['popularity'] as num).compareTo(a['popularity'] as num));

      final sortedItems = combined.map((e) => e['item'] as dynamic).toList();

      // Limit to 16 items (matching Blade template)
      final limitedItems = sortedItems.take(16).toList();

      print('GenreContentRow: ${widget.genreName} - Showing ${limitedItems.length} items (combined)');

      setState(() {
        items = limitedItems;
        isLoading = false;
        hasLoaded = true;
      });
    } catch (e) {
      setState(() {
        hasError = true;
        isLoading = false;
      });
      print('GenreContentRow: Error fetching ${widget.genreName}: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    // For lazy loaded items that haven't started loading yet, show nothing or minimal placeholder
    if (widget.lazyLoad && !hasLoaded && !isLoading) {
      // Return minimal placeholder - will start loading after delay
      return const SizedBox.shrink();
    }

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
                  widget.genreName,
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
            onPressed: () {
              setState(() {
                hasLoaded = false;
              });
              _fetchData();
            },
            child: const Text('Retry'),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Text(
        'No content available in this genre yet',
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

