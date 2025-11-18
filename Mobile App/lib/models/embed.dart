class MovieEmbed {
  final int id;
  final String serverName;
  final String embedUrl;
  final int? priority;
  final bool? isActive;
  final bool? requiresAd;
  final Map<String, dynamic>? language;

  MovieEmbed({
    required this.id,
    required this.serverName,
    required this.embedUrl,
    this.priority,
    this.isActive,
    this.requiresAd,
    this.language,
  });

  factory MovieEmbed.fromJson(Map<String, dynamic> json) {
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
    
    return MovieEmbed(
      id: json['id'] ?? 0,
      serverName: json['server_name'] ?? json['title'] ?? '',
      embedUrl: json['embed_url'] ?? json['iframe_url'] ?? '',
      priority: _parseInt(json['priority']),
      isActive: json['is_active'] ?? true,
      requiresAd: requiresAd ?? false,
      language: json['language'] != null && json['language'] is Map
          ? Map<String, dynamic>.from(json['language'])
          : null,
    );
  }

  static int? _parseInt(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is double) return value.toInt();
    if (value is String) return int.tryParse(value);
    return null;
  }
}

class DownloadLink {
  final int id;
  final String serverName;
  final String downloadUrl;
  final String? quality;
  final String? size;
  final int? priority;
  final bool? isActive;

  DownloadLink({
    required this.id,
    required this.serverName,
    required this.downloadUrl,
    this.quality,
    this.size,
    this.priority,
    this.isActive,
  });

  factory DownloadLink.fromJson(Map<String, dynamic> json) {
    return DownloadLink(
      id: json['id'] ?? 0,
      serverName: json['server_name'] ?? json['title'] ?? '',
      downloadUrl: json['download_url'] ?? json['url'] ?? '',
      quality: json['quality'],
      size: json['size'],
      priority: _parseInt(json['priority']),
      isActive: json['is_active'] ?? true,
    );
  }

  static int? _parseInt(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is double) return value.toInt();
    if (value is String) return int.tryParse(value);
    return null;
  }
}
