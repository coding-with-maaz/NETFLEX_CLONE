import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:google_mobile_ads/google_mobile_ads.dart';
import '../models/tvshow.dart';
import '../models/season.dart';
import '../models/episode.dart';
import '../models/comment.dart';
import '../services/api_service.dart';
import '../services/ad_service.dart';
import '../services/watched_content_service.dart';
import '../widgets/tvshow_card.dart';
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

class TVShowDetailPage extends StatefulWidget {
  final int tvShowId;
  final String? tvShowName;

  const TVShowDetailPage({
    Key? key,
    required this.tvShowId,
    this.tvShowName,
  }) : super(key: key);

  @override
  State<TVShowDetailPage> createState() => _TVShowDetailPageState();
}

class _TVShowDetailPageState extends State<TVShowDetailPage> {
  TVShow? _tvShow;
  List<Season> _seasons = [];
  Season? _selectedSeason;
  List<Episode> _episodes = [];
  List<TVShow> _similarShows = [];
  bool _loading = true;
  bool _loadingEpisodes = false;
  String _activeTab = 'episodes'; // episodes, details
  Episode? _expandedEpisode;
  EpisodeEmbed? _activeEmbed;
  EmbedData? _activeEmbedData;
  final ScrollController _scrollController = ScrollController();
  
  // Modals
  bool _showRequestModal = false;
  bool _showReportModal = false;
  
  // Form controllers
  final _requestTitleController = TextEditingController();
  final _requestEmailController = TextEditingController();
  final _requestDescriptionController = TextEditingController();
  final _requestTypeController = TextEditingController(text: 'tvshow');
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

  @override
  void initState() {
    super.initState();
    _fetchTVShowDetails();
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
    await ApiService.trackTVShowView(widget.tvShowId);
  }

