import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:google_mobile_ads/google_mobile_ads.dart';
import '../models/movie.dart';
import '../models/embed.dart';
import '../models/comment.dart';
import '../services/api_service.dart';
import '../services/ad_service.dart';
import '../services/watched_content_service.dart';
import '../widgets/movie_card.dart';
import '../widgets/iframe_player.dart';
import '../widgets/fullscreen_player.dart';

// Helper class to process embed URLs and extract custom styling
class EmbedData {
  final String url;
  final bool hasCustomStyling;
  final String? divStyle;
  final String? iframeStyle;
  final Map<String, String> iframeAttributes;

  EmbedData({
    required this.url,
    this.hasCustomStyling = false,
    this.divStyle,
    this.iframeStyle,
    this.iframeAttributes = const {},
  });
}

class MovieDetailPage extends StatefulWidget {
  final int movieId;

  const MovieDetailPage({
    Key? key,
    required this.movieId,
  }) : super(key: key);

  @override
  State<MovieDetailPage> createState() => _MovieDetailPageState();
}

class _MovieDetailPageState extends State<MovieDetailPage> {
  Movie? _movie;
  List<MovieEmbed> _embeds = [];
  List<DownloadLink> _downloads = [];
  List<Movie> _similarMovies = [];
  bool _loading = true;
  MovieEmbed? _activeEmbed;
  EmbedData? _activeEmbedData;
  String _activeTab = 'watch'; // watch, download, details
  final ScrollController _scrollController = ScrollController();
  
  // Modals
  bool _showRequestModal = false;
  bool _showReportModal = false;
  
  // Form controllers
  final _requestTitleController = TextEditingController();
  final _requestEmailController = TextEditingController();
  final _requestDescriptionController = TextEditingController();
  final _requestTypeController = TextEditingController(text: 'movie');
  final _reportDescriptionController = TextEditingController();
  final _reportEmailController = TextEditingController();
  String _selectedReportType = 'not_working';
  
  // Comments
  List<Comment> _comments = [];
  bool _loadingComments = false;
  bool _submittingComment = false;
  final _commentNameController = TextEditingController();
  final _commentEmailController = TextEditingController();
  final _commentTextController = TextEditingController();
  int? _replyingToCommentId;
  String? _replyingToCommentName;
  final _commentFormKey = GlobalKey<FormState>();
  bool _showCommentSuccess = false;
  
  // AdMob Banner
  BannerAd? _bannerAd;
  bool _isBannerAdLoaded = false;

