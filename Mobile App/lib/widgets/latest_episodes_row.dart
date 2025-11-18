import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/episode.dart';
import 'episode_card.dart';

class LatestEpisodesRow extends StatefulWidget {
  final String title;
  final int limit;
  final String? viewMoreLink;

  const LatestEpisodesRow({
    Key? key,
    required this.title,
    this.limit = 20,
    this.viewMoreLink,
  }) : super(key: key);

  @override
  State<LatestEpisodesRow> createState() => _LatestEpisodesRowState();
}

class _LatestEpisodesRowState extends State<LatestEpisodesRow> {
  List<Episode> episodes = [];
  bool isLoading = true;
  bool hasError = false;

  @override
  void initState() {
    super.initState();
    _fetchEpisodes();
  }

  Future<void> _fetchEpisodes() async {
    try {
      setState(() {
        isLoading = true;
        hasError = false;
      });

      final fetchedEpisodes = await ApiService.getLatestEpisodes(limit: widget.limit);
      setState(() {
        episodes = fetchedEpisodes;
        isLoading = false;
      });
    } catch (e) {
      setState(() {
        hasError = true;
        isLoading = false;
      });
      print('Error fetching episodes: $e');
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
            height: 150,
            child: isLoading
                ? _buildLoadingState()
                : hasError
                    ? _buildErrorState()
                    : episodes.isEmpty
                        ? _buildEmptyState()
                        : _buildEpisodeList(),
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
          width: 200,
          margin: const EdgeInsets.only(right: 12),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                height: 112,
                decoration: BoxDecoration(
                  color: Colors.grey[900],
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              const SizedBox(height: 8),
              Container(
                height: 12,
                width: 150,
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
            'Failed to load episodes',
            style: TextStyle(color: Colors.grey[700], fontSize: 14),
          ),
          const SizedBox(height: 8),
          TextButton(
            onPressed: _fetchEpisodes,
            child: const Text('Retry'),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Text(
        'No episodes available',
        style: TextStyle(color: Colors.grey[700], fontSize: 14),
      ),
    );
  }

  Widget _buildEpisodeList() {
    return ListView.builder(
      scrollDirection: Axis.horizontal,
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: episodes.length,
      itemBuilder: (context, index) {
        final episode = episodes[index];
        return EpisodeCard(episode: episode);
      },
    );
  }
}

