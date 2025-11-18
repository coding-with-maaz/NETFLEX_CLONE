import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../services/watched_content_service.dart';
import '../models/movie.dart';
import '../models/tvshow.dart';
import 'movie_card.dart';
import 'tvshow_card.dart';

class WatchedContentSection extends StatefulWidget {
  const WatchedContentSection({Key? key}) : super(key: key);

  @override
  State<WatchedContentSection> createState() => _WatchedContentSectionState();
}

class _WatchedContentSectionState extends State<WatchedContentSection> {
  List<dynamic> watchedItems = [];
  bool isLoading = true;
  bool hasWatched = false;

  @override
  void initState() {
    super.initState();
    _loadWatchedContent();
  }

  Future<void> _loadWatchedContent() async {
    try {
      setState(() {
        isLoading = true;
      });

      // Get watched items
      final watched = await WatchedContentService.getWatchedItems();
      
      if (watched.isEmpty) {
        setState(() {
          isLoading = false;
          hasWatched = false;
        });
        return;
      }

      hasWatched = true;

      // Sort by most recently watched
      watched.sort((a, b) => b.watchedAt.compareTo(a.watchedAt));

      // Fetch details for watched items
      final allContent = <dynamic>[];

      for (final item in watched.take(20)) {
        try {
          if (item.type == 'movie') {
            final movie = await ApiService.getMovieById(item.id);
            if (movie != null) {
              allContent.add(movie);
            }
          } else if (item.type == 'tvshow') {
            final tvShow = await ApiService.getTVShowById(item.id);
            if (tvShow != null) {
              allContent.add(tvShow);
            }
          }
        } catch (e) {
          print('[WatchedContent] Error fetching ${item.type} ${item.id}: $e');
        }
      }

      setState(() {
        watchedItems = allContent;
        isLoading = false;
      });
    } catch (e) {
      print('[WatchedContent] Error loading watched content: $e');
      setState(() {
        isLoading = false;
        hasWatched = false;
      });
    }
  }

  Future<void> _clearWatchedContent() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: Colors.grey[900],
        title: const Text(
          'Clear Watched History',
          style: TextStyle(color: Colors.white),
        ),
        content: const Text(
          'Are you sure you want to clear all watched history? This action cannot be undone.',
          style: TextStyle(color: Colors.grey),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
            ),
            child: const Text('Clear'),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      await WatchedContentService.clearWatchedContent();
      setState(() {
        watchedItems = [];
        hasWatched = false;
      });
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Watched history cleared'),
            backgroundColor: Colors.green,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return const SizedBox(
        height: 220,
        child: Center(
          child: CircularProgressIndicator(color: Colors.red),
        ),
      );
    }

    if (!hasWatched || watchedItems.isEmpty) {
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
                'You Have Watched',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                ),
              ),
              Row(
                children: [
                  IconButton(
                    icon: const Icon(Icons.refresh, color: Colors.red),
                    onPressed: _loadWatchedContent,
                    tooltip: 'Refresh watched content',
                  ),
                  IconButton(
                    icon: const Icon(Icons.delete_outline, color: Colors.red),
                    onPressed: _clearWatchedContent,
                    tooltip: 'Clear watched history',
                  ),
                ],
              ),
            ],
          ),
        ),
        SizedBox(
          height: 220,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16),
            itemCount: watchedItems.length,
            itemBuilder: (context, index) {
              final item = watchedItems[index];
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

