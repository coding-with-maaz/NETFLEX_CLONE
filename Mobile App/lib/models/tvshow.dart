import 'movie.dart';

class TVShow {
  final int id;
  final int? tmdbId;
  final String name;
  final String? slug;
  final String? originalName;
  final String? overview;
  final String? firstAirDate;
  final String? lastAirDate;
  final String? posterPath;
  final String? backdropPath;
  final double? voteAverage;
  final int? voteCount;
  final double? popularity;
  final int? numberOfSeasons;
  final int? numberOfEpisodes;
  final String? status;
  final String? type;
  final bool isFeatured;
  final bool isActive;
  final int? viewCount;
  final List<Genre>? genres;
  final Category? category;

  TVShow({
    required this.id,
    this.tmdbId,
    required this.name,
    this.slug,
    this.originalName,
    this.overview,
    this.firstAirDate,
    this.lastAirDate,
    this.posterPath,
    this.backdropPath,
    this.voteAverage,
    this.voteCount,
    this.popularity,
    this.numberOfSeasons,
    this.numberOfEpisodes,
    this.status,
    this.type,
    this.isFeatured = false,
    this.isActive = true,
    this.viewCount,
    this.genres,
    this.category,
  });

  factory TVShow.fromJson(Map<String, dynamic> json) {
    return TVShow(
      id: json['id'] ?? 0,
      tmdbId: _parseInt(json['tmdb_id']),
      name: json['name'] ?? '',
      slug: json['slug'],
      originalName: json['original_name'],
      overview: json['overview'],
      firstAirDate: json['first_air_date'],
      lastAirDate: json['last_air_date'],
      posterPath: json['poster_path'],
      backdropPath: json['backdrop_path'],
      voteAverage: _parseDouble(json['vote_average']),
      voteCount: _parseInt(json['vote_count']),
      popularity: _parseDouble(json['popularity']),
      numberOfSeasons: _parseInt(json['number_of_seasons']),
      numberOfEpisodes: _parseInt(json['number_of_episodes']),
      status: json['status'],
      type: json['type'],
      isFeatured: json['is_featured'] ?? false,
      isActive: json['status'] == 'active',
      viewCount: _parseInt(json['view_count']),
      genres: json['genres'] != null
          ? (json['genres'] as List).map((g) => Genre.fromJson(g)).toList()
          : null,
      category: json['category'] != null
          ? Category.fromJson(json['category'])
          : null,
    );
  }

  static double? _parseDouble(dynamic value) {
    if (value == null) return null;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) return double.tryParse(value);
    return null;
  }

  static int? _parseInt(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is double) return value.toInt();
    if (value is String) return int.tryParse(value);
    return null;
  }

  // Helper method to get full image URL (handles both full URLs and relative paths)
  String getImageUrl([String size = 'w500']) {
    if (posterPath == null || posterPath!.isEmpty) return '';
    if (posterPath!.startsWith('http')) return posterPath!;
    if (posterPath!.startsWith('/images/')) return posterPath!;
    return 'https://image.tmdb.org/t/p/$size$posterPath';
  }

  // Helper method to get full backdrop URL
  String getBackdropUrl([String size = 'w1280']) {
    if (backdropPath == null || backdropPath!.isEmpty) {
      // Fallback to poster if backdrop is not available
      return getImageUrl(size);
    }
    if (backdropPath!.startsWith('http')) return backdropPath!;
    if (backdropPath!.startsWith('/images/')) return backdropPath!;
    return 'https://image.tmdb.org/t/p/$size$backdropPath';
  }
}
