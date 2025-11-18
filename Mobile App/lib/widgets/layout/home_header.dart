import 'package:flutter/material.dart';

class HomeHeader extends StatefulWidget {
  final ScrollController scrollController;
  
  const HomeHeader({
    Key? key,
    required this.scrollController,
  }) : super(key: key);

  @override
  State<HomeHeader> createState() => _HomeHeaderState();
}

class _HomeHeaderState extends State<HomeHeader> {
  bool _scrolled = false;
  bool _showMobileMenu = false;
  bool _showSearch = false;
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    // Listen to scroll for header background change
    widget.scrollController.addListener(_handleScroll);
  }

  void _handleScroll() {
    final offset = widget.scrollController.offset;
    if (offset > 0 && !_scrolled) {
      setState(() => _scrolled = true);
    } else if (offset == 0 && _scrolled) {
      setState(() => _scrolled = false);
    }
  }

  @override
  void dispose() {
    widget.scrollController.removeListener(_handleScroll);
    _searchController.dispose();
    super.dispose();
  }

  void _handleSearch() {
    if (_searchController.text.trim().isNotEmpty) {
      // Navigate to search results page
      Navigator.pushNamed(
        context,
        '/search',
        arguments: _searchController.text,
      );
      setState(() {
        _showSearch = false;
        _searchController.clear();
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 300),
      decoration: BoxDecoration(
        gradient: _scrolled
            ? null
            : LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [
                  Colors.black,
                  Colors.black.withOpacity(0.0),
                ],
              ),
        color: _scrolled ? Colors.black : null,
      ),
      child: SafeArea(
        child: Column(
          children: [
            Container(
              height: 64,
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                children: [
                  // Logo - Text logo matching web design
                  GestureDetector(
                    onTap: () {
                      // Navigate to home
                      // Navigator.pushNamed(context, '/');
                    },
                    child: const Text(
                      'NAZAARABOX',
                      style: TextStyle(
                        color: Color(0xFFE50914), // Primary red color
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        letterSpacing: 0.5,
                      ),
                    ),
                  ),

                  // Desktop Navigation (for web/tablet)
                  if (MediaQuery.of(context).size.width >= 768) ...[
                    const SizedBox(width: 32),
                    _buildNavLink('Home', '/'),
                    _buildNavLink('Movies', '/movies'),
                    _buildNavLink('TV Shows', '/tvshows'),
                    _buildNavLink('Trending', '/trending'),
                    _buildNavLink("Today's Movies", '/movies/today'),
                    _buildNavLink("Today's Episodes", '/episodes/today'),
                    _buildNavLink('Request', '/request'),
                    _buildNavLink('Recent Requests', '/recent-requests'),
                  ],

                  const Spacer(),

                  // Search
                  if (_showSearch)
                    Expanded(
                      child: Container(
                        margin: const EdgeInsets.only(left: 16),
                        child: TextField(
                          controller: _searchController,
                          autofocus: true,
                          onSubmitted: (_) => _handleSearch(),
                          style: const TextStyle(color: Colors.white),
                          decoration: InputDecoration(
                            hintText: 'Titles, genres...',
                            hintStyle: TextStyle(color: Colors.grey[400]),
                            filled: true,
                            fillColor: Colors.black.withOpacity(0.7),
                            border: const OutlineInputBorder(
                              borderSide: BorderSide(color: Colors.white),
                            ),
                            enabledBorder: const OutlineInputBorder(
                              borderSide: BorderSide(color: Colors.white),
                            ),
                            focusedBorder: const OutlineInputBorder(
                              borderSide: BorderSide(color: Colors.white, width: 2),
                            ),
                            contentPadding: const EdgeInsets.symmetric(
                              horizontal: 16,
                              vertical: 8,
                            ),
                            suffixIcon: IconButton(
                              icon: const Icon(Icons.close, color: Colors.white),
                              onPressed: () {
                                setState(() {
                                  _showSearch = false;
                                  _searchController.clear();
                                });
                              },
                            ),
                          ),
                        ),
                      ),
                    )
                  else
                    IconButton(
                      icon: const Icon(Icons.search, color: Colors.white),
                      onPressed: () {
                        setState(() => _showSearch = true);
                      },
                    ),

                  // Mobile Menu Toggle
                  if (MediaQuery.of(context).size.width < 768)
                    IconButton(
                      icon: Icon(
                        _showMobileMenu ? Icons.close : Icons.menu,
                        color: Colors.white,
                      ),
                      onPressed: () {
                        setState(() => _showMobileMenu = !_showMobileMenu);
                      },
                    ),
                ],
              ),
            ),

            // Mobile Menu
            if (_showMobileMenu && MediaQuery.of(context).size.width < 768)
              Container(
                color: Colors.black.withOpacity(0.95),
                padding: const EdgeInsets.symmetric(vertical: 16),
                child: Column(
                  children: [
                    _buildMobileNavLink('Home', '/'),
                    _buildMobileNavLink('Movies', '/movies'),
                    _buildMobileNavLink('TV Shows', '/tvshows'),
                    _buildMobileNavLink('Trending', '/trending'),
                    _buildMobileNavLink("Today's Movies", '/movies/today'),
                    _buildMobileNavLink("Today's Episodes", '/episodes/today'),
                    _buildMobileNavLink('Request', '/request'),
                    _buildMobileNavLink('Recent Requests', '/recent-requests'),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildNavLink(String title, String route) {
    final currentRoute = ModalRoute.of(context)?.settings.name ?? '/';
    final isActive = currentRoute == route;
    
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 12),
      child: GestureDetector(
        onTap: () {
          print('Navigating to: $route from $currentRoute');
          Navigator.pushNamed(context, route);
        },
        child: Text(
          title,
          style: TextStyle(
            color: isActive ? Colors.white : Colors.grey[300],
            fontSize: 14,
            fontWeight: isActive ? FontWeight.w600 : FontWeight.normal,
          ),
        ),
      ),
    );
  }

  Widget _buildMobileNavLink(String title, String route) {
    final currentRoute = ModalRoute.of(context)?.settings.name ?? '/';
    final isActive = currentRoute == route;
    
    return InkWell(
      onTap: () {
        print('Navigating to: $route from $currentRoute');
        setState(() => _showMobileMenu = false);
        Navigator.pushNamed(context, route);
      },
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: Text(
          title,
          style: TextStyle(
            color: isActive ? Colors.white : Colors.grey[300],
            fontSize: 16,
          ),
        ),
      ),
    );
  }
}

