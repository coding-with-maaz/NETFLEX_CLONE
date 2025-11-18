@extends('layouts.app')

@section('title', 'Download Nazaara Box - Android App on Google Play')

@section('seo_title', 'Download Nazaara Box - Android App on Google Play')
@section('seo_description', 'Download Nazaara Box Android app from Google Play Store to stream your favorite movies and TV shows on the go. Watch latest episodes, trending content, and top-rated entertainment.')
@section('seo_type', 'website')

@section('content')
<style>
    /* Mobile-First Optimizations */
    .download-hero {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        background: linear-gradient(135deg, #1a1a1a 0%, #000000 50%, #0a0a0a 100%);
        position: relative;
        overflow: hidden;
    }

    .download-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(220, 38, 38, 0.1) 0%, transparent 70%);
        animation: pulse 8s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.3; }
        50% { transform: scale(1.1); opacity: 0.5; }
    }

    .logo-container {
        position: relative;
        margin-bottom: 2rem;
        animation: fadeInUp 0.8s ease-out;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .logo-image {
        width: 120px;
        height: 120px;
        object-fit: contain;
        filter: drop-shadow(0 8px 24px rgba(220, 38, 38, 0.3));
        transition: transform 0.3s ease;
    }

    .logo-image:hover {
        transform: scale(1.05);
    }

    .logo-image:active {
        transform: scale(0.95);
    }

    @media (min-width: 640px) {
        .logo-image {
            width: 150px;
            height: 150px;
        }
    }

    @media (min-width: 1024px) {
        .logo-container {
            justify-content: flex-start;
        }
    }

    .brand-text {
        text-align: center;
        margin-bottom: 2rem;
        animation: fadeInUp 0.8s ease-out 0.2s both;
    }

    .brand-title {
        font-size: 2.5rem;
        font-weight: 900;
        color: #ffffff;
        line-height: 1.1;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
        letter-spacing: -0.02em;
    }

    .brand-subtitle {
        font-size: 1.75rem;
        font-weight: 700;
        color: #ffffff;
        line-height: 1.2;
        margin-bottom: 0.25rem;
    }

    .brand-apk {
        font-size: 1.5rem;
        font-weight: 700;
        color: #ffffff;
        line-height: 1.2;
    }

    /* Mobile Phone Mockup - Optimized for Mobile */
    .phone-mockup-container {
        position: relative;
        margin: 2rem 0;
        animation: fadeInUp 0.8s ease-out 0.4s both;
        max-width: 280px;
        width: 100%;
        margin-left: auto;
        margin-right: auto;
    }

    .phone-mockup {
        position: relative;
        width: 100%;
        max-width: 280px;
        background: #000000;
        border-radius: 32px;
        padding: 8px;
        box-shadow: 
            0 20px 60px rgba(0, 0, 0, 0.8),
            0 0 0 2px rgba(255, 255, 255, 0.1),
            inset 0 0 0 2px rgba(255, 255, 255, 0.05);
        margin: 0 auto;
    }

    .phone-screen {
        background: #0f0f0f;
        border-radius: 24px;
        overflow: hidden;
        aspect-ratio: 9 / 19.5;
    }

    .phone-header {
        background: #000000;
        padding: 12px 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
    }

    .phone-nav {
        background: #000000;
        padding: 8px 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        gap: 16px;
    }

    /* Responsive phone content */
    @media (max-width: 479px) {
        .phone-header {
            padding: 8px 12px;
        }

        .phone-header img {
            width: 20px !important;
            height: 20px !important;
        }

        .phone-header span {
            font-size: 0.75rem !important;
        }

        .phone-nav {
            padding: 6px 12px;
            gap: 12px;
        }

        .phone-nav button {
            padding: 4px 8px !important;
            font-size: 0.65rem !important;
        }

        .phone-content {
            padding: 8px;
        }

        .phone-hero-poster {
            height: 100px;
            margin-bottom: 8px;
        }

        .phone-content-grid {
            gap: 6px;
        }

        .phone-content-row-item {
            width: 60px;
        }
    }

    .phone-content {
        background: linear-gradient(180deg, #0f0f0f 0%, #000000 100%);
        padding: 12px;
        height: calc(100% - 100px);
        overflow-y: auto;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: rgba(220, 38, 38, 0.3) transparent;
    }

    .phone-content::-webkit-scrollbar {
        width: 3px;
    }

    .phone-content::-webkit-scrollbar-track {
        background: transparent;
    }

    .phone-content::-webkit-scrollbar-thumb {
        background: rgba(220, 38, 38, 0.3);
        border-radius: 3px;
    }

    .phone-content::-webkit-scrollbar-thumb:hover {
        background: rgba(220, 38, 38, 0.5);
    }

    /* Featured Hero in Phone */
    .phone-hero-poster {
        width: 100%;
        height: 140px;
        border-radius: 8px;
        overflow: hidden;
        position: relative;
        margin-bottom: 12px;
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .phone-hero-poster:active {
        transform: scale(0.98);
    }

    .phone-hero-poster img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .phone-hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.8) 100%);
    }

    .phone-hero-info {
        position: absolute;
        bottom: 8px;
        left: 8px;
        right: 8px;
    }

    .phone-hero-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 4px;
        line-height: 1.2;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .phone-hero-rating {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 0.625rem;
        color: #fbbf24;
    }

    /* Content Grid in Phone */
    .phone-content-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-bottom: 12px;
    }

    .phone-content-item {
        aspect-ratio: 2 / 3;
        border-radius: 8px;
        overflow: hidden;
        position: relative;
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        cursor: pointer;
        transition: transform 0.2s ease;
        text-decoration: none;
        display: block;
    }

    .phone-content-item:active {
        transform: scale(0.95);
    }

    .phone-content-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .phone-content-item-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.6) 100%);
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .phone-content-item:hover .phone-content-item-overlay,
    .phone-content-item:active .phone-content-item-overlay {
        opacity: 1;
    }

    .phone-content-item-info {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 6px;
        transform: translateY(100%);
        transition: transform 0.2s ease;
    }

    .phone-content-item:hover .phone-content-item-info,
    .phone-content-item:active .phone-content-item-info {
        transform: translateY(0);
    }

    .phone-content-item-title {
        font-size: 0.625rem;
        font-weight: 600;
        color: #ffffff;
        line-height: 1.2;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Content Row in Phone */
    .phone-content-row {
        margin-bottom: 16px;
    }

    .phone-content-row-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 8px;
        padding: 0 4px;
    }

    .phone-content-row-scroll {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
        padding-bottom: 4px;
    }

    .phone-content-row-scroll::-webkit-scrollbar {
        display: none;
    }

    .phone-content-row-item {
        flex-shrink: 0;
        width: 80px;
        aspect-ratio: 2 / 3;
        border-radius: 6px;
        overflow: hidden;
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        position: relative;
        cursor: pointer;
        transition: transform 0.2s ease;
        text-decoration: none;
        display: block;
    }

    .phone-content-row-item:active {
        transform: scale(0.95);
    }

    .phone-content-row-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Download Button - Mobile Optimized */
    .download-button-container {
        width: 100%;
        max-width: 400px;
        margin: 2rem auto;
        animation: fadeInUp 0.8s ease-out 0.6s both;
    }

    .download-button {
        width: 100%;
        background: linear-gradient(135deg, #1f1f1f 0%, #0f0f0f 100%);
        border: 2px solid rgba(220, 38, 38, 0.3);
        border-radius: 16px;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
        position: relative;
        overflow: hidden;
    }

    .download-button::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(220, 38, 38, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .download-button:active {
        transform: scale(0.98);
        border-color: rgba(220, 38, 38, 0.6);
    }

    .download-button:active::before {
        left: 100%;
    }

    .download-button-left {
        display: flex;
        align-items: center;
        gap: 16px;
        flex: 1;
    }

    .download-button-text {
        display: flex;
        flex-direction: column;
    }

    .download-button-label {
        font-size: 0.875rem;
        color: #9ca3af;
        font-weight: 500;
    }

    .download-button-platform {
        font-size: 1.5rem;
        font-weight: 800;
        color: #ffffff;
        line-height: 1.2;
    }

    .download-icon {
        width: 24px;
        height: 24px;
        color: #ffffff;
        transition: transform 0.3s ease;
    }

    .download-button:active .download-icon {
        transform: translateY(4px);
    }

    .version-info {
        text-align: center;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .version-text {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .version-subtext {
        font-size: 0.75rem;
        color: #4b5563;
    }

    .branding-section {
        width: 100%;
        max-width: 600px;
        margin: 0 auto 2rem;
    }

    @media (min-width: 768px) {
        .branding-section {
            margin: 0;
        }
    }

    /* Small Mobile (320px - 479px) */
    @media (min-width: 320px) and (max-width: 479px) {
        .download-hero {
            padding: 1.5rem 1rem;
            min-height: auto;
        }

        .brand-title {
            font-size: 2rem;
        }

        .brand-subtitle {
            font-size: 1.5rem;
        }

        .brand-apk {
            font-size: 1.25rem;
        }

        .logo-image {
            width: 100px;
            height: 100px;
        }

        .phone-mockup-container {
            max-width: 240px;
        }

        .phone-mockup {
            max-width: 240px;
        }

        .download-button-container {
            max-width: 100%;
            padding: 0 0.5rem;
        }

        .download-button {
            padding: 16px 20px;
        }

        .download-button-platform {
            font-size: 1.25rem;
        }
    }

    /* Mobile (480px - 639px) */
    @media (min-width: 480px) {
        .download-hero {
            padding: 2rem 1.5rem;
        }

        .brand-title {
            font-size: 2.5rem;
        }

        .brand-subtitle {
            font-size: 1.75rem;
        }

        .brand-apk {
            font-size: 1.5rem;
        }

        .logo-image {
            width: 120px;
            height: 120px;
        }

        .phone-mockup-container {
            max-width: 280px;
        }

        .phone-mockup {
            max-width: 280px;
        }
    }

    /* Small Tablets (640px - 767px) */
    @media (min-width: 640px) {
        .download-hero {
            padding: 3rem 2rem;
        }

        .brand-title {
            font-size: 3.5rem;
        }

        .brand-subtitle {
            font-size: 2.5rem;
        }

        .brand-apk {
            font-size: 2rem;
        }

        .logo-image {
            width: 150px;
            height: 150px;
        }

        .phone-mockup-container {
            max-width: 320px;
        }

        .phone-mockup {
            max-width: 320px;
        }

        .download-button-container {
            max-width: 450px;
        }

        .download-button {
            padding: 22px 28px;
        }

        .download-button-platform {
            font-size: 1.75rem;
        }
    }

    /* Tablets (768px - 1023px) */
    @media (min-width: 768px) {
        .download-hero {
            padding: 3.5rem 2.5rem;
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2rem;
        }

        .brand-title {
            font-size: 4rem;
        }

        .brand-subtitle {
            font-size: 3rem;
        }

        .brand-apk {
            font-size: 2.25rem;
        }

        .logo-image {
            width: 160px;
            height: 160px;
        }

        .phone-mockup-container {
            max-width: 360px;
        }

        .phone-mockup {
            max-width: 360px;
        }

        .download-button-container {
            max-width: 500px;
            width: 100%;
        }

        .download-hero > div {
            flex: 0 1 45%;
            min-width: 300px;
        }
    }

    /* Desktop (1024px - 1279px) */
    @media (min-width: 1024px) {
        .download-hero {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            padding: 4rem 3rem;
            max-width: 1400px;
            margin: 0 auto;
            gap: 3rem;
        }

        .download-hero > div {
            flex: 1;
            min-width: 0;
        }

        .brand-text {
            text-align: left;
        }

        .logo-container {
            justify-content: flex-start;
        }

        .logo-image {
            width: 180px;
            height: 180px;
        }

        .brand-title {
            font-size: 4.5rem;
        }

        .brand-subtitle {
            font-size: 3.5rem;
        }

        .brand-apk {
            font-size: 2.5rem;
        }

        .phone-mockup-container {
            max-width: 400px;
        }

        .phone-mockup {
            max-width: 400px;
        }

        .download-button-container {
            max-width: 550px;
        }
    }

    /* Large Desktop (1280px - 1535px) */
    @media (min-width: 1280px) {
        .download-hero {
            padding: 5rem 4rem;
            max-width: 1600px;
        }

        .logo-image {
            width: 200px;
            height: 200px;
        }

        .brand-title {
            font-size: 5rem;
        }

        .brand-subtitle {
            font-size: 4rem;
        }

        .brand-apk {
            font-size: 3rem;
        }

        .phone-mockup-container {
            max-width: 450px;
        }

        .phone-mockup {
            max-width: 450px;
        }
    }

    /* Extra Large Desktop (1536px+) */
    @media (min-width: 1536px) {
        .download-hero {
            padding: 6rem 5rem;
            max-width: 1800px;
        }

        .logo-image {
            width: 220px;
            height: 220px;
        }

        .brand-title {
            font-size: 5.5rem;
        }

        .brand-subtitle {
            font-size: 4.5rem;
        }

        .brand-apk {
            font-size: 3.5rem;
        }

        .phone-mockup-container {
            max-width: 500px;
        }

        .phone-mockup {
            max-width: 500px;
        }

        .download-button-container {
            max-width: 600px;
        }

        .download-button {
            padding: 24px 32px;
        }

        .download-button-platform {
            font-size: 2rem;
        }
    }

    /* Landscape Mobile Orientation */
    @media (max-height: 500px) and (orientation: landscape) {
        .download-hero {
            min-height: auto;
            padding: 2rem 1rem;
        }

        .logo-image {
            width: 80px;
            height: 80px;
        }

        .brand-title {
            font-size: 2rem;
        }

        .brand-subtitle {
            font-size: 1.5rem;
        }

        .brand-apk {
            font-size: 1.25rem;
        }

        .phone-mockup-container {
            max-width: 200px;
        }

        .phone-mockup {
            max-width: 200px;
        }
    }

    /* Touch-friendly improvements */
    @media (hover: none) and (pointer: coarse) {
        .download-button {
            min-height: 64px;
        }

        .phone-content-item,
        .phone-content-row-item,
        .phone-hero-poster {
            min-height: 44px;
        }
    }

    /* Print styles */
    @media print {
        .download-hero {
            background: white;
            color: black;
        }

        .phone-mockup-container {
            display: none;
        }
    }
</style>

<div class="download-hero">
    <!-- Left Section - Branding -->
    <div class="branding-section">
        <!-- Logo -->
        <div class="logo-container">
            <img src="{{ asset('icon.png') }}" alt="Nazaara Box Logo" class="logo-image">
        </div>

        <!-- Brand Text -->
        <div class="brand-text">
            <h1 class="brand-title">Nazaarabox</h1>
            <h2 class="brand-subtitle">Download</h2>
            <h3 class="brand-apk">Nazaarabox</h3>
        </div>
    </div>

    <!-- Center Section - Phone Mockup -->
    <div class="phone-mockup-container">
        <div class="phone-mockup">
            <div class="phone-screen">
                <!-- App Header -->
                <div class="phone-header">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <img src="{{ asset('icon.png') }}" alt="Nazaara Box" style="width: 24px; height: 24px; object-fit: contain; flex-shrink: 0;">
                        <span style="color: white; font-weight: 600; font-size: 0.875rem;">Nazaarabox</span>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="phone-nav">
                    <button style="padding: 6px 12px; font-size: 0.75rem; font-weight: 600; color: #dc2626; border-bottom: 2px solid #dc2626;">
                        TV Shows
                    </button>
                    <button style="padding: 6px 12px; font-size: 0.75rem; font-weight: 500; color: #9ca3af;">
                        Movies
                    </button>
                </div>

                <!-- Content - Functional with Real Data -->
                <div class="phone-content">
                    @if($featuredMovies->isNotEmpty())
                        <!-- Featured Hero Movie -->
                        @php $featuredMovie = $featuredMovies->first(); @endphp
                        <a href="https://play.google.com/store/apps/details?id=com.pro.name.generator&hl=en" target="_blank" rel="noopener noreferrer" class="phone-hero-poster">
                            @if($featuredMovie->backdrop_path)
                                <img src="{{ str_starts_with($featuredMovie->backdrop_path, 'http') ? $featuredMovie->backdrop_path : 'https://image.tmdb.org/t/p/w500' . $featuredMovie->backdrop_path }}" 
                                     alt="{{ $featuredMovie->title }}"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div class="phone-hero-overlay"></div>
                                <div class="phone-hero-info">
                                    <div class="phone-hero-title">{{ $featuredMovie->title }}</div>
                                    @if($featuredMovie->vote_average > 0)
                                        <div class="phone-hero-rating">
                                            <svg style="width: 10px; height: 10px;" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                            <span>{{ number_format($featuredMovie->vote_average, 1) }}</span>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div style="display: none; width: 100%; height: 100%; background: linear-gradient(135deg, #92400e 0%, #78350f 100%);"></div>
                            @endif
                        </a>

                        <!-- Movies Grid -->
                        <div class="phone-content-grid">
                            @foreach($featuredMovies->take(6) as $movie)
                                <a href="https://play.google.com/store/apps/details?id=com.pro.name.generator&hl=en" target="_blank" rel="noopener noreferrer" class="phone-content-item">
                                    @if($movie->poster_path)
                                        <img src="{{ str_starts_with($movie->poster_path, 'http') ? $movie->poster_path : 'https://image.tmdb.org/t/p/w300' . $movie->poster_path }}" 
                                             alt="{{ $movie->title }}"
                                             onerror="this.parentElement.style.background='linear-gradient(135deg, #374151 0%, #1f2937 100%)'; this.style.display='none';">
                                    @else
                                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #374151 0%, #1f2937 100%);"></div>
                                    @endif
                                    <div class="phone-content-item-overlay"></div>
                                    <div class="phone-content-item-info">
                                        <div class="phone-content-item-title">{{ $movie->title }}</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if($popularTVShows->isNotEmpty())
                        <!-- TV Shows Row -->
                        <div class="phone-content-row">
                            <div class="phone-content-row-title">Popular TV Shows</div>
                            <div class="phone-content-row-scroll">
                                @foreach($popularTVShows->take(6) as $tvShow)
                                    <a href="https://play.google.com/store/apps/details?id=com.pro.name.generator&hl=en" target="_blank" rel="noopener noreferrer" class="phone-content-row-item">
                                        @if($tvShow->poster_path)
                                            <img src="{{ str_starts_with($tvShow->poster_path, 'http') ? $tvShow->poster_path : 'https://image.tmdb.org/t/p/w300' . $tvShow->poster_path }}" 
                                                 alt="{{ $tvShow->name }}"
                                                 onerror="this.parentElement.style.background='linear-gradient(135deg, #991b1b 0%, #7f1d1d 100%)'; this.style.display='none';">
                                        @else
                                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #991b1b 0%, #7f1d1d 100%);"></div>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Trending Movies Row -->
                    @if($featuredMovies->count() > 3)
                        <div class="phone-content-row">
                            <div class="phone-content-row-title">Trending Movies</div>
                            <div class="phone-content-row-scroll">
                                @foreach($featuredMovies->skip(3)->take(6) as $movie)
                                    <a href="https://play.google.com/store/apps/details?id=com.pro.name.generator&hl=en" target="_blank" rel="noopener noreferrer" class="phone-content-row-item">
                                        @if($movie->poster_path)
                                            <img src="{{ str_starts_with($movie->poster_path, 'http') ? $movie->poster_path : 'https://image.tmdb.org/t/p/w300' . $movie->poster_path }}" 
                                                 alt="{{ $movie->title }}"
                                                 onerror="this.parentElement.style.background='linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%)'; this.style.display='none';">
                                        @else
                                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);"></div>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Download Button -->
    <div class="download-button-container">
        <a href="https://play.google.com/store/apps/details?id=com.pro.name.generator&hl=en" target="_blank" rel="noopener noreferrer" class="download-button">
            <div class="download-button-left">
                <svg style="width: 40px; height: 40px; color: white; flex-shrink: 0;" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.5511 0 .9993.4486.9993.9993.0001.5511-.4482.9997-.9993.9997m-11.046 0c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.551 0 .9993.4486.9993.9993 0 .5511-.4483.9997-.9993.9997m11.4045-6.02l1.9973-3.4592a.416.416 0 00-.1521-.5676.416.416 0 00-.5676.1521l-2.0223 3.503C15.5902 8.2439 13.8533 7.8508 12 7.8508s-3.5902.3931-5.1349 1.0857L4.8429 5.4337a.4161.4161 0 00-.5676-.1521.4157.4157 0 00-.1521.5676l1.9973 3.4592C2.6889 11.1862.8535 13.2068 0 15.7041h24c-.8535-2.4973-2.6889-4.518-5.1225-6.3827"/>
                </svg>
                <div class="download-button-text">
                    <span class="download-button-label">Get it on</span>
                    <span class="download-button-platform">Google Play</span>
                </div>
            </div>
            <svg class="download-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
        </a>

        <div class="version-info">
            <p class="version-text">Version 1.0.0</p>
            <p class="version-subtext">Free download • No registration required</p>
        </div>
    </div>
</div>
@endsection
