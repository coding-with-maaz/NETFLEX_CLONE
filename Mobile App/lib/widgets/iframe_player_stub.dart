import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

class IframePlayer extends StatefulWidget {
  final String url;
  final String? iframeStyle;
  final Map<String, String> iframeAttributes;

  const IframePlayer({
    super.key,
    required this.url,
    this.iframeStyle,
    this.iframeAttributes = const {},
  });

  @override
  State<IframePlayer> createState() => _IframePlayerState();
}

class _IframePlayerState extends State<IframePlayer> {
  late WebViewController _controller;
  bool _isLoading = true;
  String? _currentUrl;

  @override
  void initState() {
    super.initState();
    _currentUrl = widget.url;
    _initializeWebView();
  }

  @override
  void didUpdateWidget(IframePlayer oldWidget) {
    super.didUpdateWidget(oldWidget);
    // Reload WebView if URL changed (server switch)
    if (oldWidget.url != widget.url) {
      debugPrint('🔄 Server switched: ${widget.url}');
      _currentUrl = widget.url;
      setState(() => _isLoading = true);
      _controller.loadRequest(Uri.parse(widget.url));
    }
  }

  String? _getVideoHostingService(String url) {
    // Map URLs to their parent service for cross-domain navigation
    final lowerUrl = url.toLowerCase();
    
    // OneDrive service (multiple domains)
    if (lowerUrl.contains('1drv.ms') || 
        lowerUrl.contains('onedrive.live.com') || 
        lowerUrl.contains('sharepoint.com')) {
      return 'onedrive';
    }
    
    // Doodstream service
    if (lowerUrl.contains('doodstream.com') || 
        lowerUrl.contains('dsvplay.com') || 
        lowerUrl.contains('dood.to') ||
        lowerUrl.contains('ds2play.com') ||
        lowerUrl.contains('ds2video.com')) {
      return 'doodstream';
    }
    
    // Vidsrc service (multiple domains)
    if (lowerUrl.contains('vidsrc.icu') || 
        lowerUrl.contains('vidsrc.to') || 
        lowerUrl.contains('vidsrc.me') ||
        lowerUrl.contains('vidsrc.net') ||
        lowerUrl.contains('vidsrc.xyz') ||
        lowerUrl.contains('vidsrc.cc')) {
      return 'vidsrc';
    }
    
    // Mixdrop service
    if (lowerUrl.contains('mixdrop.co') || 
        lowerUrl.contains('mixdrop.to') ||
        lowerUrl.contains('mixdrop.sx') ||
        lowerUrl.contains('mixdrop.bz')) {
      return 'mixdrop';
    }
    
    // Streamtape service
    if (lowerUrl.contains('streamtape.com') || 
        lowerUrl.contains('streamtape.net') ||
        lowerUrl.contains('streamtape.to')) {
      return 'streamtape';
    }
    
    // Other single-domain services
    if (lowerUrl.contains('embedsito.com')) return 'embedsito';
    if (lowerUrl.contains('embed.su')) return 'embedsu';
    if (lowerUrl.contains('upstream.to')) return 'upstream';
    if (lowerUrl.contains('youtube.com') || lowerUrl.contains('youtu.be')) return 'youtube';
    if (lowerUrl.contains('vimeo.com')) return 'vimeo';
    if (lowerUrl.contains('dailymotion.com')) return 'dailymotion';
    if (lowerUrl.contains('streamable.com')) return 'streamable';
    
    // Additional embed servers
    if (lowerUrl.contains('mdy48tn97.com')) return 'mdy48tn97';
    if (lowerUrl.contains('vidstream.pro')) return 'vidstream';
    if (lowerUrl.contains('gogo-stream.com')) return 'gogostream';
    if (lowerUrl.contains('mp4upload.com')) return 'mp4upload';
    if (lowerUrl.contains('streamlare.com')) return 'streamlare';
    if (lowerUrl.contains('filemoon.sx')) return 'filemoon';
    
    // CDN services (always allow)
    if (lowerUrl.contains('cloudflare.com') || 
        lowerUrl.contains('cloudfront.net') ||
        lowerUrl.contains('googleapis.com') ||
        lowerUrl.contains('gstatic.com') ||
        lowerUrl.contains('jwpcdn.com') ||
        lowerUrl.contains('jwplatform.com')) {
      return 'cdn';
    }
    
    return null;
  }

