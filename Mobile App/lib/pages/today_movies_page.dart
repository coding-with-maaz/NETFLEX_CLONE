import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import '../models/movie.dart';
import '../widgets/movie_card.dart';

class TodayMoviesPage extends StatefulWidget {
  const TodayMoviesPage({super.key});

  @override
  State<TodayMoviesPage> createState() => _TodayMoviesPageState();
}

class _TodayMoviesPageState extends State<TodayMoviesPage> {
  List<Movie> _movies = [];
  bool _isLoading = true;
  DateTime _selectedDate = DateTime.now();
  final DateTime _today = DateTime.now();

  @override
  void initState() {
    super.initState();
    _fetchMoviesByDate();
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

  Future<void> _fetchMoviesByDate() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final dateStr = _formatDateForApi(_selectedDate);
      final movies = await ApiService.getMoviesByDate(dateStr);
      
      setState(() {
        _movies = movies;
        _isLoading = false;
      });
      
      print('Loaded ${_movies.length} movies for date: $dateStr');
    } catch (e) {
      print('Error fetching movies by date: $e');
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _goToPreviousDay() {
    setState(() {
      _selectedDate = _selectedDate.subtract(const Duration(days: 1));
    });
    _fetchMoviesByDate();
  }

  void _goToNextDay() {
    if (!_isToday) {
      final nextDate = _selectedDate.add(const Duration(days: 1));
      if (nextDate.isBefore(_today.add(const Duration(days: 1)))) {
        setState(() {
          _selectedDate = nextDate;
        });
        _fetchMoviesByDate();
      }
    }
  }

  void _goToToday() {
    setState(() {
      _selectedDate = DateTime.now();
    });
    _fetchMoviesByDate();
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
      _fetchMoviesByDate();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: RefreshIndicator(
        onRefresh: _fetchMoviesByDate,
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
                                _isToday ? "Today's Movies" : "Movies by Date",
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

                    // Movie count badge
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
                            Icons.movie,
                            color: Colors.red,
                            size: 20,
                          ),
                          const SizedBox(width: 8),
                          Text(
                            '${_movies.length}',
                            style: const TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                              fontSize: 16,
                            ),
                          ),
                          const SizedBox(width: 8),
                          Text(
                            _movies.length == 1 ? 'Movie' : 'Movies',
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
                    if (!_isLoading && _movies.isEmpty)
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
                                'No Movies Uploaded on',
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
                                    ? 'Check back later for new movies!'
                                    : 'Try selecting a different date or browse all movies.',
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
                                      Navigator.pushNamed(context, '/movies');
                                    },
                                    icon: const Icon(Icons.play_arrow),
                                    label: const Text('Browse All Movies'),
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

                    // Movies grid
                    if (!_isLoading && _movies.isNotEmpty)
                      LayoutBuilder(
                        builder: (context, constraints) {
                          final crossAxisCount = constraints.maxWidth > 1400
                              ? 7
                              : constraints.maxWidth > 1200
                                  ? 6
                                  : constraints.maxWidth > 900
                                      ? 5
                                      : constraints.maxWidth > 600
                                          ? 4
                                          : 3;

                          return GridView.builder(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount: crossAxisCount,
                              childAspectRatio: 0.65,
                              crossAxisSpacing: 12,
                              mainAxisSpacing: 12,
                            ),
                            itemCount: _movies.length,
                            itemBuilder: (context, index) {
                              return MovieCard(movie: _movies[index]);
                            },
                          );
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
}

