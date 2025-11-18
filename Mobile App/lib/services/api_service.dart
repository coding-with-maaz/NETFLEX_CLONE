import 'dart:convert';
import 'package:http/http.dart' as http;
import 'dart:io' show Platform;
import '../models/movie.dart';
import '../models/tvshow.dart';
import '../models/episode.dart';
import '../models/embed.dart';
import '../models/season.dart';
import '../models/comment.dart';

class ApiService {
  // API Configuration
  // Change USE_PRODUCTION to true when deploying to production
  static const bool USE_PRODUCTION = true; // Set to false for local Node.js backend
  
  // Production API URL (your hosted backend)
  static const String PRODUCTION_URL = 'https://api.nazaarabox.com/api/v1';
  
  // Local Development URLs - Node.js backend (port 8080)
  static const String LOCAL_URL_WEB = 'http://localhost:8080/api/v1';
  static const String LOCAL_URL_ANDROID = 'http://10.0.2.2:8080/api/v1'; // Android emulator
  static const String LOCAL_URL_IOS = 'http://localhost:8080/api/v1'; // iOS simulator
  static const String LOCAL_URL_REAL_DEVICE = 'http://192.168.1.1:8080/api/v1'; // Update with your local IP
  
  // API Key for authenticated requests
  // IMPORTANT: Store this securely in production (use environment variables or secure storage)
  static const String API_KEY = 'nzb_api_qfUxBMPiu3aqeXjgdqKCO4KqTDJB31m4';
  
  // Get the appropriate base URL
  static String get baseUrl {
    if (USE_PRODUCTION) {
      return PRODUCTION_URL;
    }
    
    // For development, detect platform automatically
    if (Platform.isAndroid) {
      return LOCAL_URL_ANDROID;
    } else if (Platform.isIOS) {
      return LOCAL_URL_IOS;
    } else {
      return LOCAL_URL_WEB;
    }
  }
  