  bool _isAllowedVideoHosting(String url) {
    return _getVideoHostingService(url) != null;
  }

  bool _shouldBlockNavigation(String url) {
    // First check if it's an allowed video hosting domain
    if (_isAllowedVideoHosting(url)) {
      return false; // Don't block video hosting domains
    }

    // List of known ad/spam domains to block
    final blockedPatterns = [
      'doubleclick.net',
      'googlesyndication.com',
      'google-analytics.com',
      'adservice.google',
      'advertising.com',
      'adnxs.com',
      'adsystem.com',
      'adsrvr.org',
      'adroll.com',
      'serving-sys.com',
      'adcolony.com',
      'applovin.com',
      'chartboost.com',
      'unity3d.com',
      'ironsrc.com',
      'facebook.com',
      'twitter.com',
      'instagram.com',
      'pinterest.com',
      'linkedin.com',
      'reddit.com',
      'tiktok.com',
      'snapchat.com',
      'play.google.com',
      'apps.apple.com',
      'itunes.apple.com',
    ];

    final lowerUrl = url.toLowerCase();
    
    // Block known ad/social domains
    for (final pattern in blockedPatterns) {
      if (lowerUrl.contains(pattern)) {
        return true;
      }
    }

    // Block obvious app store links
    if (lowerUrl.contains('/app/') || lowerUrl.contains('/apps/')) {
      return true;
    }

    // Allow everything else (needed for video player functionality)
    return false;
  }

  void _initializeWebView() {
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(Colors.black)
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageStarted: (String url) {
            if (mounted) {
              setState(() => _isLoading = true);
            }
          },
          onPageFinished: (String url) {
            if (mounted) {
              setState(() => _isLoading = false);
            }
          },
          onWebResourceError: (WebResourceError error) {
            debugPrint('WebView error: ${error.description}');
          },
          onNavigationRequest: (NavigationRequest request) {
            // Always allow the current video URL to load
            if (request.url == _currentUrl || request.url == widget.url) {
              debugPrint('✅ Loading video URL: ${request.url}');
              return NavigationDecision.navigate;
            }

            // Block known ad/spam domains immediately
            if (_shouldBlockNavigation(request.url)) {
              debugPrint('🚫 Blocked ad/spam: ${request.url}');
              return NavigationDecision.prevent;
            }

            // For iframe/subframe resources (non-main-frame), allow if needed for video
            // This allows video players to load necessary resources
            if (!request.isMainFrame) {
              if (_isAllowedVideoHosting(request.url)) {
                debugPrint('✅ Allowing iframe resource: ${request.url}');
                return NavigationDecision.navigate;
              }
              // Allow other iframe resources (video files, scripts, etc.)
              debugPrint('✅ Allowing iframe resource: ${request.url}');
              return NavigationDecision.navigate;
            }

            // For main frame navigation (user clicks/redirects), check if it's within the same service
            if (request.isMainFrame) {
              final initialService = _getVideoHostingService(_currentUrl ?? widget.url);
              final requestService = _getVideoHostingService(request.url);
              
              // Allow navigation within the same video hosting service
              // (e.g., 1drv.ms → onedrive.live.com, vidsrc.icu → vidsrc.to)
              if (initialService != null && 
                  requestService != null && 
                  initialService == requestService) {
                debugPrint('✅ Allowing same-service navigation: ${request.url}');
                return NavigationDecision.navigate;
              }

              // Block all other main frame navigation (user clicks on ads/external links)
              debugPrint('🚫 Blocked user navigation attempt: ${request.url}');
              return NavigationDecision.prevent;
            }

            // Default: allow (shouldn't reach here)
            return NavigationDecision.navigate;
          },
        ),
      )
      ..loadRequest(Uri.parse(widget.url));
  }

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        WebViewWidget(controller: _controller),
        if (_isLoading)
          Container(
      color: Colors.black,
      child: const Center(
              child: CircularProgressIndicator(
                valueColor: AlwaysStoppedAnimation<Color>(Colors.red),
              ),
            ),
          ),
      ],
    );
  }
}

