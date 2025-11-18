import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/movie.dart';
import '../models/tvshow.dart';
import 'movie_card.dart';
import 'tvshow_card.dart';

class LazyContentRow extends StatefulWidget {
  final String title;
  final String endpoint;
  final String type; // 'movies' or 'tvshows'
  final String? viewMoreLink;

  const LazyContentRow({
    Key? key,
    required this.title,
    required this.endpoint,
    required this.type,
    this.viewMoreLink,
  }) : super(key: key);

  @override
  State<LazyContentRow> createState() => _LazyContentRowState();
}

class _LazyContentRowState extends State<LazyContentRow> {
  List<dynamic> items = [];
  bool isLoading = false;
  bool hasError = false;
  bool hasLoaded = false;

  @override
  void initState() {
    super.initState();
    // Load data after a short delay to simulate lazy loading
    Future.delayed(const Duration(milliseconds: 100), () {
      if (mounted && !hasLoaded) {
        _fetchData();
      }
    });
  }

  Future<void> _fetchData() async {
    if (hasLoaded) return;

    print('LazyContentRow: Fetching ${widget.title} from ${widget.endpoint}');

    try {
      setState(() {
        isLoading = true;
        hasError = false;
      });

      if (widget.type == 'movies') {
        final movies = await ApiService.getMoviesByEndpoint(widget.endpoint);
        print('LazyContentRow: ${widget.title} loaded ${movies.length} movies');
        setState(() {
          items = movies;
          isLoading = false;
          hasLoaded = true;
        });
      } else if (widget.type == 'tvshows') {
        final tvShows = await ApiService.getTVShows(widget.endpoint);
        print('LazyContentRow: ${widget.title} loaded ${tvShows.length} TV shows');
        setState(() {
          items = tvShows;
          isLoading = false;
          hasLoaded = true;
        });
      }
    } catch (e) {
      setState(() {
        hasError = true;
        isLoading = false;
      });
      print('LazyContentRow: Error fetching ${widget.title}: $e');
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
              child: !hasLoaded && !isLoading
                  ? _buildPlaceholder()
                  : isLoading
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

  Widget _buildPlaceholder() {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      child: Center(
        child: Text(
          'Loading...',
          style: TextStyle(color: Colors.grey[700], fontSize: 14),
        ),
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