  // Process embed URL and extract custom styling (similar to show.blade.php)
  EmbedData _processEmbedUrl(MovieEmbed embed) {
    final embedUrl = embed.embedUrl;
    
    if (embedUrl.isEmpty) {
      return EmbedData(url: '');
    }

    final trimmedUrl = embedUrl.trim();
    
    // Check if embedUrl contains full HTML structure with div wrapper
    bool hasDivWrapper = RegExp(r'<div[^>]*>', caseSensitive: false)
        .hasMatch(trimmedUrl);
    
    String? divStyle;
    String? iframeStyle;
    final Map<String, String> iframeAttributes = {};
    String processedUrl = trimmedUrl;

    if (hasDivWrapper) {
      // Extract div style attribute
      final divStyleMatch = RegExp(r'<div[^>]*style\s*=\s*"([^"]+)"', 
          caseSensitive: false, multiLine: true)
          .firstMatch(trimmedUrl) ??
          RegExp(r"<div[^>]*style\s*=\s*'([^']+)'", 
              caseSensitive: false, multiLine: true)
          .firstMatch(trimmedUrl);
      
      if (divStyleMatch != null && divStyleMatch.groupCount > 0) {
        divStyle = divStyleMatch.group(1)?.trim();
      }

      // Extract iframe and its attributes
      final iframeMatch = RegExp(r'<iframe([\s\S]*?)>', 
          caseSensitive: false)
          .firstMatch(trimmedUrl);
      
      if (iframeMatch != null && iframeMatch.groupCount > 0) {
        final iframeAttrs = iframeMatch.group(1) ?? '';
        
        // Extract iframe src
        final srcMatch = RegExp(r'src\s*=\s*"([^"]+)"', 
            caseSensitive: false, multiLine: true)
            .firstMatch(iframeAttrs) ??
            RegExp(r"src\s*=\s*'([^']+)'", 
                caseSensitive: false, multiLine: true)
            .firstMatch(iframeAttrs) ??
            RegExp(r'src\s*=\s*([^\s>]+)', 
                caseSensitive: false, multiLine: true)
            .firstMatch(iframeAttrs);
        
        if (srcMatch != null && srcMatch.groupCount > 0) {
          processedUrl = srcMatch.group(1)?.trim() ?? '';
          try {
            processedUrl = Uri.decodeComponent(processedUrl);
          } catch (e) {
            // If decoding fails, use original
          }
        }
        
        // Extract iframe style
        final iframeStyleMatch = RegExp(r'style\s*=\s*"([^"]+)"', 
            caseSensitive: false, multiLine: true)
            .firstMatch(iframeAttrs) ??
            RegExp(r"style\s*=\s*'([^']+)'", 
                caseSensitive: false, multiLine: true)
            .firstMatch(iframeAttrs);
        
        if (iframeStyleMatch != null && iframeStyleMatch.groupCount > 0) {
          iframeStyle = iframeStyleMatch.group(1)?.trim();
        }
        
        // Extract scrolling attribute
        if (RegExp(r'scrolling', caseSensitive: false).hasMatch(iframeAttrs)) {
          final scrollingMatch = RegExp(r'scrolling\s*=\s*"([^"]+)"', 
              caseSensitive: false, multiLine: true)
              .firstMatch(iframeAttrs) ??
              RegExp(r"scrolling\s*=\s*'([^']+)'", 
                  caseSensitive: false, multiLine: true)
              .firstMatch(iframeAttrs) ??
              RegExp(r'scrolling\s*=\s*([^\s>]+)', 
                  caseSensitive: false, multiLine: true)
              .firstMatch(iframeAttrs);
          
          if (scrollingMatch != null) {
            iframeAttributes['scrolling'] = 
                (scrollingMatch.group(1) ?? scrollingMatch.group(0) ?? 'auto').trim();
          }
        }
        
        // Extract allow attribute
        final allowMatch = RegExp(r'allow\s*=\s*"([^"]+)"', 
            caseSensitive: false, multiLine: true)
            .firstMatch(iframeAttrs) ??
            RegExp(r"allow\s*=\s*'([^']+)'", 
                caseSensitive: false, multiLine: true)
            .firstMatch(iframeAttrs);
        
        if (allowMatch != null && allowMatch.groupCount > 0) {
          iframeAttributes['allow'] = allowMatch.group(1)?.trim() ?? '';
        }
        
        // Check for allowfullscreen
        if (RegExp(r'allowfullscreen', caseSensitive: false)
            .hasMatch(iframeAttrs)) {
          iframeAttributes['allowfullscreen'] = 'true';
        }
      }
    } 
    // Check if embedUrl contains HTML iframe tags - extract the src attribute
    else if (RegExp(r'<iframe', caseSensitive: false).hasMatch(trimmedUrl)) {
      final srcMatch = RegExp(r'src\s*=\s*"([^"]+)"', 
          caseSensitive: false)
          .firstMatch(trimmedUrl) ??
          RegExp(r"src\s*=\s*'([^']+)'", caseSensitive: false)
          .firstMatch(trimmedUrl) ??
          RegExp(r'src\s*=\s*([^\s>]+)', caseSensitive: false)
          .firstMatch(trimmedUrl);
      
      if (srcMatch != null && srcMatch.groupCount > 0) {
        processedUrl = srcMatch.group(1)?.trim() ?? '';
        try {
          processedUrl = Uri.decodeComponent(processedUrl);
        } catch (e) {
          // If decoding fails, use original
        }
      }
    }

    // Handle Mixdrop, Dailymotion, Bilibili, and Doodstream - ensure proper embed URLs
    final lowerUrl = processedUrl.toLowerCase();
    if (lowerUrl.contains('mixdrop') || lowerUrl.contains('dailymotion') || lowerUrl.contains('bilibili') || lowerUrl.contains('doodstream') || lowerUrl.contains('dood.to') || lowerUrl.contains('dood.')) {
      debugPrint('[Movie Details] Processing Mixdrop/Dailymotion/Bilibili/Doodstream URL: $processedUrl');
      
      // For Mixdrop: ensure it's an embed URL
      if (lowerUrl.contains('mixdrop')) {
        // If it's a watch/share URL, convert to embed
        if (processedUrl.contains('/e/') || processedUrl.contains('/f/')) {
          // Already an embed URL, keep as is
          debugPrint('[Movie Details] Mixdrop embed URL detected');
        } else if (processedUrl.contains('/v/') || processedUrl.contains('/watch/')) {
          // Convert watch URL to embed format if possible
          final fileIdMatch = RegExp(r'[/]([a-zA-Z0-9]+)$').firstMatch(processedUrl) ??
              RegExp(r'[/]([a-zA-Z0-9]+)\?').firstMatch(processedUrl);
          if (fileIdMatch != null && fileIdMatch.groupCount > 0) {
            // Try to construct embed URL - this may vary by Mixdrop domain
            final domainMatch = RegExp(r'https?://([^/]+)').firstMatch(processedUrl);
            final domain = domainMatch?.group(1) ?? 'mixdrop.co';
            processedUrl = 'https://$domain/e/${fileIdMatch.group(1)}';
            debugPrint('[Movie Details] Converted Mixdrop URL to embed: $processedUrl');
          }
        }
      }
      
      // For Dailymotion: ensure it's an embed URL
      if (lowerUrl.contains('dailymotion.com')) {
        // If it's a video URL, convert to embed
        if (processedUrl.contains('/embed/')) {
          // Already an embed URL, keep as is
          debugPrint('[Movie Details] Dailymotion embed URL detected');
        } else if (processedUrl.contains('/video/')) {
          // Convert video URL to embed format
          final videoIdMatch = RegExp(r'/video/([a-zA-Z0-9]+)').firstMatch(processedUrl);
          if (videoIdMatch != null && videoIdMatch.groupCount > 0) {
            processedUrl = 'https://www.dailymotion.com/embed/video/${videoIdMatch.group(1)}';
            debugPrint('[Movie Details] Converted Dailymotion URL to embed: $processedUrl');
          }
        }
      }
      
      // For Bilibili: ensure web version (not mobile/Android)
      if (lowerUrl.contains('bilibili.tv') || lowerUrl.contains('bilibili.com')) {
        debugPrint('[Movie Details] Processing Bilibili URL: $processedUrl');
        
        // Extract video ID if present
        String? videoId;
        if (processedUrl.contains('/video/')) {
          final videoIdMatch = RegExp(r'/video/([a-zA-Z0-9]+)').firstMatch(processedUrl);
          if (videoIdMatch != null && videoIdMatch.groupCount > 0) {
            videoId = videoIdMatch.group(1);
          }
        }
        
        // Force web version by using bilibili.tv/en/ with platform=web
        try {
          final uri = Uri.parse(processedUrl);
          final queryParams = Map<String, String>.from(uri.queryParameters);
          
          // Remove mobile/Android specific parameters
          queryParams.remove('platform');
          queryParams.remove('from');
          queryParams.remove('share_source');
          queryParams.remove('share_medium');
          
          // Force web platform
          queryParams['platform'] = 'web';
          
          // Ensure it's the .tv domain (web version)
          String hostname = uri.host;
          String path = uri.path;
          
          if (hostname.contains('bilibili.com')) {
            hostname = 'www.bilibili.tv';
            if (!path.startsWith('/en/') && !path.startsWith('/en')) {
              path = '/en$path';
            }
          } else if (!hostname.contains('bilibili.tv')) {
            hostname = 'www.bilibili.tv';
            if (videoId != null) {
              path = '/en/video/$videoId';
            } else if (!path.startsWith('/en/') && !path.startsWith('/en')) {
              path = '/en$path';
            }
          }
          
          // Construct the final URL
          final newUri = Uri(
            scheme: uri.scheme,
            host: hostname,
            path: path,
            queryParameters: queryParams.isNotEmpty ? queryParams : null,
          );
          
          processedUrl = newUri.toString();
          debugPrint('[Movie Details] Bilibili web URL: $processedUrl');
        } catch (e) {
          // If URL parsing fails, try manual construction
          if (videoId != null) {
            processedUrl = 'https://www.bilibili.tv/en/video/$videoId?platform=web';
            debugPrint('[Movie Details] Constructed Bilibili web URL: $processedUrl');
          }
        }
        
        // Auto-apply Bilibili custom styling if not already present
        if (!hasDivWrapper) {
          debugPrint('[Movie Details] Applying Bilibili custom styling');
          hasDivWrapper = true;
          divStyle = 'width: 100%; height: 280px; overflow: hidden; position: relative;';
          iframeStyle = 'width: 100%; height: 330px; position: absolute; top: -60px; left: 0; border: none;';
          iframeAttributes['scrolling'] = 'no';
        }
      }
      
      // For Doodstream: ensure it's an embed URL
      if (lowerUrl.contains('doodstream') || lowerUrl.contains('dood.to') || lowerUrl.contains('dood.')) {
        debugPrint('[Movie Details] Processing Doodstream URL: $processedUrl');
        
        // Doodstream embed URLs typically use /e/ path
        if (processedUrl.contains('/e/')) {
          // Already an embed URL, keep as is
          debugPrint('[Movie Details] Doodstream embed URL detected');
        } else if (processedUrl.contains('/d/') || processedUrl.contains('/v/') || processedUrl.contains('/watch/')) {
          // Convert watch/download URL to embed format
          final fileIdMatch = RegExp(r'[/]([a-zA-Z0-9]+)$').firstMatch(processedUrl) ??
              RegExp(r'[/]([a-zA-Z0-9]+)\?').firstMatch(processedUrl);
          if (fileIdMatch != null && fileIdMatch.groupCount > 0) {
            // Extract domain
            final domainMatch = RegExp(r'https?://([^/]+)').firstMatch(processedUrl);
            String domain = domainMatch?.group(1) ?? 'doodstream.com';
            
            // Normalize to doodstream.com or dood.to
            if (domain.contains('dood.to')) {
              domain = 'dood.to';
            } else if (domain.contains('dood.')) {
              domain = 'doodstream.com';
            }
            
            processedUrl = 'https://$domain/e/${fileIdMatch.group(1)}';
            debugPrint('[Movie Details] Converted Doodstream URL to embed: $processedUrl');
          }
        }
        
        // Auto-apply Doodstream custom styling if not already present
        if (!hasDivWrapper) {
          debugPrint('[Movie Details] Applying Doodstream custom styling');
          hasDivWrapper = true;
          divStyle = 'width: 100%; height: 280px; overflow: hidden; position: relative;';
          iframeStyle = 'width: 100%; height: 330px; position: absolute; top: -60px; left: 0; border: none;';
          iframeAttributes['scrolling'] = 'no';
        }
      }
    }

    // Validate URL
    try {
      final uri = Uri.parse(processedUrl);
      if (!uri.hasScheme || (!uri.scheme.startsWith('http'))) {
        return EmbedData(url: '');
      }
    } catch (e) {
      return EmbedData(url: '');
    }

    return EmbedData(
      url: processedUrl,
      hasCustomStyling: hasDivWrapper && (divStyle != null || iframeStyle != null),
      divStyle: divStyle,
      iframeStyle: iframeStyle,
      iframeAttributes: iframeAttributes,
    );
  }