  Future<void> _fetchTVShowDetails() async {
    setState(() => _loading = true);

    try {
      // Fetch TV show details
      final tvShow = await ApiService.getTVShowById(widget.tvShowId);
      if (tvShow == null) {
        setState(() => _loading = false);
        return;
      }

      // Save to watched content when TV show details are loaded
      await WatchedContentService.saveWatchedTVShow(tvShow);

      // Fetch seasons
      final seasons = await ApiService.getTVShowSeasons(widget.tvShowId);

      // Auto-select first season and fetch episodes
      Season? selectedSeason;
      List<Episode> episodes = [];
      if (seasons.isNotEmpty) {
        selectedSeason = seasons[0];
        episodes = await ApiService.getSeasonEpisodes(
          widget.tvShowId,
          seasons[0].id,
        );
        
        // Fetch embeds for each episode (similar to show.blade.php)
        debugPrint('[TV Show Details] Fetching embeds for ${episodes.length} episodes');
        for (var episode in episodes) {
          try {
            final embeds = await ApiService.getEpisodeEmbeds(episode.id);
            
            // Debug: Log embeds and their requiresAd status
            debugPrint('[TV Show Details] Episode ${episode.id} (S${episode.seasonNumber}E${episode.episodeNumber}) - Fetched ${embeds.length} embeds');
            for (var embed in embeds) {
              debugPrint('[TV Show Details] Episode ${episode.id} - Embed ID: ${embed.id}, Server: ${embed.serverName}, requiresAd: ${embed.requiresAd}');
            }
            
            // Create a new Episode with embeds attached
            final episodeWithEmbeds = Episode(
              id: episode.id,
              tvShowId: episode.tvShowId,
              seasonId: episode.seasonId,
              episodeNumber: episode.episodeNumber,
              seasonNumber: episode.seasonNumber,
              name: episode.name,
              overview: episode.overview,
              airDate: episode.airDate,
              stillPath: episode.stillPath,
              voteAverage: episode.voteAverage,
              voteCount: episode.voteCount,
              runtime: episode.runtime,
              viewCount: episode.viewCount,
              tvShow: episode.tvShow,
              season: episode.season,
              embeds: embeds.isNotEmpty ? embeds : null,
              downloads: episode.downloads,
            );
            // Replace episode in list
            final index = episodes.indexOf(episode);
            if (index != -1) {
              episodes[index] = episodeWithEmbeds;
            }
          } catch (e) {
            debugPrint('[TV Show Details] Failed to fetch embeds for episode ${episode.id}: $e');
          }
        }
      }

      // Fetch similar TV shows
      List<TVShow> similarShows = [];
      if (tvShow.genres != null && tvShow.genres!.isNotEmpty) {
        final genreId = tvShow.genres![0].id.toString();
        final result = await ApiService.getTVShowsWithParams(
          params: {
            'genre': genreId,
            'limit': '10',
          },
        );
        similarShows = (result['tvShows'] as List<TVShow>?)
                ?.where((s) => s.id != widget.tvShowId)
                .toList() ??
            [];
      }

      setState(() {
        _tvShow = tvShow;
        _seasons = seasons;
        _selectedSeason = selectedSeason;
        _episodes = episodes;
        _similarShows = similarShows;
        _loading = false;
        
        // Auto-expand first episode and auto-select Server 1
        if (episodes.isNotEmpty) {
          final firstEpisode = episodes[0];
          if (firstEpisode.embeds != null && firstEpisode.embeds!.isNotEmpty) {
            final firstEmbed = firstEpisode.embeds![0];
            final requiresAd = firstEmbed.requiresAd == true;
            
            debugPrint('[TV Show Detail] Auto-expanding Episode 1 and selecting Server 1 - requiresAd: $requiresAd, embedId: ${firstEmbed.id}');
            
            // Check if first embed requires an ad
            if (requiresAd) {
              debugPrint('[TV Show Detail] Showing interstitial ad before auto-selecting Server 1');
              // Show interstitial ad before setting active embed
              AdService.loadAndShowInterstitialAd(
                onAdDismissed: () {
                  debugPrint('[TV Show Detail] Ad dismissed, setting active embed (auto-select)');
                  setState(() {
                    _expandedEpisode = firstEpisode;
                    _activeEmbed = firstEmbed;
                    _activeEmbedData = _processEmbedUrl(firstEmbed);
                  });
                },
              );
            } else {
              debugPrint('[TV Show Detail] No ad required, setting active embed immediately (auto-select)');
              // No ad required, set immediately
              _expandedEpisode = firstEpisode;
              _activeEmbed = firstEmbed;
              _activeEmbedData = _processEmbedUrl(firstEmbed);
            }
          } else {
            // No embeds, just expand the episode
            _expandedEpisode = firstEpisode;
          }
        }
      });
      
      // Load comments after TV show is loaded
      _loadComments();
    } catch (e) {
      debugPrint('Error fetching TV show details: $e');
      setState(() => _loading = false);
    }
  }
  
