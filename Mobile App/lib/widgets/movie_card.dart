import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../models/movie.dart';

class MovieCard extends StatelessWidget {
  final Movie movie;
  final VoidCallback? onTap;

  const MovieCard({
    Key? key,
    required this.movie,
    this.onTap,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap ?? () {
        Navigator.pushNamed(context, '/movie/${movie.id}');
      },
      child: Container(
        width: 130,
        margin: const EdgeInsets.only(right: 12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Poster Image
            Expanded(
              flex: 10,
              child: Stack(
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: AspectRatio(
                      aspectRatio: 2 / 3,
                      child: Builder(
                        builder: (context) {
                          final imageUrl = movie.getImageUrl('w500');
                          final isValidUrl = imageUrl.isNotEmpty && 
                              (imageUrl.startsWith('http://') || imageUrl.startsWith('https://')) &&
                              !imageUrl.contains('placeholder');
                          
                          if (isValidUrl) {
                            return CachedNetworkImage(
                              imageUrl: imageUrl,
                              fit: BoxFit.cover,
                              placeholder: (context, url) => Container(
                                color: Colors.grey[900],
                                child: const Center(
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    color: Colors.red,
                                  ),
                                ),
                              ),
                              errorWidget: (context, url, error) => Container(
                                color: Colors.grey[900],
                                child: const Icon(Icons.movie, color: Colors.grey),
                              ),
                            );
                          } else {
                            return Container(
                              color: Colors.grey[900],
                              child: const Icon(Icons.movie, color: Colors.grey),
                            );
                          }
                        },
                      ),
                    ),
                  ),
                  // View Count Badge
                  if (movie.viewCount != null && movie.viewCount! > 0)
                    Positioned(
                      bottom: 8,
                      right: 8,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
                        decoration: BoxDecoration(
                          color: Colors.black.withOpacity(0.8),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(
                              Icons.visibility,
                              color: Colors.red,
                              size: 10,
                            ),
                            const SizedBox(width: 3),
                            Text(
                              _formatViewCount(movie.viewCount!),
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 9,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                ],
              ),
            ),
            const SizedBox(height: 6),
            // Title - Flexible to fit available space
            Expanded(
              child: Text(
                movie.title,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                  height: 1.2,
                ),
              ),
            ),
          ],
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

