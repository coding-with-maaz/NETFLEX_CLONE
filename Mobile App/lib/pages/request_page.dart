import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/layout/home_header.dart';

class RequestPage extends StatefulWidget {
  final String? prefillTitle;
  final String? prefillType;
  final String? prefillTmdbId;
  final String? prefillYear;
  
  const RequestPage({
    Key? key,
    this.prefillTitle,
    this.prefillType,
    this.prefillTmdbId,
    this.prefillYear,
  }) : super(key: key);

  @override
  State<RequestPage> createState() => _RequestPageState();
}

class _RequestPageState extends State<RequestPage> {
  final _formKey = GlobalKey<FormState>();
  final _scrollController = ScrollController();
  
  // Form controllers
  final _titleController = TextEditingController();
  final _emailController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _tmdbIdController = TextEditingController();
  final _yearController = TextEditingController();
  String _selectedType = '';
  
  // State
  bool _isSubmitting = false;
  bool _showSuccess = false;
  bool _showError = false;
  String _errorMessage = '';
  
  // Stats
  int _totalRequests = 0;
  int _pendingRequests = 0;
  int _completedRequests = 0;
  bool _loadingStats = true;
  

  @override
  void initState() {
    super.initState();
    _loadStats();
    _prefillForm();
  }
  
  void _prefillForm() {
    if (widget.prefillTitle != null) {
      _titleController.text = widget.prefillTitle!;
    }
    if (widget.prefillType != null) {
      _selectedType = widget.prefillType!;
    }
    if (widget.prefillTmdbId != null) {
      _tmdbIdController.text = widget.prefillTmdbId!;
    }
    if (widget.prefillYear != null) {
      _yearController.text = widget.prefillYear!;
    }
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _titleController.dispose();
    _emailController.dispose();
    _descriptionController.dispose();
    _tmdbIdController.dispose();
    _yearController.dispose();
    super.dispose();
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
      
      // Get completed requests
      final completedResult = await ApiService.getContentRequests(
        status: 'completed',
        perPage: 1,
      );
      if (completedResult['success'] == true) {
        final pagination = completedResult['pagination'] as Map<String, dynamic>?;
        _completedRequests = pagination?['total'] ?? 0;
      }
    } catch (e) {
      debugPrint('Error loading stats: $e');
    } finally {
      setState(() => _loadingStats = false);
    }
  }


  Future<void> _submitRequest() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (_selectedType.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select a content type')),
      );
      return;
    }

    setState(() {
      _isSubmitting = true;
      _showSuccess = false;
      _showError = false;
    });

    try {
      final result = await ApiService.submitContentRequest(
        type: _selectedType,
        title: _titleController.text.trim(),
        email: _emailController.text.trim(),
        description: _descriptionController.text.trim().isEmpty
            ? null
            : _descriptionController.text.trim(),
        // TMDB ID and Year commented out - fields hidden from users
        // tmdbId: _tmdbIdController.text.trim().isEmpty
        //     ? null
        //     : _tmdbIdController.text.trim(),
        // year: _yearController.text.trim().isEmpty
        //     ? null
        //     : _yearController.text.trim(),
        tmdbId: null,
        year: null,
      );

      if (result['success'] == true) {
        setState(() {
          _showSuccess = true;
          _showError = false;
        });

        // Reset form
        _formKey.currentState!.reset();
        _selectedType = '';
        _titleController.clear();
        _emailController.clear();
        _descriptionController.clear();
        _tmdbIdController.clear();
        _yearController.clear();

        // Reload stats
        _loadStats();

        // Scroll to top
        _scrollController.animateTo(
          0,
          duration: const Duration(milliseconds: 500),
          curve: Curves.easeInOut,
        );
      } else {
        setState(() {
          _showError = true;
          _errorMessage = result['message'] ?? 'Failed to submit request. Please try again.';
        });
      }
    } catch (e) {
      setState(() {
        _showError = true;
        _errorMessage = 'An error occurred. Please check your connection and try again.';
      });
      debugPrint('Error submitting request: $e');
    } finally {
      setState(() => _isSubmitting = false);
    }
  }

  void _resetForm() {
    _formKey.currentState!.reset();
    _selectedType = '';
    _titleController.clear();
    _emailController.clear();
    _descriptionController.clear();
    _tmdbIdController.clear();
    _yearController.clear();
    setState(() {
      _showSuccess = false;
      _showError = false;
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
                  // Hero Section
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(vertical: 60, horizontal: 16),
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
                          'Request Content',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 36,
                            fontWeight: FontWeight.bold,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 16),
                        Text(
                          "Can't find your favorite movie or TV show? Request it and we'll add it to our collection!",
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
                                // Mobile: Stack vertically
                                return Column(
                                  children: [
                                    _buildStatCard('Total Requests', _totalRequests),
                                    const SizedBox(height: 12),
                                    _buildStatCard('Pending', _pendingRequests),
                                    const SizedBox(height: 12),
                                    _buildStatCard('Completed', _completedRequests),
                                  ],
                                );
                              } else {
                                // Desktop: Row
                                return Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    _buildStatCard('Total Requests', _totalRequests),
                                    const SizedBox(width: 16),
                                    _buildStatCard('Pending', _pendingRequests),
                                    const SizedBox(width: 16),
                                    _buildStatCard('Completed', _completedRequests),
                                  ],
                                );
                              }
                            },
                          ),
                      ],
                    ),
                  ),

                  // Request Form Section
                  Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Center(
                      child: ConstrainedBox(
                        constraints: const BoxConstraints(maxWidth: 800),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Success Message
                            if (_showSuccess)
                              Container(
                                width: double.infinity,
                                padding: const EdgeInsets.all(16),
                                margin: const EdgeInsets.only(bottom: 24),
                                decoration: BoxDecoration(
                                  color: Colors.green.withOpacity(0.1),
                                  border: Border.all(
                                    color: Colors.green.withOpacity(0.3),
                                  ),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Row(
                                  children: [
                                    const Icon(
                                      Icons.check_circle,
                                      color: Colors.green,
                                      size: 20,
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: Text(
                                        'Your request has been submitted successfully! Thank you for your suggestion.',
                                        style: TextStyle(
                                          color: Colors.green.shade300,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),

                            // Error Message
                            if (_showError)
                              Container(
                                width: double.infinity,
                                padding: const EdgeInsets.all(16),
                                margin: const EdgeInsets.only(bottom: 24),
                                decoration: BoxDecoration(
                                  color: Colors.red.withOpacity(0.1),
                                  border: Border.all(
                                    color: Colors.red.withOpacity(0.3),
                                  ),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Row(
                                  children: [
                                    const Icon(
                                      Icons.error,
                                      color: Colors.red,
                                      size: 20,
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: Text(
                                        _errorMessage,
                                        style: TextStyle(
                                          color: Colors.red.shade300,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),

                            // Info Box
                            Container(
                              width: double.infinity,
                              padding: const EdgeInsets.all(16),
                              margin: const EdgeInsets.only(bottom: 24),
                              decoration: BoxDecoration(
                                color: Colors.blue.withOpacity(0.1),
                                border: Border.all(
                                  color: Colors.blue.withOpacity(0.3),
                                ),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                'Note: We review all requests and prioritize popular content. While we can\'t guarantee that every request will be added, we do our best to fulfill as many as possible. You\'ll be notified if your requested content becomes available.',
                                style: TextStyle(
                                  color: Colors.blue.shade300,
                                  fontSize: 14,
                                ),
                              ),
                            ),

                            // Request Form
                            Container(
                              width: double.infinity,
                              padding: const EdgeInsets.all(32),
                              decoration: BoxDecoration(
                                color: Colors.grey.shade900.withOpacity(0.8),
                                border: Border.all(
                                  color: Colors.white.withOpacity(0.1),
                                ),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Form(
                                key: _formKey,
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    // Content Type
                                    Text(
                                      'Content Type *',
                                      style: TextStyle(
                                        color: Colors.grey.shade300,
                                        fontWeight: FontWeight.w500,
                                        fontSize: 14,
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    DropdownButtonFormField<String>(
                                      value: _selectedType.isEmpty ? null : _selectedType,
                                      decoration: InputDecoration(
                                        filled: true,
                                        fillColor: Colors.grey.shade800,
                                        border: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(8),
                                          borderSide: BorderSide(
                                            color: Colors.white.withOpacity(0.2),
                                          ),
                                        ),
                                        enabledBorder: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(8),
                                          borderSide: BorderSide(
                                            color: Colors.white.withOpacity(0.2),
                                          ),
                                        ),
                                        focusedBorder: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(8),
                                          borderSide: const BorderSide(
                                            color: Colors.red,
                                            width: 2,
                                          ),
                                        ),
                                      ),
                                      dropdownColor: Colors.grey.shade800,
                                      style: const TextStyle(color: Colors.white),
                                      hint: const Text('Select content type'),
                                      items: const [
                                        DropdownMenuItem(
                                          value: 'movie',
                                          child: Text('Movie'),
                                        ),
                                        DropdownMenuItem(
                                          value: 'tvshow',
                                          child: Text('TV Show / Drama'),
                                        ),
                                      ],
                                      onChanged: (value) {
                                        setState(() {
                                          _selectedType = value ?? '';
                                        });
                                      },
                                      validator: (value) {
                                        if (value == null || value.isEmpty) {
                                          return 'Please select a content type';
                                        }
                                        return null;
                                      },
                                    ),
                                    const SizedBox(height: 24),

                                    // Title
                                    Text(
                                      'Title *',
                                      style: TextStyle(
                                        color: Colors.grey.shade300,
                                        fontWeight: FontWeight.w500,
                                        fontSize: 14,
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    TextFormField(
                                      controller: _titleController,
                                      decoration: InputDecoration(
                                        hintText: 'Enter the title of the movie or TV show',
                                        hintStyle: TextStyle(color: Colors.grey.shade500),
                                        filled: true,
                                        fillColor: Colors.grey.shade800,
                                        border: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(8),
                                          borderSide: BorderSide(
                                            color: Colors.white.withOpacity(0.2),
                                          ),
                                        ),
                                        enabledBorder: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(8),
                                          borderSide: BorderSide(
                                            color: Colors.white.withOpacity(0.2),
                                          ),
                                        ),
                                        focusedBorder: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(8),
                                          borderSide: const BorderSide(
                                            color: Colors.red,
                                            width: 2,
                                          ),
                                        ),
                                      ),
                                      style: const TextStyle(color: Colors.white),
                                      validator: (value) {
                                        if (value == null || value.trim().isEmpty) {
                                          return 'Please enter a title';
                                        }
                                        return null;
                                      },
                                    ),
                                    const SizedBox(height: 24),

                                    // Email
                                    Text(
                                      'Email *',
                                      style: TextStyle(
                                        color: Colors.grey.shade300,
                                        fontWeight: FontWeight.w500,
                                        fontSize: 14,
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    TextFormField(
                                      controller: _emailController,
                                      keyboardType: TextInputType.emailAddress,
                                      decoration: InputDecoration(
                                        hintText: 'your@email.com',
                                        hintStyle: TextStyle(color: Colors.grey.shade500),
                                        filled: true,
                                        fillColor: Colors.grey.shade800,
                                        border: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(8),
                                          borderSide: BorderSide(
                                            color: Colors.white.withOpacity(0.2),
                                          ),
                                        ),
                                        enabledBorder: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(8),
                                          borderSide: BorderSide(
                                            color: Colors.white.withOpacity(0.2),
                                          ),
                                        ),
                                        focusedBorder: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(8),
                                          borderSide: const BorderSide(
                                            color: Colors.red,
                                            width: 2,
                                          ),
                                        ),
                                      ),
                                      style: const TextStyle(color: Colors.white),
                                      validator: (value) {
                                        if (value == null || value.trim().isEmpty) {
                                          return 'Please enter your email';
                                        }
                                        if (!value.contains('@') || !value.contains('.')) {
                                          return 'Please enter a valid email address';
                                        }
                                        return null;
                                      },
                                    ),
                                    Padding(
                                      padding: const EdgeInsets.only(top: 4.0),
                                      child: Text(
                                        "We'll notify you when your request is processed.",
                                        style: TextStyle(
                                          color: Colors.grey.shade500,
                                          fontSize: 12,
                                        ),
                                      ),
                                    ),
                                    const SizedBox(height: 24),

                                    // TMDB ID - Commented out for now
                                    /* Text(
                                      'TMDB ID (Optional)',
                                      style: TextStyle(
                                        color: Colors.grey.shade300,
                                        fontWeight: FontWeight.w500,
                                        fontSize: 14,
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    TextFormField(
                                      controller: _tmdbIdController,
                                      decoration: InputDecoration(
                                        hintText: 'If you know the TMDB ID, enter it here',
                                        hintStyle: TextStyle(color: Colors.grey.shade500),
                                        filled: true,
                                        fillColor: Colors.grey.shade800,
                                        border: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(8),
                                          borderSide: BorderSide(
                                            color: Colors.white.withOpacity(0.2),
                                          ),
                                        ),
                                        enabledBorder: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(8),
                                          borderSide: BorderSide(
                                            color: Colors.white.withOpacity(0.2),
                                          ),
                                        ),
                                        focusedBorder: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(8),
                                          borderSide: const BorderSide(
                                            color: Colors.red,
                                            width: 2,
                                          ),
                                        ),
                                      ),
                                      style: const TextStyle(color: Colors.white),
                                    ),
                                    const SizedBox(height: 8),
                                    Text(
                                      'You can find TMDB ID on themoviedb.org',
                                      style: TextStyle(
                                        color: Colors.grey.shade500,
                                        fontSize: 12,
                                      ),
                                    ),
                                    const SizedBox(height: 24), */

                                    // Year - Commented out for now
                                    /* Text(
                                      'Release Year (Optional)',
                                      style: TextStyle(
                                        color: Colors.grey.shade300,
                                        fontWeight: FontWeight.w500,
                                        fontSize: 14,
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    TextFormField(
                                      controller: _yearController,
                                      decoration: InputDecoration(
                                        hintText: 'e.g., 2023',
                                        hintStyle: TextStyle(color: Colors.grey.shade500),
                                        filled: true,
                                        fillColor: Colors.grey.shade800,
                                        border: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(8),
                                          borderSide: BorderSide(
                                            color: Colors.white.withOpacity(0.2),
                                          ),
                                        ),
                                        enabledBorder: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(8),
                                          borderSide: BorderSide(
                                            color: Colors.white.withOpacity(0.2),
                                          ),
                                        ),
                                        focusedBorder: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(8),
                                          borderSide: const BorderSide(
                                            color: Colors.red,
                                            width: 2,
                                          ),
                                        ),
                                      ),
                                      style: const TextStyle(color: Colors.white),
                                      keyboardType: TextInputType.number,
                                      maxLength: 4,
                                    ),
                                    const SizedBox(height: 24), */

                                    // Description
                                    Text(
                                      'Additional Details (Optional)',
                                      style: TextStyle(
                                        color: Colors.grey.shade300,
                                        fontWeight: FontWeight.w500,
                                        fontSize: 14,
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    TextFormField(
                                      controller: _descriptionController,
                                      decoration: InputDecoration(
                                        hintText: 'Any additional information about your request (e.g., specific season, language, quality preference, etc.)',
                                        hintStyle: TextStyle(color: Colors.grey.shade500),
                                        filled: true,
                                        fillColor: Colors.grey.shade800,
                                        border: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(8),
                                          borderSide: BorderSide(
                                            color: Colors.white.withOpacity(0.2),
                                          ),
                                        ),
                                        enabledBorder: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(8),
                                          borderSide: BorderSide(
                                            color: Colors.white.withOpacity(0.2),
                                          ),
                                        ),
                                        focusedBorder: OutlineInputBorder(
                                          borderRadius: BorderRadius.circular(8),
                                          borderSide: const BorderSide(
                                            color: Colors.red,
                                            width: 2,
                                          ),
                                        ),
                                      ),
                                      style: const TextStyle(color: Colors.white),
                                      maxLines: 5,
                                    ),
                                    const SizedBox(height: 32),

                                    // Submit Button
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        ElevatedButton.icon(
                                          onPressed: _isSubmitting ? null : _submitRequest,
                                          icon: _isSubmitting
                                              ? const SizedBox(
                                                  width: 20,
                                                  height: 20,
                                                  child: CircularProgressIndicator(
                                                    strokeWidth: 2,
                                                    valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                                                  ),
                                                )
                                              : const Icon(Icons.send),
                                          label: Text(_isSubmitting ? 'Submitting...' : 'Submit Request'),
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: Colors.red,
                                            foregroundColor: Colors.white,
                                            padding: const EdgeInsets.symmetric(
                                              horizontal: 24,
                                              vertical: 14,
                                            ),
                                            shape: RoundedRectangleBorder(
                                              borderRadius: BorderRadius.circular(8),
                                            ),
                                          ),
                                        ),
                                        TextButton(
                                          onPressed: _resetForm,
                                          child: Text(
                                            'Reset Form',
                                            style: TextStyle(color: Colors.grey.shade400),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
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

  Widget _buildStatCard(String label, int value) {
    return Container(
      padding: const EdgeInsets.all(20),
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
            style: const TextStyle(
              color: Colors.red,
              fontSize: 32,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            label,
            style: TextStyle(
              color: Colors.grey.shade400,
              fontSize: 14,
            ),
          ),
        ],
      ),
    );
  }

}

