import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/tvshow.dart';
import '../widgets/layout/home_header.dart';

class TopRatedTVShowsPage extends StatefulWidget {
  const TopRatedTVShowsPage({Key? key}) : super(key: key);

  @override
  State<TopRatedTVShowsPage> createState() => _TopRatedTVShowsPageState();
}

class _TopRatedTVShowsPageState extends State<TopRatedTVShowsPage> {
  final ScrollController _scrollController = ScrollController();
  
  bool _isLoading = true;
  List<TVShow> _tvShows = [];
  Map<String, dynamic> _pagination = {};

  @override
  void initState() {
    super.initState();
    _fetchTopRatedTVShows();
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _fetchTopRatedTVShows() async {
    setState(() => _isLoading = true);

    try {
      // Use top rated API method - fetch all TV shows with large limit
      final tvShows = await ApiService.getTopRatedTVShows(limit: 1000);
      
      setState(() {
        _tvShows = tvShows;
        _pagination = {
          'total': tvShows.length,
        };
        _isLoading = false;
      });
    } catch (e) {
      print('Error fetching top rated TV shows: $e');
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
      return const Color(0xFF3B82F6); // Blue
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
                              color: Colors.blue[600],
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Icon(
                              Icons.star,
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
                                  'Top Rated TV Shows',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontSize: 28,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'Highest rated TV shows with 7.0+ rating',
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

                      // Total TV Shows Count
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                        decoration: BoxDecoration(
                          color: Colors.grey[800],
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.tv, color: Colors.blue[500], size: 20),
                            const SizedBox(width: 8),
                            Text(
                              '${_pagination['total'] ?? 0}',
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 16,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Text(
                              'Total TV Shows',
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
                            child: CircularProgressIndicator(color: Colors.blue),
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
                              ],
                            ),
                          ),
                        )
                      else
                        Column(
                          children: [
                            GridView.builder(
                              shrinkWrap: true,
                              physics: const NeverScrollableScrollPhysics(),
                              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                                crossAxisCount: 2,
                                childAspectRatio: 0.75,
                                crossAxisSpacing: 16,
                                mainAxisSpacing: 20,
                              ),
                              itemCount: _tvShows.length,
                              itemBuilder: (context, index) {
                                final rank = index + 1;
                                return _buildStyledTVShowCard(_tvShows[index], rank);
                              },
                            ),
                          ],
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

  Widget _buildStyledTVShowCard(TVShow tvShow, int rank) {
    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: _getRankBadgeColor(rank).withOpacity(0.4),
            blurRadius: 20,
            spreadRadius: 2,
            offset: const Offset(0, 8),
          ),
          BoxShadow(
            color: Colors.black.withOpacity(0.6),
            blurRadius: 15,
            spreadRadius: 1,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: Material(
          color: Colors.transparent,
          child: InkWell(
            onTap: () {
              Navigator.pushNamed(
                context,
                '/tvshow/${tvShow.id}',
              );
            },
            borderRadius: BorderRadius.circular(16),
            splashColor: Colors.blue.withOpacity(0.3),
            highlightColor: Colors.blue.withOpacity(0.1),
            child: Stack(
              children: [
                // Background Image with Gradient Overlay
                Positioned.fill(
                  child: Container(
                    decoration: BoxDecoration(
                      color: Colors.grey[900],
                    ),
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        // TV Show Backdrop
                        Builder(
                          builder: (context) {
                            final imageUrl = tvShow.getBackdropUrl('w1280');
                            final isValidUrl = imageUrl.isNotEmpty && 
                                (imageUrl.startsWith('http://') || imageUrl.startsWith('https://')) &&
                                !imageUrl.contains('placeholder');
                            
                            if (isValidUrl)
                              return Image.network(
                                imageUrl,
                                fit: BoxFit.cover,
                                errorBuilder: (context, error, stackTrace) {
                                  return Container(
                                    color: Colors.grey[900],
                                    child: Icon(
                                      Icons.tv,
                                      size: 60,
                                      color: Colors.grey[700],
                                    ),
                                  );
                                },
                                loadingBuilder: (context, child, loadingProgress) {
                                  if (loadingProgress == null) return child;
                                  return Container(
                                    color: Colors.grey[900],
                                    child: Center(
                                      child: CircularProgressIndicator(
                                        color: Colors.blue,
                                        value: loadingProgress.expectedTotalBytes != null
                                            ? loadingProgress.cumulativeBytesLoaded /
                                                loadingProgress.expectedTotalBytes!
                                            : null,
                                      ),
                                    ),
                                  );
                                },
                              );
                            else
                              return Container(
                                color: Colors.grey[900],
                                child: Icon(
                                  Icons.tv,
                                  size: 60,
                                  color: Colors.grey[700],
                                ),
                              );
                          },
                        ),
                        
                        // Gradient Overlay
                        Container(
                          decoration: BoxDecoration(
                            gradient: LinearGradient(
                              begin: Alignment.topCenter,
                              end: Alignment.bottomCenter,
                              colors: [
                                Colors.transparent,
                                Colors.black.withOpacity(0.3),
                                Colors.black.withOpacity(0.8),
                                Colors.black.withOpacity(0.95),
                              ],
                              stops: const [0.0, 0.4, 0.7, 1.0],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),

                // Content Overlay
                Positioned(
                  bottom: 0,
                  left: 0,
                  right: 0,
                  child: Container(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        // TV Show Name
                        Text(
                          tvShow.name,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        
                        const SizedBox(height: 8),
                        
                        // Rating and Info Row
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          crossAxisAlignment: WrapCrossAlignment.center,
                          children: [
                            // Rating Badge
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                              decoration: BoxDecoration(
                                gradient: LinearGradient(
                                  colors: [
                                    Colors.blue,
                                    Colors.blue.withOpacity(0.8),
                                  ],
                                ),
                                borderRadius: BorderRadius.circular(8),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.blue.withOpacity(0.5),
                                    blurRadius: 8,
                                    spreadRadius: 1,
                                  ),
                                ],
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(
                                    Icons.star,
                                    color: Colors.white,
                                    size: 14,
                                  ),
                                  const SizedBox(width: 4),
                                  Text(
                                    tvShow.voteAverage != null 
                                        ? tvShow.voteAverage!.toStringAsFixed(1)
                                        : 'N/A',
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 12,
                                      fontWeight: FontWeight.bold,
                                      letterSpacing: 0.5,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            
                            // First Air Date
                            if (tvShow.firstAirDate != null && tvShow.firstAirDate!.isNotEmpty)
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
                                decoration: BoxDecoration(
                                  color: Colors.white.withOpacity(0.15),
                                  borderRadius: BorderRadius.circular(6),
                                  border: Border.all(
                                    color: Colors.white.withOpacity(0.3),
                                    width: 1,
                                  ),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(
                                      Icons.calendar_today,
                                      size: 12,
                                      color: Colors.white.withOpacity(0.9),
                                    ),
                                    const SizedBox(width: 4),
                                    Text(
                                      tvShow.firstAirDate!.substring(0, 4),
                                      style: TextStyle(
                                        color: Colors.white.withOpacity(0.9),
                                        fontSize: 11,
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),

                // Rank Badge (Top Left)
                Positioned(
                  top: 12,
                  left: 12,
                  child: Container(
                    width: 56,
                    height: 56,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                        colors: [
                          _getRankBadgeColor(rank),
                          _getRankBadgeColor(rank).withOpacity(0.8),
                        ],
                      ),
                      shape: BoxShape.circle,
                      border: Border.all(
                        color: Colors.black,
                        width: 3,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: _getRankBadgeColor(rank).withOpacity(0.6),
                          blurRadius: 12,
                          spreadRadius: 2,
                        ),
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
                          fontSize: 16,
                          letterSpacing: 0.5,
                        ),
                      ),
                    ),
                  ),
                ),

                // Bottom Gradient Border
                Positioned(
                  bottom: 0,
                  left: 0,
                  right: 0,
                  height: 4,
                  child: Container(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          Colors.blue,
                          Colors.blue.withOpacity(0.7),
                          Colors.transparent,
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

