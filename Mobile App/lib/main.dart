import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:onesignal_flutter/onesignal_flutter.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:permission_handler/permission_handler.dart';
import 'pages/home_page.dart';
import 'pages/movies_page.dart';
import 'pages/tvshows_page.dart';
import 'pages/movie_detail_page.dart';
import 'pages/tvshow_detail_page.dart';
import 'pages/popular_tvshows_page.dart';
import 'pages/top_rated_movies_page.dart';
import 'pages/top_rated_tvshows_page.dart';
import 'pages/trending_movies_page.dart';
import 'pages/trending_page.dart';
import 'pages/today_movies_page.dart';
import 'pages/today_episodes_page.dart';
import 'pages/search_results_page.dart';
import 'pages/request_page.dart';
import 'pages/recent_requests_page.dart';
import 'services/ad_service.dart';
import 'services/app_lifecycle_reactor.dart';

// Handler for background Firebase messages
@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  print('🔥 Firebase background message received!');
  print('Message ID: ${message.messageId}');
  print('Message data: ${message.data}');
  if (message.notification != null) {
    print('Notification title: ${message.notification!.title}');
    print('Notification body: ${message.notification!.body}');
  }
}

void main() async {
  // Ensure Flutter bindings are initialized
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize Firebase
  await Firebase.initializeApp();
  
  // Set up Firebase background message handler
  FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);
  
  // Initialize SharedPreferences
  final prefs = await SharedPreferences.getInstance();
  
  // Request Firebase notification permissions (only if not already granted)
  FirebaseMessaging messaging = FirebaseMessaging.instance;
  
  // Check current permission status first
  NotificationSettings currentSettings = await messaging.getNotificationSettings();
  print('Firebase notification permission current status: ${currentSettings.authorizationStatus}');
  
  // Only request permission if it hasn't been requested before
  bool hasRequestedFirebasePermission = prefs.getBool('has_requested_firebase_permission') ?? false;
  
  if (!hasRequestedFirebasePermission) {
    // Check if permission is already granted (e.g., from previous app version)
    if (currentSettings.authorizationStatus == AuthorizationStatus.authorized ||
        currentSettings.authorizationStatus == AuthorizationStatus.provisional) {
      print('Firebase notification permission already granted. Status: ${currentSettings.authorizationStatus}');
      // Mark as requested so we don't ask again
      await prefs.setBool('has_requested_firebase_permission', true);
    } else if (currentSettings.authorizationStatus == AuthorizationStatus.notDetermined) {
      // Permission not determined yet, request it
      print('Requesting Firebase notification permission...');
      NotificationSettings settings = await messaging.requestPermission(
        alert: true,
        announcement: false,
        badge: true,
        carPlay: false,
        criticalAlert: false,
        provisional: false,
        sound: true,
      );
      
      print('Firebase notification permission status: ${settings.authorizationStatus}');
      
      // Mark that we've requested permission (regardless of user's choice)
      await prefs.setBool('has_requested_firebase_permission', true);
    } else {
      // Permission was denied previously, mark as requested so we don't ask again
      print('Firebase notification permission was denied previously. Status: ${currentSettings.authorizationStatus}');
      await prefs.setBool('has_requested_firebase_permission', true);
    }
  } else {
    print('Firebase notification permission already requested previously. Current status: ${currentSettings.authorizationStatus}');
  }
  
  // Get Firebase Cloud Messaging token
  try {
    String? fcmToken = await messaging.getToken();
    print('Firebase FCM Token: $fcmToken');
    
    // Listen for token refresh
    messaging.onTokenRefresh.listen((newToken) {
      print('Firebase FCM Token refreshed: $newToken');
    });
  } catch (e) {
    print('⚠️ Firebase token error (Google Play Services may not be available): $e');
    print('Firebase notifications will work on real devices with Google Play Services');
  }
  
  // Initialize OneSignal for push notifications
  // Enable verbose logging for debugging
  OneSignal.Debug.setLogLevel(OSLogLevel.verbose);
  
  // Initialize with OneSignal App ID
  OneSignal.initialize("66108e13-8cc2-4c2a-823a-e23a203cc838");
  
  // Request OneSignal push notification permission (only if not already requested)
  bool hasRequestedOneSignalPermission = prefs.getBool('has_requested_onesignal_permission') ?? false;
  
  // Check actual system notification permission status
  PermissionStatus notificationStatus = await Permission.notification.status;
  print('System notification permission status: $notificationStatus');
  print('Has requested OneSignal permission before: $hasRequestedOneSignalPermission');
  
  // If we've already requested before, skip completely
  if (hasRequestedOneSignalPermission) {
    print('OneSignal notification permission already requested previously - skipping request');
    // Still check and update status if permission was granted
    if (notificationStatus == PermissionStatus.granted) {
      print('Notification permission is granted - OneSignal will work without requesting again');
    }
  } else {
    // First time - check current status
    if (notificationStatus == PermissionStatus.granted) {
      // Permission already granted (maybe from system settings or previous app version)
      print('Notification permission already granted - skipping OneSignal request');
      await prefs.setBool('has_requested_onesignal_permission', true);
    } else if (notificationStatus == PermissionStatus.permanentlyDenied) {
      // Permission permanently denied - don't request
      print('Notification permission permanently denied - skipping OneSignal request');
      await prefs.setBool('has_requested_onesignal_permission', true);
    } else {
      // Permission not determined or denied (but not permanently) - request it
      print('Requesting OneSignal notification permission...');
      final granted = await OneSignal.Notifications.requestPermission(true);
      print('OneSignal notification permission granted: $granted');
      
      // Mark that we've requested permission (regardless of result)
      await prefs.setBool('has_requested_onesignal_permission', true);
    }
  }
  
  // Initialize AdMob
  await AdService.initialize();
  
  // Load app open ad
  await AdService.instance.loadAppOpenAd();
  
  // Set system UI overlay style
  SystemChrome.setSystemUIOverlayStyle(
    const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.light,
      systemNavigationBarColor: Colors.black,
      systemNavigationBarIconBrightness: Brightness.light,
    ),
  );

  runApp(const NazaaraBoxApp());
}

