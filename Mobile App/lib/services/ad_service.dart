import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:google_mobile_ads/google_mobile_ads.dart';

class AdService {
  static AdService? _instance;
  static AdService get instance => _instance ??= AdService._();

  AdService._();
  
  // Track last interstitial ad time to prevent conflicts with app open ads
  static DateTime? _lastInterstitialAdTime;

  // Initialize AdMob
  static Future<void> initialize() async {
    print('📱 [AdService] Initializing AdMob...');
    final initStatus = await MobileAds.instance.initialize();
    print('📱 [AdService] AdMob initialization status: ${initStatus.adapterStatuses}');
    
    // Log ad mode for debugging
    if (kDebugMode) {
      print('🎯 [AdService] AdMob initialized in TEST MODE (using Google test ads)');
      print('📱 [AdService] Banner Test Ad ID: $bannerAdUnitId');
      print('📱 [AdService] Interstitial Test Ad ID: $interstitialAdUnitId');
      print('📱 [AdService] App Open Test Ad ID: $appOpenAdUnitId');
    } else {
      print('🎯 [AdService] AdMob initialized in PRODUCTION MODE (using real ads)');
    }
  }

  // Ad Unit IDs - Automatically switches between TEST and PRODUCTION
  static String get bannerAdUnitId {
    // Use test ads in debug mode, production ads in release mode
    if (kDebugMode) {
      // TEST AD UNIT IDs (for development/debugging)
      if (Platform.isAndroid) {
        return 'ca-app-pub-3940256099942544/6300978111'; // Google Test Banner
      } else if (Platform.isIOS) {
        return 'ca-app-pub-3940256099942544/2934735716'; // Google Test Banner iOS
      }
    } else {
      // PRODUCTION AD UNIT IDs (for release builds)
      if (Platform.isAndroid) {
        return 'ca-app-pub-2809929499941883/7381051514'; // Your Production Banner
      } else if (Platform.isIOS) {
        return 'ca-app-pub-3940256099942544/2934735716'; // iOS Banner - Add your real iOS ID
      }
    }
    throw UnsupportedError('Unsupported platform');
  }

  static String get interstitialAdUnitId {
    // Use test ads in debug mode, production ads in release mode
    if (kDebugMode) {
      // TEST AD UNIT IDs (for development/debugging)
      if (Platform.isAndroid) {
        return 'ca-app-pub-3940256099942544/1033173712'; // Google Test Interstitial
      } else if (Platform.isIOS) {
        return 'ca-app-pub-3940256099942544/4411468910'; // Google Test Interstitial iOS
      }
    } else {
      // PRODUCTION AD UNIT IDs (for release builds)
      if (Platform.isAndroid) {
        return 'ca-app-pub-2809929499941883/5133112377'; // Your Production Interstitial
      } else if (Platform.isIOS) {
        return 'ca-app-pub-3940256099942544/4411468910'; // iOS Interstitial - Add your real iOS ID
      }
    }
    throw UnsupportedError('Unsupported platform');
  }

  static String get appOpenAdUnitId {
    // Use test ads in debug mode, production ads in release mode
    if (kDebugMode) {
      // TEST AD UNIT IDs (for development/debugging)
      if (Platform.isAndroid) {
        return 'ca-app-pub-3940256099942544/3419835294'; // Google Test App Open
      } else if (Platform.isIOS) {
        return 'ca-app-pub-3940256099942544/5575463023'; // Google Test App Open iOS
      }
    } else {
      // PRODUCTION AD UNIT IDs (for release builds)
      if (Platform.isAndroid) {
        return 'ca-app-pub-2809929499941883/8469722153'; // Your Production App Open
      } else if (Platform.isIOS) {
        return 'ca-app-pub-3940256099942544/5575463023'; // iOS App Open - Add your real iOS ID
      }
    }
    throw UnsupportedError('Unsupported platform');
  }

  // Create a banner ad
  BannerAd createBannerAd({
    required void Function(Ad, LoadAdError) onAdFailedToLoad,
    required void Function(Ad) onAdLoaded,
  }) {
    return BannerAd(
      adUnitId: bannerAdUnitId,
      size: AdSize.banner,
      request: const AdRequest(),
      listener: BannerAdListener(
        onAdLoaded: onAdLoaded,
        onAdFailedToLoad: onAdFailedToLoad,
        onAdOpened: (Ad ad) => print('BannerAd onAdOpened.'),
        onAdClosed: (Ad ad) => print('BannerAd onAdClosed.'),
      ),
    );
  }

