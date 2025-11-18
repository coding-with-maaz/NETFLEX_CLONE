import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../models/tvshow.dart';

class TVShowCard extends StatelessWidget {
  final TVShow tvShow;
  final VoidCallback? onTap;

  const TVShowCard({
    Key? key,
    required this.tvShow,
    this.onTap,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap ?? () {
        Navigator.pushNamed(
          context, 
          '/tvshow/${tvShow.id}?name=${Uri.encodeComponent(tvShow.name)}',
        );
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
                      child: CachedNetworkImage(
                        imageUrl: tvShow.getImageUrl('w500'),
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
                          child: const Icon(Icons.tv, color: Colors.grey),
                        ),
                      ),
                    ),
                  ),
                  // View Count Badge
                  if (tvShow.viewCount != null && tvShow.viewCount! > 0)
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
                              color: Colors.purple,
                              size: 10,
                            ),
                            const SizedBox(width: 3),
                            Text(
                              _formatViewCount(tvShow.viewCount!),
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
                tvShow.name,
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

