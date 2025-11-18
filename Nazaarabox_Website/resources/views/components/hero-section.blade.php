{{-- Hero Section Component - Matching Flutter HeroSection --}}
@props(['featuredItems' => []])

@if(!empty($featuredItems))
<div class="hero-section" id="hero-section">
    <div class="hero-carousel" id="hero-carousel">
        @foreach($featuredItems as $index => $item)
        <div class="hero-slide {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}">
            <img 
                src="{{ $item['backdrop_path'] ?? $item['backdrop'] ?? $item['poster_path'] ?? $item['poster'] ?? '/images/placeholder.svg' }}" 
                alt="{{ $item['title'] ?? $item['name'] ?? 'Featured' }}"
                class="hero-backdrop"
                onerror="this.src='/images/placeholder.svg'"
            >
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <h1 class="hero-title">{{ $item['title'] ?? $item['name'] ?? 'Featured Content' }}</h1>
                @if(isset($item['vote_average']) || isset($item['rating']))
                <div class="hero-rating">
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    <span>{{ number_format($item['vote_average'] ?? $item['rating'] ?? 0, 1) }}</span>
                </div>
                @endif
                <a href="{{ ($item['type'] ?? 'movie') === 'tvshow' ? '/tvshow/' . ($item['id'] ?? '') . (isset($item['name']) ? '?name=' . urlencode($item['name']) : '') : '/movie/' . ($item['id'] ?? '') }}" class="hero-button">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                    <span>Watch Now</span>
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
    // Auto-play carousel - Matching Flutter HeroSection behavior
    let currentHeroSlide = 0;
    const heroSlides = document.querySelectorAll('.hero-slide');
    const totalSlides = heroSlides.length;

    function nextHeroSlide() {
        if (totalSlides === 0) return;
        
        heroSlides[currentHeroSlide].classList.remove('active');
        currentHeroSlide = (currentHeroSlide + 1) % totalSlides;
        heroSlides[currentHeroSlide].classList.add('active');
    }

    // Auto-play every 5 seconds (matching Flutter)
    if (totalSlides > 1) {
        setInterval(nextHeroSlide, 5000);
    }
</script>
@endpush
@endif

