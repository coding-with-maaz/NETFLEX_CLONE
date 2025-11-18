import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/movie.dart';
import '../models/tvshow.dart';

class WatchedItem {
  final String type; // 'movie' or 'tvshow'
  final int id;
  final DateTime watchedAt;
  final List<int>? genreIds; // For recommendations

  WatchedItem({
    required this.type,
    required this.id,
    required this.watchedAt,
    this.genreIds,
  });

  Map<String, dynamic> toJson() {
    return {
      'type': type,
      'id': id,
      'watchedAt': watchedAt.toIso8601String(),
      'genreIds': genreIds,
    };
  }

  factory WatchedItem.fromJson(Map<String, dynamic> json) {
    return WatchedItem(
      type: json['type'] ?? '',
      id: json['id'] ?? 0,
      watchedAt: DateTime.parse(json['watchedAt'] ?? DateTime.now().toIso8601String()),
      genreIds: json['genreIds'] != null ? List<int>.from(json['genreIds']) : null,
    );
  }
}

class WatchedContentService {
  static const String _watchedKey = 'watched_content';
  static const int _maxWatchedItems = 100; // Keep last 100 watched items

  // Save watched movie
  static Future<void> saveWatchedMovie(Movie movie) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final watched = await getWatchedItems();
      
      // Remove if already exists
      watched.removeWhere((item) => item.type == 'movie' && item.id == movie.id);
      
      // Add new watched item
      final genreIds = movie.genres?.map((g) => g.id).toList();
      watched.add(WatchedItem(
        type: 'movie',
        id: movie.id,
        watchedAt: DateTime.now(),
        genreIds: genreIds,
      ));
      
      // Keep only last N items
      if (watched.length > _maxWatchedItems) {
        watched.sort((a, b) => b.watchedAt.compareTo(a.watchedAt));
        watched.removeRange(_maxWatchedItems, watched.length);
      }
      
      // Save to preferences
      final jsonList = watched.map((item) => item.toJson()).toList();
      await prefs.setString(_watchedKey, jsonEncode(jsonList));
      
      print('[WatchedContent] Saved watched movie: ${movie.id}');
    } catch (e) {
      print('[WatchedContent] Error saving watched movie: $e');
    }
  }

  // Save watched TV show
  static Future<void> saveWatchedTVShow(TVShow tvShow) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final watched = await getWatchedItems();
      
      // Remove if already exists
      watched.removeWhere((item) => item.type == 'tvshow' && item.id == tvShow.id);
      
      // Add new watched item
      final genreIds = tvShow.genres?.map((g) => g.id).toList();
      watched.add(WatchedItem(
        type: 'tvshow',
        id: tvShow.id,
        watchedAt: DateTime.now(),
        genreIds: genreIds,
      ));
      
      // Keep only last N items
      if (watched.length > _maxWatchedItems) {
        watched.sort((a, b) => b.watchedAt.compareTo(a.watchedAt));
        watched.removeRange(_maxWatchedItems, watched.length);
      }
      
      // Save to preferences
      final jsonList = watched.map((item) => item.toJson()).toList();
      await prefs.setString(_watchedKey, jsonEncode(jsonList));
      
      print('[WatchedContent] Saved watched TV show: ${tvShow.id}');
    } catch (e) {
      print('[WatchedContent] Error saving watched TV show: $e');
    }
  }

  // Get all watched items
  static Future<List<WatchedItem>> getWatchedItems() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final jsonString = prefs.getString(_watchedKey);
      
      if (jsonString == null || jsonString.isEmpty) {
        return [];
      }
      
      final jsonList = jsonDecode(jsonString) as List<dynamic>;
      return jsonList.map((json) => WatchedItem.fromJson(json as Map<String, dynamic>)).toList();
    } catch (e) {
      print('[WatchedContent] Error getting watched items: $e');
      return [];
    }
  }

  // Get watched genre IDs (for recommendations)
  static Future<List<int>> getWatchedGenreIds() async {
    final watched = await getWatchedItems();
    final genreIds = <int>{};
    
    for (final item in watched) {
      if (item.genreIds != null) {
        genreIds.addAll(item.genreIds!);
      }
    }
    
    return genreIds.toList();
  }

  // Get watched movie IDs
  static Future<List<int>> getWatchedMovieIds() async {
    final watched = await getWatchedItems();
    return watched.where((item) => item.type == 'movie').map((item) => item.id).toList();
  }

  // Get watched TV show IDs
  static Future<List<int>> getWatchedTVShowIds() async {
    final watched = await getWatchedItems();
    return watched.where((item) => item.type == 'tvshow').map((item) => item.id).toList();
  }

  // Clear all watched content
  static Future<void> clearWatchedContent() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_watchedKey);
      print('[WatchedContent] Cleared all watched content');
    } catch (e) {
      print('[WatchedContent] Error clearing watched content: $e');
    }
  }
}

