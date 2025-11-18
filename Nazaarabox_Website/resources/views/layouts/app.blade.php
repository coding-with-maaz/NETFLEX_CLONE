<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Nazaara Box - Movie & TV Show Streaming')</title>

    @php
        $siteName = 'Nazaara Box';
        $siteDescription = 'Stream your favorite movies and TV shows online. Watch latest episodes, trending content, and top-rated entertainment in HD quality.';
        $siteUrl = url('/');
        // Use absolute URL for OG image (required for Open Graph)
        $siteImage = url(asset('website_og_image.png'));
        
        // Get SEO data from yield or use defaults
        $seoTitle = $__env->yieldContent('seo_title') ?: ($__env->yieldContent('title') ?: 'Nazaara Box - Movie & TV Show Streaming');
        $seoDescription = $__env->yieldContent('seo_description') ?: $siteDescription;
        $seoImage = $__env->yieldContent('seo_image') ?: $siteImage;
        $seoUrl = $__env->yieldContent('seo_url') ?: url()->current();
        $seoType = $__env->yieldContent('seo_type') ?: 'website';
    @endphp

    <!-- Primary Meta Tags -->
    <meta name="title" content="{{ $seoTitle }}">
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords" content="movies, tv shows, streaming, watch online, episodes, entertainment, Nazaara Box">
    <meta name="author" content="{{ $siteName }}">
    <meta name="robots" content="index, follow">
    <meta name="language" content="English">
    <meta name="revisit-after" content="7 days">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $seoType }}">
    <meta property="og:url" content="{{ $seoUrl }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $seoUrl }}">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoImage }}">
    <meta name="twitter:site" content="@nazaarabox">
    <meta name="twitter:creator" content="@nazaarabox">
    
    <!-- Additional SEO -->
    <link rel="canonical" href="{{ $seoUrl }}">
    @yield('additional_meta')

    <!-- Favicon and App Icons -->
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}" sizes="16x16">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icon.png') }}">
    <meta name="theme-color" content="#dc2626">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Styles -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    
    <!-- Tailwind CSS CDN - Always include as fallback -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --primary-red: #dc2626;
            --primary-red-dark: #b91c1c;
            --primary-red-hover: #ef4444;
            --bg-dark: #000000;
            --bg-dark-secondary: #0f0f0f;
            --bg-dark-surface: #171717;
            --text-primary: #ffffff;
            --text-secondary: #d1d5db;
            --text-muted: #9ca3af;
            --border-gray: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Header Styles - Matching Frontend HomeHeader */
        .app-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 50;
            transition: background-color 0.3s;
        }

        .app-header.scrolled {
            background-color: #000000;
        }

        .app-header:not(.scrolled) {
            background: linear-gradient(to bottom, rgba(0,0,0,1), rgba(0,0,0,0));
        }

        .header-wrapper {
            max-width: 100%;
        }

        .header-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
        }

        @media (min-width: 768px) {
            .header-container {
                padding: 0 32px;
                height: 80px;
            }
        }

        @media (min-width: 1024px) {
            .header-container {
                padding: 0 64px;
            }
        }

        /* Logo and Navigation Group */
        .logo-nav-group {
            display: flex;
            align-items: center;
            gap: 32px;
        }

        /* Logo - Matching Frontend "NAZAARABOX" */
        .logo-text {
            color: var(--primary-red);
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
            transition: color 0.2s;
        }

        .logo-text:hover {
            color: var(--primary-red-hover);
        }

        @media (min-width: 768px) {
            .logo-text {
                font-size: 30px;
            }
        }

        /* Navigation Links */
        .nav-links {
            display: none;
            align-items: center;
            gap: 24px;
        }

        @media (min-width: 768px) {
            .nav-links {
                display: flex;
            }
        }

        .nav-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
            white-space: nowrap;
        }

        .nav-link:hover {
            color: var(--text-primary);
        }

        .nav-link.active {
            color: var(--text-primary);
        }

        /* Header Right Side */
        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* Search Wrapper - Matching Frontend expandable search */
        .search-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input-wrapper {
            display: none;
            align-items: center;
        }

        .search-input-wrapper.active {
            display: flex;
        }

        .search-form {
            display: flex;
            align-items: center;
        }

        .search-input-expanded {
            background: rgba(0,0,0,0.7);
            border: 1px solid var(--text-primary);
            color: var(--text-primary);
            padding: 4px 16px;
            padding-right: 72px;
            width: 240px;
            font-size: 14px;
            outline: none;
            transition: all 0.2s;
            border-radius: 4px;
        }

        .search-input-expanded:focus {
            border-color: var(--text-primary);
        }

        .search-input-expanded::placeholder {
            color: var(--text-muted);
        }

        .search-submit-btn {
            position: absolute;
            right: 40px;
            background: none;
            border: none;
            color: var(--text-primary);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .search-submit-btn:hover {
            color: var(--primary-red);
        }

        .search-close-btn {
            position: absolute;
            right: 8px;
            background: none;
            border: none;
            color: var(--text-primary);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .search-close-btn:hover {
            color: var(--text-secondary);
        }

        .search-icon {
            width: 20px;
            height: 20px;
        }

        .search-toggle-btn {
            background: none;
            border: none;
            color: var(--text-primary);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .search-toggle-btn:hover {
            color: var(--text-secondary);
        }

        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            color: var(--text-primary);
            cursor: pointer;
            padding: 4px;
        }

        @media (min-width: 768px) {
            .mobile-menu-btn {
                display: none;
            }
        }

        .mobile-menu-icon {
            width: 24px;
            height: 24px;
        }

        /* Mobile Menu */
        .mobile-menu {
            display: none;
            background: rgba(0,0,0,0.95);
            border-top: 1px solid rgba(75,85,99,0.5);
            padding: 16px 0;
        }

        .mobile-menu.active {
            display: block;
        }

        @media (min-width: 768px) {
            .mobile-menu {
                display: none !important;
            }
        }

        .mobile-nav {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .mobile-nav-link {
            display: block;
            padding: 8px 16px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 16px;
            transition: color 0.2s;
        }

        .mobile-nav-link:hover,
        .mobile-nav-link.active {
            color: var(--text-primary);
        }

        /* Hero Section - Matching Flutter HeroSection */
        .hero-section {
            position: relative;
            width: 100%;
            height: 60vh;
            min-height: 500px;
            overflow: hidden;
            margin-top: 64px;
        }

        @media (min-width: 768px) {
            .hero-section {
                margin-top: 80px;
            }
        }

        .hero-carousel {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .hero-slide {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .hero-slide.active {
            opacity: 1;
        }

        .hero-backdrop {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to bottom,
                rgba(0,0,0,0.3) 0%,
                rgba(0,0,0,0.7) 70%,
                rgba(0,0,0,1) 100%
            );
        }

        .hero-content {
            position: absolute;
            bottom: 60px;
            left: 20px;
            right: 20px;
            z-index: 2;
        }

        .hero-title {
            color: var(--text-primary);
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.8);
            line-height: 1.2;
        }

        .hero-rating {
            display: flex;
            align-items: center;
            gap: 4px;
            color: var(--text-primary);
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .hero-rating svg {
            width: 20px;
            height: 20px;
            color: #fbbf24;
        }

        .hero-button {
            background-color: var(--primary-red);
            color: var(--text-primary);
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.2s;
        }

        .hero-button:hover {
            background-color: var(--primary-red-hover);
        }

        .hero-button svg {
            width: 24px;
            height: 24px;
        }

        /* Content Cards - Matching Flutter MovieCard/TVShowCard */
        .content-card {
            width: 130px;
            flex-shrink: 0;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .content-card:hover {
            transform: translateY(-4px);
        }

        .content-card-image {
            width: 100%;
            aspect-ratio: 2/3;
            object-fit: cover;
            border-radius: 8px;
            background-color: var(--bg-dark-surface);
        }

        .content-card-title {
            color: var(--text-primary);
            font-size: 12px;
            font-weight: 500;
            margin-top: 6px;
            line-height: 1.2;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .content-card-badge {
            position: absolute;
            bottom: 8px;
            right: 8px;
            background: rgba(0,0,0,0.8);
            padding: 3px 6px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 3px;
            font-size: 9px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .content-card-badge svg {
            width: 10px;
            height: 10px;
            color: var(--primary-red);
        }

        /* Content Rows - Matching Flutter ContentRow */
        .content-row {
            margin-bottom: 24px;
            padding: 0 16px;
        }

        .content-row-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .content-row-title {
            color: var(--text-primary);
            font-size: 20px;
            font-weight: bold;
        }

        .content-row-link {
            color: var(--primary-red);
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s;
        }

        .content-row-link:hover {
            color: var(--primary-red-hover);
        }

        .content-row-scroll {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            overflow-y: hidden;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .content-row-scroll::-webkit-scrollbar {
            display: none;
        }

        /* Loading Spinner */
        .spinner {
            border: 3px solid rgba(220, 38, 38, 0.3);
            border-top-color: var(--primary-red);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Main Content */
        .main-content {
            min-height: calc(100vh - 64px);
            padding-top: 0;
        }

        @media (min-width: 768px) {
            .main-content {
                min-height: calc(100vh - 80px);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .desktop-nav {
                display: none;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .header-container {
                gap: 16px;
            }

            .search-input-container {
                max-width: 100%;
            }

            .hero-section {
                height: 50vh;
                min-height: 400px;
            }

            .hero-title {
                font-size: 24px;
            }

            .hero-content {
                bottom: 40px;
                left: 16px;
                right: 16px;
            }
        }

        @media (min-width: 769px) {
            .mobile-menu {
                display: none !important;
            }
        }

        /* Footer */
        .app-footer {
            background-color: var(--bg-dark);
            padding: 32px 16px;
            text-align: center;
            color: var(--text-muted);
            margin-top: 48px;
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Header - Matching Frontend HomeHeader -->
    <header class="app-header" id="app-header">
        <div class="header-wrapper">
            <div class="header-container">
                <!-- Logo and Navigation Group -->
                <div class="logo-nav-group">
                    <!-- Logo - Matching Frontend "NAZAARABOX" style -->
                    <a href="{{ route('home') }}" class="logo-text">
                        NAZAARABOX
                    </a>

                    <!-- Desktop Navigation -->
                    <nav class="desktop-nav nav-links">
                        <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                            Home
                        </a>
                        <a href="{{ route('movies.index') }}" class="nav-link {{ request()->routeIs('movies.*') && !request()->routeIs('movies.today') && !request()->routeIs('movies.trending') && !request()->routeIs('movies.top-rated') ? 'active' : '' }}">
                            Movies
                        </a>
                        <a href="{{ route('tvshows.index') }}" class="nav-link {{ request()->routeIs('tvshows.*') && !request()->routeIs('tvshows.popular') && !request()->routeIs('tvshows.top-rated') ? 'active' : '' }}">
                            TV Shows
                        </a>
                        <a href="{{ route('trending') }}" class="nav-link {{ request()->routeIs('trending') ? 'active' : '' }}">
                            Trending
                        </a>
                        <a href="{{ route('movies.today') }}" class="nav-link {{ request()->routeIs('movies.today') ? 'active' : '' }}">
                            Today's Movies
                        </a>
                        <a href="{{ route('tvshows.today') }}" class="nav-link {{ request()->routeIs('tvshows.today') || request()->routeIs('episodes.today') ? 'active' : '' }}">
                            Today's Episodes
                        </a>
                        <a href="{{ route('request.index') }}" class="nav-link {{ request()->routeIs('request.*') || request()->routeIs('request-content') ? 'active' : '' }}" style="color: #60a5fa;">
                            Request
                        </a>
                        <a href="{{ route('download') }}" class="nav-link {{ request()->routeIs('download') ? 'active' : '' }}" style="color: var(--primary-red);">
                            📱 Download App
                        </a>
                    </nav>
                </div>

                <!-- Right Side - Search and Mobile Menu -->
                <div class="header-right">
                    <!-- Search - Matching Frontend expandable search -->
                    <div class="search-wrapper">
                        <div class="search-input-wrapper" id="search-input-wrapper">
                            <form action="{{ route('search') }}" method="GET" class="search-form">
                                <input 
                                    type="text" 
                                    name="q" 
                                    id="search-input"
                                    placeholder="Search movies, TV shows..." 
                                    class="search-input-expanded"
                                    value="{{ request()->get('q') }}"
                                    autocomplete="off"
                                >
                                <button type="submit" class="search-submit-btn" id="search-submit-btn" title="Search">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="search-icon">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </button>
                                <button type="button" class="search-close-btn" id="search-close-btn" title="Close">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="search-icon">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                        <button type="button" class="search-toggle-btn" id="search-toggle-btn">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="search-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Mobile Menu Toggle -->
                    <button type="button" class="mobile-menu-btn" id="mobile-menu-btn">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="mobile-menu-icon" id="menu-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="mobile-menu-icon" id="close-icon" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div class="mobile-menu" id="mobile-menu">
                <nav class="mobile-nav">
                    <a href="{{ route('home') }}" class="mobile-nav-link {{ request()->routeIs('home') ? 'active' : '' }}" onclick="closeMobileMenu()">
                        Home
                    </a>
                    <a href="{{ route('movies.index') }}" class="mobile-nav-link {{ request()->routeIs('movies.*') && !request()->routeIs('movies.today') && !request()->routeIs('movies.trending') && !request()->routeIs('movies.top-rated') ? 'active' : '' }}" onclick="closeMobileMenu()">
                        Movies
                    </a>
                    <a href="{{ route('tvshows.index') }}" class="mobile-nav-link {{ request()->routeIs('tvshows.*') && !request()->routeIs('tvshows.popular') && !request()->routeIs('tvshows.top-rated') ? 'active' : '' }}" onclick="closeMobileMenu()">
                        TV Shows
                    </a>
                    <a href="{{ route('trending') }}" class="mobile-nav-link {{ request()->routeIs('trending') ? 'active' : '' }}" onclick="closeMobileMenu()">
                        Trending
                    </a>
                    <a href="{{ route('movies.today') }}" class="mobile-nav-link {{ request()->routeIs('movies.today') ? 'active' : '' }}" onclick="closeMobileMenu()">
                        Today's Movies
                    </a>
                    <a href="{{ route('tvshows.today') }}" class="mobile-nav-link {{ request()->routeIs('tvshows.today') || request()->routeIs('episodes.today') ? 'active' : '' }}" onclick="closeMobileMenu()">
                        Today's Episodes
                    </a>
                    <a href="{{ route('request.index') }}" class="mobile-nav-link {{ request()->routeIs('request.*') || request()->routeIs('request-content') ? 'active' : '' }}" onclick="closeMobileMenu()" style="color: #60a5fa;">
                        Request
                    </a>
                    <a href="{{ route('download') }}" class="mobile-nav-link {{ request()->routeIs('download') ? 'active' : '' }}" onclick="closeMobileMenu()" style="color: var(--primary-red);">
                        📱 Download App
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="app-footer">
        <p>&copy; {{ date('Y') }} Nazaara Box. All rights reserved.</p>
    </footer>

    <!-- Scripts -->
    <script>
        // Mobile menu toggle - Matching Frontend behavior
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIcon = document.getElementById('menu-icon');
        const closeIcon = document.getElementById('close-icon');

        function toggleMobileMenu() {
            const isActive = mobileMenu.classList.toggle('active');
            if (menuIcon) menuIcon.style.display = isActive ? 'none' : 'block';
            if (closeIcon) closeIcon.style.display = isActive ? 'block' : 'none';
        }

        function closeMobileMenu() {
            mobileMenu.classList.remove('active');
            if (menuIcon) menuIcon.style.display = 'block';
            if (closeIcon) closeIcon.style.display = 'none';
        }

        mobileMenuBtn?.addEventListener('click', toggleMobileMenu);

        // Search toggle - Matching Frontend expandable search
        const searchToggleBtn = document.getElementById('search-toggle-btn');
        const searchInputWrapper = document.getElementById('search-input-wrapper');
        const searchInput = document.getElementById('search-input');
        const searchCloseBtn = document.getElementById('search-close-btn');
        const searchForm = searchInputWrapper?.querySelector('.search-form');

        searchToggleBtn?.addEventListener('click', function() {
            searchInputWrapper.classList.add('active');
            setTimeout(() => {
                searchInput?.focus();
            }, 100);
        });

        searchCloseBtn?.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            searchInputWrapper.classList.remove('active');
            if (searchInput) {
                searchInput.value = '';
                searchInput.blur();
            }
        });

        // Prevent form submission when clicking close button
        searchCloseBtn?.addEventListener('mousedown', function(e) {
            e.preventDefault();
        });

        // Submit search form on Enter key or button click
        searchForm?.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            const query = searchInput?.value.trim();
            if (query) {
                window.location.href = `{{ route('search') }}?q=${encodeURIComponent(query)}`;
            } else {
                // If empty, just close the search input
                searchInputWrapper.classList.remove('active');
            }
        });

        // Handle Enter key press in search input
        searchInput?.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = searchInput.value.trim();
                if (query) {
                    window.location.href = `{{ route('search') }}?q=${encodeURIComponent(query)}`;
                } else {
                    searchInputWrapper.classList.remove('active');
                }
            } else if (e.key === 'Escape') {
                e.preventDefault();
                searchInputWrapper.classList.remove('active');
                if (searchInput) {
                    searchInput.value = '';
                    searchInput.blur();
                }
            }
        });

        // Close search on outside click
        document.addEventListener('click', function(e) {
            if (searchInputWrapper && searchInputWrapper.classList.contains('active')) {
                if (!searchInputWrapper.contains(e.target) && !searchToggleBtn.contains(e.target)) {
                    searchInputWrapper.classList.remove('active');
                }
            }
        });

        // Header scroll effect - Matching Frontend behavior
        const header = document.getElementById('app-header');
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 0) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Auto-show search input if there's a query parameter
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('q');
        if (searchQuery && searchInput) {
            searchInputWrapper.classList.add('active');
            searchInput.value = searchQuery;
        }

        // Auto-focus search input when expanded (except on search page)
        if (searchInputWrapper && searchInputWrapper.classList.contains('active') && !window.location.pathname.includes('/search')) {
            setTimeout(() => {
                searchInput?.focus();
            }, 100);
        }

        // API Base URL - Using Laravel routes
        const API_BASE_URL = '{{ url("/api/v1") }}';
        
        // API Key for authenticated requests
        const API_KEY = '{{ env("API_KEY", "nzb_api_qfUxBMPiu3aqeXjgdqKCO4KqTDJB31m4") }}';
        
        // Helper function to get API headers (only for protected endpoints)
        function getApiHeaders(isPublicEndpoint = false) {
            if (isPublicEndpoint) {
                return {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                };
            }
            return {
                'X-API-Key': API_KEY,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            };
        }
        
        // Helper function to check if endpoint is public
        function isPublicEndpoint(url) {
            const path = url.toLowerCase();
            return path.includes('/utils/all') ||
                   path.includes('/search') ||
                   path.includes('/movies/search') ||
                   path.includes('/tvshows/search') ||
                   path.includes('/episodes/search') ||
                   (path.includes('/leaderboard/movies/') && path.includes('/view')) ||
                   (path.includes('/leaderboard/tvshows/') && path.includes('/view'));
        }
        
        // Helper function to make API fetch with automatic header inclusion
        async function apiFetch(url, options = {}) {
            const isPublic = isPublicEndpoint(url);
            const headers = {
                ...getApiHeaders(isPublic),
                ...(options.headers || {})
            };
            
            return fetch(url, {
                ...options,
                headers: headers
            });
        }
    </script>

    @stack('scripts')

    <!-- Footer - Matching Frontend Footer.jsx -->
    <footer class="bg-gray-900 border-t border-gray-800">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Brand -->
                <div class="space-y-4">
                    <div class="flex items-center space-x-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-600">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-white">NAZAARABOX</span>
                    </div>
                    <p class="text-gray-400 text-sm">
                        Your ultimate destination for movies and TV shows. Discover, watch, and enjoy the best content from around the world.
                    </p>
                </div>

                <!-- Quick Links -->
                <div class="space-y-4">
                    <h3 class="text-white font-semibold">Quick Links</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('home') }}" class="text-gray-400 hover:text-white transition-colors text-sm">
                                Home
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('movies.index') }}" class="text-gray-400 hover:text-white transition-colors text-sm">
                                Movies
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tvshows.index') }}" class="text-gray-400 hover:text-white transition-colors text-sm">
                                TV Shows
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('trending') }}" class="text-gray-400 hover:text-white transition-colors text-sm">
                                Trending
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- About -->
                <div class="space-y-4">
                    <h3 class="text-white font-semibold">About</h3>
                    <ul class="space-y-2">
                        <li class="text-gray-400 text-sm">
                            Content Management System
                        </li>
                        <li class="text-gray-400 text-sm">
                            Movie & TV Show Database
                        </li>
                        <li class="text-gray-400 text-sm">
                            TMDB Integration
                        </li>
                        <li class="text-gray-400 text-sm">
                            Embed & Download Links
                        </li>
                    </ul>
                </div>

                <!-- Connect -->
                <div class="space-y-4">
                    <h3 class="text-white font-semibold">Connect</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors" aria-label="GitHub">
                            <svg fill="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                                <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"></path>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors" aria-label="Twitter">
                            <svg fill="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors" aria-label="Email">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </a>
                    </div>
                    <p class="text-gray-400 text-sm">
                        Powered by TMDB API
                    </p>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400 text-sm">
                        © 2024 NAZAARABOX. All rights reserved.
                    </p>
                    <div class="flex space-x-6 mt-4 md:mt-0">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors text-sm">
                            Privacy Policy
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors text-sm">
                            Terms of Service
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors text-sm">
                            Contact
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