  Future<void> _loadComments() async {
    if (_tvShow == null) return;
    
    setState(() => _loadingComments = true);
    
    try {
      final comments = await ApiService.getComments(
        type: 'tvshow',
        id: widget.tvShowId,
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
    if (_tvShow == null) return;
    
    setState(() => _submittingComment = true);
    _showCommentSuccess = false;
    
    try {
      final result = await ApiService.submitComment(
        type: 'tvshow',
        id: widget.tvShowId,
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

  Future<void> _fetchEpisodes(Season season) async {
    setState(() => _loadingEpisodes = true);

    try {
      final episodes = await ApiService.getSeasonEpisodes(
        widget.tvShowId,
        season.id,
      );
      
      // Fetch embeds for each episode (similar to show.blade.php)
      debugPrint('[TV Show Details] Fetching embeds for ${episodes.length} episodes');
      for (var episode in episodes) {
        try {
          final embeds = await ApiService.getEpisodeEmbeds(episode.id);
          
          // Debug: Log embeds and their requiresAd status
          debugPrint('[TV Show Details] Episode ${episode.id} (S${episode.seasonNumber}E${episode.episodeNumber}) - Fetched ${embeds.length} embeds');
          for (var embed in embeds) {
            debugPrint('[TV Show Details] Episode ${episode.id} - Embed ID: ${embed.id}, Server: ${embed.serverName}, requiresAd: ${embed.requiresAd}');
          }
          
          // Create a new Episode with embeds attached
          final episodeWithEmbeds = Episode(
            id: episode.id,
            tvShowId: episode.tvShowId,
            seasonId: episode.seasonId,
            episodeNumber: episode.episodeNumber,
            seasonNumber: episode.seasonNumber,
            name: episode.name,
            overview: episode.overview,
            airDate: episode.airDate,
            stillPath: episode.stillPath,
            voteAverage: episode.voteAverage,
            voteCount: episode.voteCount,
            runtime: episode.runtime,
            viewCount: episode.viewCount,
            tvShow: episode.tvShow,
            season: episode.season,
            embeds: embeds.isNotEmpty ? embeds : null,
            downloads: episode.downloads,
          );
          // Replace episode in list
          final index = episodes.indexOf(episode);
          if (index != -1) {
            episodes[index] = episodeWithEmbeds;
          }
        } catch (e) {
          debugPrint('[TV Show Details] Failed to fetch embeds for episode ${episode.id}: $e');
        }
      }
      
      setState(() {
        _episodes = episodes;
        _loadingEpisodes = false;
        
        // Auto-expand first episode and auto-select Server 1
        if (episodes.isNotEmpty) {
          final firstEpisode = episodes[0];
          if (firstEpisode.embeds != null && firstEpisode.embeds!.isNotEmpty) {
            final firstEmbed = firstEpisode.embeds![0];
            final requiresAd = firstEmbed.requiresAd == true;
            
            debugPrint('[TV Show Detail] Auto-expanding Episode 1 and selecting Server 1 (season change) - requiresAd: $requiresAd, embedId: ${firstEmbed.id}');
            
            // Check if first embed requires an ad
            if (requiresAd) {
              debugPrint('[TV Show Detail] Showing interstitial ad before auto-selecting Server 1 (season change)');
              // Show interstitial ad before setting active embed
              AdService.loadAndShowInterstitialAd(
                onAdDismissed: () {
                  debugPrint('[TV Show Detail] Ad dismissed, setting active embed (auto-select season change)');
                  setState(() {
                    _expandedEpisode = firstEpisode;
                    _activeEmbed = firstEmbed;
                    _activeEmbedData = _processEmbedUrl(firstEmbed);
                  });
                },
              );
            } else {
              debugPrint('[TV Show Detail] No ad required, setting active embed immediately (auto-select season change)');
              // No ad required, set immediately
              _expandedEpisode = firstEpisode;
              _activeEmbed = firstEmbed;
              _activeEmbedData = _processEmbedUrl(firstEmbed);
            }
          } else {
            // No embeds, just expand the episode
            _expandedEpisode = firstEpisode;
          }
        }
      });
    } catch (e) {
      debugPrint('Error fetching episodes: $e');
      setState(() {
        _episodes = [];
        _loadingEpisodes = false;
      });
    }
  }

  void _handleSeasonChange(Season season) {
      setState(() {
        _selectedSeason = season;
        _expandedEpisode = null;
        _activeEmbed = null;
        _activeEmbedData = null;
      });
      _fetchEpisodes(season);
  }

  void _handleEpisodeClick(Episode episode) {
    setState(() {
      if (_expandedEpisode?.id == episode.id) {
        _expandedEpisode = null;
        _activeEmbed = null;
        _activeEmbedData = null;
      } else {
        _expandedEpisode = episode;
        if (episode.embeds != null && episode.embeds!.isNotEmpty) {
          final firstEmbed = episode.embeds![0];
          final requiresAd = firstEmbed.requiresAd == true;
          
          debugPrint('[TV Show Detail] Episode ${episode.episodeNumber} clicked - Server 1 requiresAd: $requiresAd, embedId: ${firstEmbed.id}');
          
          // Check if first embed requires an ad
          if (requiresAd) {
            debugPrint('[TV Show Detail] Showing interstitial ad before selecting Server 1');
            // Show interstitial ad before setting active embed
            AdService.loadAndShowInterstitialAd(
              onAdDismissed: () {
                debugPrint('[TV Show Detail] Ad dismissed, setting active embed');
                setState(() {
                  _activeEmbed = firstEmbed;
                  _activeEmbedData = _processEmbedUrl(firstEmbed);
                });
              },
            );
          } else {
            debugPrint('[TV Show Detail] No ad required, setting active embed immediately');
            // No ad required, set immediately
            _activeEmbed = firstEmbed;
            _activeEmbedData = _processEmbedUrl(firstEmbed);
          }
        }
      }
    });
  }

  // Process embed URL and extract custom styling (similar to show.blade.php)
  EmbedData _processEmbedUrl(EpisodeEmbed embed) {
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

    // Handle Mixdrop, Dailymotion, and Bilibili - ensure proper embed URLs
    final lowerUrl = processedUrl.toLowerCase();
    if (lowerUrl.contains('mixdrop') || lowerUrl.contains('dailymotion') || lowerUrl.contains('bilibili')) {
      debugPrint('[TV Show Details] Processing Mixdrop/Dailymotion/Bilibili URL: $processedUrl');
      
      // For Mixdrop: ensure it's an embed URL
      if (lowerUrl.contains('mixdrop')) {
        if (processedUrl.contains('/e/') || processedUrl.contains('/f/')) {
          debugPrint('[TV Show Details] Mixdrop embed URL detected');
        } else if (processedUrl.contains('/v/') || processedUrl.contains('/watch/')) {
          final fileIdMatch = RegExp(r'[/]([a-zA-Z0-9]+)$').firstMatch(processedUrl) ??
              RegExp(r'[/]([a-zA-Z0-9]+)\?').firstMatch(processedUrl);
          if (fileIdMatch != null && fileIdMatch.groupCount > 0) {
            final domainMatch = RegExp(r'https?://([^/]+)').firstMatch(processedUrl);
            final domain = domainMatch?.group(1) ?? 'mixdrop.co';
            processedUrl = 'https://$domain/e/${fileIdMatch.group(1)}';
            debugPrint('[TV Show Details] Converted Mixdrop URL to embed: $processedUrl');
          }
        }
      }
      
      // For Dailymotion: ensure it's an embed URL
      if (lowerUrl.contains('dailymotion.com')) {
        if (processedUrl.contains('/embed/')) {
          debugPrint('[TV Show Details] Dailymotion embed URL detected');
        } else if (processedUrl.contains('/video/')) {
          final videoIdMatch = RegExp(r'/video/([a-zA-Z0-9]+)').firstMatch(processedUrl);
          if (videoIdMatch != null && videoIdMatch.groupCount > 0) {
            processedUrl = 'https://www.dailymotion.com/embed/video/${videoIdMatch.group(1)}';
            debugPrint('[TV Show Details] Converted Dailymotion URL to embed: $processedUrl');
          }
        }
      }
      
      // For Bilibili: ensure web version (not mobile/Android)
      if (lowerUrl.contains('bilibili.tv') || lowerUrl.contains('bilibili.com')) {
        debugPrint('[TV Show Details] Processing Bilibili URL: $processedUrl');
        
        String? videoId;
        if (processedUrl.contains('/video/')) {
          final videoIdMatch = RegExp(r'/video/([a-zA-Z0-9]+)').firstMatch(processedUrl);
          if (videoIdMatch != null && videoIdMatch.groupCount > 0) {
            videoId = videoIdMatch.group(1);
          }
        }
        
        try {
          final uri = Uri.parse(processedUrl);
          final queryParams = Map<String, String>.from(uri.queryParameters);
          
          queryParams.remove('platform');
          queryParams.remove('from');
          queryParams.remove('share_source');
          queryParams.remove('share_medium');
          
          queryParams['platform'] = 'web';
          
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
          
          final newUri = Uri(
            scheme: uri.scheme,
            host: hostname,
            path: path,
            queryParameters: queryParams.isNotEmpty ? queryParams : null,
          );
          
          processedUrl = newUri.toString();
          debugPrint('[TV Show Details] Bilibili web URL: $processedUrl');
        } catch (e) {
          if (videoId != null) {
            processedUrl = 'https://www.bilibili.tv/en/video/$videoId?platform=web';
            debugPrint('[TV Show Details] Constructed Bilibili web URL: $processedUrl');
          }
        }
        
        // Auto-apply Bilibili custom styling if not already present
        if (!hasDivWrapper) {
          debugPrint('[TV Show Details] Applying Bilibili custom styling');
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

  Future<void> _handleWatchNow() async {
    setState(() => _activeTab = 'episodes');

    // Fetch episodes if needed
    if (_episodes.isEmpty && _seasons.isNotEmpty) {
      await _fetchEpisodes(_seasons[0]);
    }

    // Scroll to top and expand first episode
    _scrollController.animateTo(
      0,
      duration: const Duration(milliseconds: 500),
      curve: Curves.easeInOut,
    );

    if (_episodes.isNotEmpty) {
      final firstEpisode = _episodes[0];
      if (firstEpisode.embeds != null && firstEpisode.embeds!.isNotEmpty) {
        final firstEmbed = firstEpisode.embeds![0];
        // Check if first embed requires an ad
        if (firstEmbed.requiresAd == true) {
          // Show interstitial ad before setting active embed
          AdService.loadAndShowInterstitialAd(
            onAdDismissed: () {
      setState(() {
        _expandedEpisode = firstEpisode;
                _activeEmbed = firstEmbed;
                _activeEmbedData = _processEmbedUrl(firstEmbed);
              });
            },
          );
        } else {
          // No ad required, set immediately
          setState(() {
            _expandedEpisode = firstEpisode;
            _activeEmbed = firstEmbed;
            _activeEmbedData = _processEmbedUrl(firstEmbed);
          });
        }
      } else {
        setState(() {
          _expandedEpisode = firstEpisode;
        });
      }
    }
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

    if (_tvShow == null) {
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
                'TV Show not found',
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
                child: const Text('Back to TV Shows'),
              ),
            ],
          ),
        ),
      );
    }

    final backdropUrl = _tvShow!.getBackdropUrl('w1280');

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
                      // Episodes Tab
                      if (_activeTab == 'episodes') _buildEpisodesTab(),

                      // Details Tab
                      if (_activeTab == 'details') _buildDetailsTab(),
                    ],
                  ),
                ),

                // Similar TV Shows
                if (_similarShows.isNotEmpty) _buildSimilarShows(),
                
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
                  _tvShow!.name,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 36,
                    fontWeight: FontWeight.bold,
                    height: 1.2,
                  ),
                ),
                