  // Get headers with API key for protected endpoints
  static Map<String, String> get headers {
    return {
      'X-API-Key': API_KEY,
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
  }
  
  // Check if an endpoint is public (doesn't require API key)
  static bool isPublicEndpoint(String url) {
    final uri = Uri.parse(url);
    final path = uri.path.toLowerCase();
    
    // Public endpoints that don't require API key
    return path.contains('/utils/all') ||
           path.contains('/search') ||
           path.contains('/movies/search') ||
           path.contains('/tvshows/search') ||
           path.contains('/episodes/search') ||
           (path.contains('/leaderboard/movies/') && path.contains('/view')) ||
           (path.contains('/leaderboard/tvshows/') && path.contains('/view')) ||
           path.contains('/requests') ||
           path.contains('/reports/embed') ||
           path.contains('/comments');
  }
  
  // Add API key to query parameters if needed
  static Uri addApiKeyToUri(Uri uri) {
    if (isPublicEndpoint(uri.toString())) {
      return uri; // Don't add key to public endpoints
    }
    
    final queryParams = Map<String, String>.from(uri.queryParameters);
    queryParams['api_key'] = API_KEY;
    
    return uri.replace(queryParameters: queryParams);
  }
  
  // Add API key to URL string
  static String addApiKeyToUrl(String url) {
    if (isPublicEndpoint(url)) {
      return url; // Don't add key to public endpoints
    }
    
    final uri = Uri.parse(url);
    final queryParams = Map<String, String>.from(uri.queryParameters);
    queryParams['api_key'] = API_KEY;
    
    return uri.replace(queryParameters: queryParams).toString();
  }

  // ==================== Movies API ====================

  // Get featured movies
  static Future<List<Movie>> getFeaturedMovies({int limit = 5}) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/movies?is_featured=true&limit=$limit&sort_by=popularity'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null && data['data']['movies'] != null) {
          final List<dynamic> movies = data['data']['movies'];
          return movies.map((json) => Movie.fromJson(json)).toList();
        }
      }
      return [];
    } catch (e) {
      print('Error fetching featured movies: $e');
      return [];
    }
  }

  // Get movies with parameters
  static Future<Map<String, dynamic>> getMovies({Map<String, dynamic>? params}) async {
    try {
      final queryParams = params ?? {};
      final uri = Uri.parse('$baseUrl/movies').replace(
        queryParameters: queryParams.map((key, value) => MapEntry(key, value.toString())),
      );
      
      final response = await http.get(uri, headers: headers);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        
        if (data['success'] == true && data['data'] != null && data['data']['movies'] != null) {
          final List<dynamic> movies = data['data']['movies'];
          return {
            'movies': movies.map((json) => Movie.fromJson(json)).toList(),
            'pagination': data['data']['pagination'] ?? {},
          };
        }
      }
      return {'movies': [], 'pagination': {}};
    } catch (e) {
      print('Error fetching movies: $e');
      return {'movies': [], 'pagination': {}};
    }
  }

  // Get movies by endpoint (legacy method for content rows)
  static Future<List<Movie>> getMoviesByEndpoint(String endpoint) async {
    try {
      print('[ApiService] getMoviesByEndpoint called with endpoint: $endpoint');
      
      // Ensure endpoint starts with /
      final cleanEndpoint = endpoint.startsWith('/') ? endpoint : '/$endpoint';
      final url = '$baseUrl$cleanEndpoint';
      
      print('[ApiService] Full URL: $url');
      
      final uri = Uri.parse(url);
      final finalUri = isPublicEndpoint(url) ? uri : addApiKeyToUri(uri);
      final finalHeaders = isPublicEndpoint(url) ? <String, String>{} : headers;
      
      print('[ApiService] Making request to: $finalUri');
      print('[ApiService] Headers: $finalHeaders');
      
      final response = await http.get(finalUri, headers: finalHeaders);

      print('[ApiService] Response status: ${response.statusCode}');
      print('[ApiService] Response body preview: ${response.body.substring(0, response.body.length > 500 ? 500 : response.body.length)}');

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        print('[ApiService] Parsed data: success=${data['success']}, data=${data['data'] != null}');
        
        // Handle different response formats
        List<dynamic> movies = [];
        
        if (data['success'] == true && data['data'] != null) {
          // Format 1: data.data.movies (nested)
          if (data['data']['movies'] != null) {
            movies = data['data']['movies'] as List<dynamic>;
            print('[ApiService] Found movies in data.data.movies: ${movies.length}');
          }
          // Format 2: data.movies (direct)
          else if (data['data'] is List) {
            movies = data['data'] as List<dynamic>;
            print('[ApiService] Found movies in data (List): ${movies.length}');
          }
          // Format 3: data.data is a List
          else if (data['data'] is Map && (data['data'] as Map).containsKey('movies')) {
            movies = data['data']['movies'] as List<dynamic>;
            print('[ApiService] Found movies in data.movies (Map): ${movies.length}');
          }
        }
        // Format 4: Direct array response
        else if (data is List) {
          movies = data;
          print('[ApiService] Found movies in direct List: ${movies.length}');
        }
        // Format 5: data.movies at root
        else if (data['movies'] != null) {
          movies = data['movies'] as List<dynamic>;
          print('[ApiService] Found movies at root: ${movies.length}');
        }
        
        if (movies.isNotEmpty) {
          final result = movies.map((json) {
            try {
              return Movie.fromJson(json);
            } catch (e) {
              print('[ApiService] Error parsing movie JSON: $e');
              print('[ApiService] Problematic JSON: $json');
              return null;
            }
          }).whereType<Movie>().toList();
          
          print('[ApiService] Successfully parsed ${result.length} movies');
          return result;
        } else {
          print('[ApiService] No movies found in response');
        }
      } else {
        print('[ApiService] HTTP error: ${response.statusCode}');
        print('[ApiService] Response body: ${response.body}');
      }
      
      return [];
    } catch (e, stackTrace) {
      print('[ApiService] Error fetching movies by endpoint: $e');
      print('[ApiService] Stack trace: $stackTrace');
      return [];
    }
  }

  // Get movies by date
  static Future<List<Movie>> getMoviesByDate(String date) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/movies/today/all?date=$date'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          // Backend returns { movies: [...] } inside data
          final moviesData = data['data'];
          List<dynamic> movies = [];
          if (moviesData is List) {
            movies = moviesData;
          } else if (moviesData is Map && moviesData['movies'] != null) {
            movies = moviesData['movies'] is List ? moviesData['movies'] : [];
          }
          print('Fetched ${movies.length} movies for date: $date');
          return movies.map((json) => Movie.fromJson(json)).toList();
        } else {
          print('API response indicates failure or missing data for movies by date: ${data['success']}');
        }
      } else {
        print('HTTP error ${response.statusCode} when fetching movies by date: ${response.body}');
      }
      return [];
    } catch (e) {
      print('Error fetching movies by date: $e');
      return [];
    }
  }

  // Search movies
  static Future<Map<String, dynamic>> searchMovies({
    required String query,
    Map<String, dynamic>? params,
  }) async {
    try {
      final queryParams = {
        'q': query,
        ...?params,
      };
      final uri = Uri.parse('$baseUrl/movies/search').replace(
        queryParameters: queryParams.map((key, value) => MapEntry(key, value.toString())),
      );
      
      final response = await http.get(uri, headers: headers);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null && data['data']['movies'] != null) {
          final List<dynamic> movies = data['data']['movies'];
          return {
            'movies': movies.map((json) => Movie.fromJson(json)).toList(),
            'pagination': data['data']['pagination'] ?? {},
          };
        }
      }
      return {'movies': [], 'pagination': {}};
    } catch (e) {
      print('Error searching movies: $e');
      return {'movies': [], 'pagination': {}};
    }
  }

  // Get movie by ID
  static Future<Movie?> getMovieById(int id) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/movies/$id'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return Movie.fromJson(data['data']);
        }
      }
      return null;
    } catch (e) {
      print('Error fetching movie by ID: $e');
      return null;
    }
  }

  // Get movie embeds
  static Future<List<MovieEmbed>> getMovieEmbeds(int movieId) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/embeds/movies/$movieId'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          // Backend returns { embeds: [...] } inside data
          final embedsData = data['data'];
          List<dynamic> embeds = [];
          if (embedsData is List) {
            embeds = embedsData;
          } else if (embedsData is Map && embedsData['embeds'] != null) {
            embeds = embedsData['embeds'] is List ? embedsData['embeds'] : [];
          }
          print('Fetched ${embeds.length} embeds for movie $movieId');
          return embeds.map((json) => MovieEmbed.fromJson(json)).toList();
        } else {
          print('API response indicates failure or missing data for movie embeds: ${data['success']}');
        }
      } else {
        print('HTTP error ${response.statusCode} when fetching movie embeds: ${response.body}');
      }
      return [];
    } catch (e) {
      print('Error fetching movie embeds: $e');
      return [];
    }
  }

  // Get movie downloads
  static Future<List<DownloadLink>> getMovieDownloads(int movieId) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/downloads/movies/$movieId'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          final List<dynamic> downloads = data['data'] is List ? data['data'] : [];
          return downloads.map((json) => DownloadLink.fromJson(json)).toList();
        }
      }
      return [];
    } catch (e) {
      print('Error fetching movie downloads: $e');
      return [];
    }
  }

  // Get top rated movies
  static Future<List<Movie>> getTopRatedMovies({int limit = 20}) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/movies/top-rated?limit=$limit'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null && data['data']['movies'] != null) {
          final List<dynamic> movies = data['data']['movies'];
          return movies.map((json) => Movie.fromJson(json)).toList();
        }
      }
      return [];
    } catch (e) {
      print('Error fetching top rated movies: $e');
      return [];
    }
  }

  // Get trending movies
  static Future<List<Movie>> getTrendingMovies({String period = 'week', int limit = 20}) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/movies/trending?period=$period&limit=$limit'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null && data['data']['movies'] != null) {
          final List<dynamic> movies = data['data']['movies'];
          return movies.map((json) => Movie.fromJson(json)).toList();
        }
      }
      return [];
    } catch (e) {
      print('Error fetching trending movies: $e');
      return [];
    }
  }

  // Track movie view
  static Future<void> trackMovieView(int movieId) async {
    try {
      // This is a public endpoint, no API key needed
      await http.post(Uri.parse('$baseUrl/leaderboard/movies/$movieId/view'));
    } catch (e) {
      print('Error tracking movie view: $e');
    }
  }

  // ==================== TV Shows API ====================

  // Get featured TV shows
  static Future<List<TVShow>> getFeaturedTVShows({int limit = 5}) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/tvshows?is_featured=true&limit=$limit&sort_by=popularity'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null && data['data']['tvShows'] != null) {
          final List<dynamic> tvShows = data['data']['tvShows'];
          return tvShows.map((json) => TVShow.fromJson(json)).toList();
        }
      }
      return [];
    } catch (e) {
      print('Error fetching featured TV shows: $e');
      return [];
    }
  }

  // Get TV shows with parameters
  static Future<Map<String, dynamic>> getTVShowsWithParams({Map<String, dynamic>? params}) async {
    try {
      final queryParams = params ?? {};
      final uri = Uri.parse('$baseUrl/tvshows').replace(
        queryParameters: queryParams.map((key, value) => MapEntry(key, value.toString())),
      );
      
      final response = await http.get(uri, headers: headers);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        
        if (data['success'] == true && data['data'] != null && data['data']['tvShows'] != null) {
          final List<dynamic> tvShows = data['data']['tvShows'];
          return {
            'tvShows': tvShows.map((json) => TVShow.fromJson(json)).toList(),
            'pagination': data['data']['pagination'] ?? {},
          };
        }
      }
      return {'tvShows': [], 'pagination': {}};
    } catch (e) {
      print('Error fetching TV shows: $e');
      return {'tvShows': [], 'pagination': {}};
    }
  }

  // Get TV shows by endpoint (for content rows)
  static Future<List<TVShow>> getTVShows(String endpoint) async {
    try {
      print('[ApiService] getTVShows called with endpoint: $endpoint');
      
      // Ensure endpoint starts with /
      final cleanEndpoint = endpoint.startsWith('/') ? endpoint : '/$endpoint';
      final url = '$baseUrl$cleanEndpoint';
      
      print('[ApiService] Full URL: $url');
      
      final uri = Uri.parse(url);
      final finalUri = isPublicEndpoint(url) ? uri : addApiKeyToUri(uri);
      final finalHeaders = isPublicEndpoint(url) ? <String, String>{} : headers;
      
      print('[ApiService] Making request to: $finalUri');
      
      final response = await http.get(finalUri, headers: finalHeaders);

      print('[ApiService] Response status: ${response.statusCode}');

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        print('[ApiService] Parsed data: success=${data['success']}, data=${data['data'] != null}');
        
        // Handle different response formats
        List<dynamic> tvShows = [];
        
        if (data['success'] == true && data['data'] != null) {
          // Format 1: data.data.tvShows (nested)
          if (data['data']['tvShows'] != null) {
            tvShows = data['data']['tvShows'] as List<dynamic>;
            print('[ApiService] Found TV shows in data.data.tvShows: ${tvShows.length}');
          }
          // Format 2: data is a List
          else if (data['data'] is List) {
            tvShows = data['data'] as List<dynamic>;
            print('[ApiService] Found TV shows in data (List): ${tvShows.length}');
          }
          // Format 3: data.data is a Map with tvShows
          else if (data['data'] is Map && (data['data'] as Map).containsKey('tvShows')) {
            tvShows = data['data']['tvShows'] as List<dynamic>;
            print('[ApiService] Found TV shows in data.tvShows (Map): ${tvShows.length}');
          }
        }
        // Format 4: Direct array response
        else if (data is List) {
          tvShows = data;
          print('[ApiService] Found TV shows in direct List: ${tvShows.length}');
        }
        // Format 5: data.tvShows at root
        else if (data['tvShows'] != null) {
          tvShows = data['tvShows'] as List<dynamic>;
          print('[ApiService] Found TV shows at root: ${tvShows.length}');
        }
        
        if (tvShows.isNotEmpty) {
          final result = tvShows.map((json) {
            try {
              return TVShow.fromJson(json);
            } catch (e) {
              print('[ApiService] Error parsing TV show JSON: $e');
              return null;
            }
          }).whereType<TVShow>().toList();
          
          print('[ApiService] Successfully parsed ${result.length} TV shows');
          return result;
        } else {
          print('[ApiService] No TV shows found in response');
        }
      } else {
        print('[ApiService] HTTP error: ${response.statusCode}');
      }
      
      return [];
    } catch (e, stackTrace) {
      print('[ApiService] Error fetching TV shows by endpoint: $e');
      print('[ApiService] Stack trace: $stackTrace');
      return [];
    }
  }

  // Get TV show by ID
  static Future<TVShow?> getTVShowById(int id) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/tvshows/$id'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return TVShow.fromJson(data['data']);
        }
      }
      return null;
    } catch (e) {
      print('Error fetching TV show by ID: $e');
      return null;
    }
  }

  // Get TV show seasons
  static Future<List<Season>> getTVShowSeasons(int tvShowId) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/tvshows/$tvShowId/seasons'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          // Backend returns { seasons: [...] } inside data
          final seasonsData = data['data'];
          List<dynamic> seasons = [];
          if (seasonsData is List) {
            seasons = seasonsData;
          } else if (seasonsData is Map && seasonsData['seasons'] != null) {
            seasons = seasonsData['seasons'] is List ? seasonsData['seasons'] : [];
          }
          print('Fetched ${seasons.length} seasons for TV show $tvShowId');
          return seasons.map((json) => Season.fromJson(json)).toList();
        } else {
          print('API response indicates failure or missing data: ${data['success']}');
        }
      } else {
        print('HTTP error ${response.statusCode} when fetching seasons: ${response.body}');
      }
      return [];
    } catch (e) {
      print('Error fetching TV show seasons: $e');
      return [];
    }
  }

  // Get season episodes
  static Future<List<Episode>> getSeasonEpisodes(int tvShowId, int seasonId) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/tvshows/$tvShowId/seasons/$seasonId/episodes'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          // Backend returns { episodes: [...] } inside data
          final episodesData = data['data'];
          List<dynamic> episodes = [];
          if (episodesData is List) {
            episodes = episodesData;
          } else if (episodesData is Map && episodesData['episodes'] != null) {
            episodes = episodesData['episodes'] is List ? episodesData['episodes'] : [];
          }
          print('Fetched ${episodes.length} episodes for TV show $tvShowId, season $seasonId');
          return episodes.map((episodeJson) {
            try {
              return Episode.fromJson(episodeJson);
            } catch (e) {
              print('Error parsing episode: $e');
              return null;
            }
          }).whereType<Episode>().toList();
        } else {
          print('API response indicates failure or missing data: ${data['success']}');
        }
      } else {
        print('HTTP error ${response.statusCode} when fetching episodes: ${response.body}');
      }
      return [];
    } catch (e) {
      print('Error fetching season episodes: $e');
      return [];
    }
  }

  // Search TV shows
  static Future<Map<String, dynamic>> searchTVShows({
    required String query,
    Map<String, dynamic>? params,
  }) async {
    try {
      final queryParams = {
        'q': query,
        ...?params,
      };
      final uri = Uri.parse('$baseUrl/tvshows/search').replace(
        queryParameters: queryParams.map((key, value) => MapEntry(key, value.toString())),
      );
      
      final response = await http.get(uri, headers: headers);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null && data['data']['tvShows'] != null) {
          final List<dynamic> tvShows = data['data']['tvShows'];
          return {
            'tvShows': tvShows.map((json) => TVShow.fromJson(json)).toList(),
            'pagination': data['data']['pagination'] ?? {},
          };
        }
      }
      return {'tvShows': [], 'pagination': {}};
    } catch (e) {
      print('Error searching TV shows: $e');
      return {'tvShows': [], 'pagination': {}};
    }
  }

  // Get top rated TV shows
  static Future<List<TVShow>> getTopRatedTVShows({int limit = 20}) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/tvshows/top-rated?limit=$limit'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null && data['data']['tvShows'] != null) {
          final List<dynamic> tvShows = data['data']['tvShows'];
          return tvShows.map((json) => TVShow.fromJson(json)).toList();
        }
      }
      return [];
    } catch (e) {
      print('Error fetching top rated TV shows: $e');
      return [];
    }
  }

  // Get popular TV shows
  static Future<List<TVShow>> getPopularTVShows({int limit = 20}) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/tvshows/popular?limit=$limit'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null && data['data']['tvShows'] != null) {
          final List<dynamic> tvShows = data['data']['tvShows'];
          return tvShows.map((json) => TVShow.fromJson(json)).toList();
        }
      }
      return [];
    } catch (e) {
      print('Error fetching popular TV shows: $e');
      return [];
    }
  }

  // Track TV show view
  static Future<void> trackTVShowView(int tvShowId) async {
    try {
      // This is a public endpoint, no API key needed
      await http.post(Uri.parse('$baseUrl/leaderboard/tvshows/$tvShowId/view'));
    } catch (e) {
      print('Error tracking TV show view: $e');
    }
  }

  // ==================== Episodes API ====================

  // Get latest episodes
  static Future<List<Episode>> getLatestEpisodes({int limit = 20}) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/episodes/latest/all?limit=$limit'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          // The API returns data.data as an array directly
          final List<dynamic> episodes = data['data'] is List 
              ? data['data'] 
              : (data['data']['episodes'] ?? []);
          
          return episodes.map((episodeJson) {
            try {
              return Episode.fromJson(episodeJson);
            } catch (e) {
              print('Error parsing episode: $e');
              print('Episode JSON: $episodeJson');
              return null;
            }
          }).whereType<Episode>().toList();
        }
      }
      return [];
    } catch (e) {
      print('Error fetching latest episodes: $e');
      return [];
    }
  }

  // Get episodes by date
  static Future<List<Episode>> getEpisodesByDate(String date) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/episodes/today/all?date=$date'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          // Backend returns { episodes: [...] } inside data
          final episodesData = data['data'];
          List<dynamic> episodes = [];
          if (episodesData is List) {
            episodes = episodesData;
          } else if (episodesData is Map && episodesData['episodes'] != null) {
            episodes = episodesData['episodes'] is List ? episodesData['episodes'] : [];
          }
          print('Fetched ${episodes.length} episodes for date: $date');
          return episodes.map((episodeJson) {
            try {
              return Episode.fromJson(episodeJson);
            } catch (e) {
              print('Error parsing episode: $e');
              return null;
            }
          }).whereType<Episode>().toList();
        } else {
          print('API response indicates failure or missing data for episodes by date: ${data['success']}');
        }
      } else {
        print('HTTP error ${response.statusCode} when fetching episodes by date: ${response.body}');
      }
      return [];
    } catch (e) {
      print('Error fetching episodes by date: $e');
      return [];
    }
  }

  // Get episode by ID
  static Future<Episode?> getEpisodeById(int id) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/episodes/$id'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return Episode.fromJson(data['data']);
        }
      }
      return null;
    } catch (e) {
      print('Error fetching episode by ID: $e');
      return null;
    }
  }

  // Get episode embeds
  static Future<List<EpisodeEmbed>> getEpisodeEmbeds(int episodeId) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/embeds/episodes/$episodeId'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          // Backend returns { embeds: [...] } inside data
          final embedsData = data['data'];
          List<dynamic> embeds = [];
          if (embedsData is List) {
            embeds = embedsData;
          } else if (embedsData is Map && embedsData['embeds'] != null) {
            embeds = embedsData['embeds'] is List ? embedsData['embeds'] : [];
          }
          print('Fetched ${embeds.length} embeds for episode $episodeId');
          return embeds.map((json) => EpisodeEmbed.fromJson(json)).toList();
        } else {
          print('API response indicates failure or missing data for embeds: ${data['success']}');
        }
      } else {
        print('HTTP error ${response.statusCode} when fetching embeds: ${response.body}');
      }
      return [];
    } catch (e) {
      print('Error fetching episode embeds: $e');
      return [];
    }
  }

  // Search episodes
  static Future<Map<String, dynamic>> searchEpisodes({
    required String query,
    Map<String, dynamic>? params,
  }) async {
    try {
      final queryParams = {
        'q': query,
        ...?params,
      };
      final uri = Uri.parse('$baseUrl/episodes/search').replace(
        queryParameters: queryParams.map((key, value) => MapEntry(key, value.toString())),
      );
      
      final response = await http.get(uri, headers: headers);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null && data['data']['episodes'] != null) {
          final List<dynamic> episodes = data['data']['episodes'];
          return {
            'episodes': episodes.map((json) => Episode.fromJson(json)).toList(),
            'pagination': data['data']['pagination'] ?? {},
          };
        }
      }
      return {'episodes': [], 'pagination': {}};
    } catch (e) {
      print('Error searching episodes: $e');
      return {'episodes': [], 'pagination': {}};
    }
  }

  // ==================== Leaderboard & Trending API ====================

  // Get trending content (movies and TV shows combined)
  static Future<Map<String, dynamic>> getTrendingContent({
    String period = 'week',
    int limit = 50,
  }) async {
    try {
      final url = '$baseUrl/leaderboard/trending?period=$period&limit=$limit';
      print('ApiService: Fetching trending content from: $url');
      
      final response = await http.get(Uri.parse(url), headers: headers);

      print('ApiService: Trending response status: ${response.statusCode}');

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        print('ApiService: Trending response data structure: ${data.keys}');
        
        if (data['success'] == true && data['data'] != null) {
          final responseData = data['data'];
          print('ApiService: Response data keys: ${responseData.keys}');
          
          final movies = (responseData['movies'] ?? []) as List;
          final tvShows = (responseData['tvShows'] ?? []) as List;
          
          print('ApiService: Parsing ${movies.length} movies and ${tvShows.length} TV shows');
          
          return {
            'movies': movies.map((json) => Movie.fromJson(json as Map<String, dynamic>)).toList(),
            'tvShows': tvShows.map((json) => TVShow.fromJson(json as Map<String, dynamic>)).toList(),
            'period': responseData['period'] ?? period,
          };
        } else {
          print('ApiService: Response was not successful. success=${data['success']}, data=${data['data']}');
        }
      } else {
        print('ApiService: HTTP error ${response.statusCode}: ${response.body}');
      }
      
      return {'movies': [], 'tvShows': [], 'period': period};
    } catch (e, stackTrace) {
      print('ApiService: Error fetching trending content: $e');
      print('ApiService: Stack trace: $stackTrace');
      return {'movies': [], 'tvShows': [], 'period': period};
    }
  }

  // Get movies leaderboard
  static Future<List<Movie>> getMoviesLeaderboard({
    String period = 'week',
    int limit = 20,
  }) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/leaderboard/movies/leaderboard?period=$period&limit=$limit'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null && data['data']['movies'] != null) {
          final List<dynamic> movies = data['data']['movies'];
          return movies.map((json) => Movie.fromJson(json)).toList();
        }
      }
      return [];
    } catch (e) {
      print('Error fetching movies leaderboard: $e');
      return [];
    }
  }

  // Get TV shows leaderboard
  static Future<List<TVShow>> getTVShowsLeaderboard({
    String period = 'week',
    int limit = 20,
  }) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/leaderboard/tvshows/leaderboard?period=$period&limit=$limit'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          // Backend returns { tvShows: [...] } inside data
          final tvShowsData = data['data'];
          List<dynamic> tvShows = [];
          if (tvShowsData is List) {
            tvShows = tvShowsData;
          } else if (tvShowsData is Map && tvShowsData['tvShows'] != null) {
            tvShows = tvShowsData['tvShows'] is List ? tvShowsData['tvShows'] : [];
          }
          print('Fetched ${tvShows.length} TV shows from leaderboard (period: $period)');
          return tvShows.map((json) => TVShow.fromJson(json)).toList();
        } else {
          print('API response indicates failure or missing data for TV shows leaderboard: ${data['success']}');
        }
      } else {
        print('HTTP error ${response.statusCode} when fetching TV shows leaderboard: ${response.body}');
      }
      return [];
    } catch (e) {
      print('Error fetching TV shows leaderboard: $e');
      return [];
    }
  }

  // ==================== Unified Search API ====================

  // Unified search across movies, TV shows, and episodes
  static Future<Map<String, dynamic>> unifiedSearch({
    required String query,
    String type = 'all', // 'movies', 'tvshows', 'episodes', 'all'
    int page = 1,
    int limit = 20,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/search').replace(
        queryParameters: {
          'q': query,
          'type': type,
          'page': page.toString(),
          'limit': limit.toString(),
        },
      );
      
      final response = await http.get(uri, headers: headers);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return {
            'movies': (data['data']['results']['movies'] ?? []).map((json) => Movie.fromJson(json)).toList(),
            'tvShows': (data['data']['results']['tvShows'] ?? []).map((json) => TVShow.fromJson(json)).toList(),
            'episodes': (data['data']['results']['episodes'] ?? []).map((json) => Episode.fromJson(json)).toList(),
            'totals': data['data']['totals'] ?? {},
            'pagination': data['data']['pagination'] ?? {},
          };
        }
      }
      return {
        'movies': [],
        'tvShows': [],
        'episodes': [],
        'totals': {},
        'pagination': {},
      };
    } catch (e) {
      print('Error performing unified search: $e');
      return {
        'movies': [],
        'tvShows': [],
        'episodes': [],
        'totals': {},
        'pagination': {},
      };
    }
  }

  // ==================== Utility API ====================

  // Get utility data (genres, countries, categories, languages)
  static Future<Map<String, dynamic>> getUtilityData() async {
    try {
      // This is a public endpoint, no API key needed
      final response = await http.get(Uri.parse('$baseUrl/utils/all'));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return {
            'genres': data['data']['genres'] ?? [],
            'countries': data['data']['countries'] ?? [],
            'categories': data['data']['categories'] ?? [],
            'languages': data['data']['languages'] ?? [],
          };
        }
      }
      return {};
    } catch (e) {
      print('Error fetching utility data: $e');
      return {};
    }
  }

  // ==================== Content Requests API ====================

  // Get content requests (public endpoint - no API key required)
  static Future<Map<String, dynamic>> getContentRequests({
    String? status,
    String? type,
    String? search,
    int perPage = 10,
    int page = 1,
    String? sortBy,
    String? sortOrder,
  }) async {
    try {
      final queryParams = <String, String>{
        'per_page': perPage.toString(),
        'page': page.toString(),
        if (status != null && status.isNotEmpty && status != 'all') 'status': status,
        if (type != null && type.isNotEmpty && type != 'all') 'type': type,
        if (search != null && search.isNotEmpty) 'search': search,
        if (sortBy != null && sortBy.isNotEmpty) 'sort_by': sortBy,
        if (sortOrder != null && sortOrder.isNotEmpty) 'sort_order': sortOrder,
      };

      // Ensure baseUrl doesn't have trailing slash
      final cleanBaseUrl = baseUrl.endsWith('/') ? baseUrl.substring(0, baseUrl.length - 1) : baseUrl;
      final uri = Uri.parse('$cleanBaseUrl/requests').replace(
        queryParameters: queryParams,
      );

      print('[ApiService] Fetching content requests from: $uri');

      final response = await http.get(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      );

      print('[ApiService] Get requests response status: ${response.statusCode}');

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': data['success'] ?? true,
          'data': data['data'] ?? {},
          'requests': data['data']?['requests'] ?? [],
          'pagination': data['data']?['pagination'] ?? {},
        };
      } else {
        final data = json.decode(response.body);
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to fetch requests',
          'data': {},
          'requests': [],
          'pagination': {},
        };
      }
    } catch (e) {
      print('Error fetching content requests: $e');
      return {
        'success': false,
        'message': 'An error occurred. Please try again later.',
        'data': {},
        'requests': [],
        'pagination': {},
      };
    }
  }

  // Submit a content request (public endpoint - no API key required)
  static Future<Map<String, dynamic>> submitContentRequest({
    required String type, // 'movie' or 'tvshow'
    required String title,
    required String email,
    String? description,
    String? tmdbId,
    String? year,
  }) async {
    try {
      final body = {
        'type': type,
        'title': title,
        'email': email,
        if (description != null && description.isNotEmpty) 'description': description,
        if (tmdbId != null && tmdbId.isNotEmpty) 'tmdb_id': tmdbId,
        if (year != null && year.isNotEmpty) 'year': year,
      };

      // Ensure baseUrl doesn't have trailing slash and requests doesn't have leading slash
      final cleanBaseUrl = baseUrl.endsWith('/') ? baseUrl.substring(0, baseUrl.length - 1) : baseUrl;
      final url = '$cleanBaseUrl/requests';
      
      print('[ApiService] Base URL: $baseUrl');
      print('[ApiService] Clean Base URL: $cleanBaseUrl');
      print('[ApiService] Full URL: $url');
      print('[ApiService] Request body: $body');

      final uri = Uri.parse(url);
      print('[ApiService] Parsed URI: $uri');
      print('[ApiService] URI path: ${uri.path}');
      print('[ApiService] URI host: ${uri.host}');

      final response = await http.post(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: json.encode(body),
      );

      print('[ApiService] Response status: ${response.statusCode}');
      print('[ApiService] Response headers: ${response.headers}');
      print('[ApiService] Response body: ${response.body}');

      if (response.statusCode == 200 || response.statusCode == 201) {
        final data = json.decode(response.body);
        return {
          'success': data['success'] ?? true,
          'message': data['message'] ?? 'Request submitted successfully',
        };
      } else {
        String errorMessage = 'Failed to submit request';
        
        // Handle different status codes
        if (response.statusCode == 404) {
          errorMessage = 'API endpoint not found. Please check your server configuration.';
        } else if (response.statusCode == 422) {
          // Validation error
          try {
            final data = json.decode(response.body);
            if (data['errors'] != null) {
              final errors = data['errors'] as Map<String, dynamic>;
              final errorList = errors.values.expand((e) => e is List ? e : [e]).toList();
              if (errorList.isNotEmpty) {
                errorMessage = errorList.join(', ');
              } else {
                errorMessage = data['message'] ?? 'Validation failed';
              }
            } else {
              errorMessage = data['message'] ?? 'Validation failed';
            }
          } catch (e) {
            errorMessage = 'Validation failed. Please check your input.';
          }
        } else {
          // Try to parse JSON error response
          try {
            final data = json.decode(response.body);
            errorMessage = data['message'] ?? 
                          data['error'] ?? 
                          'Failed to submit request (Status: ${response.statusCode})';
            
            // Include validation errors if present
            if (data['errors'] != null) {
              final errors = data['errors'] as Map<String, dynamic>;
              final errorList = errors.values.expand((e) => e is List ? e : [e]).toList();
              if (errorList.isNotEmpty) {
                errorMessage = errorList.join(', ');
              }
            }
          } catch (e) {
            // Response is not JSON (might be HTML 404 page)
            if (response.statusCode == 404) {
              errorMessage = 'API endpoint not found. The server may not be configured correctly.';
            } else {
              errorMessage = 'Failed to submit request (Status: ${response.statusCode}). Please try again later.';
            }
          }
        }
        
        return {
          'success': false,
          'message': errorMessage,
        };
      }
    } catch (e, stackTrace) {
      print('Error submitting content request: $e');
      print('Stack trace: $stackTrace');
      return {
        'success': false,
        'message': 'An error occurred. Please check your connection and try again. Error: ${e.toString()}',
      };
    }
  }

  // ==================== Embed Reports API ====================

  // Submit an embed report (public endpoint - no API key required)
  static Future<Map<String, dynamic>> submitEmbedReport({
    required String contentType, // 'movie' or 'episode'
    required int contentId,
    int? embedId,
    required String reportType, // 'not_working', 'wrong_content', 'poor_quality', 'broken_link', 'other'
    required String email,
    String? description,
  }) async {
    try {
      final body = {
        'content_type': contentType,
        'content_id': contentId,
        if (embedId != null) 'embed_id': embedId,
        'report_type': reportType,
        'email': email,
        if (description != null && description.isNotEmpty) 'description': description,
      };

      final response = await http.post(
        Uri.parse('$baseUrl/reports/embed'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: json.encode(body),
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        final data = json.decode(response.body);
        return {
          'success': data['success'] ?? true,
          'message': data['message'] ?? 'Report submitted successfully',
        };
      } else {
        final data = json.decode(response.body);
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to submit report',
        };
      }
    } catch (e) {
      print('Error submitting embed report: $e');
      return {
        'success': false,
        'message': 'An error occurred. Please try again later.',
      };
    }
  }

  // ==================== Comments API ====================

  // Get comments for a content item (public endpoint - no API key required)
  static Future<List<Comment>> getComments({
    required String type, // 'movie', 'tvshow', or 'episode'
    required int id,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/comments').replace(
        queryParameters: {
          'type': type,
          'id': id.toString(),
        },
      );

      final response = await http.get(
        uri,
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        print('Comments API Response: ${data['success']}, data keys: ${data['data']?.keys}');
        
        if (data['success'] == true && data['data'] != null) {
          // Backend returns { comments: [...] } inside data
          final commentsData = data['data'];
          List<dynamic> comments = [];
          
          if (commentsData is List) {
            comments = commentsData;
          } else if (commentsData is Map && commentsData['comments'] != null) {
            comments = commentsData['comments'] is List ? commentsData['comments'] : [];
          }
          
          print('Fetched ${comments.length} comments for $type ID $id');
          
          if (comments.isNotEmpty) {
            return comments
                .map((json) {
                  try {
                    return Comment.fromJson(json as Map<String, dynamic>);
                  } catch (e) {
                    print('Error parsing comment: $e, json: $json');
                    return null;
                  }
                })
                .whereType<Comment>()
                .toList();
          }
        } else {
          print('API response indicates failure or missing data for comments: ${data['success']}');
        }
      } else {
        print('HTTP error ${response.statusCode} when fetching comments: ${response.body}');
      }
      return [];
    } catch (e) {
      print('Error fetching comments: $e');
      return [];
    }
  }

  // Submit a comment (public endpoint - no API key required)
  static Future<Map<String, dynamic>> submitComment({
    required String type, // 'movie', 'tvshow', or 'episode'
    required int id,
    required String name,
    required String email,
    required String comment,
    int? parentId, // For replies
  }) async {
    try {
      final body = {
        'type': type,
        'id': id,
        'name': name,
        'email': email,
        'comment': comment,
        if (parentId != null) 'parent_id': parentId,
      };

      final response = await http.post(
        Uri.parse('$baseUrl/comments'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: json.encode(body),
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        final data = json.decode(response.body);
        return {
          'success': data['success'] ?? true,
          'message': data['message'] ?? 'Comment submitted successfully',
          'data': data['data'],
        };
      } else {
        final data = json.decode(response.body);
        String errorMessage = 'Failed to submit comment';
        
        if (data['errors'] != null) {
          final errors = data['errors'] as Map<String, dynamic>;
          final errorList = errors.values.expand((e) => e is List ? e : [e]).toList();
          if (errorList.isNotEmpty) {
            errorMessage = errorList.join(', ');
          }
        } else {
          errorMessage = data['message'] ?? errorMessage;
        }
        
        return {
          'success': false,
          'message': errorMessage,
        };
      }
    } catch (e) {
      print('Error submitting comment: $e');
      return {
        'success': false,
        'message': 'An error occurred. Please try again later.',
      };
    }
  }
}

