<?php

namespace App\Traits;

trait FormatsImageUrls
{
    /**
     * Format poster path - ensure full URL for TMDB images
     */
    protected function formatPosterPath($posterPath, $tmdbId = null, $baseSize = 'w500')
    {
        if ($posterPath) {
            if (str_starts_with($posterPath, 'http')) {
                return $posterPath;
            } elseif (str_starts_with($posterPath, '/')) {
                return 'https://image.tmdb.org/t/p/' . $baseSize . $posterPath;
            } else {
                return 'https://image.tmdb.org/t/p/' . $baseSize . '/' . $posterPath;
            }
        }
        
        // If no poster but have TMDB ID, we could fetch from TMDB
        // For now, return placeholder
        return '/images/placeholder.svg';
    }

    /**
     * Format backdrop path - ensure full URL for TMDB images
     */
    protected function formatBackdropPath($backdropPath, $posterPath = null, $baseSize = 'w1280')
    {
        if ($backdropPath) {
            if (str_starts_with($backdropPath, 'http')) {
                return $backdropPath;
            } elseif (str_starts_with($backdropPath, '/')) {
                return 'https://image.tmdb.org/t/p/' . $baseSize . $backdropPath;
            } else {
                return 'https://image.tmdb.org/t/p/' . $baseSize . '/' . $backdropPath;
            }
        }
        
        // Fallback to poster if available
        if ($posterPath && $posterPath !== '/images/placeholder.svg') {
            return $posterPath;
        }
        
        return '/images/placeholder.svg';
    }

    /**
     * Format still path for episodes - ensure full URL for TMDB images
     */
    protected function formatStillPath($stillPath, $baseSize = 'w500')
    {
        if ($stillPath) {
            if (str_starts_with($stillPath, 'http')) {
                return $stillPath;
            } elseif (str_starts_with($stillPath, '/')) {
                return 'https://image.tmdb.org/t/p/' . $baseSize . $stillPath;
            } else {
                return 'https://image.tmdb.org/t/p/' . $baseSize . '/' . $stillPath;
            }
        }
        
        return '/images/placeholder.svg';
    }
}

