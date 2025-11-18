import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../services/watched_content_service.dart';
import '../models/movie.dart';
import '../models/tvshow.dart';
import 'movie_card.dart';
import 'tvshow_card.dart';

class RecommendedContentSection extends StatefulWidget {
  const RecommendedContentSection({Key? key}) : super(key: key);

  @override
  State<RecommendedContentSection> createState() => _RecommendedContentSectionState();
}

class _RecommendedContentSectionState extends State<RecommendedContentSection> {
  List<dynamic> recommendedItems = [];
  bool isLoadingRecommended = true;
  bool hasRecommended = false;

  @override
  void initState() {
    super.initState();
    _loadRecommendedContent();
  }

  Future<void> _loadRecommendedContent() async {
    try {
      setState(() {
        isLoadingRecommended = true;
      });

      // Get watched genre IDs
      final watchedGenreIds = await WatchedContentService.getWatchedGenreIds();
      final watchedMovieIds = await WatchedContentService.getWatchedMovieIds();
      final watchedTVShowIds = await WatchedContentService.getWatchedTVShowIds();

      if (watchedGenreIds.isEmpty) {
        setState(() {
          isLoadingRecommended = false;
          hasRecommended = false;
        });
        return;
      }

      hasRecommended = true;

      // Fetch content based on watched genres
      final allContent = <dynamic>[];

      // Fetch movies by genre
      for (final genreId in watchedGenreIds.take(3)) {
        try {
          final moviesData = await ApiService.getMovies(
            params: {
              'genre': genreId.toString(),
              'limit': '10',
            },
          );
          final movies = (moviesData['movies'] as List<Movie>?) ?? [];
          // Exclude already watched movies
          allContent.addAll(
            movies.where((m) => !watchedMovieIds.contains(m.id)),
          );
        } catch (e) {
          print('[Recommended] Error fetching movies for genre $genreId: $e');
        }
      }

      // Fetch TV shows by genre
      for (final genreId in watchedGenreIds.take(3)) {
        try {
          final tvShowsData = await ApiService.getTVShowsWithParams(
            params: {
              'genre': genreId.toString(),
              'limit': '10',
            },
          );
          final tvShows = (tvShowsData['tvShows'] as List<TVShow>?) ?? [];
          // Exclude already watched TV shows
          allContent.addAll(
            tvShows.where((t) => !watchedTVShowIds.contains(t.id)),
          );
        } catch (e) {
          print('[Recommended] Error fetching TV shows for genre $genreId: $e');
        }
      }

      // Remove duplicates
      final seen = <String>{};
      final uniqueContent = <dynamic>[];
      for (final item in allContent) {
        final key = item is Movie ? 'movie_${item.id}' : 'tvshow_${(item as TVShow).id}';
        if (!seen.contains(key)) {
          seen.add(key);
          uniqueContent.add(item);
        }
      }

      // Shuffle and take top 20
      uniqueContent.shuffle();
      final recommended = uniqueContent.take(20).toList();

      setState(() {
        recommendedItems = recommended;
        isLoadingRecommended = false;
      });
    } catch (e) {
      print('[Recommended] Error loading recommended content: $e');
      setState(() {
        isLoadingRecommended = false;
        hasRecommended = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (isLoadingRecommended) {
      return const SizedBox(
        height: 220,
        child: Center(
          child: CircularProgressIndicator(color: Colors.red),
        ),
      );
    }

    if (!hasRecommended || recommendedItems.isEmpty) {
      return const SizedBox.shrink();
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Recommended For You',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                ),
              ),
              IconButton(
                icon: const Icon(Icons.refresh, color: Colors.red),
                onPressed: _loadRecommendedContent,
                tooltip: 'Refresh recommendations',
              ),
            ],
          ),
        ),
        SizedBox(
          height: 220,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16),
            itemCount: recommendedItems.length,
            itemBuilder: (context, index) {
              final item = recommendedItems[index];
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
        ),
        const SizedBox(height: 24),
      ],
    );
  }
}

