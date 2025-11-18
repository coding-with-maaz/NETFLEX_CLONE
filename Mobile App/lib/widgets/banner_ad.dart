import 'package:flutter/material.dart';
import 'package:google_mobile_ads/google_mobile_ads.dart';

/// A collapsible, adaptive banner ad widget that is sticky at the bottom
/// Google's collapsible banners have their own built-in UI controls
class CollapsibleBannerAdWidget extends StatefulWidget {
  final String adUnitId;
  final Color? backgroundColor;

  const CollapsibleBannerAdWidget({
    Key? key,
    required this.adUnitId,
    this.backgroundColor,
  }) : super(key: key);

  @override
  State<CollapsibleBannerAdWidget> createState() => _CollapsibleBannerAdWidgetState();
}

class _CollapsibleBannerAdWidgetState extends State<CollapsibleBannerAdWidget> {
  BannerAd? _bannerAd;
  AdSize? _adSize;
  bool _isAdLoaded = false;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    // Delay loading to ensure MediaQuery is available
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadAd();
    });
  }

  Future<void> _loadAd() async {
    if (!mounted) return;

    try {
      // Get the screen width from MediaQuery
      final screenWidth = MediaQuery.of(context).size.width.truncate();
      print('Loading collapsible banner ad with screen width: $screenWidth');
      print('Ad Unit ID: ${widget.adUnitId}');

      // Get the adaptive banner size for the current screen width
      final size = await AdSize.getCurrentOrientationAnchoredAdaptiveBannerAdSize(
        screenWidth,
      );

      if (size == null) {
        print('❌ Unable to get adaptive banner size');
        if (mounted) {
          setState(() {
            _isLoading = false;
          });
        }
        return;
      }

      print('✅ Adaptive banner size: ${size.width}x${size.height}');

      if (mounted) {
        setState(() {
          _adSize = size;
        });
      }

      // Create an ad request with collapsible parameter
      // Placement "bottom" aligns the bottom of the expanded ad to the bottom of the banner
      const adRequest = AdRequest(
        extras: {
          'collapsible': 'bottom',
        },
      );

      // Create the banner ad
      _bannerAd = BannerAd(
        adUnitId: widget.adUnitId,
        size: _adSize!,
        request: adRequest,
        listener: BannerAdListener(
          onAdLoaded: (Ad ad) {
            print('✅ Collapsible banner ad loaded successfully');
            if (mounted) {
              setState(() {
                _isAdLoaded = true;
                _isLoading = false;
              });
            }
          },
          onAdFailedToLoad: (Ad ad, LoadAdError error) {
            print('❌ Failed to load collapsible banner ad:');
            print('   Error code: ${error.code}');
            print('   Error domain: ${error.domain}');
            print('   Error message: ${error.message}');
            print('   Response info: ${error.responseInfo}');
            ad.dispose();
            if (mounted) {
              setState(() {
                _isLoading = false;
                _isAdLoaded = false;
              });
            }
          },
          onAdOpened: (Ad ad) {
            print('📱 Collapsible banner ad opened');
          },
          onAdClosed: (Ad ad) {
            print('📱 Collapsible banner ad closed by user');
            // User closed the ad via Google's built-in controls
            // Dispose the ad
            ad.dispose();
            if (mounted) {
              setState(() {
                _isAdLoaded = false;
                _bannerAd = null;
              });
            }
          },
          onAdImpression: (Ad ad) {
            print('👁️ Collapsible banner ad impression recorded');
          },
        ),
      );

      // Load the ad
      print('🔄 Loading banner ad...');
      _bannerAd!.load();
    } catch (e, stackTrace) {
      print('❌ Error loading collapsible banner ad: $e');
      print('Stack trace: $stackTrace');
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }


  @override
  void dispose() {
    _bannerAd?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    // Don't show anything if ad failed to load and not loading
    if (!_isLoading && !_isAdLoaded) {
      return const SizedBox.shrink();
    }

    // Show loading or ad container
    final adHeight = _adSize?.height.toDouble() ?? 50;

    return Container(
      color: widget.backgroundColor ?? Colors.black,
      width: double.infinity,
      alignment: Alignment.center,
      child: Container(
        width: double.infinity,
        height: adHeight,
        alignment: Alignment.center,
        child: _isLoading
            ? SizedBox(
                height: adHeight,
                child: Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(Colors.grey),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Loading ad...',
                        style: TextStyle(
                          color: Colors.grey[400],
                          fontSize: 10,
                        ),
                      ),
                    ],
                  ),
                ),
              )
            : _isAdLoaded && _bannerAd != null
                ? SizedBox(
                    height: adHeight,
                    width: double.infinity,
                    child: AdWidget(ad: _bannerAd!),
                  )
                : SizedBox(
                    height: adHeight,
                    child: Center(
                      child: Text(
                        'Ad failed to load',
                        style: TextStyle(
                          color: Colors.grey[600],
                          fontSize: 10,
                        ),
                      ),
                    ),
                  ),
      ),
    );
  }
}

