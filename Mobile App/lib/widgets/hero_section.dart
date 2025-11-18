import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'dart:async';
import '../models/movie.dart';
import '../models/tvshow.dart';

class HeroSection extends StatefulWidget {
  final List<dynamic> featuredItems;

  const HeroSection({
    Key? key,
    required this.featuredItems,
  }) : super(key: key);

  @override
  State<HeroSection> createState() => _HeroSectionState();
}

class _HeroSectionState extends State<HeroSection> {
  int _currentIndex = 0;
  late PageController _pageController;
  Timer? _autoPlayTimer;

  @override
  void initState() {
    super.initState();
    // Start at a random page for variety each time
    final randomIndex = widget.featuredItems.isNotEmpty
        ? (DateTime.now().millisecondsSinceEpoch % widget.featuredItems.length)
        : 0;
    _currentIndex = randomIndex;
    _pageController = PageController(initialPage: randomIndex);
    _startAutoPlay();
  }

  @override
  void dispose() {
    _autoPlayTimer?.cancel();
    _pageController.dispose();
    super.dispose();
  }

  void _startAutoPlay() {
    _autoPlayTimer = Timer.periodic(const Duration(seconds: 5), (timer) {
      if (_pageController.hasClients) {
        int nextPage = (_currentIndex + 1) % widget.featuredItems.length;
        _pageController.animateToPage(
          nextPage,
          duration: const Duration(milliseconds: 800),
          curve: Curves.fastOutSlowIn,
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    if (widget.featuredItems.isEmpty) {
      return const SizedBox.shrink();
    }

    return Stack(
      children: [
        // Carousel using PageView
        SizedBox(
          height: MediaQuery.of(context).size.height * 0.6,
          child: PageView.builder(
            controller: _pageController,
            itemCount: widget.featuredItems.length,
            onPageChanged: (index) {
              setState(() {
                _currentIndex = index;
              });
            },
            itemBuilder: (context, index) {
              final item = widget.featuredItems[index];
              final isMovie = item is Movie;
              final title = isMovie ? item.title : (item as TVShow).name;
              final backdropUrl = isMovie 
                  ? item.getBackdropUrl('w1280')
                  : (item as TVShow).getBackdropUrl('w1280');
              final voteAverage = isMovie 
                  ? item.voteAverage
                  : (item as TVShow).voteAverage;
              final itemId = isMovie 
                  ? item.id
                  : (item as TVShow).id;

              return Stack(
              fit: StackFit.expand,
              children: [
                // Backdrop Image
                CachedNetworkImage(
                  imageUrl: backdropUrl,
                  fit: BoxFit.cover,
                  placeholder: (context, url) => Container(
                    color: Colors.grey[900],
                  ),
                  errorWidget: (context, url, error) => Container(
                    color: Colors.grey[900],
                    child: const Icon(Icons.image, color: Colors.grey, size: 50),
                  ),
                ),
                // Gradient Overlay
                Container(
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                      colors: [
                        Colors.black.withOpacity(0.3),
                        Colors.black.withOpacity(0.7),
                        Colors.black,
                      ],
                      stops: const [0.0, 0.7, 1.0],
                    ),
                  ),
                ),
                // Content
                Positioned(
                  bottom: 60,
                  left: 20,
                  right: 20,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Title
                      Text(
                        title,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 28,
                          fontWeight: FontWeight.bold,
                          shadows: [
                            Shadow(
                              offset: Offset(0, 2),
                              blurRadius: 4,
                              color: Colors.black,
                            ),
                          ],
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 8),
                      // Rating
                      if (voteAverage != null)
                        Row(
                          children: [
                            const Icon(
                              Icons.star,
                              color: Colors.amber,
                              size: 20,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              voteAverage.toStringAsFixed(1),
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      const SizedBox(height: 20),
                      // Action Button
                      ElevatedButton.icon(
                        onPressed: () {
                          // Navigate to detail page
                          if (isMovie) {
                            Navigator.pushNamed(context, '/movie/$itemId');
                          } else {
                            Navigator.pushNamed(context, '/tvshow/$itemId?name=${Uri.encodeComponent(title)}');
                          }
                        },
                        icon: const Icon(Icons.play_arrow, size: 24),
                        label: const Text('Watch Now'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.red,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(
                            horizontal: 24,
                            vertical: 12,
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
              );
            },
          ),
        ),
      ],
    );
  }
}