  @override
  void initState() {
    super.initState();
    _fetchMovieDetails();
    _trackView();
    _loadBannerAd();
    _loadInterstitialAd();
  }
  
  void _loadBannerAd() {
    _bannerAd = AdService.instance.createBannerAd(
      onAdLoaded: (ad) {
        setState(() {
          _isBannerAdLoaded = true;
        });
        print('Banner ad loaded');
      },
      onAdFailedToLoad: (ad, error) {
        print('Banner ad failed to load: $error');
        ad.dispose();
      },
    );
    _bannerAd?.load();
  }
  
  void _loadInterstitialAd() {
    // Don't show ad here - will be shown when user selects an embed that requires_ad
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _bannerAd?.dispose();
    _requestTitleController.dispose();
    _requestEmailController.dispose();
    _requestDescriptionController.dispose();
    _reportDescriptionController.dispose();
    _reportEmailController.dispose();
    _commentNameController.dispose();
    _commentEmailController.dispose();
    _commentTextController.dispose();
    super.dispose();
  }

  Future<void> _trackView() async {
    await ApiService.trackMovieView(widget.movieId);
  }

  Future<void> _fetchMovieDetails() async {
    setState(() => _loading = true);

    try {
      // Fetch movie details
      final movie = await ApiService.getMovieById(widget.movieId);
      if (movie == null) {
        setState(() => _loading = false);
        return;
      }

      // Save to watched content when movie details are loaded
      await WatchedContentService.saveWatchedMovie(movie);

      // Fetch embeds
      final embeds = await ApiService.getMovieEmbeds(widget.movieId);
      
      // Debug: Log embeds and their requiresAd status
      debugPrint('[Movie Detail] Fetched ${embeds.length} embeds for movie ${widget.movieId}');
      for (var embed in embeds) {
        debugPrint('[Movie Detail] Embed ID: ${embed.id}, Server: ${embed.serverName}, requiresAd: ${embed.requiresAd}');
      }

      // Fetch downloads
      final downloads = await ApiService.getMovieDownloads(widget.movieId);

      // Fetch similar movies (same genre)
      List<Movie> similarMovies = [];
      if (movie.genres != null && movie.genres!.isNotEmpty) {
        final genreId = movie.genres![0].id.toString();
        final result = await ApiService.getMovies(
          params: {
            'genre': genreId,
            'limit': '10',
          },
        );
        similarMovies = (result['movies'] as List<Movie>?)
                ?.where((m) => m.id != widget.movieId)
                .toList() ??
            [];
      }

      setState(() {
        _movie = movie;
        _embeds = embeds;
        _downloads = downloads;
        _similarMovies = similarMovies;
        _loading = false;
        
        // Auto-select the first embed (Server 1)
        if (embeds.isNotEmpty) {
          final firstEmbed = embeds[0];
          final requiresAd = firstEmbed.requiresAd == true;
          
          debugPrint('[Movie Detail] Auto-selecting Server 1 - requiresAd: $requiresAd, embedId: ${firstEmbed.id}');
          
          // Check if first embed requires an ad
          if (requiresAd) {
            debugPrint('[Movie Detail] Showing interstitial ad before auto-selecting Server 1');
            // Show interstitial ad before setting active embed
            AdService.loadAndShowInterstitialAd(
              onAdDismissed: () {
                debugPrint('[Movie Detail] Ad dismissed, setting active embed (auto-select)');
                setState(() {
                  _activeEmbed = firstEmbed;
                  _activeEmbedData = _processEmbedUrl(firstEmbed);
                });
              },
            );
          } else {
            debugPrint('[Movie Detail] No ad required, setting active embed immediately (auto-select)');
            // No ad required, set immediately
            _activeEmbed = firstEmbed;
            _activeEmbedData = _processEmbedUrl(firstEmbed);
          }
        }
      });
      
      // Load comments after movie is loaded
      _loadComments();
    } catch (e) {
      debugPrint('Error fetching movie details: $e');
      setState(() => _loading = false);
    }
  }
  
  Future<void> _loadComments() async {
    if (_movie == null) return;
    
    setState(() => _loadingComments = true);
    
    try {
      final comments = await ApiService.getComments(
        type: 'movie',
        id: widget.movieId,
      );
      
      setState(() {
        _comments = comments;
        _loadingComments = false;
      });
    } catch (e) {
      debugPrint('Error loading comments: $e');
      setState(() => _loadingComments = false);
    }
  }
  
  Future<void> _submitComment() async {
    if (!_commentFormKey.currentState!.validate()) return;
    if (_movie == null) return;
    
    setState(() => _submittingComment = true);
    _showCommentSuccess = false;
    
    try {
      final result = await ApiService.submitComment(
        type: 'movie',
        id: widget.movieId,
        name: _commentNameController.text.trim(),
        email: _commentEmailController.text.trim(),
        comment: _commentTextController.text.trim(),
        parentId: _replyingToCommentId,
      );
      
      if (mounted) {
        if (result['success'] == true) {
          setState(() {
            _submittingComment = false;
            _showCommentSuccess = true;
            _commentNameController.clear();
            _commentEmailController.clear();
            _commentTextController.clear();
            _replyingToCommentId = null;
            _replyingToCommentName = null;
          });
          
          // Reload comments after a short delay
          Future.delayed(const Duration(seconds: 1), () {
            _loadComments();
          });
          
          // Hide success message after 5 seconds
          Future.delayed(const Duration(seconds: 5), () {
            if (mounted) {
              setState(() => _showCommentSuccess = false);
            }
          });
        } else {
          setState(() => _submittingComment = false);
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(result['message'] ?? 'Failed to submit comment'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    } catch (e) {
      debugPrint('Error submitting comment: $e');
      if (mounted) {
        setState(() => _submittingComment = false);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('An error occurred. Please try again later.'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }
  
  void _startReply(int commentId, String commentName) {
    setState(() {
      _replyingToCommentId = commentId;
      _replyingToCommentName = commentName;
    });
    // Scroll to comment form
    Future.delayed(const Duration(milliseconds: 300), () {
      _scrollController.animateTo(
        _scrollController.position.maxScrollExtent,
        duration: const Duration(milliseconds: 500),
        curve: Curves.easeInOut,
      );
    });
  }
  
  void _cancelReply() {
    setState(() {
      _replyingToCommentId = null;
      _replyingToCommentName = null;
    });
  }

  void _handleWatchNow() {
    setState(() {
      _activeTab = 'watch';
      if (_embeds.isNotEmpty && _activeEmbed == null) {
        final firstEmbed = _embeds[0];
        final requiresAd = firstEmbed.requiresAd == true;
        
        debugPrint('[Movie Detail] Watch Now clicked - first embed requiresAd: $requiresAd, embedId: ${firstEmbed.id}');
        
        // Check if first embed requires an ad
        if (requiresAd) {
          debugPrint('[Movie Detail] Showing interstitial ad before playing (Watch Now)');
          // Show interstitial ad before setting active embed
          AdService.loadAndShowInterstitialAd(
            onAdDismissed: () {
              debugPrint('[Movie Detail] Ad dismissed, setting active embed (Watch Now)');
              setState(() {
                _activeEmbed = firstEmbed;
                _activeEmbedData = _processEmbedUrl(firstEmbed);
              });
            },
          );
        } else {
          debugPrint('[Movie Detail] No ad required, setting active embed immediately (Watch Now)');
          // No ad required, set immediately
          setState(() {
            _activeEmbed = firstEmbed;
            _activeEmbedData = _processEmbedUrl(firstEmbed);
          });
        }
      }
    });
    // Scroll to top
    _scrollController.animateTo(
      0,
      duration: const Duration(milliseconds: 500),
      curve: Curves.easeInOut,
    );
  }

  Future<void> _launchUrl(String url) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Could not open link')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return Scaffold(
        backgroundColor: Colors.black,
        appBar: AppBar(
          backgroundColor: Colors.black,
          elevation: 0,
          iconTheme: const IconThemeData(color: Colors.white),
        ),
        body: const Center(
          child: CircularProgressIndicator(
            valueColor: AlwaysStoppedAnimation<Color>(Colors.red),
          ),
        ),
      );
    }

    if (_movie == null) {
      return Scaffold(
        backgroundColor: Colors.black,
        appBar: AppBar(
          backgroundColor: Colors.black,
          elevation: 0,
          iconTheme: const IconThemeData(color: Colors.white),
        ),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Text(
                'Movie not found',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => Navigator.pop(context),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.red,
                ),
                child: const Text('Back to Movies'),
              ),
            ],
          ),
        ),
      );
    }

