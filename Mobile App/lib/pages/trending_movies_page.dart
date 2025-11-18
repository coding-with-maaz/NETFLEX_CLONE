import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/movie.dart';
import '../widgets/movie_card.dart';
import '../widgets/layout/home_header.dart';

class TrendingMoviesPage extends StatefulWidget {
  const TrendingMoviesPage({Key? key}) : super(key: key);

  @override
  State<TrendingMoviesPage> createState() => _TrendingMoviesPageState();
}

class _TrendingMoviesPageState extends State<TrendingMoviesPage> {
  final ScrollController _scrollController = ScrollController();
  
  bool _isLoading = true;
  List<Map<String, dynamic>> _movies = [];
  String _period = 'today';

  final List<String> _periods = ['today', 'week', 'month', 'overall'];

  @override
  void initState() {
    super.initState();
    _fetchTrendingMovies();
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _fetchTrendingMovies() async {
    setState(() => _isLoading = true);

    try {
      final movies = await ApiService.getMoviesLeaderboard(
        period: _period,
        limit: 50,
      );
      
      setState(() {
        _movies = movies.asMap().entries.map((entry) {
          return {
            'rank': entry.key + 1,
            'movie': entry.value,
            'viewCount': 0, // Would come from API if available
          };
        }).toList();
        _isLoading = false;
      });
    } catch (e) {
      print('Error fetching trending movies: $e');
      setState(() {
        _movies = [];
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
                              color: Colors.red[600],
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
                                  'Trending Movies',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontSize: 28,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'Most watched movies right now',
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
                                  _fetchTrendingMovies();
                                },
                                child: Container(
                                  padding: const EdgeInsets.symmetric(vertical: 10),
                                  decoration: BoxDecoration(
                                    color: _period == period
                                        ? Colors.red[600]
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

                      // Movies Count
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                        decoration: BoxDecoration(
                          color: Colors.grey[800],
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.movie, color: Colors.red[500], size: 20),
                            const SizedBox(width: 8),
                            Text(
                              '${_movies.length}',
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 16,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Text(
                              'Movies',
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
                            child: CircularProgressIndicator(color: Colors.red),
                          ),
                        )
                      else if (_movies.isEmpty)
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
                                    Icons.movie,
                                    size: 40,
                                    color: Colors.grey,
                                  ),
                                ),
                                const SizedBox(height: 24),
                                const Text(
                                  'No Movies Found',
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
                                ? 6
                                : MediaQuery.of(context).size.width > 900
                                    ? 5
                                    : MediaQuery.of(context).size.width > 600
                                        ? 4
                                        : 2,
                            childAspectRatio: 0.55,
                            crossAxisSpacing: 16,
                            mainAxisSpacing: 24,
                          ),
                          itemCount: _movies.length,
                          itemBuilder: (context, index) {
                            final item = _movies[index];
                            final rank = item['rank'] as int;
                            final movie = item['movie'] as Movie;

                            return Stack(
                              clipBehavior: Clip.none,
                              children: [
                                MovieCard(movie: movie),
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
                            );
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
}

