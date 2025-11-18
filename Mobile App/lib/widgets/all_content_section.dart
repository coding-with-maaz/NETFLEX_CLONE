import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/movie.dart';
import '../models/tvshow.dart';
import 'movie_card.dart';
import 'tvshow_card.dart';

class AllContentSection extends StatefulWidget {
  const AllContentSection({Key? key}) : super(key: key);

  @override
  State<AllContentSection> createState() => _AllContentSectionState();
}

class _AllContentSectionState extends State<AllContentSection> {
  List<dynamic> allItems = [];
  bool isLoading = false;
  bool hasMore = true;
  bool hasError = false;
  int currentPage = 1;
  final int perPage = 20;

  @override
  void initState() {
    super.initState();
    // Load initial content
    _loadMoreContent();
  }

  Future<void> _loadMoreContent() async {
    if (isLoading) return;

    setState(() {
      isLoading = true;
    });

    try {
      // Fetch movies and TV shows in parallel
      final results = await Future.wait([
        ApiService.getMovies(
          params: {
            'page': currentPage.toString(),
            'per_page': perPage.toString(),
          },
        ),
        ApiService.getTVShowsWithParams(
          params: {
            'page': currentPage.toString(),
            'per_page': perPage.toString(),
          },
        ),
      ]);

      final moviesData = results[0];
      final tvShowsData = results[1];

      final movies = (moviesData['movies'] as List<Movie>?) ?? [];
      final tvShows = (tvShowsData['tvShows'] as List<TVShow>?) ?? [];

      print('[AllContent] Loaded ${movies.length} movies and ${tvShows.length} TV shows on page $currentPage');

      // Get pagination info
      final moviesPagination = moviesData['pagination'] as Map<String, dynamic>? ?? {};
      final tvShowsPagination = tvShowsData['pagination'] as Map<String, dynamic>? ?? {};

      print('[AllContent] Movies pagination: $moviesPagination');
      print('[AllContent] TV Shows pagination: $tvShowsPagination');

      // Combine movies and TV shows
      final newItems = <dynamic>[...movies, ...tvShows];

      // Shuffle to mix movies and TV shows
      newItems.shuffle();

      // Check if we have more pages
      final moviesLastPage = moviesPagination['last_page'] as int? ?? 1;
      final tvShowsLastPage = tvShowsPagination['last_page'] as int? ?? 1;
      final maxLastPage = moviesLastPage > tvShowsLastPage ? moviesLastPage : tvShowsLastPage;

      print('[AllContent] Total items after load: ${allItems.length + newItems.length}, hasMore: ${currentPage < maxLastPage}');

      setState(() {
        allItems.addAll(newItems);
        currentPage++;
        hasMore = currentPage <= maxLastPage;
        isLoading = false;
      });
    } catch (e, stackTrace) {
      print('[AllContent] Error loading content: $e');
      print('[AllContent] Stack trace: $stackTrace');
      setState(() {
        isLoading = false;
        hasMore = false;
        hasError = true;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Padding(
          padding: EdgeInsets.symmetric(horizontal: 16, vertical: 16),
          child: Text(
            'All Movies & TV Shows',
            style: TextStyle(
              color: Colors.white,
              fontSize: 22,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
        if (allItems.isEmpty && isLoading)
          const Center(
            child: Padding(
              padding: EdgeInsets.all(32.0),
              child: CircularProgressIndicator(color: Colors.red),
            ),
          )
        else if (allItems.isEmpty && hasError)
          Center(
            child: Padding(
              padding: const EdgeInsets.all(32.0),
              child: Column(
                children: [
                  const Icon(Icons.error_outline, color: Colors.red, size: 48),
                  const SizedBox(height: 16),
                  const Text(
                    'Failed to load content',
                    style: TextStyle(color: Colors.grey),
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () {
                      setState(() {
                        hasError = false;
                        currentPage = 1;
                        allItems = [];
                        hasMore = true;
                      });
                      _loadMoreContent();
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.red,
                    ),
                    child: const Text('Retry'),
                  ),
                ],
              ),
            ),
          )
        else if (allItems.isEmpty)
          const Center(
            child: Padding(
              padding: EdgeInsets.all(32.0),
              child: Text(
                'No content available',
                style: TextStyle(color: Colors.grey),
              ),
            ),
          )
        else
          GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              padding: const EdgeInsets.symmetric(horizontal: 16),
              gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: MediaQuery.of(context).size.width >= 768 ? 4 : 2,
                childAspectRatio: 0.65,
                crossAxisSpacing: 12,
                mainAxisSpacing: 12,
              ),
              itemCount: allItems.length + (isLoading ? 1 : 0),
              itemBuilder: (context, index) {
                // Trigger load more when near the end
                if (index >= allItems.length - 5 && !isLoading && hasMore) {
                  WidgetsBinding.instance.addPostFrameCallback((_) {
                    _loadMoreContent();
                  });
                }

                if (index >= allItems.length) {
                  return const Center(
                    child: Padding(
                      padding: EdgeInsets.all(16.0),
                      child: CircularProgressIndicator(color: Colors.red),
                    ),
                  );
                }

                final item = allItems[index];
                if (item is Movie) {
                  return MovieCard(
                    movie: item,
                    onTap: () {
                      Navigator.pushNamed(context, '/movie/${item.id}');
                    },
                  );
                } else if (item is TVShow) {
                  return TVShowCard(
                    tvShow: item,
                    onTap: () {
                      Navigator.pushNamed(
                        context,
                        '/tvshow/${item.id}?name=${Uri.encodeComponent(item.name)}',
                      );
                    },
                  );
                }
                return const SizedBox.shrink();
              },
            ),
        if (isLoading && allItems.isNotEmpty)
          const Center(
            child: Padding(
              padding: EdgeInsets.all(16.0),
              child: CircularProgressIndicator(color: Colors.red),
            ),
          ),
        const SizedBox(height: 100), // Extra padding for bottom ad
      ],
    );
  }
}