  // Load and show an interstitial ad
  static Future<void> loadAndShowInterstitialAd({
    VoidCallback? onAdDismissed,
  }) async {
    print('📱 [AdService] Loading interstitial ad...');
    print('📱 [AdService] Ad Unit ID: $interstitialAdUnitId');
    
    await InterstitialAd.load(
      adUnitId: interstitialAdUnitId,
      request: const AdRequest(),
      adLoadCallback: InterstitialAdLoadCallback(
        onAdLoaded: (InterstitialAd ad) {
          print('✅ [AdService] Interstitial ad loaded successfully');
          ad.fullScreenContentCallback = FullScreenContentCallback(
            onAdShowedFullScreenContent: (InterstitialAd ad) {
              print('✅ [AdService] Interstitial ad showed full screen content');
              // Mark the time when interstitial ad is shown
              _lastInterstitialAdTime = DateTime.now();
            },
            onAdDismissedFullScreenContent: (InterstitialAd ad) {
              print('✅ [AdService] Interstitial ad dismissed');
              ad.dispose();
              if (onAdDismissed != null) {
                onAdDismissed();
              }
            },
            onAdFailedToShowFullScreenContent: (InterstitialAd ad, AdError error) {
              print('❌ [AdService] Interstitial ad failed to show: $error');
              print('❌ [AdService] Error code: ${error.code}, message: ${error.message}');
              ad.dispose();
              if (onAdDismissed != null) {
                onAdDismissed();
              }
            },
          );
          // Show the ad immediately after loading
          print('📱 [AdService] Attempting to show interstitial ad...');
          ad.show();
        },
        onAdFailedToLoad: (LoadAdError error) {
          print('❌ [AdService] Interstitial ad failed to load');
          print('❌ [AdService] Error code: ${error.code}, message: ${error.message}');
          print('❌ [AdService] Response info: ${error.responseInfo}');
          if (onAdDismissed != null) {
            onAdDismissed();
          }
        },
      ),
    );
  }

  // App Open Ad management
  AppOpenAd? _appOpenAd;
  bool _isShowingAd = false;
  DateTime? _appOpenLoadTime;

  // Load app open ad
  Future<void> loadAppOpenAd() async {
    await AppOpenAd.load(
      adUnitId: appOpenAdUnitId,
      request: const AdRequest(),
      adLoadCallback: AppOpenAdLoadCallback(
        onAdLoaded: (AppOpenAd ad) {
          print('App open ad loaded');
          _appOpenAd = ad;
          _appOpenLoadTime = DateTime.now();
        },
        onAdFailedToLoad: (LoadAdError error) {
          print('App open ad failed to load: $error');
        },
      ),
    );
  }

  // Show app open ad if available
  void showAppOpenAdIfAvailable() {
    if (_isShowingAd) {
      print('App open ad is already showing');
      return;
    }

    // Check if an interstitial ad was shown recently (within last 10 seconds)
    // This prevents showing app open ad right after interstitial ad
    if (_lastInterstitialAdTime != null) {
      final timeSinceInterstitial = DateTime.now().difference(_lastInterstitialAdTime!);
      if (timeSinceInterstitial.inSeconds < 10) {
        print('Interstitial ad was shown recently (${timeSinceInterstitial.inSeconds}s ago), skipping app open ad');
        return;
      }
    }

    if (_appOpenAd == null) {
      print('App open ad is not ready yet');
      loadAppOpenAd(); // Preload next ad
      return;
    }

    // Check if ad is too old (4 hours)
    if (_appOpenLoadTime != null) {
      final difference = DateTime.now().difference(_appOpenLoadTime!);
      if (difference.inHours >= 4) {
        print('App open ad is too old, loading new one');
        _appOpenAd?.dispose();
        _appOpenAd = null;
        loadAppOpenAd();
        return;
      }
    }

    _appOpenAd!.fullScreenContentCallback = FullScreenContentCallback(
      onAdShowedFullScreenContent: (AppOpenAd ad) {
        print('App open ad showed full screen content');
        _isShowingAd = true;
      },
      onAdDismissedFullScreenContent: (AppOpenAd ad) {
        print('App open ad dismissed');
        _isShowingAd = false;
        ad.dispose();
        _appOpenAd = null;
        loadAppOpenAd(); // Preload next ad
      },
      onAdFailedToShowFullScreenContent: (AppOpenAd ad, AdError error) {
        print('App open ad failed to show: $error');
        _isShowingAd = false;
        ad.dispose();
        _appOpenAd = null;
        loadAppOpenAd(); // Preload next ad
      },
    );

    _appOpenAd!.show();
  }

  // Dispose method
  void dispose() {
    _appOpenAd?.dispose();
  }
}

