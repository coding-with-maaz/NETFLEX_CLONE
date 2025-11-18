class Episode {
  final int id;
  final int? tvShowId;
  final int? seasonId;
  final int episodeNumber;
  final int seasonNumber;
  final String name;
  final String? overview;
  final String? airDate;
  final String? stillPath;
  final double? voteAverage;
  final int? voteCount;
  final int? runtime;
  final int? viewCount;
  final TVShowInfo? tvShow;
  final SeasonInfo? season;
  final List<EpisodeEmbed>? embeds;
  final List<EpisodeDownload>? downloads;

  Episode({
    required this.id,
    this.tvShowId,
    this.seasonId,
    required this.episodeNumber,
    required this.seasonNumber,
    required this.name,
    this.overview,
    this.airDate,
    this.stillPath,
    this.voteAverage,
    this.voteCount,
    this.runtime,
    this.viewCount,
    this.tvShow,
    this.season,
    this.embeds,
    this.downloads,
  });

  factory Episode.fromJson(Map<String, dynamic> json) {
    // Parse season with error handling
    SeasonInfo? season;
    try {
      if (json['season'] != null && json['season'] is Map<String, dynamic>) {
        season = SeasonInfo.fromJson(json['season']);
      }
    } catch (e) {
      print('Error parsing season for episode ${json['id']}: $e');
      season = null;
    }

    // Parse tvShow with error handling - support multiple field names
    TVShowInfo? tvShow;
    try {
      Map<String, dynamic>? tvShowData;
      if (json['tv_show'] != null && json['tv_show'] is Map<String, dynamic>) {
        tvShowData = json['tv_show'] as Map<String, dynamic>;
      } else if (json['tvShow'] != null && json['tvShow'] is Map<String, dynamic>) {
        tvShowData = json['tvShow'] as Map<String, dynamic>;
      } else if (json['tvshow'] != null && json['tvshow'] is Map<String, dynamic>) {
        tvShowData = json['tvshow'] as Map<String, dynamic>;
      }
      
      if (tvShowData != null) {
        tvShow = TVShowInfo.fromJson(tvShowData);
      }
    } catch (e) {
      print('Error parsing tvShow for episode ${json['id']}: $e');
      tvShow = null;
    }

    // Parse embeds
    List<EpisodeEmbed>? embeds;
    if (json['embeds'] != null && json['embeds'] is List) {
      embeds = (json['embeds'] as List)
          .map((e) => EpisodeEmbed.fromJson(e))
          .toList();
    }

    // Parse downloads
    List<EpisodeDownload>? downloads;
    if (json['downloads'] != null && json['downloads'] is List) {
      downloads = (json['downloads'] as List)
          .map((d) => EpisodeDownload.fromJson(d))
          .toList();
    }

    return Episode(
      id: _parseInt(json['id']) ?? 0,
      tvShowId: _parseInt(json['tv_show_id'] ?? json['tvShowId'] ?? json['tvshow_id']),
      seasonId: _parseInt(json['season_id'] ?? json['seasonId']),
      episodeNumber: _parseInt(json['episode_number'] ?? json['episodeNumber'] ?? json['number']) ?? 0,
      seasonNumber: _parseInt(json['season_number'] ?? json['seasonNumber']) ?? 
                   (season?.seasonNumber ?? 0),
      name: json['name'] ?? '',
      overview: json['overview'],
      airDate: json['air_date'],
      stillPath: json['still_path'],
      voteAverage: _parseDouble(json['vote_average']),
      voteCount: _parseInt(json['vote_count']),
      runtime: _parseInt(json['runtime']),
      viewCount: _parseInt(json['view_count']),
      tvShow: tvShow,
      season: season,
      embeds: embeds,
      downloads: downloads,
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
    if (value is String) {
      final trimmed = value.trim();
      if (trimmed.isEmpty) return null;
      return int.tryParse(trimmed);
    }
    if (value is bool) return value ? 1 : 0;
    if (value is List) return value.length;
    return null;
  }

  // Helper method to get full image URL (handles both full URLs and relative paths)
  String getImageUrl([String size = 'w500']) {
    if (stillPath == null || stillPath!.isEmpty) {
      // Fallback to TV show poster if still_path is not available
      final posterPath = tvShow?.posterPath;
      if (posterPath != null && posterPath.isNotEmpty) {
        if (posterPath.startsWith('http')) {
          return posterPath;
        }
        return 'https://image.tmdb.org/t/p/$size$posterPath';
      }
      return '';
    }
    final path = stillPath!;
    if (path.startsWith('http')) return path;
    if (path.startsWith('/images/')) return path;
    return 'https://image.tmdb.org/t/p/$size$path';
  }
}

class TVShowInfo {
  final int id;
  final String name;
  final String? slug;
  final String? posterPath;
  final String? backdropPath;

  TVShowInfo({
    required this.id, 
    required this.name, 
    this.slug,
    this.posterPath,
    this.backdropPath,
  });

  factory TVShowInfo.fromJson(Map<String, dynamic> json) {
    return TVShowInfo(
      id: _parseIntGlobal(json['id']) ?? 0,
      name: json['name'] ?? '',
      slug: json['slug'],
      posterPath: json['poster_path'],
      backdropPath: json['backdrop_path'],
    );
  }
}

class SeasonInfo {
  final int id;
  final int seasonNumber;
  final String? name;
  final int? episodeCount;

  SeasonInfo({
    required this.id,
    required this.seasonNumber,
    this.name,
    this.episodeCount,
  });

  factory SeasonInfo.fromJson(Map<String, dynamic> json) {
    // Handle episodeCount - try episode_count first, then episodes array length
    int? episodeCount;
    try {
      if (json['episode_count'] != null) {
        episodeCount = _parseIntGlobal(json['episode_count']);
      } else if (json['episodes'] != null) {
        if (json['episodes'] is List) {
          episodeCount = (json['episodes'] as List).length;
        } else {
          episodeCount = _parseIntGlobal(json['episodes']);
        }
      }
    } catch (e) {
      print('Error parsing episode count: $e');
      episodeCount = null;
    }

    return SeasonInfo(
      id: _parseIntGlobal(json['id']) ?? 0,
      seasonNumber: _parseIntGlobal(json['season_number'] ?? json['number']) ?? 0,
      name: json['name'],
      episodeCount: episodeCount,
    );
  }
}

// Embed for episode
class EpisodeEmbed {
  final int id;
  final String serverName;
  final String embedUrl;
  final int? priority;
  final bool? isActive;
  final bool? requiresAd;

  EpisodeEmbed({
    required this.id,
    required this.serverName,
    required this.embedUrl,
    this.priority,
    this.isActive,
    this.requiresAd,
  });

  factory EpisodeEmbed.fromJson(Map<String, dynamic> json) {
    // Parse requires_ad - handle both boolean and string values
    bool? requiresAd;
    if (json['requires_ad'] != null) {
      if (json['requires_ad'] is bool) {
        requiresAd = json['requires_ad'] as bool;
      } else if (json['requires_ad'] is String) {
        requiresAd = json['requires_ad'].toString().toLowerCase() == 'true' || json['requires_ad'] == '1';
      } else if (json['requires_ad'] is int) {
        requiresAd = json['requires_ad'] == 1;
      }
    }
    
    return EpisodeEmbed(
      id: json['id'] ?? 0,
      serverName: json['server_name'] ?? json['title'] ?? '',
      embedUrl: json['embed_url'] ?? json['iframe_url'] ?? '',
      priority: _parseIntGlobal(json['priority']),
      isActive: json['is_active'] ?? true,
      requiresAd: requiresAd ?? false,
    );
  }
}

// Download for episode
class EpisodeDownload {
  final int id;
  final String serverName;
  final String downloadUrl;
  final String? quality;
  final String? size;
  final int? priority;
  final bool? isActive;

  EpisodeDownload({
    required this.id,
    required this.serverName,
    required this.downloadUrl,
    this.quality,
    this.size,
    this.priority,
    this.isActive,
  });

  factory EpisodeDownload.fromJson(Map<String, dynamic> json) {
    return EpisodeDownload(
      id: json['id'] ?? 0,
      serverName: json['server_name'] ?? json['title'] ?? '',
      downloadUrl: json['download_url'] ?? json['url'] ?? '',
      quality: json['quality'],
      size: json['size'],
      priority: _parseIntGlobal(json['priority']),
      isActive: json['is_active'] ?? true,
    );
  }
}

// Global helper function for nested classes
int? _parseIntGlobal(dynamic value) {
  if (value == null) return null;
  if (value is int) return value;
  if (value is double) return value.toInt();
  if (value is String) {
    final trimmed = value.trim();
    if (trimmed.isEmpty) return null;
    return int.tryParse(trimmed);
  }
  if (value is bool) return value ? 1 : 0;
  if (value is List) return value.length;
  return null;
}
