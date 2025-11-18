class Season {
  final int id;
  final int? tvShowId;
  final int seasonNumber;
  final String? name;
  final String? overview;
  final String? posterPath;
  final String? airDate;
  final int? episodeCount;
  final List<dynamic>? episodes;

  Season({
    required this.id,
    this.tvShowId,
    required this.seasonNumber,
    this.name,
    this.overview,
    this.posterPath,
    this.airDate,
    this.episodeCount,
    this.episodes,
  });

  factory Season.fromJson(Map<String, dynamic> json) {
    // Handle episodeCount - try episode_count first, then episodes array length
    int? episodeCount;
    try {
      if (json['episode_count'] != null) {
        episodeCount = _parseInt(json['episode_count']);
      } else if (json['episodes'] != null && json['episodes'] is List) {
        episodeCount = (json['episodes'] as List).length;
      } else if (json['number_of_episodes'] != null) {
        episodeCount = _parseInt(json['number_of_episodes']);
      }
    } catch (e) {
      print('Error parsing episode count: $e');
      episodeCount = null;
    }

    return Season(
      id: json['id'] ?? 0,
      tvShowId: _parseInt(json['tv_show_id']),
      seasonNumber: _parseInt(json['season_number'] ?? json['number']) ?? 0,
      name: json['name'],
      overview: json['overview'],
      posterPath: json['poster_path'],
      airDate: json['air_date'],
      episodeCount: episodeCount,
      episodes: json['episodes'],
    );
  }

  static int? _parseInt(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is double) return value.toInt();
    if (value is String) {
      final trimmed = value.trim();
      if (trimmed.isEmpty) return null;
      return int.tryParse(trimmed);
    }
    return null;
  }

  // Helper method to get full image URL (handles both full URLs and relative paths)
  String getImageUrl([String size = 'w500']) {
    if (posterPath == null || posterPath!.isEmpty) return '';
    if (posterPath!.startsWith('http')) return posterPath!;
    if (posterPath!.startsWith('/images/')) return posterPath!;
    return 'https://image.tmdb.org/t/p/$size$posterPath';
  }
}
