import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/layout/home_header.dart';

class RecentRequestsPage extends StatefulWidget {
  const RecentRequestsPage({Key? key}) : super(key: key);

  @override
  State<RecentRequestsPage> createState() => _RecentRequestsPageState();
}

class _RecentRequestsPageState extends State<RecentRequestsPage> {
  final _scrollController = ScrollController();
  
  // Filters
  String _selectedStatus = 'pending'; // Default to pending
  String _selectedType = 'all';
  String _searchQuery = '';
  final _searchController = TextEditingController();
  
  // Data
  List<dynamic> _requests = [];
  bool _isLoading = true;
  bool _hasMore = true;
  int _currentPage = 1;
  final int _perPage = 20;
  
  // Stats
  int _totalRequests = 0;
  int _pendingRequests = 0;
  int _approvedRequests = 0;
  int _completedRequests = 0;
  int _rejectedRequests = 0;
  bool _loadingStats = true;

  @override
  void initState() {
    super.initState();
    _loadStats();
    _loadRequests();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent * 0.8) {
      if (!_isLoading && _hasMore) {
        _loadMoreRequests();
      }
    }
  }

  Future<void> _loadStats() async {
    setState(() => _loadingStats = true);
    
    try {
      // Get total requests
      final totalResult = await ApiService.getContentRequests(perPage: 1);
      if (totalResult['success'] == true) {
        final pagination = totalResult['pagination'] as Map<String, dynamic>?;
        _totalRequests = pagination?['total'] ?? 0;
      }
      
      // Get pending requests
      final pendingResult = await ApiService.getContentRequests(
        status: 'pending',
        perPage: 1,
      );
      if (pendingResult['success'] == true) {
        final pagination = pendingResult['pagination'] as Map<String, dynamic>?;
        _pendingRequests = pagination?['total'] ?? 0;
      }
      
      // Get approved requests
      final approvedResult = await ApiService.getContentRequests(
        status: 'approved',
        perPage: 1,
      );
      if (approvedResult['success'] == true) {
        final pagination = approvedResult['pagination'] as Map<String, dynamic>?;
        _approvedRequests = pagination?['total'] ?? 0;
      }
      
      // Get completed requests
      final completedResult = await ApiService.getContentRequests(
        status: 'completed',
        perPage: 1,
      );
      if (completedResult['success'] == true) {
        final pagination = completedResult['pagination'] as Map<String, dynamic>?;
        _completedRequests = pagination?['total'] ?? 0;
      }
      
      // Get rejected requests
      final rejectedResult = await ApiService.getContentRequests(
        status: 'rejected',
        perPage: 1,
      );
      if (rejectedResult['success'] == true) {
        final pagination = rejectedResult['pagination'] as Map<String, dynamic>?;
        _rejectedRequests = pagination?['total'] ?? 0;
      }
    } catch (e) {
      debugPrint('Error loading stats: $e');
    } finally {
      setState(() => _loadingStats = false);
    }
  }

  Future<void> _loadRequests({bool reset = false}) async {
    if (reset) {
      setState(() {
        _currentPage = 1;
        _requests = [];
        _hasMore = true;
      });
    }
    
    setState(() => _isLoading = true);
    
    try {
      final result = await ApiService.getContentRequests(
        status: _selectedStatus == 'all' ? null : _selectedStatus,
        type: _selectedType == 'all' ? null : _selectedType,
        search: _searchQuery.isEmpty ? null : _searchQuery,
        perPage: _perPage,
        page: _currentPage,
        sortBy: 'requested_at',
        sortOrder: 'desc',
      );
      
      if (result['success'] == true) {
        final newRequests = result['requests'] ?? [];
        final pagination = result['pagination'] as Map<String, dynamic>?;
        
        setState(() {
          if (reset) {
            _requests = newRequests;
          } else {
            _requests.addAll(newRequests);
          }
          
          final lastPage = pagination?['last_page'] ?? 1;
          _hasMore = _currentPage < lastPage;
          _isLoading = false;
        });
      } else {
        setState(() {
          _isLoading = false;
        });
      }
    } catch (e) {
      debugPrint('Error loading requests: $e');
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _loadMoreRequests() async {
    if (_isLoading || !_hasMore) return;
    
    setState(() {
      _currentPage++;
    });
    
    await _loadRequests(reset: false);
  }

  void _onSearchChanged(String value) {
    _searchQuery = value;
    // Debounce search - reload after user stops typing
    Future.delayed(const Duration(milliseconds: 500), () {
      if (_searchController.text == value) {
        _loadRequests(reset: true);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: Column(
        children: [
          HomeHeader(scrollController: _scrollController),
          Expanded(
            child: SingleChildScrollView(
              controller: _scrollController,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Header Section
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 16),
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                        colors: [
                          Colors.red.withOpacity(0.1),
                          Colors.grey.shade900.withOpacity(0.9),
                        ],
                      ),
                      border: Border(
                        bottom: BorderSide(
                          color: Colors.white.withOpacity(0.1),
                          width: 1,
                        ),
                      ),
                    ),
                    child: Column(
                      children: [
                        const Text(
                          'Recent Requests',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 36,
                            fontWeight: FontWeight.bold,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'View and filter all content requests from users',
                          style: TextStyle(
                            color: Colors.grey.shade300,
                            fontSize: 18,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 32),
                        // Stats
                        if (_loadingStats)
                          const CircularProgressIndicator(
                            valueColor: AlwaysStoppedAnimation<Color>(Colors.red),
                          )
                        else
                          LayoutBuilder(
                            builder: (context, constraints) {
                              if (constraints.maxWidth < 600) {
                                // Mobile: Grid
                                return Column(
                                  children: [
                                    Row(
                                      children: [
                                        Expanded(child: _buildStatCard('Total', _totalRequests)),
                                        const SizedBox(width: 8),
                                        Expanded(child: _buildStatCard('Pending', _pendingRequests, Colors.yellow)),
                                      ],
                                    ),
                                    const SizedBox(height: 8),
                                    Row(
                                      children: [
                                        Expanded(child: _buildStatCard('Approved', _approvedRequests, Colors.blue)),
                                        const SizedBox(width: 8),
                                        Expanded(child: _buildStatCard('Completed', _completedRequests, Colors.green)),
                                      ],
                                    ),
                                    const SizedBox(height: 8),
                                    _buildStatCard('Rejected', _rejectedRequests, Colors.red),
                                  ],
                                );
                              } else {
                                // Desktop: Row
                                return Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    _buildStatCard('Total', _totalRequests),
                                    const SizedBox(width: 12),
                                    _buildStatCard('Pending', _pendingRequests, Colors.yellow),
                                    const SizedBox(width: 12),
                                    _buildStatCard('Approved', _approvedRequests, Colors.blue),
                                    const SizedBox(width: 12),
                                    _buildStatCard('Completed', _completedRequests, Colors.green),
                                    const SizedBox(width: 12),
                                    _buildStatCard('Rejected', _rejectedRequests, Colors.red),
                                  ],
                                );
                              }
                            },
                          ),
                      ],
                    ),
                  ),

                  // Filters Section
                  Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Center(
                      child: ConstrainedBox(
                        constraints: const BoxConstraints(maxWidth: 1200),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Search Bar
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                              decoration: BoxDecoration(
                                color: Colors.grey.shade900,
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(
                                  color: Colors.white.withOpacity(0.1),
                                ),
                              ),
                              child: TextField(
                                controller: _searchController,
                                onChanged: _onSearchChanged,
                                style: const TextStyle(color: Colors.white),
                                decoration: InputDecoration(
                                  hintText: 'Search by title...',
                                  hintStyle: TextStyle(color: Colors.grey.shade500),
                                  prefixIcon: Icon(Icons.search, color: Colors.grey.shade400),
                                  suffixIcon: _searchQuery.isNotEmpty
                                      ? IconButton(
                                          icon: Icon(Icons.clear, color: Colors.grey.shade400),
                                          onPressed: () {
                                            _searchController.clear();
                                            _searchQuery = '';
                                            _loadRequests(reset: true);
                                          },
                                        )
                                      : null,
                                  border: InputBorder.none,
                                ),
                              ),
                            ),
                            const SizedBox(height: 16),
                            
                            // Filter Chips
                            Wrap(
                              spacing: 12,
                              runSpacing: 12,
                              children: [
                                // Status Filter
                                _buildFilterChip(
                                  'Status',
                                  _selectedStatus,
                                  ['all', 'pending', 'approved', 'completed', 'rejected'],
                                  (value) {
                                    setState(() => _selectedStatus = value);
                                    _loadRequests(reset: true);
                                  },
                                ),
                                
                                // Type Filter
                                _buildFilterChip(
                                  'Type',
                                  _selectedType,
                                  ['all', 'movie', 'tvshow'],
                                  (value) {
                                    setState(() => _selectedType = value);
                                    _loadRequests(reset: true);
                                  },
                                ),
                              ],
                            ),
                            const SizedBox(height: 24),

                            // Request Button
                            Container(
                              width: double.infinity,
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: Colors.red.withOpacity(0.1),
                                border: Border.all(
                                  color: Colors.red.withOpacity(0.3),
                                ),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: LayoutBuilder(
                                builder: (context, constraints) {
                                  final isMobile = constraints.maxWidth < 600;
                                  
                                  if (isMobile) {
                                    // Mobile: Stack vertically
                                    return Column(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            Icon(
                                              Icons.add_circle_outline,
                                              color: Colors.red.shade300,
                                              size: 20,
                                            ),
                                            const SizedBox(width: 8),
                                            Flexible(
                                              child: Text(
                                                'You can also request content',
                                                style: TextStyle(
                                                  color: Colors.red.shade300,
                                                  fontSize: 16,
                                                  fontWeight: FontWeight.w500,
                                                ),
                                                textAlign: TextAlign.center,
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 12),
                                        ElevatedButton.icon(
                                          onPressed: () {
                                            Navigator.pushNamed(context, '/request');
                                          },
                                          icon: const Icon(Icons.arrow_forward, size: 16),
                                          label: const Text('Request Now'),
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: Colors.red,
                                            foregroundColor: Colors.white,
                                            padding: const EdgeInsets.symmetric(
                                              horizontal: 24,
                                              vertical: 12,
                                            ),
                                            shape: RoundedRectangleBorder(
                                              borderRadius: BorderRadius.circular(6),
                                            ),
                                          ),
                                        ),
                                      ],
                                    );
                                  } else {
                                    // Desktop: Row layout
                                    return Row(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Icon(
                                          Icons.add_circle_outline,
                                          color: Colors.red.shade300,
                                          size: 20,
                                        ),
                                        const SizedBox(width: 8),
                                        Text(
                                          'You can also request content',
                                          style: TextStyle(
                                            color: Colors.red.shade300,
                                            fontSize: 16,
                                            fontWeight: FontWeight.w500,
                                          ),
                                        ),
                                        const SizedBox(width: 12),
                                        ElevatedButton.icon(
                                          onPressed: () {
                                            Navigator.pushNamed(context, '/request');
                                          },
                                          icon: const Icon(Icons.arrow_forward, size: 16),
                                          label: const Text('Request Now'),
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: Colors.red,
                                            foregroundColor: Colors.white,
                                            padding: const EdgeInsets.symmetric(
                                              horizontal: 16,
                                              vertical: 8,
                                            ),
                                            shape: RoundedRectangleBorder(
                                              borderRadius: BorderRadius.circular(6),
                                            ),
                                          ),
                                        ),
                                      ],
                                    );
                                  }
                                },
                              ),
                            ),
                            const SizedBox(height: 24),

                            // Requests List
                            if (_isLoading && _requests.isEmpty)
                              const Center(
                                child: Padding(
                                  padding: EdgeInsets.all(48.0),
                                  child: CircularProgressIndicator(
                                    valueColor: AlwaysStoppedAnimation<Color>(Colors.red),
                                  ),
                                ),
                              )
                            else if (_requests.isEmpty)
                              Container(
                                width: double.infinity,
                                padding: const EdgeInsets.all(48),
                                decoration: BoxDecoration(
                                  color: Colors.grey.shade900,
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  'No requests found.${_selectedStatus != 'all' ? ' Try changing the filters.' : ''}',
                                  style: TextStyle(
                                    color: Colors.grey.shade400,
                                  ),
                                  textAlign: TextAlign.center,
                                ),
                              )
                            else
                              Column(
                                children: [
                                  ..._requests.map((request) => _buildRequestCard(request)),
                                  if (_isLoading && _requests.isNotEmpty)
                                    const Padding(
                                      padding: EdgeInsets.all(16.0),
                                      child: CircularProgressIndicator(
                                        valueColor: AlwaysStoppedAnimation<Color>(Colors.red),
                                      ),
                                    ),
                                  if (!_hasMore && _requests.isNotEmpty)
                                    Padding(
                                      padding: const EdgeInsets.all(16.0),
                                      child: Text(
                                        'No more requests to load',
                                        style: TextStyle(
                                          color: Colors.grey.shade400,
                                        ),
                                        textAlign: TextAlign.center,
                                      ),
                                    ),
                                ],
                              ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard(String label, int value, [Color? color]) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.grey.shade900.withOpacity(0.6),
        border: Border.all(
          color: Colors.white.withOpacity(0.1),
        ),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        children: [
          Text(
            value.toString(),
            style: TextStyle(
              color: color ?? Colors.red,
              fontSize: 28,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            label,
            style: TextStyle(
              color: Colors.grey.shade400,
              fontSize: 12,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(
    String label,
    String selectedValue,
    List<String> options,
    Function(String) onChanged,
  ) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.grey.shade900,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: Colors.white.withOpacity(0.1),
        ),
      ),
      child: Wrap(
        spacing: 4,
        runSpacing: 4,
        crossAxisAlignment: WrapCrossAlignment.center,
        children: [
          Padding(
            padding: const EdgeInsets.only(right: 4),
            child: Text(
              '$label: ',
              style: TextStyle(
                color: Colors.grey.shade400,
                fontSize: 14,
              ),
            ),
          ),
          ...options.map((option) {
            final isSelected = selectedValue == option;
            return ChoiceChip(
              label: Text(
                option == 'all' ? 'All' : option[0].toUpperCase() + option.substring(1),
                style: TextStyle(
                  color: isSelected ? Colors.white : Colors.grey.shade400,
                  fontSize: 12,
                ),
              ),
              selected: isSelected,
              onSelected: (selected) {
                if (selected) onChanged(option);
              },
              selectedColor: Colors.red,
              backgroundColor: Colors.grey.shade800,
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            );
          }).toList(),
        ],
      ),
    );
  }

  Widget _buildRequestCard(Map<String, dynamic> request) {
    final status = request['status'] ?? 'pending';
    final statusColors = {
      'pending': Colors.yellow,
      'approved': Colors.blue,
      'completed': Colors.green,
      'rejected': Colors.red,
    };
    final statusColor = statusColors[status] ?? Colors.grey;

    final requestedAt = request['requested_at'];
    String dateStr = '';
    if (requestedAt != null) {
      try {
        final date = DateTime.parse(requestedAt);
        dateStr = '${date.day}/${date.month}/${date.year}';
      } catch (e) {
        dateStr = requestedAt.toString();
      }
    }

    final requestCount = request['request_count'] ?? 1;
    final type = request['type'] ?? 'movie';

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.grey.shade900,
        border: Border.all(
          color: Colors.grey.shade800,
        ),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      request['title'] ?? 'Untitled',
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w600,
                        fontSize: 18,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.red.withOpacity(0.2),
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            type.toUpperCase(),
                            style: TextStyle(
                              color: Colors.red.shade300,
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                        Text(
                          dateStr,
                          style: TextStyle(
                            color: Colors.grey.shade400,
                            fontSize: 12,
                          ),
                        ),
                        if (requestCount > 1) ...[
                          Text(
                            '•',
                            style: TextStyle(color: Colors.grey.shade400),
                          ),
                          Text(
                            '$requestCount requests',
                            style: TextStyle(
                              color: Colors.grey.shade400,
                              fontSize: 12,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: statusColor.withOpacity(0.2),
                  border: Border.all(
                    color: statusColor.withOpacity(0.5),
                  ),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  status.toUpperCase(),
                  style: TextStyle(
                    color: statusColor.shade300,
                    fontSize: 10,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
          if (request['description'] != null &&
              request['description'].toString().isNotEmpty) ...[
            const SizedBox(height: 12),
            Text(
              request['description'],
              style: TextStyle(
                color: Colors.grey.shade400,
                fontSize: 14,
              ),
            ),
          ],
          if (request['tmdb_id'] != null || request['year'] != null) ...[
            const SizedBox(height: 8),
            Wrap(
              spacing: 12,
              children: [
                if (request['tmdb_id'] != null)
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.info_outline, size: 14, color: Colors.grey.shade500),
                      const SizedBox(width: 4),
                      Text(
                        'TMDB: ${request['tmdb_id']}',
                        style: TextStyle(
                          color: Colors.grey.shade500,
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                if (request['year'] != null)
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.calendar_today, size: 14, color: Colors.grey.shade500),
                      const SizedBox(width: 4),
                      Text(
                        'Year: ${request['year']}',
                        style: TextStyle(
                          color: Colors.grey.shade500,
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
              ],
            ),
          ],
          // Admin Notes/Response
          if (request['admin_notes'] != null &&
              request['admin_notes'].toString().isNotEmpty) ...[
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.blue.shade900.withOpacity(0.2),
                border: Border.all(
                  color: Colors.blue.shade600.withOpacity(0.4),
                  width: 1.5,
                ),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: Colors.blue.shade600,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Icon(
                          Icons.note_alt,
                          color: Colors.white,
                          size: 18,
                        ),
                      ),
                      const SizedBox(width: 12),
                      const Text(
                        'Admin Response',
                        style: TextStyle(
                          color: Colors.blue,
                          fontSize: 16,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Text(
                    request['admin_notes'],
                    style: TextStyle(
                      color: Colors.blue.shade200,
                      fontSize: 14,
                      height: 1.5,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }
}

