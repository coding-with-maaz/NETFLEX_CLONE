import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../models/episode.dart';

class EpisodeCard extends StatelessWidget {
  final Episode episode;
  final VoidCallback? onTap;

  const EpisodeCard({
    Key? key,
    required this.episode,
    this.onTap,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap ?? () {
        // Default navigation to TV show detail page
        if (episode.tvShow != null && episode.tvShow!.id > 0) {
          // Use tvShow.id when available and valid
          Navigator.pushNamed(
            context,
            '/tvshow/${episode.tvShow!.id}?name=${Uri.encodeComponent(episode.tvShow!.name)}',
          );
        } else if (episode.tvShowId != null && episode.tvShowId! > 0) {
          // Fallback to tvShowId if tvShow object is not available but ID is valid
          Navigator.pushNamed(
            context,
            '/tvshow/${episode.tvShowId}',
          );
        } else {
          // Show episode info if no valid TV show ID is available
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('${episode.name} - S${episode.seasonNumber}E${episode.episodeNumber}\nTV show information not available'),
              backgroundColor: Colors.red,
              duration: const Duration(seconds: 2),
            ),
          );
        }
      },
      child: Container(
        width: 200,
        margin: const EdgeInsets.only(right: 12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            // Episode Still Image
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: AspectRatio(
                aspectRatio: 16 / 9,
                child: Stack(
                  children: [
                    CachedNetworkImage(
                      imageUrl: episode.getImageUrl('w500'),
                      fit: BoxFit.cover,
                      width: double.infinity,
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
                    // Episode number badge
                    Positioned(
                      top: 8,
                      left: 8,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.red,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          'S${episode.seasonNumber} E${episode.episodeNumber}',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 6),
            // TV Show Name
            if (episode.tvShow != null)
              Text(
                episode.tvShow!.name,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: Colors.grey[400],
                  fontSize: 10,
                ),
              ),
            if (episode.tvShow != null) const SizedBox(height: 2),
            // Episode Title
            Flexible(
              child: Text(
                episode.name,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

