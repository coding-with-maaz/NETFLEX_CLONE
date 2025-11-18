import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:url_launcher/url_launcher.dart';
import '../models/tvshow.dart';
import '../models/season.dart';
import '../models/episode.dart';
import '../services/api_service.dart';
import '../widgets/tvshow_card.dart';

class TVShowDetailPage extends StatefulWidget {
  final int tvShowId;
  final String? tvShowName;

  const TVShowDetailPage({
    Key? key,
    required this.tvShowId,
    this.tvShowName,
  }) : super(key: key);

  @override
  State<TVShowDetailPage> createState() => _TVShowDetailPageState();
}

class _TVShowDetailPageState extends State<TVShowDetailPage> {
  TVShow? _tvShow;
  List<Season> _seasons = [];
  Season? _selectedSeason;
  List<Episode> _episodes = [];
  List<TVShow> _similarShows = [];
  bool _loading = true;
  bool _loadingEpisodes = false;
  String _activeTab = 'episodes'; // episodes, details
  Episode? _expandedEpisode;
  EpisodeEmbed? _activeEmbed;
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _fetchTVShowDetails();
    _trackView();
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _trackView() async {
    await ApiService.trackTVShowView(widget.tvShowId);
  }

  Future<void> _fetchTVShowDetails() async {
    setState(() => _loading = true);

    try {
      // Fetch TV show details
      final tvShow = await ApiService.getTVShowById(widget.tvShowId);
      if (tvShow == null) {
        setState(() => _loading = false);
        return;
      }

      // Fetch seasons
      final seasons = await ApiService.getTVShowSeasons(widget.tvShowId);

      // Auto-select first season and fetch episodes
      Season? selectedSeason;
      List<Episode> episodes = [];
      if (seasons.isNotEmpty) {
        selectedSeason = seasons[0];
        episodes = await ApiService.getSeasonEpisodes(
          widget.tvShowId,
          seasons[0].id,
        );
      }

      // Fetch similar TV shows
      List<TVShow> similarShows = [];
      if (tvShow.genres != null && tvShow.genres!.isNotEmpty) {
        final genreId = tvShow.genres![0].id.toString();
        final result = await ApiService.getTVShowsWithParams(
          params: {
            'genre': genreId,
            'limit': '10',
          },
        );
        similarShows = (result['tvShows'] as List<TVShow>?)
                ?.where((s) => s.id != widget.tvShowId)
                .toList() ??
            [];
      }

      setState(() {
        _tvShow = tvShow;
        _seasons = seasons;
        _selectedSeason = selectedSeason;
        _episodes = episodes;
        _similarShows = similarShows;
        _loading = false;
      });
    } catch (e) {
      print('Error fetching TV show details: $e');
      setState(() => _loading = false);
    }
  }

  Future<void> _fetchEpisodes(Season season) async {
    setState(() => _loadingEpisodes = true);

    try {
      final episodes = await ApiService.getSeasonEpisodes(
        widget.tvShowId,
        season.id,
      );
      setState(() {
        _episodes = episodes;
        _loadingEpisodes = false;
      });
    } catch (e) {
      print('Error fetching episodes: $e');
      setState(() {
        _episodes = [];
        _loadingEpisodes = false;
      });
    }
  }

  void _handleSeasonChange(Season season) {
    setState(() {
      _selectedSeason = season;
      _expandedEpisode = null;
      _activeEmbed = null;
    });
    _fetchEpisodes(season);
  }

  void _handleEpisodeClick(Episode episode) {
    setState(() {
      if (_expandedEpisode?.id == episode.id) {
        _expandedEpisode = null;
        _activeEmbed = null;
      } else {
        _expandedEpisode = episode;
        if (episode.embeds != null && episode.embeds!.isNotEmpty) {
          _activeEmbed = episode.embeds![0];
        }
      }
    });
  }