                // Original Title
                if (_tvShow!.originalName != null && 
                    _tvShow!.originalName != _tvShow!.name)
                  Padding(
                    padding: const EdgeInsets.only(top: 8.0),
                    child: Text(
                      _tvShow!.originalName!,
                      style: const TextStyle(
                        color: Colors.grey,
                        fontSize: 18,
                        height: 1.2,
                      ),
                    ),
                  ),
                
                const SizedBox(height: 16),

                // Metadata
                Wrap(
                  spacing: 12,
                  runSpacing: 8,
                  children: [
                    if (_tvShow!.voteAverage != null)
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
                              _tvShow!.voteAverage!.toStringAsFixed(1),
                              style: TextStyle(
                                color: Colors.yellow.shade700,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      ),
                    if (_tvShow!.firstAirDate != null &&
                        _tvShow!.firstAirDate!.length >= 4)
                      _buildMetadataChip(
                        Icons.calendar_today,
                        _tvShow!.firstAirDate!.substring(0, 4),
                      ),
                    if (_tvShow!.numberOfSeasons != null)
                      _buildMetadataChip(
                        Icons.tv,
                        '${_tvShow!.numberOfSeasons} Season${_tvShow!.numberOfSeasons! > 1 ? 's' : ''}',
                      ),
                    if (_tvShow!.numberOfEpisodes != null)
                      _buildMetadataChip(
                        Icons.video_library,
                        '${_tvShow!.numberOfEpisodes} Episodes',
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
                        _getStatusText(_tvShow!.status),
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
                if (_tvShow!.genres != null && _tvShow!.genres!.isNotEmpty)
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: _tvShow!.genres!.map((genre) {
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
                if (_tvShow!.overview != null && _tvShow!.overview!.isNotEmpty)
                  Text(
                    _tvShow!.overview!,
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
                        backgroundColor: _activeTab == 'details'
                            ? Colors.red
                            : Colors.grey.shade800,
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
                            'type': 'tvshow',
                            'tmdbId': _tvShow!.tmdbId?.toString(),
                            'year': _tvShow!.firstAirDate != null &&
                                    _tvShow!.firstAirDate!.length >= 4
                                ? _tvShow!.firstAirDate!.substring(0, 4)
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

  Widget _buildEpisodesTab() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Header with season selector
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Episodes',
              style: TextStyle(
                color: Colors.white,
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
            ),
            // Season Selector
            if (_seasons.isNotEmpty)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12),
                decoration: BoxDecoration(
                  color: Colors.grey.shade800,
                  border: Border.all(color: Colors.grey.shade700),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: DropdownButton<int>(
                  value: _selectedSeason?.id,
                  dropdownColor: Colors.grey.shade800,
                  underline: const SizedBox(),
                  style: const TextStyle(color: Colors.white),
                  icon: const Icon(Icons.arrow_drop_down, color: Colors.white),
                  onChanged: (value) {
                    final season = _seasons.firstWhere((s) => s.id == value);
                    _handleSeasonChange(season);
                  },
                  items: _seasons.map((season) {
                    return DropdownMenuItem<int>(
                      value: season.id,
                      child: Text(
                        'Season ${season.seasonNumber}${season.episodeCount != null ? ' (${season.episodeCount} episodes)' : ''}',
                      ),
                    );
                  }).toList(),
                ),
              ),
          ],
        ),
        const SizedBox(height: 16),

        // Episodes List
        if (_loadingEpisodes)
          const Center(
            child: Padding(
              padding: EdgeInsets.all(48.0),
              child: CircularProgressIndicator(
                valueColor: AlwaysStoppedAnimation<Color>(Colors.red),
              ),
            ),
          )
        else if (_episodes.isNotEmpty)
          ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _episodes.length,
            itemBuilder: (context, index) {
              return _buildEpisodeCard(_episodes[index]);
            },
          )
        else
          Container(
            padding: const EdgeInsets.all(48),
            decoration: BoxDecoration(
              color: Colors.grey.shade900,
              border: Border.all(color: Colors.grey.shade800),
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Center(
              child: Column(
                children: [
                  Icon(Icons.calendar_today, color: Colors.grey, size: 64),
                  SizedBox(height: 16),
                  Text(
                    'Season Coming Soon',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  SizedBox(height: 8),
                  Text(
                    'Episodes will be available soon. Stay tuned!',
                    style: TextStyle(color: Colors.grey),
                  ),
                ],
              ),
            ),
          ),
      ],
    );
  }

  Widget _buildEpisodeCard(Episode episode) {
    final isExpanded = _expandedEpisode?.id == episode.id;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.grey.shade900,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        children: [
          // Episode Header
          InkWell(
            onTap: () => _handleEpisodeClick(episode),
            child: Padding(
              padding: const EdgeInsets.all(16.0),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Episode Number
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: Colors.grey.shade800,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Center(
                      child: Text(
                        '${episode.episodeNumber}',
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 18,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 16),

                  // Episode Info
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          episode.name,
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w600,
                            fontSize: 16,
                          ),
                        ),
                        if (episode.overview != null &&
                            episode.overview!.isNotEmpty) ...[
                          const SizedBox(height: 4),
                          Text(
                            episode.overview!,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              color: Colors.grey.shade400,
                              fontSize: 14,
                            ),
                          ),
                        ],
                        if (episode.airDate != null) ...[
                          const SizedBox(height: 4),
                          Text(
                            _formatDate(episode.airDate!),
                            style: TextStyle(
                              color: Colors.grey.shade600,
                              fontSize: 12,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),

                  // Expand Icon
                  Icon(
                    isExpanded ? Icons.expand_less : Icons.expand_more,
                    color: Colors.white,
                  ),
                ],
              ),
            ),
          ),

          // Expanded Content
          if (isExpanded)
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                border: Border(
                  top: BorderSide(color: Colors.grey.shade800),
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Video Player
                  if (episode.embeds != null && episode.embeds!.isNotEmpty) ...[
                    // Notice if any embed requires ad
                    if (episode.embeds!.any((embed) => embed.requiresAd == true))
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
                        ...episode.embeds!.asMap().entries.map((entry) {
                          final index = entry.key;
                          final embed = entry.value;
                          final isActive = _activeEmbed?.id == embed.id;
                          final requiresAd = embed.requiresAd == true;
                          
                          // Debug logging
                          debugPrint('[TV Show Detail] Episode ${episode.episodeNumber} - Server ${index + 1} - requiresAd: $requiresAd, embedId: ${embed.id}');
                          
                          return ElevatedButton(
                            onPressed: () {
                              debugPrint('[TV Show Detail] Episode ${episode.episodeNumber} - Server ${index + 1} clicked - requiresAd: $requiresAd');
                              
                              // Check if this embed requires an ad
                              if (requiresAd) {
                                debugPrint('[TV Show Detail] Showing interstitial ad before playing server ${index + 1}');
                                // Show interstitial ad before switching to this embed
                                AdService.loadAndShowInterstitialAd(
                                  onAdDismissed: () {
                                    debugPrint('[TV Show Detail] Ad dismissed, setting active embed');
                              setState(() {
                                _activeEmbed = embed;
                                _activeEmbedData = _processEmbedUrl(embed);
                              });
                                  },
                                );
                              } else {
                                debugPrint('[TV Show Detail] No ad required, setting active embed immediately');
                                // No ad required, switch immediately
                                setState(() {
                                  _activeEmbed = embed;
                                  _activeEmbedData = _processEmbedUrl(embed);
                                });
                              }
                            },
                            style: ElevatedButton.styleFrom(
                              backgroundColor:
                                  isActive ? Colors.red : Colors.grey.shade800,
                              foregroundColor:
                                  isActive ? Colors.white : Colors.grey.shade300,
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
                        if (_activeEmbed != null && _expandedEpisode != null)
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
                    // Video iframe player - Responsive & Full View
                    // Supports all embed servers including mdy48tn97.com and others
                    if (_activeEmbed != null && _activeEmbedData != null)
                      _buildVideoPlayer(),
                  ] else
                    Center(
                      child: Padding(
                        padding: const EdgeInsets.all(16.0),
                        child: Text(
                          'No streaming links available for this episode.',
                          style: TextStyle(color: Colors.grey.shade400),
                        ),
                      ),
                    ),

                  // Download Links
                  if (episode.downloads != null &&
                      episode.downloads!.isNotEmpty) ...[
                    const SizedBox(height: 16),
                    const Text(
                      'Download Options',
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                    const SizedBox(height: 12),
                    GridView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: 2,
                        childAspectRatio: 2.5,
                        crossAxisSpacing: 12,
                        mainAxisSpacing: 12,
                      ),
                      itemCount: episode.downloads!.length,
                      itemBuilder: (context, index) {
                        final download = episode.downloads![index];
                        return InkWell(
                          onTap: () => _launchUrl(download.downloadUrl),
                          child: Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: Colors.grey.shade800,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Text(
                                        download.quality ?? 'HD',
                                        style: const TextStyle(
                                          color: Colors.white,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                      if (download.size != null)
                                        Text(
                                          download.size!,
                                          style: TextStyle(
                                            color: Colors.grey.shade400,
                                            fontSize: 12,
                                          ),
                                        ),
                                    ],
                                  ),
                                ),
                                const Icon(Icons.download,
                                    color: Colors.red, size: 20),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
                  ],
                ],
              ),
            ),
        ],
      ),
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
                                    title: '${_tvShow!.name} - Episode ${_expandedEpisode!.episodeNumber}',
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
                            title: '${_tvShow!.name} - Episode ${_expandedEpisode!.episodeNumber}',
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

  Widget _buildDetailsTab() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Show Details',
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
                    _tvShow!.overview ?? 'No description available.',
                    style: TextStyle(
                      color: Colors.grey.shade300,
                      height: 1.5,
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Category
                  if (_tvShow!.category != null) ...[
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
                        _tvShow!.category!.name,
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
                    if (_tvShow!.firstAirDate != null)
                      _buildInfoRow(
                        'First Air Date',
                        _formatDate(_tvShow!.firstAirDate!),
                      ),
                    if (_tvShow!.lastAirDate != null)
                      _buildInfoRow(
                        'Last Air Date',
                        _formatDate(_tvShow!.lastAirDate!),
                      ),
                    if (_tvShow!.numberOfSeasons != null)
                      _buildInfoRow(
                        'Seasons',
                        _tvShow!.numberOfSeasons!.toString(),
                      ),
                    if (_tvShow!.numberOfEpisodes != null)
                      _buildInfoRow(
                        'Episodes',
                        _tvShow!.numberOfEpisodes!.toString(),
                      ),
                    if (_tvShow!.voteAverage != null)
                      _buildInfoRow(
                        'Rating',
                        '${_tvShow!.voteAverage!.toStringAsFixed(1)} / 10',
                      ),
                    if (_tvShow!.voteCount != null)
                      _buildInfoRow(
                        'Votes',
                        _tvShow!.voteCount!.toStringAsFixed(0),
                      ),
                    if (_tvShow!.popularity != null)
                      _buildInfoRow(
                        'Popularity',
                        _tvShow!.popularity!.toStringAsFixed(1),
                      ),
                    _buildInfoRow(
                      'Status',
                      _getStatusText(_tvShow!.status),
                      valueColor: _isStatusActive(_tvShow!.status)
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

  Widget _buildSimilarShows() {
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
              itemCount: _similarShows.length,
              itemBuilder: (context, index) {
                return TVShowCard(
                  tvShow: _similarShows[index],
                  onTap: () {
                    Navigator.pushReplacement(
                      context,
                      MaterialPageRoute(
                        builder: (context) => TVShowDetailPage(
                          tvShowId: _similarShows[index].id,
                          tvShowName: _similarShows[index].name,
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
                            tmdbId: _tvShow!.tmdbId?.toString(),
                            year: _tvShow!.firstAirDate != null &&
                                    _tvShow!.firstAirDate!.length >= 4
                                ? _tvShow!.firstAirDate!.substring(0, 4)
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
                          if (_activeEmbed == null || _expandedEpisode == null) {
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
                            contentType: 'episode',
                            contentId: _expandedEpisode!.id,
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

