import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/movie.dart';
import '../models/tvshow.dart';
import '../widgets/hero_section.dart';
import '../widgets/content_row.dart';
import '../widgets/latest_episodes_row.dart';
import '../widgets/recommended_content_section.dart';
import '../widgets/watched_content_section.dart';
import '../widgets/categories_section.dart';
import '../widgets/all_content_section.dart';
import '../widgets/layout/home_header.dart';
import '../widgets/banner_ad.dart';
import '../services/ad_service.dart';

class HomePage extends StatefulWidget {
  const HomePage({Key? key}) : super(key: key);

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  bool isLoading = true;
  List<dynamic> featuredItems = [];
  final ScrollController _scrollController = ScrollController();
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _fetchFeaturedContent();
    
    // Show interstitial ad when app opens
    _showOpeningInterstitialAd();
  }
  
  Future<void> _showOpeningInterstitialAd() async {
    print('🏠 [HomePage] Scheduling opening interstitial ad...');
    
    // Wait for the first frame to be rendered before showing the ad
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      print('🏠 [HomePage] First frame rendered, waiting before showing ad...');
      
      // Add a longer delay to ensure:
      // 1. UI is fully ready
      // 2. AdMob is fully initialized
      // 3. App open ad (if any) has finished
      await Future.delayed(const Duration(milliseconds: 2000));
      
      print('🏠 [HomePage] Delay complete, showing opening interstitial ad...');
      
      // Show interstitial ad on app opening
      try {
        await AdService.loadAndShowInterstitialAd(
          onAdDismissed: () {
            print('🏠 [HomePage] Opening interstitial ad dismissed');
          },
        );
        print('🏠 [HomePage] Interstitial ad request sent');
      } catch (e) {
        print('🏠 [HomePage] Error showing opening interstitial ad: $e');
      }
    });
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchFeaturedContent() async {
    try {
      setState(() {
        isLoading = true;
      });

      // Fetch diverse mix of content: Featured, Latest, and Trending
      final results = await Future.wait([
        // Featured content
        ApiService.getFeaturedMovies(limit: 8),
        ApiService.getFeaturedTVShows(limit: 8),
        // Latest content (sorted by ID descending to get newest)
        ApiService.getMovies(params: {'limit': '10', 'sort_by': 'id', 'sort_order': 'desc'}),
        ApiService.getTVShowsWithParams(params: {'limit': '10', 'sort_by': 'id', 'sort_order': 'desc'}),
        // Trending content
        ApiService.getTrendingMovies(period: 'week', limit: 8),
        // Trending TV shows via leaderboard
        ApiService.getTrendingContent(period: 'week', limit: 20),
      ]);

      final featuredMovies = results[0] as List<Movie>;
      final featuredTVShows = results[1] as List<TVShow>;
      final latestMoviesData = results[2] as Map<String, dynamic>;
      final latestTVShowsData = results[3] as Map<String, dynamic>;
      final trendingMovies = results[4] as List<Movie>;
      final trendingData = results[5] as Map<String, dynamic>;

      final latestMovies = (latestMoviesData['movies'] as List<Movie>?) ?? [];
      final latestTVShows = (latestTVShowsData['tvShows'] as List<TVShow>?) ?? [];
      final trendingTVShows = (trendingData['tvShows'] as List<TVShow>?) ?? [];

      // Combine all content sources
      final allContent = <dynamic>[
        ...featuredMovies,
        ...featuredTVShows,
        ...latestMovies,
        ...latestTVShows,
        ...trendingMovies,
        ...trendingTVShows,
      ];

      // Remove duplicates based on ID and type
      final seen = <String>{};
      final uniqueContent = <dynamic>[];
      
      for (final item in allContent) {
        final key = item is Movie 
            ? 'movie_${item.id}' 
            : 'tvshow_${(item as TVShow).id}';
        
        if (!seen.contains(key)) {
          seen.add(key);
          uniqueContent.add(item);
        }
      }

      // Randomize the content for variety
      uniqueContent.shuffle();

      // Prioritize items with higher ratings and newer content
      uniqueContent.sort((a, b) {
        final aVote = a is Movie ? a.voteAverage : (a as TVShow).voteAverage;
        final bVote = b is Movie ? b.voteAverage : (b as TVShow).voteAverage;
        final aId = a is Movie ? a.id : (a as TVShow).id;
        final bId = b is Movie ? b.id : (b as TVShow).id;
        
        // First sort by rating (higher is better), then by ID (newer is better)
        if (aVote != null && bVote != null) {
          final ratingDiff = bVote.compareTo(aVote);
          if (ratingDiff != 0) return ratingDiff;
        }
        return bId.compareTo(aId);
      });

      // Take top 12 items for hero carousel (mix of quality and variety)
      final featured = uniqueContent.take(12).toList();
      
      // Final shuffle to add randomness while keeping quality
      featured.shuffle();

      setState(() {
        featuredItems = featured;
        isLoading = false;
      });

      final movieCount = featured.whereType<Movie>().length;
      final tvShowCount = featured.whereType<TVShow>().length;
      print('Hero Carousel: Showing ${featuredItems.length} diverse items ($movieCount movies, $tvShowCount TV shows) - Mixed from Featured, Latest, and Trending');
    } catch (e) {
      print('Error fetching featured content: $e');
      // Fallback to just featured content if other sources fail
      try {
        final results = await Future.wait([
          ApiService.getFeaturedMovies(limit: 5),
          ApiService.getFeaturedTVShows(limit: 5),
        ]);
        final featuredMovies = results[0] as List<Movie>;
        final featuredTVShows = results[1] as List<TVShow>;
        final featured = <dynamic>[...featuredMovies, ...featuredTVShows]..shuffle();
        
        setState(() {
          featuredItems = featured;
          isLoading = false;
        });
      } catch (fallbackError) {
        print('Error in fallback: $fallbackError');
        setState(() {
          featuredItems = [];
          isLoading = false;
        });
      }
    }
  }


  Widget _buildSearchSection() {
    return StatefulBuilder(
      builder: (context, setState) {
        return Container(
      width: double.infinity,
      padding: EdgeInsets.symmetric(
        horizontal: MediaQuery.of(context).size.width >= 768 ? 80 : 24,
        vertical: 40,
      ),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [
            Colors.black.withOpacity(0.0),
            Colors.black.withOpacity(0.3),
            Colors.black,
          ],
        ),
      ),
      child: Column(
        children: [
          // Search Title
          Text(
            'Search Movies & TV Shows',
            style: TextStyle(
              color: Colors.white,
              fontSize: MediaQuery.of(context).size.width >= 768 ? 32 : 24,
              fontWeight: FontWeight.bold,
              letterSpacing: 0.5,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 12),
          Text(
            'Discover your favorite content',
            style: TextStyle(
              color: Colors.grey[400],
              fontSize: MediaQuery.of(context).size.width >= 768 ? 18 : 16,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 32),
          
          // Search Bar
          Container(
            constraints: const BoxConstraints(maxWidth: 800),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: Colors.red.withOpacity(0.3),
                  blurRadius: 20,
                  spreadRadius: 2,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: TextField(
              controller: _searchController,
              onChanged: (value) {
                setState(() {}); // Update to show/hide clear button
              },
              onSubmitted: (value) {
                if (value.trim().isNotEmpty) {
                  Navigator.pushNamed(
                    context,
                    '/search',
                    arguments: value.trim(),
                  );
                }
              },
              style: const TextStyle(
                color: Colors.white,
                fontSize: 18,
              ),
              decoration: InputDecoration(
                hintText: 'Search for movies, TV shows, episodes...',
                hintStyle: TextStyle(
                  color: Colors.grey[500],
                  fontSize: MediaQuery.of(context).size.width >= 768 ? 18 : 16,
                ),
                filled: true,
                fillColor: Colors.grey[900]!.withOpacity(0.95),
                prefixIcon: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Icon(
                    Icons.search,
                    color: Colors.red,
                    size: 28,
                  ),
                ),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: Icon(
                          Icons.clear,
                          color: Colors.grey[400],
                        ),
                        onPressed: () {
                          _searchController.clear();
                          setState(() {}); // Update to hide clear button
                        },
                      )
                    : null,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: BorderSide(
                    color: Colors.grey[800]!,
                    width: 2,
                  ),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: BorderSide(
                    color: Colors.grey[800]!,
                    width: 2,
                  ),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: const BorderSide(
                    color: Colors.red,
                    width: 3,
                  ),
                ),
                contentPadding: EdgeInsets.symmetric(
                  horizontal: 24,
                  vertical: MediaQuery.of(context).size.width >= 768 ? 20 : 18,
                ),
              ),
            ),
          ),
          
          const SizedBox(height: 24),
          
          // Quick Search Suggestions
          Wrap(
            spacing: 12,
            runSpacing: 12,
            alignment: WrapAlignment.center,
            children: [
              _buildQuickSearchChip('Trending', () {
                Navigator.pushNamed(context, '/trending');
              }),
              _buildQuickSearchChip('Top Rated Movies', () {
                Navigator.pushNamed(context, '/movies/top-rated');
              }),
              _buildQuickSearchChip('Popular TV Shows', () {
                Navigator.pushNamed(context, '/tvshows/popular');
              }),
              _buildQuickSearchChip("Today's Episodes", () {
                Navigator.pushNamed(context, '/episodes/today');
              }),
              _buildQuickSearchChip('18+', () {
                Navigator.pushNamed(
                  context,
                  '/movies?genre=18+',
                );
              }),
            ],
          ),
        ],
      ),
    );
      },
    );
  }

  Widget _buildQuickSearchChip(String label, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
        decoration: BoxDecoration(
          color: Colors.grey[900]!.withOpacity(0.8),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: Colors.grey[800]!,
            width: 1,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              Icons.trending_up,
              color: Colors.red,
              size: 18,
            ),
            const SizedBox(width: 8),
            Text(
              label,
              style: TextStyle(
                color: Colors.grey[300],
                fontSize: 14,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return const Scaffold(
        backgroundColor: Colors.black,
        body: Center(
          child: CircularProgressIndicator(
            color: Colors.red,
          ),
        ),
      );
    }

    return Scaffold(
      backgroundColor: Colors.black,
      body: RefreshIndicator(
        onRefresh: _fetchFeaturedContent,
        color: Colors.red,
        backgroundColor: Colors.grey[900],
        child: Stack(
          children: [
            SingleChildScrollView(
              controller: _scrollController,
              physics: const AlwaysScrollableScrollPhysics(),
              child: Column(
                children: [
              // Hero/Banner Section - ONLY shows featured content
              if (featuredItems.isNotEmpty)
                HeroSection(featuredItems: featuredItems),

              // Beautiful Search Section - Below Hero
              _buildSearchSection(),

              // Content Rows
              Padding(
                padding: EdgeInsets.only(
                  top: featuredItems.isNotEmpty ? 0 : 80,
                  bottom: 100, // Extra padding for bottom banner ad
                ),
                child: Column(
                  children: [
                    // Latest Episodes
                    const LatestEpisodesRow(
                      title: 'Latest Episodes',
                      limit: 20,
                      viewMoreLink: '/episodes/today',
                    ),

                    // Trending Now - Weekly Trending (Mix of Movies and TV Shows)
                    const ContentRow(
                      title: 'Trending Now (This Week)',
                      endpoint: '/leaderboard/trending?period=week&limit=16',
                      type: 'leaderboard',
                      contentType: 'all',
                      viewMoreLink: '/trending',
                    ),

                    // Categories Section (4 buttons with images)
                    const CategoriesSection(),

                    // You Have Watched Section
                    const WatchedContentSection(),

                    // Recommended Content (Based on Watched History)
                    const RecommendedContentSection(),

                    // All Movies & TV Shows (Infinite Scroll)
                    const AllContentSection(),
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

            // Bottom Sticky Collapsible Banner Ad
            // Google's collapsible banners have built-in UI controls
            Positioned(
              bottom: 0,
              left: 0,
              right: 0,
              child: SafeArea(
                top: false,
                child: CollapsibleBannerAdWidget(
                  adUnitId: AdService.bannerAdUnitId,
                  backgroundColor: Colors.black,
                ),
              ),
            ),

            // Sticky Request Content Icon (Right Bottom - Above Recent Requests)
            Positioned(
              bottom: 150, // Above the recent requests button
              right: 16,
              child: SafeArea(
                child: FloatingActionButton(
                  onPressed: () {
                    Navigator.pushNamed(context, '/request');
                  },
                  backgroundColor: Colors.blue.shade600,
                  child: const Icon(Icons.add, color: Colors.white),
                ),
              ),
            ),

            // Sticky Recent Requests Icon (Right Bottom)
            Positioned(
              bottom: 80, // Above the banner ad
              right: 16,
              child: SafeArea(
                child: FloatingActionButton(
                  onPressed: () {
                    Navigator.pushNamed(context, '/recent-requests');
                  },
                  backgroundColor: Colors.red,
                  child: Stack(
                    children: [
                      const Icon(Icons.request_quote, color: Colors.white),
                      // Badge for pending requests count (optional)
                      Positioned(
                        right: 0,
                        top: 0,
                        child: Container(
                          padding: const EdgeInsets.all(4),
                          decoration: const BoxDecoration(
                            color: Colors.yellow,
                            shape: BoxShape.circle,
                          ),
                          child: const Text(
                            '!',
                            style: TextStyle(
                              color: Colors.black,
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

