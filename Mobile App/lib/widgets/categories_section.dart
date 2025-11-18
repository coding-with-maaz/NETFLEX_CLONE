import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../services/api_service.dart';
import '../models/movie.dart';

class CategoriesSection extends StatefulWidget {
  const CategoriesSection({Key? key}) : super(key: key);

  @override
  State<CategoriesSection> createState() => _CategoriesSectionState();
}

class _CategoriesSectionState extends State<CategoriesSection> {
  List<Category> categories = [];
  bool isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadCategories();
  }

  Future<void> _loadCategories() async {
    try {
      setState(() {
        isLoading = true;
      });

      final utilityData = await ApiService.getUtilityData();
      final categoriesData = utilityData['categories'] as List<dynamic>? ?? [];

      // Convert to Category objects
      final allCategories = categoriesData
          .map((c) => Category.fromJson(c as Map<String, dynamic>))
          .toList();

      // Take first 4 categories
      final topCategories = allCategories.take(4).toList();

      setState(() {
        categories = topCategories;
        isLoading = false;
      });
    } catch (e) {
      print('[CategoriesSection] Error loading categories: $e');
      setState(() {
        isLoading = false;
      });
    }
  }

  String _getCategoryImageUrl(String? categoryName) {
    // Map category names to image URLs or use placeholder
    // You can replace these with actual image URLs from your assets or CDN
    // Default placeholder - you can replace with actual category images
    return 'https://via.placeholder.com/300x200/1a1a1a/ffffff?text=${Uri.encodeComponent(categoryName ?? 'Category')}';
  }

  IconData _getCategoryIcon(String? categoryName) {
    final name = (categoryName ?? '').toLowerCase();
    if (name.contains('movie') || name.contains('film')) {
      return Icons.movie;
    } else if (name.contains('tv') || name.contains('show') || name.contains('series')) {
      return Icons.tv;
    } else if (name.contains('anime')) {
      return Icons.animation;
    } else if (name.contains('drama')) {
      return Icons.theater_comedy;
    } else if (name.contains('action')) {
      return Icons.sports_martial_arts;
    } else if (name.contains('comedy')) {
      return Icons.sentiment_very_satisfied;
    } else if (name.contains('horror')) {
      return Icons.warning;
    } else if (name.contains('romance')) {
      return Icons.favorite;
    } else {
      return Icons.category;
    }
  }

  Color _getCategoryColor(String? categoryName) {
    final name = (categoryName ?? '').toLowerCase();
    if (name.contains('movie') || name.contains('film')) {
      return Colors.blue;
    } else if (name.contains('tv') || name.contains('show') || name.contains('series')) {
      return Colors.purple;
    } else if (name.contains('anime')) {
      return Colors.pink;
    } else if (name.contains('drama')) {
      return Colors.orange;
    } else {
      return Colors.red;
    }
  }

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return const SizedBox(
        height: 180,
        child: Center(
          child: CircularProgressIndicator(color: Colors.red),
        ),
      );
    }

    if (categories.isEmpty) {
      return const SizedBox.shrink();
    }

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Categories',
            style: TextStyle(
              color: Colors.white,
              fontSize: 22,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: categories.map((category) {
              return Expanded(
                child: Padding(
                  padding: const EdgeInsets.only(right: 12),
                  child: _buildCategoryButton(category),
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  Widget _buildCategoryButton(Category category) {
    final imageUrl = _getCategoryImageUrl(category.name);
    final icon = _getCategoryIcon(category.name);
    final color = _getCategoryColor(category.name);

    return InkWell(
      onTap: () {
        // Navigate to TV shows page with category filter
        // Use category name to match the filter dropdown which uses names
        Navigator.pushNamed(
          context,
          '/tvshows?category=${Uri.encodeComponent(category.name)}',
        );
      },
      borderRadius: BorderRadius.circular(12),
      child: Container(
        height: 140,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(12),
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              color.withOpacity(0.8),
              color.withOpacity(0.6),
            ],
          ),
          boxShadow: [
            BoxShadow(
              color: color.withOpacity(0.3),
              blurRadius: 8,
              spreadRadius: 2,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Stack(
          children: [
            // Background image with overlay
            ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: CachedNetworkImage(
                imageUrl: imageUrl,
                width: double.infinity,
                height: double.infinity,
                fit: BoxFit.cover,
                errorWidget: (context, url, error) => Container(
                  color: color.withOpacity(0.3),
                  child: Icon(
                    icon,
                    size: 48,
                    color: Colors.white.withOpacity(0.5),
                  ),
                ),
                placeholder: (context, url) => Container(
                  color: color.withOpacity(0.3),
                  child: Icon(
                    icon,
                    size: 48,
                    color: Colors.white.withOpacity(0.5),
                  ),
                ),
              ),
            ),
            // Gradient overlay
            Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(12),
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.transparent,
                    Colors.black.withOpacity(0.7),
                  ],
                ),
              ),
            ),
            // Category name
            Positioned(
              bottom: 0,
              left: 0,
              right: 0,
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Text(
                  category.name,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                  ),
                  textAlign: TextAlign.center,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ),
            // Icon overlay (top-right)
            Positioned(
              top: 8,
              right: 8,
              child: Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: Colors.black.withOpacity(0.5),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Icon(
                  icon,
                  color: Colors.white,
                  size: 20,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

