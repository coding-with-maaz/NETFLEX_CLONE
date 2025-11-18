import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import '../models/episode.dart';

class TodayEpisodesPage extends StatefulWidget {
  const TodayEpisodesPage({super.key});

  @override
  State<TodayEpisodesPage> createState() => _TodayEpisodesPageState();
}

class _TodayEpisodesPageState extends State<TodayEpisodesPage> {
  List<Episode> _episodes = [];
  bool _isLoading = true;
  DateTime _selectedDate = DateTime.now();
  final DateTime _today = DateTime.now();

  @override
  void initState() {
    super.initState();
    _fetchEpisodesByDate();
  }

  String _formatDateForApi(DateTime date) {
    return DateFormat('yyyy-MM-dd').format(date);
  }

  String _formatDateForDisplay(DateTime date) {
    return DateFormat('EEEE, MMMM d, yyyy').format(date);
  }

  bool get _isToday {
    return _selectedDate.year == _today.year &&
        _selectedDate.month == _today.month &&
        _selectedDate.day == _today.day;
  }

  Future<void> _fetchEpisodesByDate() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final dateStr = _formatDateForApi(_selectedDate);
      final episodes = await ApiService.getEpisodesByDate(dateStr);
      
      setState(() {
        _episodes = episodes;
        _isLoading = false;
      });
      