class NazaaraBoxApp extends StatefulWidget {
  const NazaaraBoxApp({super.key});

  @override
  State<NazaaraBoxApp> createState() => _NazaaraBoxAppState();
}

// Global navigator key for accessing context from anywhere
final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

class _NazaaraBoxAppState extends State<NazaaraBoxApp> {
  late AppLifecycleReactor _appLifecycleReactor;

  @override
  void initState() {
    super.initState();
    _appLifecycleReactor = AppLifecycleReactor(adService: AdService.instance);
    _appLifecycleReactor.listenToAppStateChanges();
    
    // Setup Firebase foreground message handler
    _setupFirebaseMessaging();
  }
  
  void _setupFirebaseMessaging() {
    // Handle foreground messages (when app is open)
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      print('🔥 Firebase foreground message received!');
      print('Message ID: ${message.messageId}');
      print('Message data: ${message.data}');
      
      if (message.notification != null) {
        print('📬 Firebase notification title: ${message.notification!.title}');
        print('📬 Firebase notification body: ${message.notification!.body}');
        
        // Show a snackbar or dialog to display the notification
        _showNotificationDialog(
          message.notification!.title ?? 'New Notification',
          message.notification!.body ?? '',
        );
      }
    });
    
    // Handle notification tap when app is in background/terminated
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      print('Firebase notification opened from background!');
      print('Message data: ${message.data}');
      
      // Handle navigation based on notification data
      if (message.data.containsKey('route')) {
        // Navigate to specific route if provided in notification data
        // You can customize this based on your needs
      }
    });
    
    // Check if app was opened from a terminated state via notification
    FirebaseMessaging.instance.getInitialMessage().then((RemoteMessage? message) {
      if (message != null) {
        print('Firebase app opened from terminated state via notification!');
        print('Message data: ${message.data}');
      }
    });
  }
  
  void _showNotificationDialog(String title, String body) {
    // This will be called when a notification arrives in foreground
    // We need a BuildContext, so we'll show it after the first frame
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final context = navigatorKey.currentContext;
      if (context != null) {
        showDialog(
          context: context,
          builder: (BuildContext context) {
            return AlertDialog(
              backgroundColor: Colors.grey[900],
              title: Text(
                title,
                style: const TextStyle(color: Colors.white),
              ),
              content: Text(
                body,
                style: const TextStyle(color: Colors.white70),
              ),
              actions: [
                TextButton(
                  onPressed: () {
                    Navigator.of(context).pop();
                  },
                  child: const Text(
                    'OK',
                    style: TextStyle(color: Colors.red),
                  ),
                ),
              ],
            );
          },
        );
      }
    });
  }

  @override
  void dispose() {
    _appLifecycleReactor.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      navigatorKey: navigatorKey,
      title: 'Nazaara Box - Movie & TV Show Streaming',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        brightness: Brightness.dark,
        primaryColor: Colors.red,
        scaffoldBackgroundColor: Colors.black,
        colorScheme: ColorScheme.dark(
          primary: Colors.red,
          secondary: Colors.red[700]!,
          surface: Colors.grey[900]!,
          background: Colors.black,
        ),
        appBarTheme: const AppBarTheme(
          backgroundColor: Colors.black,
          elevation: 0,
          centerTitle: false,
          systemOverlayStyle: SystemUiOverlayStyle(
            statusBarColor: Colors.transparent,
            statusBarIconBrightness: Brightness.light,
          ),
        ),
        textTheme: const TextTheme(
          bodyLarge: TextStyle(color: Colors.white),
          bodyMedium: TextStyle(color: Colors.white),
          titleLarge: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
        ),
        useMaterial3: true,
      ),
      initialRoute: '/',
      onGenerateRoute: (settings) {
        // Handle routes with parameters
        if (settings.name?.startsWith('/movie/') == true) {
          final uri = Uri.parse(settings.name!);
          final id = int.tryParse(uri.pathSegments.last);
          if (id != null && id > 0) {
            return MaterialPageRoute(
              builder: (context) => MovieDetailPage(
                movieId: id,
              ),
            );
          }
        }

        if (settings.name?.startsWith('/tvshow/') == true) {
          final uri = Uri.parse(settings.name!);
          final id = int.tryParse(uri.pathSegments.last);
          final name = uri.queryParameters['name'];
          if (id != null && id > 0) {
            return MaterialPageRoute(
              builder: (context) => TVShowDetailPage(
                tvShowId: id,
                tvShowName: name,
              ),
            );
          }
        }

        // Default routes - handle specific routes first!
        switch (settings.name) {
          case '/':
            return MaterialPageRoute(builder: (context) => const HomePage());
          case '/search':
            final query = settings.arguments as String?;
            return MaterialPageRoute(
              builder: (context) => SearchResultsPage(initialQuery: query),
            );
          case '/tvshows/popular':
            return MaterialPageRoute(builder: (context) => const PopularTVShowsPage());
          case '/movies/top-rated':
            return MaterialPageRoute(builder: (context) => const TopRatedMoviesPage());
          case '/tvshows/top-rated':
            return MaterialPageRoute(builder: (context) => const TopRatedTVShowsPage());
          case '/movies/trending':
            return MaterialPageRoute(builder: (context) => const TrendingMoviesPage());
          case '/trending':
            return MaterialPageRoute(builder: (context) => const TrendingPage());
          case '/movies/today':
            return MaterialPageRoute(builder: (context) => const TodayMoviesPage());
          case '/episodes/today':
            return MaterialPageRoute(builder: (context) => const TodayEpisodesPage());
          case '/request':
            final args = settings.arguments as Map<String, dynamic>?;
            return MaterialPageRoute(
              builder: (context) => RequestPage(
                prefillTitle: args?['title'],
                prefillType: args?['type'],
                prefillTmdbId: args?['tmdbId'],
                prefillYear: args?['year'],
              ),
            );
          case '/recent-requests':
            return MaterialPageRoute(builder: (context) => const RecentRequestsPage());
          default:
            // Handle generic /movies route with query parameters
            if (settings.name?.startsWith('/movies') == true) {
              final uri = Uri.parse(settings.name!);
              return MaterialPageRoute(
                builder: (context) => MoviesPage(
                  initialFilters: uri.queryParameters,
                ),
              );
            }
            
            // Handle generic /tvshows route with query parameters
            if (settings.name?.startsWith('/tvshows') == true) {
              final uri = Uri.parse(settings.name!);
              return MaterialPageRoute(
                builder: (context) => TVShowsPage(
                  initialFilters: uri.queryParameters,
                ),
              );
            }
            
            // Route not found
            return MaterialPageRoute(
              builder: (context) => Scaffold(
                body: Center(
                  child: Text('Route not found: ${settings.name}'),
                ),
              ),
            );
        }
      },
    );
  }
}