    final backdropUrl = _movie!.getBackdropUrl('w1280');

    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        children: [
          SingleChildScrollView(
            controller: _scrollController,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Hero Section
                _buildHeroSection(backdropUrl),

                // Tabs Content
                Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Watch Tab
                      if (_activeTab == 'watch') _buildWatchTab(),

                      // Download Tab
                      if (_activeTab == 'download') _buildDownloadTab(),

                      // Details Tab
                      if (_activeTab == 'details') _buildDetailsTab(),
                    ],
                  ),
                ),

                // Similar Movies
                if (_similarMovies.isNotEmpty) _buildSimilarMovies(),
                
                // Comments Section
                _buildCommentsSection(),
              ],
            ),
          ),
          // Request Content Modal
          if (_showRequestModal) _buildRequestModal(),
          // Report Embed Modal
          if (_showReportModal) _buildReportModal(),
        ],
      ),
    );
  }

  Widget _buildHeroSection(String backdropUrl) {
    return Stack(
      children: [
        // Backdrop Image
        SizedBox(
          height: 600,
          width: double.infinity,
          child: Stack(
            children: [
              if (backdropUrl.isNotEmpty)
                CachedNetworkImage(
                  imageUrl: backdropUrl,
                  fit: BoxFit.cover,
                  width: double.infinity,
                  height: double.infinity,
                  placeholder: (context, url) => Container(
                    color: Colors.grey[900],
                  ),
                  errorWidget: (context, url, error) => Container(
                    color: Colors.grey[900],
                  ),
                )
              else
                Container(color: Colors.grey[900]),
              // Gradients
              Container(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [
                      Colors.black.withOpacity(0),
                      Colors.black.withOpacity(0.6),
                      Colors.black,
                    ],
                  ),
                ),
              ),
              Container(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.centerLeft,
                    end: Alignment.centerRight,
                    colors: [
                      Colors.black.withOpacity(0.8),
                      Colors.black.withOpacity(0),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),

        // Content
        SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Back Button
                IconButton(
                  onPressed: () => Navigator.pop(context),
                  icon: const Icon(Icons.arrow_back, color: Colors.white),
                  style: IconButton.styleFrom(
                    backgroundColor: Colors.black.withOpacity(0.5),
                  ),
                ),

                const SizedBox(height: 350),

                // Title
                Text(
                  _movie!.title,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 36,
                    fontWeight: FontWeight.bold,
                    height: 1.2,
                  ),
                ),
                const SizedBox(height: 16),

                // Metadata
                Wrap(
                  spacing: 12,
                  runSpacing: 8,
                  children: [
                    if (_movie!.voteAverage != null)
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.yellow.shade700.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              Icons.star,
                              color: Colors.yellow.shade700,
                              size: 16,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              _movie!.voteAverage!.toStringAsFixed(1),
                              style: TextStyle(
                                color: Colors.yellow.shade700,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      ),
                    if (_movie!.releaseDate != null &&
                        _movie!.releaseDate!.length >= 4)
                      _buildMetadataChip(
                        Icons.calendar_today,
                        _movie!.releaseDate!.substring(0, 4),
                      ),
                    if (_movie!.runtime != null)
                      _buildMetadataChip(
                        Icons.access_time,
                        '${_movie!.runtime! ~/ 60}h ${_movie!.runtime! % 60}m',
                      ),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 6,
                      ),
                      decoration: BoxDecoration(
                        border: Border.all(color: Colors.grey.shade400),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        _getStatusText(_movie!.status),
                        style: TextStyle(
                          color: Colors.grey.shade300,
                          fontSize: 12,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                // Genres
                if (_movie!.genres != null && _movie!.genres!.isNotEmpty)
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: _movie!.genres!.map((genre) {
                      return Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.grey.shade800.withOpacity(0.8),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          genre.name,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 12,
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                const SizedBox(height: 16),

                // Overview
                if (_movie!.overview != null && _movie!.overview!.isNotEmpty)
                  Text(
                    _movie!.overview!,
                    maxLines: 3,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      color: Colors.grey.shade300,
                      fontSize: 16,
                      height: 1.4,
                    ),
                  ),
                const SizedBox(height: 24),

                // Action Buttons
                Wrap(
                  spacing: 12,
                  runSpacing: 12,
                  children: [
                    if (_embeds.isNotEmpty)
                      ElevatedButton.icon(
                        onPressed: _handleWatchNow,
                        icon: const Icon(Icons.play_arrow, color: Colors.white),
                        label: const Text(
                          'Watch Now',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.red,
                          padding: const EdgeInsets.symmetric(
                            horizontal: 24,
                            vertical: 12,
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                        ),
                      ),
                    if (_downloads.isNotEmpty)
                      ElevatedButton.icon(
                        onPressed: () => setState(() => _activeTab = 'download'),
                        icon: const Icon(Icons.download, color: Colors.white),
                        label: const Text(
                          'Download',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.grey.shade800,
                          padding: const EdgeInsets.symmetric(
                            horizontal: 24,
                            vertical: 12,
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                        ),
                      ),
                    ElevatedButton.icon(
                      onPressed: () => setState(() => _activeTab = 'details'),
                      icon: const Icon(Icons.info_outline, color: Colors.white),
                      label: const Text(
                        'More Info',
                        style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.grey.shade800,
                        padding: const EdgeInsets.symmetric(
                          horizontal: 24,
                          vertical: 12,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                    ),
                    ElevatedButton.icon(
                      onPressed: () {
                        Navigator.pushNamed(
                          context,
                          '/request',
                          arguments: {
                            'type': 'movie',
                            'tmdbId': _movie!.tmdbId?.toString(),
                            'year': _movie!.releaseDate != null &&
                                    _movie!.releaseDate!.length >= 4
                                ? _movie!.releaseDate!.substring(0, 4)
                                : null,
                          },
                        );
                      },
                      icon: const Icon(Icons.request_quote, color: Colors.white),
                      label: const Text(
                        'Request',
                        style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.blue.shade600,
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
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildMetadataChip(IconData icon, String text) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, color: Colors.white, size: 16),
        const SizedBox(width: 4),
        Text(
          text,
          style: const TextStyle(color: Colors.white, fontSize: 14),
        ),
      ],
    );
  }

  Widget _buildWatchTab() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Watch ${_movie!.title}',
          style: const TextStyle(
            color: Colors.white,
            fontSize: 24,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 16),
        // Mobile Rotation Notice
        LayoutBuilder(
          builder: (context, constraints) {
            // Show on mobile devices (screens smaller than tablet)
            if (constraints.maxWidth < 768) {
              return Container(
                margin: const EdgeInsets.only(bottom: 16),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.blue.shade900.withOpacity(0.3),
                  border: Border.all(color: Colors.blue.shade600.withOpacity(0.5)),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.screen_rotation,
                      color: Colors.blue.shade300,
                      size: 20,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'For better experience, rotate your mobile device to landscape mode',
                        style: TextStyle(
                          color: Colors.blue.shade300,
                          fontSize: 13,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                  ],
                ),
              );
            }
            return const SizedBox.shrink();
          },
        ),
        if (_embeds.isNotEmpty) ...[
          // Notice if any embed requires ad
          if (_embeds.any((embed) => embed.requiresAd == true))
            Container(
              margin: const EdgeInsets.only(bottom: 16),
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.orange.shade900.withOpacity(0.3),
                border: Border.all(color: Colors.orange.shade600.withOpacity(0.5)),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                children: [
                  Icon(
                    Icons.info_outline,
                    color: Colors.orange.shade300,
                    size: 20,
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Some servers require watching an ad before playback',
                      style: TextStyle(
                        color: Colors.orange.shade300,
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          // Server Selection
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              ..._embeds.asMap().entries.map((entry) {
                final index = entry.key;
                final embed = entry.value;
                final isActive = _activeEmbed?.id == embed.id;
                final requiresAd = embed.requiresAd == true;
                
                // Debug logging
                debugPrint('[Movie Detail] Server ${index + 1} - requiresAd: $requiresAd, embedId: ${embed.id}');
                
                return ElevatedButton(
                  onPressed: () {
                    debugPrint('[Movie Detail] Server ${index + 1} clicked - requiresAd: $requiresAd');
                    
                    // Check if this embed requires an ad
                    if (requiresAd) {
                      debugPrint('[Movie Detail] Showing interstitial ad before playing server ${index + 1}');
                      // Show interstitial ad before switching to this embed
                      AdService.loadAndShowInterstitialAd(
                        onAdDismissed: () {
                          debugPrint('[Movie Detail] Ad dismissed, setting active embed');
                          setState(() {
                            _activeEmbed = embed;
                            _activeEmbedData = _processEmbedUrl(embed);
                          });
                        },
                      );
                    } else {
                      debugPrint('[Movie Detail] No ad required, setting active embed immediately');
                      // No ad required, switch immediately
                      setState(() {
                        _activeEmbed = embed;
                        _activeEmbedData = _processEmbedUrl(embed);
                      });
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: isActive ? Colors.red : Colors.grey.shade800,
                    foregroundColor: isActive ? Colors.white : Colors.grey.shade300,
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        'Server ${index + 1}',
                        style: const TextStyle(fontWeight: FontWeight.w500),
                      ),
                      if (requiresAd) ...[
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.orange.shade700,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                Icons.ads_click,
                                size: 12,
                                color: Colors.white,
                              ),
                              const SizedBox(width: 3),
                              Text(
                                'Ad',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ],
                  ),
                );
              }).toList(),
              // Report Problem Button
              if (_activeEmbed != null)
                ElevatedButton.icon(
                  onPressed: () {
                    setState(() => _showReportModal = true);
                  },
                  icon: const Icon(Icons.report_problem, size: 16),
                  label: const Text('Report Problem'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.yellow.shade700,
                    foregroundColor: Colors.white,
                  ),
                ),
            ],
          ),
          const SizedBox(height: 16),
          // Banner Ad - Centered
          if (_isBannerAdLoaded && _bannerAd != null)
            Center(
              child: Container(
                margin: const EdgeInsets.symmetric(vertical: 16),
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.grey[900],
                  borderRadius: BorderRadius.circular(8),
                ),
                child: SizedBox(
                  width: _bannerAd!.size.width.toDouble(),
                  height: _bannerAd!.size.height.toDouble(),
                  child: AdWidget(ad: _bannerAd!),
                ),
              ),
            ),
          // Video Player - Responsive & Full View
          if (_activeEmbed != null && _activeEmbedData != null)
            _buildVideoPlayer(),
        ] else
          Container(
            padding: const EdgeInsets.all(32),
            decoration: BoxDecoration(
              color: Colors.grey.shade900,
              border: Border.all(color: Colors.grey.shade800),
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Center(
              child: Text(
                'No streaming links available for this movie.',
                style: TextStyle(color: Colors.grey),
                textAlign: TextAlign.center,
              ),
            ),
          ),
      ],
    );
  }

  Widget _buildVideoPlayer() {
    final embedData = _activeEmbedData!;
    
    if (embedData.url.isEmpty) {
                return Container(
        padding: const EdgeInsets.all(32),
        decoration: BoxDecoration(
          color: Colors.grey.shade900,
          border: Border.all(color: Colors.grey.shade800),
          borderRadius: BorderRadius.circular(8),
        ),
        child: const Center(
          child: Text(
            'Invalid embed URL. Please try another server.',
            style: TextStyle(color: Colors.grey),
            textAlign: TextAlign.center,
          ),
        ),
      );
    }

    // If custom styling is detected, use custom container
    if (embedData.hasCustomStyling) {
      return LayoutBuilder(
        builder: (context, constraints) {
          Widget iframeWidget = _buildIframe(
            embedData.url,
            divStyle: embedData.divStyle,
            iframeStyle: embedData.iframeStyle,
            iframeAttributes: embedData.iframeAttributes,
          );

          // Apply div wrapper if divStyle exists
          if (embedData.divStyle != null) {
            // Parse div style to extract dimensions
            final divStyleStr = embedData.divStyle!;
            double? height;
            
            // Extract height (e.g., "height:270px")
            final heightMatch = RegExp(r'height\s*:\s*(\d+(?:\.\d+)?)px').firstMatch(divStyleStr);
            if (heightMatch != null) {
              height = double.tryParse(heightMatch.group(1) ?? '');
            }

            Widget container = iframeWidget;
            
            // Apply custom dimensions - always use 100% width
            if (height != null) {
              container = SizedBox(
                width: constraints.maxWidth, // Always 100% width
                height: height,
                child: iframeWidget,
              );
            } else {
              // If no height, still use full width
              container = SizedBox(
                width: constraints.maxWidth,
                child: iframeWidget,
              );
            }

            // Wrap in Container with custom styling
            return Container(
                  decoration: BoxDecoration(
                    color: Colors.black,
                    borderRadius: BorderRadius.circular(8),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.3),
                        blurRadius: 10,
                        spreadRadius: 2,
                      ),
                    ],
                  ),
                  child: Stack(
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(8),
                    child: container,
                      ),
                      // Fullscreen button overlay (bottom-right)
                      Positioned(
                        bottom: 8,
                        right: 8,
                        child: Material(
                          color: Colors.transparent,
                          child: InkWell(
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => FullscreenPlayer(
                                url: embedData.url,
                                    title: _movie!.title,
                                  ),
                                ),
                              );
                            },
                            child: Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                color: Colors.black.withOpacity(0.7),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: const Icon(
                                Icons.fullscreen,
                                color: Colors.white,
                                size: 24,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                );
          }

          // No div wrapper, use standard responsive player
          return _buildStandardVideoPlayer(embedData);
        },
      );
    }

    // Standard player without custom styling
    return _buildStandardVideoPlayer(embedData);
  }

  Widget _buildStandardVideoPlayer(EmbedData embedData) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final width = constraints.maxWidth;
        final height = width * 9 / 16; // 16:9 aspect ratio
        
        return Container(
          width: width,
          height: height,
            decoration: BoxDecoration(
            color: Colors.black,
              borderRadius: BorderRadius.circular(8),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.3),
                blurRadius: 10,
                spreadRadius: 2,
              ),
            ],
            ),
          child: Stack(
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: _buildIframe(
                  embedData.url,
                  iframeAttributes: embedData.iframeAttributes,
                ),
              ),
              // Fullscreen button overlay (bottom-right)
              Positioned(
                bottom: 8,
                right: 8,
                child: Material(
                  color: Colors.transparent,
                  child: InkWell(
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => FullscreenPlayer(
                            url: embedData.url,
                            title: _movie!.title,
                          ),
                        ),
                      );
                    },
                    child: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: Colors.black.withOpacity(0.7),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: const Icon(
                        Icons.fullscreen,
                        color: Colors.white,
                        size: 24,
                      ),
                    ),
              ),
            ),
          ),
      ],
          ),
        );
      },
    );
  }

  Widget _buildIframe(
    String url, {
    String? divStyle,
    String? iframeStyle,
    Map<String, String> iframeAttributes = const {},
  }) {
    // Use iframe player for both web and mobile
    // Supports all embed servers including mdy48tn97.com and others
    return IframePlayer(
      url: url,
      iframeStyle: iframeStyle,
      iframeAttributes: iframeAttributes,
    );
  }

  Widget _buildDownloadTab() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Download ${_movie!.title}',
          style: const TextStyle(
            color: Colors.white,
            fontSize: 24,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 16),
        if (_downloads.isNotEmpty)
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 3,
              childAspectRatio: 1.5,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
            ),
            itemCount: _downloads.length,
            itemBuilder: (context, index) {
              final download = _downloads[index];
              return InkWell(
                onTap: () => _launchUrl(download.downloadUrl),
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade900,
                    border: Border.all(color: Colors.grey.shade800),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(
                              download.quality ?? 'HD',
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          const Icon(Icons.download, color: Colors.red, size: 20),
                        ],
                      ),
                      if (download.size != null)
                        Text(
                          'Size: ${download.size}',
                          style: TextStyle(
                            color: Colors.grey.shade400,
                            fontSize: 12,
                          ),
                        ),
                      if (download.serverName.isNotEmpty)
                        Text(
                          download.serverName,
                          style: TextStyle(
                            color: Colors.grey.shade400,
                            fontSize: 12,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      const Text(
                        'Click to download',
                        style: TextStyle(
                          color: Colors.red,
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                ),
              );
            },
          )
        else
          Container(
            padding: const EdgeInsets.all(32),
            decoration: BoxDecoration(
              color: Colors.grey.shade900,
              border: Border.all(color: Colors.grey.shade800),
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Center(
              child: Text(
                'No download links available for this movie.',
                style: TextStyle(color: Colors.grey),
                textAlign: TextAlign.center,
              ),
            ),
          ),
      ],
    );
  }

  Widget _buildDetailsTab() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Movie Details',
          style: TextStyle(
            color: Colors.white,
            fontSize: 24,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 16),
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Left Column
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Overview
                  const Text(
                    'Overview',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    _movie!.overview ?? 'No description available.',
                    style: TextStyle(
                      color: Colors.grey.shade300,
                      height: 1.5,
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Category
                  if (_movie!.category != null) ...[
                    const Row(
                      children: [
                        Icon(Icons.category, color: Colors.white, size: 20),
                        SizedBox(width: 8),
                        Text(
                          'Category',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 6,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.grey.shade800,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                        _movie!.category!.name,
                            style: TextStyle(color: Colors.grey.shade300),
                          ),
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(width: 24),

            // Right Column - Information Panel
            Expanded(
              child: Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.grey.shade900,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Information',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 16),
                    if (_movie!.releaseDate != null)
                      _buildInfoRow(
                        'Release Date',
                        _formatDate(_movie!.releaseDate!),
                      ),
                    if (_movie!.runtime != null)
                      _buildInfoRow(
                        'Runtime',
                        '${_movie!.runtime! ~/ 60}h ${_movie!.runtime! % 60}m',
                      ),
                    if (_movie!.voteAverage != null)
                      _buildInfoRow(
                        'Rating',
                        '${_movie!.voteAverage!.toStringAsFixed(1)} / 10',
                      ),
                    if (_movie!.voteCount != null)
                      _buildInfoRow(
                        'Votes',
                        _movie!.voteCount!.toStringAsFixed(0),
                      ),
                    if (_movie!.popularity != null)
                      _buildInfoRow(
                        'Popularity',
                        _movie!.popularity!.toStringAsFixed(1),
                      ),
                    _buildInfoRow(
                      'Status',
                      _getStatusText(_movie!.status),
                      valueColor: _isStatusActive(_movie!.status)
                          ? Colors.green.shade400
                          : Colors.yellow.shade400,
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildInfoRow(String label, String value, {Color? valueColor}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                flex: 2,
                child: Text(
                  label,
                  style: TextStyle(color: Colors.grey.shade400),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                flex: 3,
                child: Text(
                  value,
                  textAlign: TextAlign.right,
                  maxLines: 3,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    color: valueColor ?? Colors.white,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Divider(color: Colors.grey.shade800, height: 1),
        ],
      ),
    );
  }

  Widget _buildSimilarMovies() {
    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'More Like This',
            style: TextStyle(
              color: Colors.white,
              fontSize: 24,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          SizedBox(
            height: 220,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: _similarMovies.length,
              itemBuilder: (context, index) {
                return MovieCard(
                  movie: _similarMovies[index],
                  onTap: () {
                    Navigator.pushReplacement(
                      context,
                      MaterialPageRoute(
                        builder: (context) => MovieDetailPage(
                          movieId: _similarMovies[index].id,
                        ),
                      ),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  String _formatDate(String dateString) {
    try {
      final date = DateTime.parse(dateString);
      return '${date.day}/${date.month}/${date.year}';
    } catch (e) {
      return dateString;
    }
  }

  // Request Content Modal
  Widget _buildRequestModal() {
    return GestureDetector(
      onTap: () => setState(() => _showRequestModal = false),
      child: Container(
        color: Colors.black.withOpacity(0.75),
        child: Center(
          child: GestureDetector(
            onTap: () {}, // Prevent closing when tapping inside
            child: Container(
              margin: const EdgeInsets.all(16),
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: Colors.grey.shade900,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Request Content',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      IconButton(
                        onPressed: () => setState(() => _showRequestModal = false),
                        icon: const Icon(Icons.close, color: Colors.white),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<String>(
                    value: _requestTypeController.text,
                    decoration: InputDecoration(
                      labelText: 'Content Type',
                      labelStyle: const TextStyle(color: Colors.grey),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                      filled: true,
                      fillColor: Colors.grey.shade800,
                    ),
                    dropdownColor: Colors.grey.shade800,
                    style: const TextStyle(color: Colors.white),
                    items: const [
                      DropdownMenuItem(value: 'movie', child: Text('Movie')),
                      DropdownMenuItem(value: 'tvshow', child: Text('TV Show')),
                    ],
                    onChanged: (value) {
                      if (value != null) {
                        _requestTypeController.text = value;
                      }
                    },
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: _requestTitleController,
                    decoration: InputDecoration(
                      labelText: 'Title *',
                      labelStyle: const TextStyle(color: Colors.grey),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                      filled: true,
                      fillColor: Colors.grey.shade800,
                    ),
                    style: const TextStyle(color: Colors.white),
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: _requestEmailController,
                    keyboardType: TextInputType.emailAddress,
                    decoration: InputDecoration(
                      labelText: 'Email *',
                      labelStyle: const TextStyle(color: Colors.grey),
                      hintText: 'your@email.com',
                      hintStyle: TextStyle(color: Colors.grey.shade500),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                      filled: true,
                      fillColor: Colors.grey.shade800,
                    ),
                    style: const TextStyle(color: Colors.white),
                  ),
                  Padding(
                    padding: const EdgeInsets.only(top: 4.0, bottom: 16.0),
                    child: Text(
                      "We'll notify you when your request is processed.",
                      style: TextStyle(
                        color: Colors.grey.shade500,
                        fontSize: 12,
                      ),
                    ),
                  ),
                  TextField(
                    controller: _requestDescriptionController,
                    decoration: InputDecoration(
                      labelText: 'Description (Optional)',
                      labelStyle: const TextStyle(color: Colors.grey),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                      filled: true,
                      fillColor: Colors.grey.shade800,
                    ),
                    style: const TextStyle(color: Colors.white),
                    maxLines: 3,
                  ),
                  const SizedBox(height: 24),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      TextButton(
                        onPressed: () => setState(() => _showRequestModal = false),
                        child: const Text('Cancel'),
                      ),
                      const SizedBox(width: 8),
                      ElevatedButton(
                        onPressed: () async {
                          if (_requestTitleController.text.isEmpty) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Please enter a title')),
                            );
                            return;
                          }

                          if (_requestEmailController.text.isEmpty || !_requestEmailController.text.contains('@')) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Please enter a valid email address')),
                            );
                            return;
                          }

                          final result = await ApiService.submitContentRequest(
                            type: _requestTypeController.text,
                            title: _requestTitleController.text,
                            email: _requestEmailController.text,
                            description: _requestDescriptionController.text.isEmpty
                                ? null
                                : _requestDescriptionController.text,
                            tmdbId: _movie!.tmdbId?.toString(),
                            year: _movie!.releaseDate != null &&
                                    _movie!.releaseDate!.length >= 4
                                ? _movie!.releaseDate!.substring(0, 4)
                                : null,
                          );

                          if (mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(result['message'] ?? 'Request submitted'),
                                backgroundColor: result['success'] == true
                                    ? Colors.green
                                    : Colors.red,
                              ),
                            );

                            if (result['success'] == true) {
                              setState(() {
                                _showRequestModal = false;
                                _requestTitleController.clear();
                                _requestEmailController.clear();
                                _requestDescriptionController.clear();
                              });
                            }
                          }
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.blue.shade600,
                        ),
                        child: const Text('Submit Request'),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  // Report Embed Modal
  Widget _buildReportModal() {
    return GestureDetector(
      onTap: () => setState(() => _showReportModal = false),
      child: Container(
        color: Colors.black.withOpacity(0.75),
        child: Center(
          child: GestureDetector(
            onTap: () {}, // Prevent closing when tapping inside
            child: Container(
              margin: const EdgeInsets.all(16),
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: Colors.grey.shade900,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Report Embed Problem',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      IconButton(
                        onPressed: () => setState(() => _showReportModal = false),
                        icon: const Icon(Icons.close, color: Colors.white),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: _reportEmailController,
                    keyboardType: TextInputType.emailAddress,
                    decoration: InputDecoration(
                      labelText: 'Email *',
                      labelStyle: const TextStyle(color: Colors.grey),
                      hintText: 'your@email.com',
                      hintStyle: TextStyle(color: Colors.grey.shade500),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                      filled: true,
                      fillColor: Colors.grey.shade800,
                    ),
                    style: const TextStyle(color: Colors.white),
                  ),
                  Padding(
                    padding: const EdgeInsets.only(top: 4.0, bottom: 16.0),
                    child: Text(
                      "We'll notify you when your report is processed.",
                      style: TextStyle(
                        color: Colors.grey.shade500,
                        fontSize: 12,
                      ),
                    ),
                  ),
                  DropdownButtonFormField<String>(
                    value: _selectedReportType,
                    decoration: InputDecoration(
                      labelText: 'Problem Type',
                      labelStyle: const TextStyle(color: Colors.grey),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                      filled: true,
                      fillColor: Colors.grey.shade800,
                    ),
                    dropdownColor: Colors.grey.shade800,
                    style: const TextStyle(color: Colors.white),
                    items: const [
                      DropdownMenuItem(
                        value: 'not_working',
                        child: Text('Not Working'),
                      ),
                      DropdownMenuItem(
                        value: 'wrong_content',
                        child: Text('Wrong Content'),
                      ),
                      DropdownMenuItem(
                        value: 'poor_quality',
                        child: Text('Poor Quality'),
                      ),
                      DropdownMenuItem(
                        value: 'broken_link',
                        child: Text('Broken Link'),
                      ),
                      DropdownMenuItem(
                        value: 'other',
                        child: Text('Other'),
                      ),
                    ],
                    onChanged: (value) {
                      if (value != null) {
                        setState(() => _selectedReportType = value);
                      }
                    },
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: _reportDescriptionController,
                    decoration: InputDecoration(
                      labelText: 'Description (Optional)',
                      labelStyle: const TextStyle(color: Colors.grey),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                      filled: true,
                      fillColor: Colors.grey.shade800,
                    ),
                    style: const TextStyle(color: Colors.white),
                    maxLines: 3,
                  ),
                  const SizedBox(height: 24),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      TextButton(
                        onPressed: () => setState(() => _showReportModal = false),
                        child: const Text('Cancel'),
                      ),
                      const SizedBox(width: 8),
                      ElevatedButton(
                        onPressed: () async {
                          if (_activeEmbed == null) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('No embed selected')),
                            );
                            return;
                          }

                          if (_reportEmailController.text.isEmpty || !_reportEmailController.text.contains('@')) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Please enter a valid email address')),
                            );
                            return;
                          }

                          final result = await ApiService.submitEmbedReport(
                            contentType: 'movie',
                            contentId: widget.movieId,
                            embedId: _activeEmbed!.id,
                            reportType: _selectedReportType,
                            email: _reportEmailController.text.trim(),
                            description: _reportDescriptionController.text.isEmpty
                                ? null
                                : _reportDescriptionController.text,
                          );

                          if (mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(result['message'] ?? 'Report submitted'),
                                backgroundColor: result['success'] == true
                                    ? Colors.green
                                    : Colors.red,
                              ),
                            );

                            if (result['success'] == true) {
                              setState(() {
                                _showReportModal = false;
                                _reportDescriptionController.clear();
                                _reportEmailController.clear();
                                _selectedReportType = 'not_working';
                              });
                            }
                          }
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.red.shade600,
                        ),
                        child: const Text('Submit Report'),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildCommentsSection() {
    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Comments',
            style: TextStyle(
              color: Colors.white,
              fontSize: 24,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          
          // Comment Form
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.grey.shade900,
              borderRadius: BorderRadius.circular(8),
            ),
            child: Form(
              key: _commentFormKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Leave a Comment',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 16),
                  
                  // Replying indicator
                  if (_replyingToCommentId != null && _replyingToCommentName != null)
                    Container(
                      padding: const EdgeInsets.all(12),
                      margin: const EdgeInsets.only(bottom: 16),
                      decoration: BoxDecoration(
                        color: Colors.blue.shade900.withOpacity(0.3),
                        border: Border.all(color: Colors.blue.shade600),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: Text(
                              'Replying to ${_replyingToCommentName}',
                              style: TextStyle(
                                color: Colors.blue.shade300,
                                fontSize: 14,
                              ),
                            ),
                          ),
                          TextButton(
                            onPressed: _cancelReply,
                            child: const Text('Cancel'),
                          ),
                        ],
                      ),
                    ),
                  
                  // Success message
                  if (_showCommentSuccess)
                    Container(
                      padding: const EdgeInsets.all(12),
                      margin: const EdgeInsets.only(bottom: 16),
                      decoration: BoxDecoration(
                        color: Colors.green.shade900.withOpacity(0.3),
                        border: Border.all(color: Colors.green.shade600),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        children: [
                          Icon(Icons.check_circle, color: Colors.green.shade300, size: 20),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              'Your comment has been submitted and is under review. It will be visible once approved by an administrator.',
                              style: TextStyle(
                                color: Colors.green.shade300,
                                fontSize: 13,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  
                  // Name and Email fields
                  Row(
                    children: [
                      Expanded(
                        child: TextFormField(
                          controller: _commentNameController,
                          decoration: InputDecoration(
                            labelText: 'Name *',
                            labelStyle: const TextStyle(color: Colors.grey),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                            ),
                            filled: true,
                            fillColor: Colors.grey.shade800,
                          ),
                          style: const TextStyle(color: Colors.white),
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) {
                              return 'Please enter your name';
                            }
                            return null;
                          },
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: TextFormField(
                          controller: _commentEmailController,
                          decoration: InputDecoration(
                            labelText: 'Email *',
                            labelStyle: const TextStyle(color: Colors.grey),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                            ),
                            filled: true,
                            fillColor: Colors.grey.shade800,
                          ),
                          style: const TextStyle(color: Colors.white),
                          keyboardType: TextInputType.emailAddress,
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) {
                              return 'Please enter your email';
                            }
                            if (!value.contains('@')) {
                              return 'Please enter a valid email';
                            }
                            return null;
                          },
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  
                  // Comment text field
                  TextFormField(
                    controller: _commentTextController,
                    decoration: InputDecoration(
                      labelText: 'Comment *',
                      labelStyle: const TextStyle(color: Colors.grey),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                      filled: true,
                      fillColor: Colors.grey.shade800,
                    ),
                    style: const TextStyle(color: Colors.white),
                    maxLines: 4,
                    validator: (value) {
                      if (value == null || value.trim().isEmpty) {
                        return 'Please enter a comment';
                      }
                      if (value.trim().length < 3) {
                        return 'Comment must be at least 3 characters';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),
                  
                  // Submit button
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: _submittingComment ? null : _submitComment,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.red,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                      ),
                      child: _submittingComment
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                              ),
                            )
                          : const Text(
                              'Post Comment',
                              style: TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 24),
          
          // Comments List
          if (_loadingComments)
            const Center(
              child: Padding(
                padding: EdgeInsets.all(32.0),
                child: CircularProgressIndicator(
                  valueColor: AlwaysStoppedAnimation<Color>(Colors.red),
                ),
              ),
            )
          else if (_comments.isEmpty)
            Center(
              child: Padding(
                padding: const EdgeInsets.all(32.0),
                child: Column(
                  children: [
                    Icon(Icons.comment_outlined, color: Colors.grey.shade600, size: 64),
                    const SizedBox(height: 16),
                    Text(
                      'No comments yet. Be the first to comment!',
                      style: TextStyle(
                        color: Colors.grey.shade400,
                        fontSize: 16,
                      ),
                    ),
                  ],
                ),
              ),
            )
          else
            ..._comments
                .where((comment) => comment.parentId == null)
                .map((comment) => _buildCommentItem(comment)),
        ],
      ),
    );
  }

  Widget _buildCommentItem(Comment comment) {
    final isReply = comment.parentId != null;
    final isPending = comment.status != 'approved';
    
    return Container(
      margin: EdgeInsets.only(
        bottom: 12,
        left: isReply ? 48 : 0,
      ),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.grey.shade900,
        borderRadius: BorderRadius.circular(8),
        border: isReply
            ? Border(
                left: BorderSide(color: Colors.red, width: 3),
              )
            : null,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Comment header
          Row(
            children: [
              Expanded(
                child: Row(
                  children: [
                    Text(
                      comment.name,
                      style: TextStyle(
                        color: isPending ? Colors.grey.shade400 : Colors.white,
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                    if (comment.isAdminReply) ...[
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.red,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: const Text(
                          'Admin',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                    if (isPending) ...[
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.orange.shade700,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: const Text(
                          'Pending',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              Text(
                _formatCommentDate(comment.createdAt),
                style: TextStyle(
                  color: Colors.grey.shade500,
                  fontSize: 12,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          
          // Comment content
          Text(
            comment.comment,
            style: TextStyle(
              color: isPending ? Colors.grey.shade500 : Colors.grey.shade300,
              fontSize: 14,
              height: 1.5,
              fontStyle: isPending ? FontStyle.italic : FontStyle.normal,
            ),
          ),
          const SizedBox(height: 12),
          
          // Reply button
          TextButton.icon(
            onPressed: () => _startReply(comment.id, comment.name),
            icon: const Icon(Icons.reply, size: 16),
            label: const Text('Reply'),
            style: TextButton.styleFrom(
              foregroundColor: Colors.blue.shade300,
              padding: const EdgeInsets.symmetric(horizontal: 8),
            ),
          ),
          
          // Replies
          if (comment.replies != null && comment.replies!.isNotEmpty)
            ...comment.replies!
                .map((reply) => _buildCommentItem(reply)),
        ],
      ),
    );
  }

  String _formatCommentDate(String dateString) {
    try {
      final date = DateTime.parse(dateString);
      final now = DateTime.now();
      final difference = now.difference(date);
      
      if (difference.inDays > 7) {
        return '${date.day}/${date.month}/${date.year}';
      } else if (difference.inDays > 0) {
        return '${difference.inDays}d ago';
      } else if (difference.inHours > 0) {
        return '${difference.inHours}h ago';
      } else if (difference.inMinutes > 0) {
        return '${difference.inMinutes}m ago';
      } else {
        return 'Just now';
      }
    } catch (e) {
      return dateString;
    }
  }

  bool _isStatusActive(String? status) {
    if (status == null || status.isEmpty) return true; // Default to active if not set
    return status.toLowerCase() == 'active';
  }

  String _getStatusText(String? status) {
    if (status == null || status.isEmpty) return 'Available'; // Default to Available
    return _isStatusActive(status) ? 'Available' : 'Coming Soon';
  }
}