      print('Loaded ${_episodes.length} episodes for date: $dateStr');
    } catch (e) {
      print('Error fetching episodes by date: $e');
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _goToPreviousDay() {
    setState(() {
      _selectedDate = _selectedDate.subtract(const Duration(days: 1));
    });
    _fetchEpisodesByDate();
  }

  void _goToNextDay() {
    if (!_isToday) {
      final nextDate = _selectedDate.add(const Duration(days: 1));
      if (nextDate.isBefore(_today.add(const Duration(days: 1)))) {
        setState(() {
          _selectedDate = nextDate;
        });
        _fetchEpisodesByDate();
      }
    }
  }

  void _goToToday() {
    setState(() {
      _selectedDate = DateTime.now();
    });
    _fetchEpisodesByDate();
  }

  Future<void> _selectDate() async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime(2000),
      lastDate: _today,
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: ColorScheme.dark(
              primary: Colors.red,
              onPrimary: Colors.white,
              surface: Colors.grey[900]!,
              onSurface: Colors.white,
            ),
            dialogBackgroundColor: Colors.grey[900],
          ),
          child: child!,
        );
      },
    );

    if (picked != null && picked != _selectedDate) {
      setState(() {
        _selectedDate = picked;
      });
      _fetchEpisodesByDate();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: RefreshIndicator(
        onRefresh: _fetchEpisodesByDate,
        color: Colors.red,
        backgroundColor: Colors.grey[900],
        child: CustomScrollView(
          slivers: [
            // App Bar with back button
            SliverAppBar(
              floating: true,
              backgroundColor: Colors.black,
              leading: IconButton(
                icon: const Icon(Icons.arrow_back, color: Colors.white),
                onPressed: () => Navigator.pop(context),
              ),
            ),

            // Content
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Header with calendar icon
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: Colors.red,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Icon(
                            Icons.calendar_today,
                            color: Colors.white,
                            size: 32,
                          ),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                _isToday ? "Today's Episodes" : "Episodes by Date",
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 28,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                _formatDateForDisplay(_selectedDate),
                                style: TextStyle(
                                  color: Colors.grey[400],
                                  fontSize: 14,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 24),

                    // Date picker controls
                    Row(
                      children: [
                        // Previous day button
                        IconButton(
                          onPressed: _goToPreviousDay,
                          icon: const Icon(Icons.chevron_left),
                          style: IconButton.styleFrom(
                            backgroundColor: Colors.grey[850],
                            foregroundColor: Colors.white,
                          ),
                          tooltip: 'Previous Day',
                        ),

                        const SizedBox(width: 8),

                        // Date picker button
                        Expanded(
                          child: ElevatedButton.icon(
                            onPressed: _selectDate,
                            icon: const Icon(Icons.calendar_month),
                            label: Text(_formatDateForApi(_selectedDate)),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.grey[850],
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(8),
                                side: BorderSide(color: Colors.grey[700]!),
                              ),
                            ),
                          ),
                        ),

                        const SizedBox(width: 8),

                        // Next day button
                        IconButton(
                          onPressed: _isToday ? null : _goToNextDay,
                          icon: const Icon(Icons.chevron_right),
                          style: IconButton.styleFrom(
                            backgroundColor: _isToday ? Colors.grey[900] : Colors.grey[850],
                            foregroundColor: _isToday ? Colors.grey[600] : Colors.white,
                            disabledBackgroundColor: Colors.grey[900],
                            disabledForegroundColor: Colors.grey[600],
                          ),
                          tooltip: 'Next Day',
                        ),

                        const SizedBox(width: 8),

                        // Today button
                        if (!_isToday)
                          ElevatedButton(
                            onPressed: _goToToday,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.red,
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(8),
                              ),
                            ),
                            child: const Text(
                              'Today',
                              style: TextStyle(fontWeight: FontWeight.w600),
                            ),
                          ),
                      ],
                    ),

                    const SizedBox(height: 24),

                    // Episode count badge
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                      decoration: BoxDecoration(
                        color: Colors.grey[850],
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(
                            Icons.tv,
                            color: Colors.red,
                            size: 20,
                          ),
                          const SizedBox(width: 8),
                          Text(
                            '${_episodes.length}',
                            style: const TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                              fontSize: 16,
                            ),
                          ),
                          const SizedBox(width: 8),
                          Text(
                            _episodes.length == 1 ? 'Episode' : 'Episodes',
                            style: TextStyle(
                              color: Colors.grey[400],
                              fontSize: 14,
                            ),
                          ),
                          const SizedBox(width: 4),
                          Text(
                            'Uploaded',
                            style: TextStyle(
                              color: Colors.grey[400],
                              fontSize: 14,
                            ),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 24),

                    // Loading state
                    if (_isLoading)
                      const Center(
                        child: Padding(
                          padding: EdgeInsets.all(40),
                          child: CircularProgressIndicator(
                            color: Colors.red,
                          ),
                        ),
                      ),

                    // Empty state
                    if (!_isLoading && _episodes.isEmpty)
                      Center(
                        child: Padding(
                          padding: const EdgeInsets.all(40),
                          child: Column(
                            children: [
                              Container(
                                width: 80,
                                height: 80,
                                decoration: BoxDecoration(
                                  color: Colors.grey[850],
                                  shape: BoxShape.circle,
                                ),
                                child: Icon(
                                  Icons.calendar_today,
                                  size: 40,
                                  color: Colors.grey[600],
                                ),
                              ),
                              const SizedBox(height: 24),
                              Text(
                                'No Episodes Uploaded on',
                                style: TextStyle(
                                  color: Colors.grey[400],
                                  fontSize: 18,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                _formatDateForDisplay(_selectedDate),
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 22,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 16),
                              Text(
                                _isToday
                                    ? 'Check back later for new episodes!'
                                    : 'Try selecting a different date or browse all TV shows.',
                                style: TextStyle(
                                  color: Colors.grey[500],
                                  fontSize: 14,
                                ),
                              ),
                              const SizedBox(height: 32),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  if (!_isToday)
                                    ElevatedButton.icon(
                                      onPressed: _goToToday,
                                      icon: const Icon(Icons.calendar_today),
                                      label: const Text('Go to Today'),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: Colors.grey[850],
                                        foregroundColor: Colors.white,
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 24,
                                          vertical: 16,
                                        ),
                                      ),
                                    ),
                                  if (!_isToday) const SizedBox(width: 16),
                                  ElevatedButton.icon(
                                    onPressed: () {
                                      Navigator.pushNamed(context, '/tvshows');
                                    },
                                    icon: const Icon(Icons.play_arrow),
                                    label: const Text('Browse All TV Shows'),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: Colors.red,
                                      foregroundColor: Colors.white,
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 24,
                                        vertical: 16,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),

                    // Episodes grid - Fixed 2 columns per row
                    if (!_isLoading && _episodes.isNotEmpty)
                      GridView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: 2,
                          childAspectRatio: 0.75, // Adjusted for better card proportions
                          crossAxisSpacing: 16,
                          mainAxisSpacing: 20,
                        ),
                        itemCount: _episodes.length,
                        itemBuilder: (context, index) {
                          final episode = _episodes[index];
                          return _buildStyledEpisodeCard(episode);
                        },
                      ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStyledEpisodeCard(Episode episode) {
    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.red.withOpacity(0.3),
            blurRadius: 20,
            spreadRadius: 2,
            offset: const Offset(0, 8),
          ),
          BoxShadow(
            color: Colors.black.withOpacity(0.6),
            blurRadius: 15,
            spreadRadius: 1,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: Material(
          color: Colors.transparent,
          child: InkWell(
            onTap: () {
              if (episode.tvShowId != null) {
                Navigator.pushNamed(
                  context,
                  '/tvshow/${episode.tvShowId}',
                  arguments: {'episodeId': episode.id},
                );
              } else if (episode.tvShow != null) {
                Navigator.pushNamed(
                  context,
                  '/tvshow/${episode.tvShow!.id}',
                  arguments: {'episodeId': episode.id},
                );
              }
            },
            borderRadius: BorderRadius.circular(16),
            splashColor: Colors.red.withOpacity(0.3),
            highlightColor: Colors.red.withOpacity(0.1),
            child: Stack(
              children: [
            // Background Image with Gradient Overlay
            Positioned.fill(
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.grey[900],
                ),
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    // Episode Image
                    Builder(
                      builder: (context) {
                        final imageUrl = episode.getImageUrl('w500');
                        // Check if URL is valid (starts with http/https, not placeholder or relative paths)
                        final isValidUrl = imageUrl.isNotEmpty && 
                            (imageUrl.startsWith('http://') || imageUrl.startsWith('https://')) &&
                            !imageUrl.contains('placeholder');
                        
                        if (isValidUrl)
                          return Image.network(
                            imageUrl,
                            fit: BoxFit.cover,
                            errorBuilder: (context, error, stackTrace) {
                              return Container(
                                color: Colors.grey[900],
                                child: Icon(
                                  Icons.tv,
                                  size: 60,
                                  color: Colors.grey[700],
                                ),
                              );
                            },
                            loadingBuilder: (context, child, loadingProgress) {
                              if (loadingProgress == null) return child;
                              return Container(
                                color: Colors.grey[900],
                                child: Center(
                                  child: CircularProgressIndicator(
                                    color: Colors.red,
                                    value: loadingProgress.expectedTotalBytes != null
                                        ? loadingProgress.cumulativeBytesLoaded /
                                            loadingProgress.expectedTotalBytes!
                                        : null,
                                  ),
                                ),
                              );
                            },
                          );
                        else
                          return Container(
                            color: Colors.grey[900],
                            child: Icon(
                              Icons.tv,
                              size: 60,
                              color: Colors.grey[700],
                            ),
                          );
                      },
                    ),
                    
                    // Gradient Overlay
                    Container(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment.topCenter,
                          end: Alignment.bottomCenter,
                          colors: [
                            Colors.transparent,
                            Colors.black.withOpacity(0.3),
                            Colors.black.withOpacity(0.8),
                            Colors.black.withOpacity(0.95),
                          ],
                          stops: const [0.0, 0.4, 0.7, 1.0],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // Content Overlay
            Positioned(
              bottom: 0,
              left: 0,
              right: 0,
              child: Container(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    // TV Show Title
                    if (episode.tvShow != null)
                      Text(
                        episode.tvShow!.name,
                        style: TextStyle(
                          color: Colors.white.withOpacity(0.9),
                          fontSize: 12,
                          fontWeight: FontWeight.w500,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    
                    const SizedBox(height: 4),
                    
                    // Episode Title
                    Text(
                      episode.name,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    
                    const SizedBox(height: 8),
                    
                    // Episode Info Row - Season/Episode Badge
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors: [
                            Colors.red,
                            Colors.red.withOpacity(0.8),
                          ],
                        ),
                        borderRadius: BorderRadius.circular(8),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.red.withOpacity(0.5),
                            blurRadius: 8,
                            spreadRadius: 1,
                          ),
                        ],
                      ),
                      child: Text(
                        'S${episode.seasonNumber}E${episode.episodeNumber}',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          letterSpacing: 0.5,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // Top Right Badge
            Positioned(
              top: 12,
              right: 12,
              child: Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.black.withOpacity(0.7),
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: Colors.white.withOpacity(0.2),
                    width: 1.5,
                  ),
                ),
                child: const Icon(
                  Icons.play_circle_filled,
                  color: Colors.white,
                  size: 24,
                ),
              ),
            ),

            // Bottom Gradient Border
            Positioned(
              bottom: 0,
              left: 0,
              right: 0,
              height: 4,
              child: Container(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      Colors.red,
                      Colors.red.withOpacity(0.7),
                      Colors.transparent,
                    ],
                  ),
                ),
              ),
            ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

