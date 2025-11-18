{{-- Movie Card Component - Matching Frontend MovieCard.jsx --}}
@props(['movie'])

@php
    $imageUrl = $movie['poster_path'] 
        ? 'https://image.tmdb.org/t/p/w500' . $movie['poster_path']
        : '/images/placeholder.svg';
    
    $rating = isset($movie['vote_average']) ? number_format((float)$movie['vote_average'], 1, '.', '') : null;
    $matchPercent = isset($movie['vote_average']) ? round((float)$movie['vote_average'] * 10) : null;
    $year = isset($movie['release_date']) && $movie['release_date'] 
        ? date('Y', strtotime($movie['release_date'])) 
        : null;
    $runtime = isset($movie['runtime']) && $movie['runtime'] 
        ? floor($movie['runtime'] / 60) . 'h ' . ($movie['runtime'] % 60) . 'm'
        : null;
    
    $viewCount = $movie['view_count'] ?? null;
    $genres = $movie['genres'] ?? [];
    $movieId = $movie['id'] ?? null;
    $title = $movie['title'] ?? 'Untitled';
@endphp

<div 
    class="movie-card group"
    onclick="window.location.href='/movie/{{ $movieId }}'"
    style="position: relative; flex-shrink: 0; width: 160px; transition: all 0.3s; cursor: pointer;"
>
    @media (min-width: 768px) {
        .movie-card {
            width: 192px;
        }
    }
    @media (min-width: 1024px) {
        .movie-card {
            width: 224px;
        }
    }

    <!-- Movie Poster -->
    <div class="movie-card-poster" style="position: relative; border-radius: 8px; overflow: hidden;">
        <img 
            src="{{ $imageUrl }}" 
            alt="{{ $title }}"
            loading="lazy"
            onerror="this.src='/images/placeholder.svg'"
            style="width: 100%; height: 240px; object-fit: cover; transition: transform 0.3s;"
            class="movie-card-image"
        >
        
        <!-- Gradient Overlay on Hover -->
        <div 
            class="movie-card-gradient"
            style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent, transparent); opacity: 0; transition: opacity 0.3s;"
        ></div>

        <!-- View Count Badge -->
        @if($viewCount !== null && $viewCount > 0)
        <div 
            class="movie-card-badge-view"
            style="position: absolute; top: 8px; right: 8px; background-color: var(--primary-red); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 4px;"
        >
            <svg fill="currentColor" viewBox="0 0 24 24" style="width: 12px; height: 12px;">
                <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
            </svg>
            <span>{{ $viewCount }}</span>
        </div>
        @endif

        <!-- Rating Badge -->
        @if($rating)
        <div 
            class="movie-card-badge-rating"
            style="position: absolute; top: 8px; left: 8px; background-color: rgba(234, 179, 8, 0.9); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 4px;"
        >
            <span>⭐</span>
            <span>{{ $rating }}</span>
        </div>
        @endif
    </div>

    <!-- Hover Info Card -->
    <div 
        class="movie-card-hover-info"
        style="position: absolute; left: 0; right: 0; top: 100%; margin-top: 8px; background-color: #181818; border-radius: 8px; box-shadow: 0 8px 16px rgba(0,0,0,0.5); padding: 16px; z-index: 30; transform: scale(1.1); transform-origin: top; display: none;"
    >
        <!-- Action Buttons -->
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
            <button
                onclick="event.stopPropagation(); window.location.href='/movie/{{ $movieId }}';"
                style="background-color: white; color: black; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; border: none; cursor: pointer;"
                onmouseover="this.style.backgroundColor='rgba(255,255,255,0.8)'"
                onmouseout="this.style.backgroundColor='white'"
            >
                <svg fill="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                    <path d="M8 5v14l11-7z"/>
                </svg>
            </button>
            <button
                onclick="event.stopPropagation();"
                style="border: 2px solid #9ca3af; color: white; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; background: transparent; cursor: pointer;"
                onmouseover="this.style.borderColor='white'"
                onmouseout="this.style.borderColor='#9ca3af'"
            >
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
            <button
                onclick="event.stopPropagation(); window.location.href='/movie/{{ $movieId }}';"
                style="border: 2px solid #9ca3af; color: white; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; background: transparent; cursor: pointer; margin-left: auto;"
                onmouseover="this.style.borderColor='white'"
                onmouseout="this.style.borderColor='#9ca3af'"
            >
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>

        <!-- Title -->
        <h3 style="color: white; font-weight: 600; font-size: 14px; margin-bottom: 8px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            {{ $title }}
        </h3>

        <!-- Metadata -->
        <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; margin-bottom: 8px;">
            @if($matchPercent)
            <span style="color: #4ade80; font-weight: 600;">{{ $matchPercent }}% Match</span>
            @endif
            @if($year)
            <span style="color: #9ca3af;">{{ $year }}</span>
            @endif
            @if($runtime)
            <span style="color: #9ca3af;">{{ $runtime }}</span>
            @endif
        </div>

        <!-- Genres -->
        @if(count($genres) > 0)
        <div style="display: flex; flex-wrap: wrap; gap: 4px; font-size: 12px; color: #9ca3af;">
            @foreach(array_slice($genres, 0, 3) as $index => $genre)
                <span>{{ $genre['name'] ?? $genre }}</span>
                @if($index < min(2, count($genres) - 1))
                    <span> • </span>
                @endif
            @endforeach
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .movie-card {
        position: relative;
        flex-shrink: 0;
        width: 160px;
        transition: all 0.3s;
        cursor: pointer;
    }

    @media (min-width: 768px) {
        .movie-card {
            width: 192px;
        }
    }

    @media (min-width: 1024px) {
        .movie-card {
            width: 224px;
        }
    }

    .movie-card-poster {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
    }

    .movie-card-image {
        width: 100%;
        height: 240px;
        object-fit: cover;
        transition: transform 0.3s;
    }

    @media (min-width: 768px) {
        .movie-card-image {
            height: 288px;
        }
    }

    @media (min-width: 1024px) {
        .movie-card-image {
            height: 320px;
        }
    }

    .movie-card:hover .movie-card-image {
        transform: scale(1.1);
    }

    .movie-card-gradient {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent, transparent);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .movie-card:hover .movie-card-gradient {
        opacity: 1;
    }

    .movie-card:hover .movie-card-hover-info {
        display: block !important;
    }
</style>
@endpush

