import 'package:flutter/material.dart';
import 'ad_service.dart';

class AppLifecycleReactor extends WidgetsBindingObserver {
  final AdService adService;

  AppLifecycleReactor({required this.adService});

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    super.didChangeAppLifecycleState(state);
    
    print('App lifecycle state changed to: $state');
    
    // Show app open ad when app resumes from background
    if (state == AppLifecycleState.resumed) {
      print('App resumed - showing app open ad');
      adService.showAppOpenAdIfAvailable();
    }
  }

  void listenToAppStateChanges() {
    WidgetsBinding.instance.addObserver(this);
  }

  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
  }
}

