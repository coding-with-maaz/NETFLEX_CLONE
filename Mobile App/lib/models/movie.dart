class Movie {
  final int id;
  final int? tmdbId;
  final String title;
  final String? slug;
  final String? originalTitle;
  final String? overview;
  final String? releaseDate;
  final String? posterPath;
  final String? backdropPath;
  final double? voteAverage;
  final int? voteCount;
  final double? popularity;
  final int? runtime;
  final String? status;
  final String? tagline;
  final bool isFeatured;
  final bool isActive;
  final int? viewCount;
  final List<Genre>? genres;
  final Category? category;

  Movie({
    required this.id,
    this.tmdbId,
    required this.title,
    this.slug,
    this.originalTitle,
    this.overview,
    this.releaseDate,
    this.posterPath,
    this.backdropPath,
    this.voteAverage,
    this.voteCount,
    this.popularity,
    this.runtime,
    this.status,
    this.tagline,
    this.isFeatured = false,
    this.isActive = true,
    this.viewCount,
    this.genres,
    this.category,
  });

  factory Movie.fromJson(Map<String, dynamic> json) {
    return Movie(
      id: json['id'] ?? 0,
      tmdbId: _parseInt(json['tmdb_id']),
      title: json['title'] ?? '',
      slug: json['slug'],
      originalTitle: json['original_title'],
      overview: json['overview'],
      releaseDate: json['release_date'],
      posterPath: json['poster_path'],
      backdropPath: json['backdrop_path'],
      voteAverage: _parseDouble(json['vote_average']),
      voteCount: _parseInt(json['vote_count']),
      popularity: _parseDouble(json['popularity']),
      runtime: _parseInt(json['runtime']),
      status: json['status'],
      tagline: json['tagline'],
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

class Genre {
  final int id;
  final String name;
  final String? slug;

  Genre({required this.id, required this.name, this.slug});

  factory Genre.fromJson(Map<String, dynamic> json) {
    return Genre(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      slug: json['slug'],
    );
  }
}

class Category {
  final int id;
  final String name;
  final String? slug;

  Category({required this.id, required this.name, this.slug});

  factory Category.fromJson(Map<String, dynamic> json) {
    return Category(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      slug: json['slug'],
    );
  }
}