  Future<void> _handleWatchNow() async {
    setState(() => _activeTab = 'episodes');

    // Fetch episodes if needed
    if (_episodes.isEmpty && _seasons.isNotEmpty) {
      await _fetchEpisodes(_seasons[0]);
    }

    // Scroll to top and expand first episode
    _scrollController.animateTo(
      0,
      duration: const Duration(milliseconds: 500),
      curve: Curves.easeInOut,
    );

    if (_episodes.isNotEmpty) {
      final firstEpisode = _episodes[0];
      setState(() {
        _expandedEpisode = firstEpisode;
        if (firstEpisode.embeds != null && firstEpisode.embeds!.isNotEmpty) {
          _activeEmbed = firstEpisode.embeds![0];
        }
      });
    }
  }

  Future<void> _launchUrl(String url) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Could not open link')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return Scaffold(
        backgroundColor: Colors.black,
        appBar: AppBar(
          backgroundColor: Colors.black,
          elevation: 0,
          iconTheme: const IconThemeData(color: Colors.white),
        ),
        body: const Center(
          child: CircularProgressIndicator(
            valueColor: AlwaysStoppedAnimation<Color>(Colors.red),
          ),
        ),
      );
    }

    if (_tvShow == null) {
      return Scaffold(
        backgroundColor: Colors.black,
        appBar: AppBar(
          backgroundColor: Colors.black,
          elevation: 0,
          iconTheme: const IconThemeData(color: Colors.white),
        ),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Text(
                'TV Show not found',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => Navigator.pop(context),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.red,
                ),
                child: const Text('Back to TV Shows'),
              ),
            ],
          ),
        ),
      );
    }

    final backdropUrl = _tvShow!.getBackdropUrl('w1280');

    return Scaffold(
      backgroundColor: Colors.black,
      body: SingleChildScrollView(
        controller: _scrollController,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Hero Section
            _buildHeroSection(backdropUrl),

            // Tabs Content
            Padding(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Episodes Tab
                  if (_activeTab == 'episodes') _buildEpisodesTab(),

                  // Details Tab
                  if (_activeTab == 'details') _buildDetailsTab(),
                ],
              ),
            ),

            // Similar TV Shows
            if (_similarShows.isNotEmpty) _buildSimilarShows(),
          ],
        ),
      ),
    );
  }

  Widget _buildHeroSection(String backdropUrl) {
    return Stack(
      children: [
        // Backdrop Image
        SizedBox(
          height: 600,
          width: double.infinity,
          child: Stack(
            children: [
              if (backdropUrl.isNotEmpty)
                CachedNetworkImage(
                  imageUrl: backdropUrl,
                  fit: BoxFit.cover,
                  width: double.infinity,
                  height: double.infinity,
                  placeholder: (context, url) => Container(
                    color: Colors.grey[900],
                  ),
                  errorWidget: (context, url, error) => Container(
                    color: Colors.grey[900],
                  ),
                )
              else
                Container(color: Colors.grey[900]),
              // Gradients
              Container(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [
                      Colors.black.withOpacity(0),
                      Colors.black.withOpacity(0.6),
                      Colors.black,
                    ],
                  ),
                ),
              ),
              Container(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.centerLeft,
                    end: Alignment.centerRight,
                    colors: [
                      Colors.black.withOpacity(0.8),
                      Colors.black.withOpacity(0),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),

        // Content
        SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Back Button
                IconButton(
                  onPressed: () => Navigator.pop(context),
                  icon: const Icon(Icons.arrow_back, color: Colors.white),
                  style: IconButton.styleFrom(
                    backgroundColor: Colors.black.withOpacity(0.5),
                  ),
                ),

                const SizedBox(height: 350),

                // Title
                Text(
                  _tvShow!.name,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 36,
                    fontWeight: FontWeight.bold,
                    height: 1.2,
                  ),
                ),
                const SizedBox(height: 16),

                // Metadata
                Wrap(
                  spacing: 12,
                  runSpacing: 8,
                  children: [
                    if (_tvShow!.voteAverage != null)
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.yellow.shade700.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              Icons.star,
                              color: Colors.yellow.shade700,
                              size: 16,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              _tvShow!.voteAverage!.toStringAsFixed(1),
                              style: TextStyle(
                                color: Colors.yellow.shade700,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      ),
                    if (_tvShow!.firstAirDate != null &&
                        _tvShow!.firstAirDate!.length >= 4)
                      _buildMetadataChip(
                        Icons.calendar_today,
                        _tvShow!.firstAirDate!.substring(0, 4),
                      ),
                    if (_tvShow!.numberOfSeasons != null)
                      _buildMetadataChip(
                        Icons.tv,
                        '${_tvShow!.numberOfSeasons} Season${_tvShow!.numberOfSeasons! > 1 ? 's' : ''}',
                      ),
                    if (_tvShow!.numberOfEpisodes != null)
                      _buildMetadataChip(
                        Icons.video_library,
                        '${_tvShow!.numberOfEpisodes} Episodes',
                      ),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 6,
                      ),
                      decoration: BoxDecoration(
                        border: Border.all(color: Colors.grey.shade400),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        _tvShow!.status == 'active' ? 'Available' : 'Coming Soon',
                        style: TextStyle(
                          color: Colors.grey.shade300,
                          fontSize: 12,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                // Genres
                if (_tvShow!.genres != null && _tvShow!.genres!.isNotEmpty)
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: _tvShow!.genres!.map((genre) {
                      return Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.grey.shade800.withOpacity(0.8),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          genre.name,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 12,
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                const SizedBox(height: 16),

                // Overview
                if (_tvShow!.overview != null && _tvShow!.overview!.isNotEmpty)
                  Text(
                    _tvShow!.overview!,
                    maxLines: 3,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      color: Colors.grey.shade300,
                      fontSize: 16,
                      height: 1.4,
                    ),
                  ),
                const SizedBox(height: 24),

                // Action Buttons
                Wrap(
                  spacing: 12,
                  runSpacing: 12,
                  children: [
                    ElevatedButton.icon(
                      onPressed: _handleWatchNow,
                      icon: const Icon(Icons.play_arrow, color: Colors.white),
                      label: const Text(
                        'Watch Now',
                        style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.red,
                        padding: const EdgeInsets.symmetric(
                          horizontal: 24,
                          vertical: 12,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                    ),
                    ElevatedButton.icon(
                      onPressed: () => setState(() => _activeTab = 'details'),
                      icon: const Icon(Icons.info_outline, color: Colors.white),
                      label: const Text(
                        'More Info',
                        style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: _activeTab == 'details'
                            ? Colors.red
                            : Colors.grey.shade800,
                        padding: const EdgeInsets.symmetric(
                          horizontal: 24,
                          vertical: 12,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildMetadataChip(IconData icon, String text) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, color: Colors.white, size: 16),
        const SizedBox(width: 4),
        Text(
          text,
          style: const TextStyle(color: Colors.white, fontSize: 14),
        ),
      ],
    );
  }

  Widget _buildEpisodesTab() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Header with season selector
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Episodes',
              style: TextStyle(
                color: Colors.white,
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
            ),
            // Season Selector
            if (_seasons.isNotEmpty)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12),
                decoration: BoxDecoration(
                  color: Colors.grey.shade800,
                  border: Border.all(color: Colors.grey.shade700),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: DropdownButton<int>(
                  value: _selectedSeason?.id,
                  dropdownColor: Colors.grey.shade800,
                  underline: const SizedBox(),
                  style: const TextStyle(color: Colors.white),
                  icon: const Icon(Icons.arrow_drop_down, color: Colors.white),
                  onChanged: (value) {
                    final season = _seasons.firstWhere((s) => s.id == value);
                    _handleSeasonChange(season);
                  },
                  items: _seasons.map((season) {
                    return DropdownMenuItem<int>(
                      value: season.id,
                      child: Text(
                        'Season ${season.seasonNumber}${season.episodeCount != null ? ' (${season.episodeCount} episodes)' : ''}',
                      ),
                    );
                  }).toList(),
                ),
              ),
          ],
        ),
        const SizedBox(height: 16),

        // Episodes List
        if (_loadingEpisodes)
          const Center(
            child: Padding(
              padding: EdgeInsets.all(48.0),
              child: CircularProgressIndicator(
                valueColor: AlwaysStoppedAnimation<Color>(Colors.red),
              ),
            ),
          )
        else if (_episodes.isNotEmpty)
          ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _episodes.length,
            itemBuilder: (context, index) {
              return _buildEpisodeCard(_episodes[index]);
            },
          )
        else
          Container(
            padding: const EdgeInsets.all(48),
            decoration: BoxDecoration(
              color: Colors.grey.shade900,
              border: Border.all(color: Colors.grey.shade800),
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Center(
              child: Column(
                children: [
                  Icon(Icons.calendar_today, color: Colors.grey, size: 64),
                  SizedBox(height: 16),
                  Text(
                    'Season Coming Soon',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  SizedBox(height: 8),
                  Text(
                    'Episodes will be available soon. Stay tuned!',
                    style: TextStyle(color: Colors.grey),
                  ),
                ],
              ),
            ),
          ),
      ],
    );
  }

  Widget _buildEpisodeCard(Episode episode) {
    final isExpanded = _expandedEpisode?.id == episode.id;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.grey.shade900,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        children: [
          // Episode Header
          InkWell(
            onTap: () => _handleEpisodeClick(episode),
            child: Padding(
              padding: const EdgeInsets.all(16.0),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Episode Number
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: Colors.grey.shade800,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Center(
                      child: Text(
                        '${episode.episodeNumber}',
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 18,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 16),

                  // Episode Info
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          episode.name,
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w600,
                            fontSize: 16,
                          ),
                        ),
                        if (episode.overview != null &&
                            episode.overview!.isNotEmpty) ...[
                          const SizedBox(height: 4),
                          Text(
                            episode.overview!,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              color: Colors.grey.shade400,
                              fontSize: 14,
                            ),
                          ),
                        ],
                        if (episode.airDate != null) ...[
                          const SizedBox(height: 4),
                          Text(
                            _formatDate(episode.airDate!),
                            style: TextStyle(
                              color: Colors.grey.shade600,
                              fontSize: 12,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),

                  // Expand Icon
                  Icon(
                    isExpanded ? Icons.expand_less : Icons.expand_more,
                    color: Colors.white,
                  ),
                ],
              ),
            ),
          ),

          // Expanded Content
          if (isExpanded)
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                border: Border(
                  top: BorderSide(color: Colors.grey.shade800),
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Video Player
                  if (episode.embeds != null && episode.embeds!.isNotEmpty) ...[
                    // Server Selection
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: episode.embeds!.asMap().entries.map((entry) {
                        final index = entry.key;
                        final embed = entry.value;
                        final isActive = _activeEmbed?.id == embed.id;
                        return ElevatedButton(
                          onPressed: () {
                            setState(() => _activeEmbed = embed);
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor:
                                isActive ? Colors.red : Colors.grey.shade800,
                            foregroundColor:
                                isActive ? Colors.white : Colors.grey.shade300,
                          ),
                          child: Text(
                            'Server ${index + 1}',
                          ),
                        );
                      }).toList(),
                    ),
                    const SizedBox(height: 16),
                    // Video iframe placeholder
                    if (_activeEmbed != null)
                      AspectRatio(
                        aspectRatio: 16 / 9,
                        child: Container(
                          decoration: BoxDecoration(
                            color: Colors.black,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                const Icon(Icons.play_circle_outline,
                                    color: Colors.white, size: 64),
                                const SizedBox(height: 16),
                                const Text(
                                  'Video Player',
                                  style: TextStyle(
                                      color: Colors.white, fontSize: 18),
                                ),
                                const SizedBox(height: 8),
                                ElevatedButton(
                                  onPressed: () =>
                                      _launchUrl(_activeEmbed!.embedUrl),
                                  style: ElevatedButton.styleFrom(
                                      backgroundColor: Colors.red),
                                  child: const Text('Open in Browser'),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                  ] else
                    Center(
                      child: Padding(
                        padding: const EdgeInsets.all(16.0),
                        child: Text(
                          'No streaming links available for this episode.',
                          style: TextStyle(color: Colors.grey.shade400),
                        ),
                      ),
                    ),

                  // Download Links
                  if (episode.downloads != null &&
                      episode.downloads!.isNotEmpty) ...[
                    const SizedBox(height: 16),
                    const Text(
                      'Download Options',
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                    const SizedBox(height: 12),
                    GridView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: 2,
                        childAspectRatio: 2.5,
                        crossAxisSpacing: 12,
                        mainAxisSpacing: 12,
                      ),
                      itemCount: episode.downloads!.length,
                      itemBuilder: (context, index) {
                        final download = episode.downloads![index];
                        return InkWell(
                          onTap: () => _launchUrl(download.downloadUrl),
                          child: Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: Colors.grey.shade800,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Text(
                                        download.quality ?? 'HD',
                                        style: const TextStyle(
                                          color: Colors.white,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                      if (download.size != null)
                                        Text(
                                          download.size!,
                                          style: TextStyle(
                                            color: Colors.grey.shade400,
                                            fontSize: 12,
                                          ),
                                        ),
                                    ],
                                  ),
                                ),
                                const Icon(Icons.download,
                                    color: Colors.red, size: 20),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
                  ],
                ],
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildDetailsTab() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Show Details',
          style: TextStyle(
            color: Colors.white,
            fontSize: 24,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 16),
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Left Column
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Overview
                  const Text(
                    'Overview',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    _tvShow!.overview ?? 'No description available.',
                    style: TextStyle(
                      color: Colors.grey.shade300,
                      height: 1.5,
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Category
                  if (_tvShow!.category != null) ...[
                    const Row(
                      children: [
                        Icon(Icons.category, color: Colors.white, size: 20),
                        SizedBox(width: 8),
                        Text(
                          'Category',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 6,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.grey.shade800,
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        _tvShow!.category!.name,
                        style: TextStyle(color: Colors.grey.shade300),
                      ),
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(width: 24),

            // Right Column - Information Panel
            Expanded(
              child: Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.grey.shade900,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Information',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 16),
                    if (_tvShow!.firstAirDate != null)
                      _buildInfoRow(
                        'First Air Date',
                        _formatDate(_tvShow!.firstAirDate!),
                      ),
                    if (_tvShow!.lastAirDate != null)
                      _buildInfoRow(
                        'Last Air Date',
                        _formatDate(_tvShow!.lastAirDate!),
                      ),
                    if (_tvShow!.numberOfSeasons != null)
                      _buildInfoRow(
                        'Seasons',
                        _tvShow!.numberOfSeasons!.toString(),
                      ),
                    if (_tvShow!.numberOfEpisodes != null)
                      _buildInfoRow(
                        'Episodes',
                        _tvShow!.numberOfEpisodes!.toString(),
                      ),
                    if (_tvShow!.voteAverage != null)
                      _buildInfoRow(
                        'Rating',
                        '${_tvShow!.voteAverage!.toStringAsFixed(1)} / 10',
                      ),
                    if (_tvShow!.voteCount != null)
                      _buildInfoRow(
                        'Votes',
                        _tvShow!.voteCount!.toStringAsFixed(0),
                      ),
                    if (_tvShow!.popularity != null)
                      _buildInfoRow(
                        'Popularity',
                        _tvShow!.popularity!.toStringAsFixed(1),
                      ),
                    _buildInfoRow(
                      'Status',
                      _tvShow!.status == 'active' ? 'Available' : 'Coming Soon',
                      valueColor: _tvShow!.status == 'active'
                          ? Colors.green.shade400
                          : Colors.yellow.shade400,
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildInfoRow(String label, String value, {Color? valueColor}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                label,
                style: TextStyle(color: Colors.grey.shade400),
              ),
              Text(
                value,
                style: TextStyle(
                  color: valueColor ?? Colors.white,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Divider(color: Colors.grey.shade800, height: 1),
        ],
      ),
    );
  }

  Widget _buildSimilarShows() {
    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'More Like This',
            style: TextStyle(
              color: Colors.white,
              fontSize: 24,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          SizedBox(
            height: 220,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: _similarShows.length,
              itemBuilder: (context, index) {
                return TVShowCard(
                  tvShow: _similarShows[index],
                  onTap: () {
                    Navigator.pushReplacement(
                      context,
                      MaterialPageRoute(
                        builder: (context) => TVShowDetailPage(
                          tvShowId: _similarShows[index].id,
                          tvShowName: _similarShows[index].name,
                        ),
                      ),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  String _formatDate(String dateString) {
    try {
      final date = DateTime.parse(dateString);
      return '${date.day}/${date.month}/${date.year}';
    } catch (e) {
      return dateString;
    }
  }
}

