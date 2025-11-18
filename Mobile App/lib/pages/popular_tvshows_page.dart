import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/tvshow.dart';
import '../widgets/layout/home_header.dart';
import 'tvshow_detail_page.dart';

class PopularTVShowsPage extends StatefulWidget {
  const PopularTVShowsPage({Key? key}) : super(key: key);

  @override
  State<PopularTVShowsPage> createState() => _PopularTVShowsPageState();
}

class _PopularTVShowsPageState extends State<PopularTVShowsPage> {
  final ScrollController _scrollController = ScrollController();
  
  bool _isLoading = true;
  List<Map<String, dynamic>> _tvShows = [];
  String _period = 'week';

  final List<String> _periods = ['today', 'week', 'month', 'overall'];

  @override
  void initState() {
    super.initState();
    _fetchPopularTVShows();
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _fetchPopularTVShows() async {
    setState(() => _isLoading = true);

    try {
      final tvShows = await ApiService.getTVShowsLeaderboard(
        period: _period,
        limit: 50,
      );
      
      setState(() {
        _tvShows = tvShows.asMap().entries.map((entry) {
          return {
            'rank': entry.key + 1,
            'tvShow': entry.value,
            'viewCount': 0, // Would come from API if available
          };
        }).toList();
        _isLoading = false;
      });
    } catch (e) {
      print('Error fetching popular TV shows: $e');
      setState(() {
        _tvShows = [];
        _isLoading = false;
      });
    }
  }

  Color _getRankBadgeColor(int rank) {
    if (rank == 1) {
      return const Color(0xFFFBBF24); // Yellow
    } else if (rank == 2) {
      return const Color(0xFFD1D5DB); // Gray
    } else if (rank == 3) {
      return const Color(0xFFFB923C); // Orange
    } else if (rank <= 10) {
      return const Color(0xFFEF4444); // Red
    } else {
      return const Color(0xFF374151); // Dark Gray
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        children: [
          SingleChildScrollView(
            controller: _scrollController,
            child: Column(
              children: [
                const SizedBox(height: 80),
                
                Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Back Button
                      IconButton(
                        onPressed: () => Navigator.pop(context),
                        icon: const Icon(Icons.arrow_back, color: Colors.white),
                        padding: EdgeInsets.zero,
                        alignment: Alignment.centerLeft,
                      ),

                      const SizedBox(height: 16),

                      // Header
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: Colors.purple[600],
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Icon(
                              Icons.trending_up,
                              color: Colors.white,
                              size: 32,
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'Popular TV Shows',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontSize: 28,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'Most watched shows based on view counts',
                                  style: TextStyle(
                                    color: Colors.grey[400],
                                    fontSize: 14,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(height: 24),

                      // Period Filter
                      Container(
                        padding: const EdgeInsets.all(4),
                        decoration: BoxDecoration(
                          color: Colors.grey[800],
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          children: _periods.map((period) {
                            return Expanded(
                              child: GestureDetector(
                                onTap: () {
                                  setState(() => _period = period);
                                  _fetchPopularTVShows();
                                },
                                child: Container(
                                  padding: const EdgeInsets.symmetric(vertical: 10),
                                  decoration: BoxDecoration(
                                    color: _period == period
                                        ? Colors.purple[600]
                                        : Colors.transparent,
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Text(
                                    period[0].toUpperCase() + period.substring(1),
                                    textAlign: TextAlign.center,
                                    style: TextStyle(
                                      color: _period == period
                                          ? Colors.white
                                          : Colors.grey[400],
                                      fontWeight: FontWeight.w600,
                                      fontSize: 13,
                                    ),
                                  ),
                                ),
                              ),
                            );
                          }).toList(),
                        ),
                      ),

                      const SizedBox(height: 24),

                      // TV Shows Count
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                        decoration: BoxDecoration(
                          color: Colors.grey[800],
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.tv, color: Colors.purple[500], size: 20),
                            const SizedBox(width: 8),
                            Text(
                              '${_tvShows.length}',
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 16,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Text(
                              'TV Shows',
                              style: TextStyle(
                                color: Colors.grey[400],
                                fontSize: 14,
                              ),
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 24),

                      // Loading or Content
                      if (_isLoading)
                        const Center(
                          child: Padding(
                            padding: EdgeInsets.all(80.0),
                            child: CircularProgressIndicator(color: Colors.purple),
                          ),
                        )
                      else if (_tvShows.isEmpty)
                        Center(
                          child: Padding(
                            padding: const EdgeInsets.all(80.0),
                            child: Column(
                              children: [
                                Container(
                                  width: 80,
                                  height: 80,
                                  decoration: BoxDecoration(
                                    color: Colors.grey[800],
                                    shape: BoxShape.circle,
                                  ),
                                  child: const Icon(
                                    Icons.tv,
                                    size: 40,
                                    color: Colors.grey,
                                  ),
                                ),
                                const SizedBox(height: 24),
                                const Text(
                                  'No TV Shows Found',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontSize: 24,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  'Try selecting a different time period.',
                                  style: TextStyle(
                                    color: Colors.grey[400],
                                    fontSize: 16,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        )
                      else
                        GridView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: MediaQuery.of(context).size.width > 1200
                                ? 4
                                : MediaQuery.of(context).size.width > 900
                                    ? 3
                                    : MediaQuery.of(context).size.width > 600
                                        ? 2
                                        : 1,
                            childAspectRatio: 0.65,
                            crossAxisSpacing: 16,
                            mainAxisSpacing: 24,
                          ),
                          itemCount: _tvShows.length,
                          itemBuilder: (context, index) {
                            final item = _tvShows[index];
                            final rank = item['rank'] as int;
                            final tvShow = item['tvShow'] as TVShow;

                            return _buildTVShowCard(tvShow, rank);
                          },
                        ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Floating Header
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            child: HomeHeader(scrollController: _scrollController),
          ),
        ],
      ),
    );
  }

  Widget _buildTVShowCard(TVShow tvShow, int rank) {
    return GestureDetector(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => TVShowDetailPage(
              tvShowId: tvShow.id,
              tvShowName: tvShow.name,
            ),
          ),
        );
      },
      child: Stack(
        clipBehavior: Clip.none,
        children: [
          // Card
          Container(
            decoration: BoxDecoration(
              color: Colors.grey[900],
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.grey[800]!),
            ),
            child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Poster
              Expanded(
                child: Stack(
                  children: [
                    ClipRRect(
                      borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
                      child: tvShow.posterPath != null
                          ? Image.network(
                              tvShow.getImageUrl('w500'),
                              width: double.infinity,
                              fit: BoxFit.cover,
                            )
                          : Container(
                              color: Colors.grey[800],
                              child: const Center(
                                child: Icon(Icons.tv, size: 64, color: Colors.grey),
                              ),
                            ),
                    ),
                    // Rating Badge
                    if (tvShow.voteAverage != null)
                      Positioned(
                        top: 8,
                        right: 8,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.amber,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.star, size: 14, color: Colors.black),
                              const SizedBox(width: 4),
                              Text(
                                tvShow.voteAverage!.toStringAsFixed(1),
                                style: const TextStyle(
                                  color: Colors.black,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 13,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    // View Count Badge
                    if (tvShow.viewCount != null && tvShow.viewCount! > 0)
                      Positioned(
                        bottom: 8,
                        right: 8,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.black.withOpacity(0.8),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(
                                Icons.visibility,
                                color: Colors.purple,
                                size: 14,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                _formatViewCount(tvShow.viewCount!),
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w600,
                                  fontSize: 12,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                  ],
                ),
              ),
              // TV Show Name
              Padding(
                padding: const EdgeInsets.all(12.0),
                child: Text(
                  tvShow.name,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
        ),

        // Rank Badge
        Positioned(
          top: -12,
          left: -12,
          child: Container(
            width: 48,
            height: 48,
            decoration: BoxDecoration(
              color: _getRankBadgeColor(rank),
              shape: BoxShape.circle,
              border: Border.all(color: Colors.black, width: 4),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.5),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Center(
              child: Text(
                '#$rank',
                style: TextStyle(
                  color: rank <= 3 ? Colors.black : Colors.white,
                  fontWeight: FontWeight.bold,
                  fontSize: 14,
                ),
              ),
            ),
          ),
        ),
        ],
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

