@extends('layouts.app')

@section('title', 'Movie Details - Nazaara Box')

@section('seo_title', 'Watch Movie Online - Nazaara Box')
@section('seo_description', 'Watch this movie online in HD quality on Nazaara Box. Stream with multiple server options and download links available.')
@section('seo_type', 'video.movie')
@section('seo_url', url()->current())

@push('styles')
<style>
    /* Adjust padding for fixed header */
    .movie-detail-wrapper {
        padding-top: 64px; /* Mobile header height */
    }

    @media (min-width: 768px) {
        .movie-detail-wrapper {
            padding-top: 80px; /* Desktop header height */
        }
    }

    /* Hero Section */
    .movie-hero {
        position: relative;
        height: 70vh;
        min-height: 500px;
    }

    @media (min-width: 768px) {
        .movie-hero {
            height: 80vh;
        }
    }

    .movie-hero-backdrop {
        position: absolute;
        inset: 0;
    }

    .movie-hero-backdrop img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .movie-hero-gradient-1 {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,1), rgba(0,0,0,0.6), transparent);
    }

    .movie-hero-gradient-2 {
        position: absolute;
        inset: 0;
        background: linear-gradient(to right, rgba(0,0,0,1), transparent, transparent);
    }

    /* Video Player Styles */
    .video-player-container {
        position: relative;
        width: 100%;
        background-color: black;
        border-radius: 8px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.5);
        overflow: hidden;
    }

    .video-player-wrapper {
        position: relative;
        padding-top: 56.25%; /* 16:9 aspect ratio */
    }

    .video-player-wrapper.custom-styling {
        padding-top: 0; /* Remove aspect ratio padding for custom styled players */
        height: auto;
        display: flex;
        justify-content: center;
        align-items: flex-start;
    }

    .video-player-wrapper iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .video-player-wrapper.custom-styling iframe {
        position: relative; /* Let custom styling control positioning */
    }

    /* Override custom div width - always use 100% */
    .video-player-wrapper.custom-styling > div {
        width: 100% !important;
        max-width: 100%;
    }

    /* Server Selection Buttons */
    .server-btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .server-btn-active {
        background-color: #dc2626; /* bg-red-600 */
        color: white;
    }

    .server-btn-inactive {
        background-color: #1f2937; /* bg-gray-800 */
        color: #d1d5db; /* text-gray-300 */
    }

    .server-btn-inactive:hover {
        background-color: #374151; /* hover:bg-gray-700 */
    }

    /* Fullscreen Button */
    .fullscreen-btn {
        position: absolute;
        bottom: 16px;
        right: 16px;
        background-color: rgba(0,0,0,0.7);
        color: white;
        padding: 12px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 10;
    }

    .video-player-container:hover .fullscreen-btn {
        opacity: 1;
    }

    .fullscreen-btn:hover {
        background-color: rgba(0,0,0,0.9);
    }

    /* Download Card */
    .download-card {
        background-color: #111827; /* bg-gray-900 */
        border: 1px solid #1f2937; /* border-gray-800 */
        border-radius: 8px;
        padding: 16px;
        transition: border-color 0.2s;
        text-decoration: none;
        display: block;
    }

    .download-card:hover {
        border-color: #dc2626; /* border-red-600 */
    }

    /* Movie Card Styles (for similar movies) */
    .movie-card {
        position: relative;
        flex-shrink: 0;
        width: 160px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .movie-card:hover .movie-card-poster {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.6);
        border-radius: 12px;
    }

    .movie-card-image {
        width: 100%;
        height: 240px;
        object-fit: cover;
        transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        display: block;
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
        transform: scale(1.15);
    }

    .movie-card-gradient {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(220,38,38,0.3) 50%, transparent 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 1;
    }

    .movie-card:hover .movie-card-gradient {
        opacity: 1;
    }

    .movie-card-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 60%);
        opacity: 1;
        transition: opacity 0.3s ease;
        z-index: 1;
    }

    .movie-card:hover .movie-card-overlay {
        opacity: 0;
    }

    .movie-card-badge {
        position: absolute;
        top: 8px;
        background: linear-gradient(135deg, rgba(234, 179, 8, 0.95) 0%, rgba(217, 119, 6, 0.95) 100%);
        color: white;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 4px;
        z-index: 2;
        backdrop-filter: blur(4px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
    }

    .movie-card:hover .movie-card-badge {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(234, 179, 8, 0.5);
    }

    .movie-card-title {
        margin-top: 12px;
        color: white;
        font-weight: 600;
        font-size: 14px;
        line-height: 1.3;
        transition: color 0.3s ease;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .movie-card:hover .movie-card-title {
        color: #dc2626;
    }

    .movie-card-meta {
        margin-top: 6px;
        color: #9ca3af;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .movie-card-hover-info {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 12px);
        background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
        border-radius: 12px;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.8);
        padding: 20px;
        z-index: 50;
        transform: scale(0.95);
        transform-origin: top;
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: none;
        border: 1px solid rgba(220, 38, 38, 0.2);
    }

    .movie-card:hover .movie-card-hover-info {
        opacity: 1;
        transform: scale(1);
        pointer-events: auto;
    }

    /* Ad Container Styles - Full Page Overlay */
    .ad-container {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.95);
        z-index: 9999999 !important; /* Maximum z-index to appear over all iframes */
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.5s;
        backdrop-filter: blur(10px);
        pointer-events: auto !important;
    }

    .ad-container.show {
        opacity: 1;
        visibility: visible;
    }

    /* Remove body padding since we're using full overlay */
    body.ad-active {
        padding-bottom: 0;
        overflow: hidden;
    }
    
    /* Ensure ad shows over video embeds and all iframes */
    body.ad-active iframe,
    body.ad-active video,
    body.ad-active .video-player-container,
    body.ad-active .video-player-wrapper {
        pointer-events: none !important;
        z-index: 1 !important;
    }

    /* Ensure all iframes have lower z-index than ad */
    .ad-container.show ~ * iframe,
    body.ad-active iframe {
        pointer-events: none !important;
    }

    .ad-wrapper {
        max-width: 580px;
        width: 95%;
        margin: 0 auto;
        position: relative;
        background: transparent;
        border: none;
        border-radius: 0;
        padding: 16px;
        box-shadow: none;
        text-align: center;
        animation: adSlideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 18px;
    }

    @keyframes adSlideIn {
        from {
            transform: scale(0.9);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .ad-countdown {
        position: absolute;
        top: 12px;
        right: 12px;
        background: rgba(0, 0, 0, 0.8);
        color: #dc2626;
        border: 2px solid #dc2626;
        border-radius: 50%;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: bold;
        z-index: 10000;
        font-family: 'Arial', sans-serif;
    }

    .ad-content {
        width: 100%;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
        gap: 24px;
    }
    
    /* Phone Mockup in Ad */
    .ad-phone-mockup-container {
        position: relative;
        max-width: 200px;
        width: 100%;
        margin: 0 auto;
    }

    .ad-phone-mockup {
        position: relative;
        width: 100%;
        max-width: 200px;
        background: #000000;
        border-radius: 28px;
        padding: 6px;
        box-shadow:
            0 15px 40px rgba(0, 0, 0, 0.9),
            0 0 0 2px rgba(220, 38, 38, 0.3),
            0 0 0 3px rgba(255, 255, 255, 0.1),
            inset 0 0 0 1px rgba(255, 255, 255, 0.05);
        margin: 0 auto;
        transition: transform 0.3s ease;
    }

    .ad-phone-mockup:hover {
        transform: scale(1.02);
    }

    .ad-phone-screen {
        background: #0f0f0f;
        border-radius: 24px;
        overflow: hidden;
        aspect-ratio: 9 / 19.5;
    }

    .ad-phone-header {
        background: #000000;
        padding: 12px 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ad-phone-header img {
        width: 24px;
        height: 24px;
        object-fit: contain;
        flex-shrink: 0;
    }

    .ad-phone-header span {
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .ad-phone-nav {
        background: #000000;
        padding: 8px 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        gap: 16px;
    }

    .ad-phone-nav button {
        padding: 6px 12px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #dc2626;
        border: none;
        background: transparent;
        border-bottom: 2px solid #dc2626;
        cursor: pointer;
    }

    .ad-phone-content {
        background: linear-gradient(180deg, #0f0f0f 0%, #000000 100%);
        padding: 12px;
        height: calc(100% - 100px);
        overflow-y: auto;
    }

    .ad-phone-hero-poster {
        position: relative;
        width: 100%;
        height: 140px;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 12px;
        cursor: pointer;
        transition: transform 0.2s ease;
        text-decoration: none;
        display: block;
    }

    .ad-phone-hero-poster:active {
        transform: scale(0.98);
    }

    .ad-phone-hero-poster img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .ad-phone-hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 60%);
    }

    .ad-phone-hero-info {
        position: absolute;
        bottom: 8px;
        left: 8px;
        right: 8px;
        color: white;
    }

    .ad-phone-hero-title {
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .ad-phone-content-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-bottom: 12px;
    }

    .ad-phone-content-item {
        position: relative;
        aspect-ratio: 2/3;
        border-radius: 6px;
        overflow: hidden;
        cursor: pointer;
        transition: transform 0.2s ease;
        text-decoration: none;
        display: block;
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    }

    .ad-phone-content-item:hover {
        transform: scale(1.05);
    }

    .ad-phone-content-item:active {
        transform: scale(0.95);
    }

    .ad-phone-content-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .ad-button {
        background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 3px 12px rgba(220, 38, 38, 0.5);
        min-width: 160px;
        justify-content: center;
        flex-shrink: 0;
    }

    .ad-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(220, 38, 38, 0.7);
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .ad-button:active {
        transform: translateY(-1px);
    }

    /* Responsive Styles for All Devices */
    
    /* Small Mobile (320px - 479px) */
    @media (max-width: 479px) {
        .ad-wrapper {
            padding: 12px 10px;
            width: 98%;
            gap: 14px;
        }

        .ad-content {
            flex-direction: column;
            gap: 16px;
        }

        .ad-button {
            padding: 12px 24px;
            font-size: 14px;
            min-width: 160px;
        }

        .ad-phone-mockup-container {
            max-width: 150px;
        }

        .ad-phone-mockup {
            max-width: 150px;
            border-radius: 24px;
            padding: 6px;
        }
    }

        .ad-phone-header {
            padding: 10px 12px;
        }

        .ad-phone-header img {
            width: 20px;
            height: 20px;
        }

        .ad-phone-header span {
            font-size: 0.75rem;
        }

        .ad-phone-nav {
            padding: 6px 12px;
            gap: 12px;
        }

        .ad-phone-nav button {
            padding: 4px 8px;
            font-size: 0.65rem;
        }

        .ad-phone-content {
            padding: 8px;
        }

        .ad-phone-hero-poster {
            height: 100px;
            margin-bottom: 8px;
        }

        .ad-phone-content-grid {
            gap: 6px;
        }
    }

    /* Mobile (480px - 767px) */
    @media (min-width: 480px) and (max-width: 767px) {
        .ad-wrapper {
            padding: 16px 14px;
            width: 95%;
            gap: 18px;
        }

        .ad-content {
            flex-direction: column;
            gap: 20px;
        }

        .ad-button {
            padding: 14px 28px;
            font-size: 15px;
            min-width: 170px;
        }

        .ad-phone-mockup-container {
            max-width: 180px;
        }

        .ad-phone-mockup {
            max-width: 180px;
            border-radius: 28px;
            padding: 7px;
        }
    }

    /* Tablet (768px - 1023px) */
    @media (min-width: 768px) and (max-width: 1023px) {
        .ad-wrapper {
            padding: 20px 18px;
            width: 90%;
            gap: 20px;
        }

        .ad-content {
            flex-direction: row;
            gap: 24px;
        }

        .ad-button {
            padding: 14px 30px;
            font-size: 16px;
            min-width: 180px;
        }

        .ad-phone-mockup-container {
            max-width: 200px;
        }

        .ad-phone-mockup {
            max-width: 200px;
            border-radius: 32px;
            padding: 8px;
        }
    }

    /* Desktop (1024px - 1279px) */
    @media (min-width: 1024px) and (max-width: 1279px) {
        .ad-wrapper {
            padding: 24px 20px;
            width: 85%;
            gap: 22px;
        }

        .ad-content {
            flex-direction: row;
            gap: 28px;
        }

        .ad-button {
            padding: 14px 32px;
            font-size: 16px;
            min-width: 180px;
        }

        .ad-phone-mockup-container {
            max-width: 220px;
        }

        .ad-phone-mockup {
            max-width: 220px;
            border-radius: 36px;
            padding: 9px;
        }
    }

    /* Large Desktop (1280px+) */
    @media (min-width: 1280px) {
        .ad-wrapper {
            padding: 28px 24px;
            width: 80%;
            gap: 24px;
        }

        .ad-content {
            flex-direction: row;
            gap: 32px;
        }

        .ad-button {
            padding: 14px 32px;
            font-size: 16px;
            min-width: 180px;
        }

        .ad-phone-mockup-container {
            max-width: 240px;
        }

        .ad-phone-mockup {
            max-width: 240px;
            border-radius: 40px;
            padding: 10px;
        }

        .ad-phone-header {
            padding: 14px 20px;
        }

        .ad-phone-header img {
            width: 28px;
            height: 28px;
        }

        .ad-phone-header span {
            font-size: 1rem;
        }

        .ad-phone-nav {
            padding: 10px 20px;
        }

        .ad-phone-nav button {
            padding: 8px 16px;
            font-size: 0.875rem;
        }

        .ad-phone-hero-poster {
            height: 180px;
            margin-bottom: 16px;
        }

        .ad-phone-content-grid {
            gap: 12px;
        }
    }

    /* Landscape Mobile */
    @media (max-height: 500px) and (orientation: landscape) {
        .ad-wrapper {
            padding: 10px 12px;
            gap: 12px;
        }

        .ad-content {
            flex-direction: row;
            gap: 16px;
        }

        .ad-phone-mockup-container {
            max-width: 140px;
        }

        .ad-phone-mockup {
            max-width: 140px;
            border-radius: 20px;
            padding: 5px;
        }

        .ad-button {
            padding: 10px 20px;
            font-size: 13px;
            min-width: 140px;
        }
    }

    /* Comments Section Styles */
    .comment-item {
        background-color: #111827;
        border-radius: 8px;
        padding: 16px;
        border-left: 3px solid transparent;
        transition: all 0.2s;
    }

    .comment-item:hover {
        background-color: #1f2937;
    }

    .comment-item.reply {
        margin-left: 48px;
        border-left-color: #dc2626;
    }

    .comment-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }

    .comment-author {
        font-weight: 600;
        color: white;
        font-size: 16px;
    }

    .comment-email {
        color: #9ca3af;
        font-size: 14px;
    }

    .comment-date {
        color: #6b7280;
        font-size: 12px;
    }

    .comment-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .comment-badge.admin {
        background-color: #dc2626;
        color: white;
    }

    .comment-content {
        color: #d1d5db;
        line-height: 1.6;
        margin: 12px 0;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    .comment-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }

    .comment-action-btn {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
    }

    .comment-reply-btn {
        background-color: transparent;
        color: #3b82f6;
        border: 1px solid #3b82f6;
    }

    .comment-reply-btn:hover {
        background-color: #3b82f6;
        color: white;
    }

    .comment-replies {
        margin-top: 16px;
        padding-left: 24px;
        border-left: 2px solid #374151;
    }

    .comment-reply-indicator {
        background-color: #1f2937;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 12px;
        border-left: 3px solid #dc2626;
    }

    .comment-reply-indicator p {
        color: #9ca3af;
        font-size: 12px;
        margin: 0 0 4px 0;
    }

    .comment-reply-indicator .reply-text {
        color: #d1d5db;
        font-size: 14px;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-black movie-detail-wrapper">
    <!-- Loading State -->
    <div id="loading-state" class="min-h-screen bg-black flex items-center justify-center" style="padding-top: 64px;">
        <div class="spinner"></div>
    </div>

    <!-- Error State -->
    <div id="error-state" class="min-h-screen bg-black flex items-center justify-center" style="display: none; padding-top: 64px;">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-white mb-4">Movie not found</h2>
            <a href="{{ route('movies.index') }}" class="text-red-500 hover:text-red-400">Back to Movies</a>
        </div>
    </div>

    <!-- Content -->
    <div id="movie-content" style="display: none;">
        <!-- Hero Section -->
        <div id="hero-section" class="movie-hero">
            <!-- Content will be dynamically loaded -->
        </div>

        <!-- Tabs Content -->
        <div class="container mx-auto px-4 md:px-8 lg:px-16 py-12">
            <!-- Watch Tab -->
            <div id="watch-tab" class="watch-section">
                <div id="watch-content">
                    <!-- Will be loaded dynamically -->
                </div>
            </div>

            <!-- Download Tab -->
            <div id="download-tab" class="download-section" style="display: none;">
                <div id="download-content">
                    <!-- Will be loaded dynamically -->
                </div>
            </div>

            <!-- Details Tab -->
            <div id="details-tab" class="details-section" style="display: none;">
                <div id="details-content">
                    <!-- Will be loaded dynamically -->
                </div>
            </div>
        </div>

        <!-- Similar Movies -->
        <div id="similar-movies-section" class="container mx-auto px-4 md:px-8 lg:px-16 pb-16" style="display: none;">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">More Like This</h2>
                    <p class="text-gray-400 text-sm">Movies you might enjoy</p>
                </div>
            </div>
            <div id="similar-movies-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6"></div>
        </div>

        <!-- Comments Section -->
        <div id="comments-section" class="container mx-auto px-4 md:px-8 lg:px-16 py-12">
            <h2 class="text-2xl font-bold text-white mb-6">Comments</h2>
            
            <!-- Comment Form -->
            <div class="bg-gray-900 rounded-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-white mb-4">Leave a Comment</h3>
                <form id="comment-form" onsubmit="submitComment(event)" class="space-y-4">
                    <input type="hidden" id="comment-parent-id" name="parent_id" value="">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Name *</label>
                            <input type="text" id="comment-name" name="name" required class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 focus:border-red-600 focus:outline-none" placeholder="Your name">
                        </div>
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Email *</label>
                            <input type="email" id="comment-email" name="email" required class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 focus:border-red-600 focus:outline-none" placeholder="your@email.com">
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-300 text-sm font-medium mb-2">Comment *</label>
                        <textarea id="comment-text" name="comment" required rows="4" class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 focus:border-red-600 focus:outline-none" placeholder="Write your comment here..."></textarea>
                    </div>
                    <div class="flex items-center justify-between">
                        <p id="comment-replying-to" class="text-sm text-gray-400" style="display: none;"></p>
                        <div class="flex gap-3">
                            <button type="button" id="cancel-reply-btn" onclick="cancelReply()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded font-medium transition-colors" style="display: none;">Cancel</button>
                            <button type="submit" id="submit-comment-btn" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded font-medium transition-colors flex items-center gap-2">
                                <span id="submit-comment-text">Post Comment</span>
                                <div id="submit-comment-spinner" class="spinner" style="display: none; width: 16px; height: 16px; border-width: 2px;"></div>
                            </button>
                        </div>
                    </div>
                    <div id="comment-success-message" class="mt-4 p-4 bg-green-600/20 border border-green-600/50 rounded-lg" style="display: none;">
                        <div class="flex items-center gap-2 text-green-400">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm font-medium">Your comment has been submitted and is under review. It will be visible once approved by an administrator.</p>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Comments List -->
            <div id="comments-list" class="space-y-6">
                <!-- Comments will be loaded here -->
            </div>

            <!-- Loading State -->
            <div id="comments-loading" class="text-center py-8">
                <div class="spinner"></div>
                <p class="text-gray-400 mt-4">Loading comments...</p>
            </div>

            <!-- Empty State -->
            <div id="comments-empty" class="text-center py-12" style="display: none;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-16 w-16 text-gray-600 mx-auto mb-4">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <p class="text-gray-400 text-lg">No comments yet. Be the first to comment!</p>
            </div>
        </div>
    </div>

    <!-- Ad Container - Full Page Overlay -->
    <div id="ad-container" class="ad-container">
        <div class="ad-wrapper">
            <div id="ad-countdown" class="ad-countdown">20</div>
            <div class="ad-content" id="ad-content">
                <!-- Ad content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <!-- Report Embed Modal -->
    <div id="report-embed-modal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-gray-900 rounded-lg max-w-md w-full p-6 relative">
            <button onclick="closeReportEmbedModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <h3 class="text-xl font-bold text-white mb-4">Report Embed Problem</h3>
            <form id="report-embed-form" onsubmit="submitEmbedReport(event)">
                <input type="hidden" id="report-content-id" name="content_id">
                <input type="hidden" id="report-embed-id" name="embed_id">
                <input type="hidden" id="report-content-type" name="content_type" value="movie">
                
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-medium mb-2">Email *</label>
                    <input type="email" id="report-email" name="email" class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 focus:border-red-600 focus:outline-none" placeholder="your@email.com" required>
                    <p class="text-gray-500 text-xs mt-1">We'll notify you when your report is processed.</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-medium mb-2">Problem Type</label>
                    <select id="report-type" name="report_type" class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 focus:border-red-600 focus:outline-none" required>
                        <option value="">Select a problem type</option>
                        <option value="not_working">Not Working</option>
                        <option value="wrong_content">Wrong Content</option>
                        <option value="poor_quality">Poor Quality</option>
                        <option value="broken_link">Broken Link</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-medium mb-2">Description (Optional)</label>
                    <textarea id="report-description" name="description" rows="3" class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 focus:border-red-600 focus:outline-none" placeholder="Please describe the problem..."></textarea>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="closeReportEmbedModal()" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-medium transition-colors">
                        Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Request Content Modal -->
    <div id="request-content-modal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-gray-900 rounded-lg max-w-md w-full p-6 relative">
            <button onclick="closeRequestContentModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <h3 class="text-xl font-bold text-white mb-4">Request Content</h3>
            <form id="request-content-form" onsubmit="submitContentRequest(event)">
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-medium mb-2">Content Type</label>
                    <select id="request-type" name="type" class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 focus:border-red-600 focus:outline-none" required>
                        <option value="movie">Movie</option>
                        <option value="tvshow">TV Show</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-medium mb-2">Title *</label>
                    <input type="text" id="request-title" name="title" class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 focus:border-red-600 focus:outline-none" placeholder="Enter movie or TV show title" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-medium mb-2">Email *</label>
                    <input type="email" id="request-email" name="email" class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 focus:border-red-600 focus:outline-none" placeholder="your@email.com" required>
                    <p class="text-gray-500 text-xs mt-1">We'll notify you when your request is processed.</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-medium mb-2">Description (Optional)</label>
                    <textarea id="request-description" name="description" rows="3" class="w-full bg-gray-800 text-white border border-gray-700 rounded px-4 py-2 focus:border-red-600 focus:outline-none" placeholder="Additional details about your request..."></textarea>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="closeRequestContentModal()" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-medium transition-colors">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // State management - Matching Frontend MovieDetailPage.jsx
    let movie = null;
    let embeds = [];
    let downloads = [];
    let similarMovies = [];
    let loading = false;
    let activeEmbed = null;
    let activeTab = 'watch';
    let isFullscreen = false;
    let playerRef = null;
    let adTimer = null;
    let adShown = false;
    let downloadClicked = false;
    let countdownTimer = null;
    let countdownSeconds = 20;

    const movieId = {{ $id }};

    // Track view - Matching Frontend
    async function trackView() {
        try {
            await fetch(`${API_BASE_URL}/leaderboard/movies/${movieId}/view`, {
                method: 'POST'
            });
        } catch (error) {
            // Error tracking view
        }
    }

    // Handle watch now - Matching Frontend
    function handleWatchNow() {
        activeTab = 'watch';
        
        // Ensure we have an active embed selected
        if (embeds.length > 0 && !activeEmbed) {
            activeEmbed = embeds[0];
        }
        
        renderTabs();

        setTimeout(() => {
            const videoSection = document.getElementById('video-player-section');
            if (videoSection) {
                videoSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 100);
    }

    // Toggle fullscreen - Matching Frontend
    function toggleFullscreen() {
        const player = playerRef;
        if (!player) return;

        if (!document.fullscreenElement) {
            player.requestFullscreen().then(() => {
                isFullscreen = true;
                updateFullscreenButton();
            }).catch((err) => {
            });
        } else {
            document.exitFullscreen().then(() => {
                isFullscreen = false;
                updateFullscreenButton();
            });
        }
    }

    function updateFullscreenButton() {
        const btn = document.querySelector('.fullscreen-btn');
        if (btn) {
            const icon = btn.querySelector('svg');
            if (icon) {
                if (isFullscreen) {
                    // Minimize icon
                    icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>`;
                } else {
                    // Maximize icon
                    icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 3H5a2 2 0 00-2 2v3m18 0V5a2 2 0 00-2-2h-3m0 18h3a2 2 0 002-2v-3M3 16v3a2 2 0 002 2h3"></path>`;
                }
            }
        }
    }

    // Fetch movie details - Matching Frontend
    async function fetchMovieDetails() {
        loading = true;
        document.getElementById('loading-state').style.display = 'flex';
        document.getElementById('movie-content').style.display = 'none';

        try {
            // Fetch movie details
            const movieResponse = await apiFetch(`${API_BASE_URL}/movies/${movieId}`);
            const movieResult = await movieResponse.json();
            movie = movieResult.data || movieResult;
            
            // Update SEO tags with movie data
            updateSEOTags();

            // Fetch embeds
            const embedsResponse = await apiFetch(`${API_BASE_URL}/embeds/movies/${movieId}`);
            if (embedsResponse.ok) {
                const embedsResult = await embedsResponse.json();
                embeds = embedsResult.data || embedsResult || [];
                if (embeds.length > 0) {
                    activeEmbed = embeds[0];
                }
            } else {
                embeds = [];
            }

            // Fetch downloads
            const downloadsResponse = await apiFetch(`${API_BASE_URL}/downloads/movies/${movieId}`);
            if (downloadsResponse.ok) {
                const downloadsResult = await downloadsResponse.json();
                downloads = downloadsResult.data || downloadsResult || [];
            } else {
                downloads = [];
            }

            // Fetch similar movies (same genre)
            if (movie.genres && movie.genres.length > 0) {
                const genre = movie.genres[0].name;
                const similarResponse = await apiFetch(`${API_BASE_URL}/movies?genre=${encodeURIComponent(genre)}&limit=10`);
                const similarResult = await similarResponse.json();
                const allSimilar = similarResult.data?.movies || similarResult.data || [];
                similarMovies = allSimilar.filter(m => m.id !== parseInt(movieId)).slice(0, 10);
            }

            renderMovie();
            trackView();
            // Load comments after movie is loaded
            setTimeout(() => {
                loadComments();
            }, 500);
        } catch (error) {
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('error-state').style.display = 'flex';
        } finally {
            loading = false;
        }
    }

    // Render movie - Matching Frontend
    function renderMovie() {
        const backdropUrl = movie.backdrop_path
            ? `https://image.tmdb.org/t/p/original${movie.backdrop_path}`
            : movie.poster_path
            ? `https://image.tmdb.org/t/p/original${movie.poster_path}`
            : '/images/placeholder.svg';

        const heroSection = document.getElementById('hero-section');
        const year = movie.release_date ? new Date(movie.release_date).getFullYear() : null;
        const rating = movie.vote_average ? parseFloat(movie.vote_average).toFixed(1) : null;
        const runtime = movie.runtime ? `${Math.floor(movie.runtime / 60)}h ${movie.runtime % 60}m` : null;

        heroSection.innerHTML = `
            <!-- Backdrop Image -->
            <div class="movie-hero-backdrop">
                <img src="${backdropUrl}" alt="${movie.title || 'Movie'}" onerror="this.src='/images/placeholder.svg'">
                <div class="movie-hero-gradient-1"></div>
                <div class="movie-hero-gradient-2"></div>
            </div>

            <!-- Content -->
            <div class="relative container mx-auto px-4 md:px-8 lg:px-16 h-full flex flex-col justify-end pb-12">
                <!-- Back Button -->
                <button
                    onclick="window.history.back()"
                    class="absolute top-8 left-4 md:left-8 flex items-center space-x-2 text-white hover:text-gray-300 transition-colors"
                >
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-6 w-6">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Back</span>
                </button>

                <!-- Title -->
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-4">
                    ${movie.title || 'Untitled'}
                </h1>

                <!-- Metadata -->
                <div class="flex flex-wrap items-center gap-4 text-white mb-6">
                    ${rating ? `
                    <div class="flex items-center space-x-2 bg-yellow-600/20 px-3 py-1 rounded">
                        <svg fill="currentColor" viewBox="0 0 20 20" class="h-5 w-5 text-yellow-400">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <span class="font-semibold text-yellow-400">${rating}</span>
                    </div>
                    ` : ''}
                    ${year ? `
                    <div class="flex items-center space-x-2">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>${year}</span>
                    </div>
                    ` : ''}
                    ${runtime ? `
                    <div class="flex items-center space-x-2">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>${runtime}</span>
                    </div>
                    ` : ''}
                    <span class="px-3 py-1 border border-gray-400 rounded text-sm">
                        ${movie.status === 'active' ? 'Available' : 'Coming Soon'}
                    </span>
                </div>

                <!-- Genres -->
                ${movie.genres && movie.genres.length > 0 ? `
                <div class="flex flex-wrap gap-2 mb-6">
                    ${movie.genres.map(genre => `
                        <span class="px-3 py-1 bg-gray-800/80 text-white rounded-full text-sm">
                            ${genre.name || genre}
                        </span>
                    `).join('')}
                </div>
                ` : ''}

                <!-- Overview -->
                <p class="text-gray-300 text-lg max-w-3xl mb-8 line-clamp-3">
                    ${movie.overview || 'No description available.'}
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-4">
                    <button
                        onclick="handleWatchNow()"
                        id="watch-now-btn"
                        class="flex items-center space-x-2 bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded font-semibold transition-colors"
                    >
                        <svg fill="currentColor" viewBox="0 0 24 24" class="h-6 w-6">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        <span>Watch Now</span>
                    </button>
                    ${downloads.length > 0 ? `
                    <button
                        onclick="setActiveTab('download')"
                        class="flex items-center space-x-2 bg-gray-800 hover:bg-gray-700 text-white px-8 py-3 rounded font-semibold transition-colors"
                    >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <span>Download</span>
                    </button>
                    ` : ''}
                    <button
                        onclick="setActiveTab('details')"
                        id="more-info-btn"
                        class="flex items-center space-x-2 bg-gray-800 hover:bg-gray-700 text-white px-8 py-3 rounded font-semibold transition-colors"
                    >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>More Info</span>
                    </button>
                    <button
                        onclick="openRequestContentModal()"
                        class="flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded font-semibold transition-colors"
                        title="Request this movie or another content"
                    >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Request</span>
                    </button>
                </div>
            </div>
        `;

        renderTabs();
        renderDownload();
        renderDetails();
        renderSimilarMovies();

        document.getElementById('loading-state').style.display = 'none';
        document.getElementById('movie-content').style.display = 'block';
        
        // Ensure watch tab is visible
        const watchTab = document.getElementById('watch-tab');
        if (watchTab && activeTab === 'watch') {
            watchTab.style.display = 'block';
        }
    }

    // Set active tab
    function setActiveTab(tab) {
        activeTab = tab;
        renderTabs();
    }

    // Render tabs
    function renderTabs() {
        const watchTab = document.getElementById('watch-tab');
        const downloadTab = document.getElementById('download-tab');
        const detailsTab = document.getElementById('details-tab');
        const moreInfoBtn = document.getElementById('more-info-btn');
        const watchNowBtn = document.getElementById('watch-now-btn');

        if (activeTab === 'watch') {
            if (watchTab) {
                watchTab.style.display = 'block';
            }
            if (downloadTab) downloadTab.style.display = 'none';
            if (detailsTab) detailsTab.style.display = 'none';
            if (watchNowBtn) {
                watchNowBtn.className = 'flex items-center space-x-2 bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded font-semibold transition-colors';
            }
            if (moreInfoBtn) {
                moreInfoBtn.className = 'flex items-center space-x-2 bg-gray-800 hover:bg-gray-700 text-white px-8 py-3 rounded font-semibold transition-colors';
            }
            renderWatch();
        } else if (activeTab === 'download') {
            if (watchTab) watchTab.style.display = 'none';
            if (downloadTab) downloadTab.style.display = 'block';
            if (detailsTab) detailsTab.style.display = 'none';
            renderDownload();
        } else {
            if (watchTab) watchTab.style.display = 'none';
            if (downloadTab) downloadTab.style.display = 'none';
            if (detailsTab) detailsTab.style.display = 'block';
            if (watchNowBtn) {
                watchNowBtn.className = 'flex items-center space-x-2 bg-gray-800 hover:bg-gray-700 text-white px-8 py-3 rounded font-semibold transition-colors';
            }
            if (moreInfoBtn) {
                moreInfoBtn.className = 'flex items-center space-x-2 bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded font-semibold transition-colors';
            }
            renderDetails();
        }
    }

    // Render watch tab - Matching Frontend
    function renderWatch() {
        const watchContent = document.getElementById('watch-content');
        if (!watchContent) {
            return;
        }
        
        
        watchContent.innerHTML = `
            <div id="video-player-section">
                <h2 class="text-2xl font-bold text-white mb-6">Watch ${movie.title || 'Movie'}</h2>
                
                <!-- Mobile Rotation Notice -->
                <div class="bg-blue-600/20 border border-blue-600/50 rounded-lg p-3 mb-6 block md:hidden">
                    <div class="flex items-center space-x-2 text-blue-300">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5 flex-shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <p class="text-sm font-medium">For better experience, rotate your mobile device to landscape mode</p>
                    </div>
                </div>
                
                ${embeds.length > 0 ? `
                <div class="space-y-6">
                    <!-- Server Selection -->
                    <div class="flex flex-wrap gap-2 items-center">
                        ${embeds.map((embed, index) => {
                            const isActive = activeEmbed?.id === embed.id;
                            return `
                            <button
                                onclick="setActiveEmbedById(${embed.id})"
                                class="server-btn ${isActive ? 'server-btn-active' : 'server-btn-inactive'}"
                            >
                                Server ${index + 1}
                            </button>
                            `;
                        }).join('')}
                        ${activeEmbed ? `
                        <button
                            onclick="openReportEmbedModal(${movieId}, ${activeEmbed.id}, 'movie')"
                            class="flex items-center space-x-2 px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded text-sm font-medium transition-colors"
                            title="Report problem with this embed"
                        >
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span>Report Problem</span>
                        </button>
                        ` : ''}
                    </div>

                    <!-- Video Player -->
                    ${activeEmbed ? (() => {
                        const embedData = processEmbedUrl(activeEmbed);
                        const embedUrl = embedData.url || '';
                        const hasCustomStyling = embedData.hasCustomStyling || false;
                        const divStyle = embedData.divStyle || '';
                        const iframeStyle = embedData.iframeStyle || '';
                        const iframeAttributes = embedData.iframeAttributes || {};
                        
                        // Check if this is a vidsrc.icu embed and enable navigation blocking immediately
                        const isVidsrcEmbed = embedUrl.includes('vidsrc.icu') || embedUrl.includes('vidsrc.io');
                        if (isVidsrcEmbed) {
                            // Use setTimeout to ensure function is available
                            setTimeout(() => {
                                if (typeof enableNavigationBlock === 'function') {
                                    enableNavigationBlock();
                                }
                            }, 100);
                        }
                        
                        
                        if (!embedUrl || embedUrl.trim() === '') {
                            return `
                            <div class="bg-gray-900 border border-gray-800 rounded-lg p-8 text-center">
                                <p class="text-gray-400">Invalid embed URL. Please try another server.</p>
                            </div>
                            `;
                        }
                        
                        // Check if it's a service that typically blocks iframes (like pkembed)
                        // Exclude Mixdrop, Dailymotion, and Bilibili from this check as they support iframes
                        const lowerEmbedUrl = embedUrl.toLowerCase();
                        const mightBlockIframe = (lowerEmbedUrl.includes('pkembed.online') || 
                                                  (lowerEmbedUrl.includes('embed') && lowerEmbedUrl.includes('online'))) &&
                                                  !lowerEmbedUrl.includes('mixdrop') && 
                                                  !lowerEmbedUrl.includes('dailymotion') &&
                                                  !lowerEmbedUrl.includes('bilibili');
                        
                        // Generate unique iframe ID
                        const iframeId = 'video-iframe-' + Date.now();
                        
                        // For services that might block iframes, show direct link option immediately
                        const showDirectLink = mightBlockIframe;
                        
                        // Build iframe attributes
                        let iframeAttrs = '';
                        if (hasCustomStyling && iframeStyle) {
                            iframeAttrs += ` style="${iframeStyle.replace(/"/g, '&quot;')}"`;
                        } else {
                            iframeAttrs += ` class="absolute top-0 left-0 w-full h-full"`;
                        }
                        
                        if (iframeAttributes.scrolling) {
                            iframeAttrs += ` scrolling="${iframeAttributes.scrolling}"`;
                        }
                        
                        if (iframeAttributes.allowfullscreen || iframeAttributes.allowfullscreen === undefined) {
                            iframeAttrs += ` allowfullscreen`;
                        }
                        
                        if (iframeAttributes.allow) {
                            iframeAttrs += ` allow="${iframeAttributes.allow.replace(/"/g, '&quot;')}"`;
                        } else {
                            iframeAttrs += ` allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"`;
                        }
                        
                        // If custom styling, wrap in div
                        let finalDivStyle = divStyle;
                        if (hasCustomStyling && divStyle) {
                            // Always set width to 100% for custom styled embeds
                            const widthMatch = divStyle.match(/width\s*:\s*(\d+(?:\.\d+)?)%/);
                            if (widthMatch) {
                                // Replace any width with 100%
                                finalDivStyle = divStyle.replace(/width\s*:\s*(\d+(?:\.\d+)?)%/, 'width:100%');
                            } else {
                                // If no width specified, add it as 100%
                                finalDivStyle = divStyle + (divStyle.trim().endsWith(';') ? '' : ';') + ' width:100%';
                            }
                        }
                        const wrapperDiv = hasCustomStyling && finalDivStyle ? 
                            `<div style="${finalDivStyle.replace(/"/g, '&quot;')}">` : '';
                        const wrapperDivClose = hasCustomStyling && finalDivStyle ? `</div>` : '';
                        
                        return `
                    <div class="video-player-container group" id="video-player-container">
                        ${showDirectLink ? `
                        <!-- Direct Link Banner (for services that block iframes) -->
                        <div class="bg-yellow-600/20 border border-yellow-600/50 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5 text-yellow-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-yellow-300 font-medium">This embed may not work in iframe</p>
                                        <p class="text-yellow-400/80 text-sm">Click below to watch in a new tab</p>
                                    </div>
                                </div>
                                <a href="${embedUrl}" target="_blank" rel="noopener noreferrer" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded font-semibold transition-colors flex items-center space-x-2">
                                    <svg fill="currentColor" viewBox="0 0 24 24" class="h-4 w-4">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                    <span>Watch Now</span>
                                </a>
                            </div>
                        </div>
                        ` : ''}
                        
                        <div class="video-player-wrapper ${hasCustomStyling ? 'custom-styling' : ''}">
                            ${wrapperDiv}
                            <iframe
                                id="${iframeId}"
                                src="${String(embedUrl).replace(/"/g, '&quot;')}"
                                ${iframeAttrs}
                                frameborder="0"
                                referrerpolicy="no-referrer-when-downgrade"
                                ${lowerEmbedUrl.includes('bilibili') ? 'data-platform="web"' : ''}
                                ${mightBlockIframe ? 'loading="eager"' : 'loading="lazy"'}
                                onerror=""
                                onload=""
                            ></iframe>
                            ${wrapperDivClose}
                            
                            <!-- Fallback: Open in new tab button (shown if iframe fails) -->
                            <div id="iframe-fallback-${iframeId}" class="absolute inset-0 bg-black/95 flex items-center justify-center" style="display: ${showDirectLink ? 'flex' : 'none'};">
                                <div class="text-center p-8 max-w-md">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-20 w-20 text-red-500 mx-auto mb-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="text-white text-xl font-semibold mb-2">Video cannot be embedded</p>
                                    <p class="text-gray-400 mb-6">This embed service blocks iframe embedding. Click below to watch the video in a new tab.</p>
                                    <a href="${embedUrl}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center space-x-3 bg-red-600 hover:bg-red-700 text-white px-8 py-4 rounded-lg font-bold text-lg transition-colors shadow-lg">
                                        <svg fill="currentColor" viewBox="0 0 24 24" class="h-6 w-6">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                        <span>Open Video in New Tab</span>
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Fullscreen Button -->
                            <button
                                onclick="toggleFullscreen()"
                                class="fullscreen-btn"
                                title="${isFullscreen ? 'Exit Fullscreen' : 'Enter Fullscreen'}"
                            >
                                ${isFullscreen ? `
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                ` : `
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 3H5a2 2 0 00-2 2v3m18 0V5a2 2 0 00-2-2h-3m0 18h3a2 2 0 002-2v-3M3 16v3a2 2 0 002 2h3"></path>
                                </svg>
                                `}
                            </button>

                            <!-- Mobile Fullscreen Hint -->
                            <div class="absolute bottom-4 left-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300" style="display: none;">
                                <div class="bg-black/70 text-white text-xs px-3 py-2 rounded-lg flex items-center space-x-2">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 3H5a2 2 0 00-2 2v3m18 0V5a2 2 0 00-2-2h-3m0 18h3a2 2 0 002-2v-3M3 16v3a2 2 0 002 2h3"></path>
                                    </svg>
                                    <span>Tap for fullscreen</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    `;
                    })() : ''}
                </div>
                ` : `
                <div class="bg-gray-900 border border-gray-800 rounded-lg p-8 text-center">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-12 w-12 text-gray-500 mx-auto mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-gray-400 text-lg mb-2">No streaming links available</p>
                    <p class="text-gray-500 text-sm">Check back later or try downloading the movie instead.</p>
                </div>
                `}
            </div>
        `;

        // Set player ref after render
        playerRef = document.getElementById('video-player-container');
        
        // Check if iframe loaded properly (for services that might block iframes)
        setTimeout(() => {
            const iframes = document.querySelectorAll('iframe[id^="video-iframe-"]');
            iframes.forEach(iframe => {
                const iframeId = iframe.id;
                const fallback = document.getElementById('iframe-fallback-' + iframeId);
                const embedUrl = iframe.src;
                const mightBlock = embedUrl.includes('pkembed.online') || 
                                  (embedUrl.includes('embed') && embedUrl.includes('online'));
                
                if (fallback) {
                    // For services that might block iframes, show fallback faster
                    const checkDelay = mightBlock ? 2000 : 5000;
                    const maxChecks = mightBlock ? 3 : 10;
                    
                    // Check if iframe has loaded content
                    let checkCount = 0;
                    
                    const checkIframe = setInterval(() => {
                        checkCount++;
                        
                        try {
                            // Try to check if iframe has content (may fail due to CORS)
                            const hasContent = iframe.contentWindow && iframe.contentWindow.document && 
                                              iframe.contentWindow.document.body && 
                                              iframe.contentWindow.document.body.innerHTML.trim() !== '';
                            
                            if (hasContent) {
                                clearInterval(checkIframe);
                                // Hide fallback if content is loaded
                                if (fallback.style.display === 'flex') {
                                    fallback.style.display = 'none';
                                }
                                return;
                            }
                        } catch(e) {
                            // Cross-origin - can't check directly, which is normal
                            // For services that might block, show fallback after timeout
                            if (mightBlock && checkCount >= maxChecks) {
                                clearInterval(checkIframe);
                                fallback.style.display = 'flex';
                                return;
                            }
                            
                            // Assume it's loading if no error and iframe has dimensions
                            if (iframe.offsetWidth > 0 && iframe.offsetHeight > 0) {
                                // Iframe has dimensions, might be loading
                                // Don't show fallback yet
                                if (!mightBlock && checkCount >= maxChecks) {
                                    clearInterval(checkIframe);
                                }
                                return;
                            }
                        }
                        
                        // After multiple checks with no content, show fallback
                        if (checkCount >= maxChecks) {
                            clearInterval(checkIframe);
                            // Show fallback if iframe appears empty or if it's a known blocking service
                            if (mightBlock || iframe.offsetHeight < 100 || iframe.offsetWidth < 100) {
                                fallback.style.display = 'flex';
                            }
                        }
                    }, 1000);
                }
            });
        }, 2000);
    }

    // OneDrive Helper Functions - Matching Frontend OneDriveEmbed.jsx
    function isValidOneDriveUrl(url) {
        try {
            const urlObj = new URL(url);
            return urlObj.hostname.includes('1drv.ms') || 
                   urlObj.hostname.includes('onedrive.live.com');
        } catch (error) {
            return false;
        }
    }

    function convertOneDriveToEmbed(sharingUrl) {
        try {
            // If already an embed URL, return as is
            if (sharingUrl.includes('onedrive.live.com/embed')) {
                return sharingUrl;
            }
            
            const url = new URL(sharingUrl);
            
            // Handle 1drv.ms short URLs
            if (url.hostname.includes('1drv.ms')) {
                // Pattern: https://1drv.ms/v/c/{cid}/{fileId}
                // Example: https://1drv.ms/v/c/5f4978ae064cd140/IQQ6TKe_jdWiQ7d57gWMDMM7AVbQgLi9_Gx2Zkb8xeb03Tc
                const pathParts = url.pathname.split('/').filter(part => part);
                
                // Extract cid and fileId from path
                let cid = null;
                let fileId = null;
                
                // Look for 'c' in the path
                const cIndex = pathParts.indexOf('c');
                if (cIndex !== -1 && pathParts.length > cIndex + 2) {
                    cid = pathParts[cIndex + 1];
                    fileId = pathParts[cIndex + 2];
                } else if (pathParts.length >= 4 && pathParts[1] === 'c') {
                    // Alternative pattern: /v/c/{cid}/{fileId}
                    cid = pathParts[2];
                    fileId = pathParts[3];
                } else {
                    // Try regex pattern match
                    const pathMatch = url.pathname.match(/\/v\/c\/([^\/]+)\/([^\/\?]+)/);
                    if (pathMatch && pathMatch[1] && pathMatch[2]) {
                        cid = pathMatch[1];
                        fileId = pathMatch[2];
                    }
                }
                
                if (cid && fileId) {
                    // For 1drv.ms URLs, the redeem parameter must be base64 encoded original URL
                    // OneDrive uses redeem to resolve the proper file ID and constructs id/resid automatically
                    // Base64 encode the original sharing URL directly
                    const redeemBase64 = btoa(sharingUrl);
                    
                    const cidUpper = cid.toUpperCase();
                    // Construct tentative id/resid - OneDrive will resolve the actual IDs from redeem
                    // Format: {UPPERCASE_CID}!{fileId}
                    const tentativeId = `${cidUpper}!${fileId}`;
                    
                    // Construct embed URL matching the working format:
                    // cid (lowercase), id (uppercase CID!fileId), resid (same), 
                    // ithint=video,mp4, embed=1, migratedtospo=true, redeem (base64 URL)
                    const embedUrl = `https://onedrive.live.com/embed?cid=${encodeURIComponent(cid)}&id=${encodeURIComponent(tentativeId)}&resid=${encodeURIComponent(tentativeId)}&ithint=video,mp4&embed=1&migratedtospo=true&redeem=${redeemBase64}`;
                    return embedUrl;
                }
            }
            
            // Handle onedrive.live.com URLs that aren't embed format
            if (url.hostname.includes('onedrive.live.com')) {
                // Try to extract parameters from query string or path
                const params = new URLSearchParams(url.search);
                const cid = params.get('cid') || url.pathname.match(/cid=([^&\/]+)/)?.[1];
                const resid = params.get('resid') || url.pathname.match(/resid=([^&\/]+)/)?.[1];
                
                if (cid) {
                    const embedUrl = `https://onedrive.live.com/embed?cid=${cid}${resid ? `&resid=${resid}` : ''}&ithint=video,mp4&embed=1`;
                    return embedUrl;
                }
            }
            
            return sharingUrl;
        } catch (error) {
            return sharingUrl;
        }
    }

    function processEmbedUrl(embed) {
        if (!embed) {
            return { url: '', hasCustomStyling: false };
        }
        
        // Try multiple field names to find the embed URL
        let embedUrl = embed.embed_url || embed.iframe_url || embed.url || embed.embedUrl || '';
        
        // Ensure we have a valid URL
        if (!embedUrl || embedUrl.trim() === '') {
            return { url: '', hasCustomStyling: false };
        }
        
        // Clean and validate URL
        embedUrl = embedUrl.trim();
        
        // Check if embedUrl contains full HTML structure with div wrapper (custom styling)
        // Normalize whitespace first to handle multi-line HTML
        const normalizedEmbed = embedUrl.replace(/\s+/g, ' ').trim();
        let hasDivWrapper = /<div[^>]*>/i.test(normalizedEmbed);
        let divStyle = '';
        let iframeStyle = '';
        let iframeAttributes = {};
        
        if (hasDivWrapper) {
            // Extract div style attribute (handle both normalized and original)
            const divStyleMatch = embedUrl.match(/<div[^>]*style\s*=\s*"([^"]+)"/is) || 
                                   embedUrl.match(/<div[^>]*style\s*=\s*'([^']+)'/is) ||
                                   normalizedEmbed.match(/<div[^>]*style\s*=\s*"([^"]+)"/i) || 
                                   normalizedEmbed.match(/<div[^>]*style\s*=\s*'([^']+)'/i);
            if (divStyleMatch && divStyleMatch[1]) {
                divStyle = divStyleMatch[1].trim();
            }
            
            // Extract iframe and its attributes (handle multi-line with 's' flag)
            const iframeMatch = embedUrl.match(/<iframe([\s\S]*?)>/i);
            if (iframeMatch && iframeMatch[1]) {
                const iframeAttrs = iframeMatch[1];
                
                // Extract iframe src (handle multi-line attributes)
                let srcMatch = iframeAttrs.match(/src\s*=\s*"([^"]+)"/is);
                if (!srcMatch) {
                    srcMatch = iframeAttrs.match(/src\s*=\s*'([^']+)'/is);
                }
                if (!srcMatch) {
                    srcMatch = iframeAttrs.match(/src\s*=\s*([^\s>]+)/is);
                }
                
                if (srcMatch && srcMatch[1]) {
                    embedUrl = srcMatch[1].trim();
                    try {
                        embedUrl = decodeURIComponent(embedUrl);
                    } catch(e) {
                        // If decoding fails, use original
                    }
                }
                
                // Extract iframe style (handle multi-line)
                const iframeStyleMatch = iframeAttrs.match(/style\s*=\s*"([^"]+)"/is) || 
                                        iframeAttrs.match(/style\s*=\s*'([^']+)'/is);
                if (iframeStyleMatch && iframeStyleMatch[1]) {
                    iframeStyle = iframeStyleMatch[1].trim();
                }
                
                // Extract other iframe attributes (handle multi-line)
                if (/scrolling/i.test(iframeAttrs)) {
                    const scrollingMatch = iframeAttrs.match(/scrolling\s*=\s*"([^"]+)"/is) ||
                                         iframeAttrs.match(/scrolling\s*=\s*'([^']+)'/is) ||
                                         iframeAttrs.match(/scrolling\s*=\s*([^\s>]+)/is);
                    if (scrollingMatch) {
                        iframeAttributes.scrolling = (scrollingMatch[1] || scrollingMatch[2] || scrollingMatch[3] || 'auto').trim();
                    }
                }
                
                if (/allowfullscreen/i.test(iframeAttrs) || /allowFullscreen/i.test(iframeAttrs)) {
                    iframeAttributes.allowfullscreen = true;
                }
                
                // Extract allow attribute (handle multi-line)
                const allowMatch = iframeAttrs.match(/allow\s*=\s*"([^"]+)"/is) ||
                                  iframeAttrs.match(/allow\s*=\s*'([^']+)'/is);
                if (allowMatch && allowMatch[1]) {
                    iframeAttributes.allow = allowMatch[1].trim();
                }
            }
        }
        // Check if embedUrl contains HTML iframe tags - extract the src attribute
        else if (/<iframe/i.test(embedUrl) || /<IFRAME/i.test(embedUrl)) {
            // Try to extract src from iframe tag using regex (case-insensitive)
            let srcMatch = embedUrl.match(/src\s*=\s*"([^"]+)"/i);
            if (!srcMatch) {
                srcMatch = embedUrl.match(/src\s*=\s*'([^']+)'/i);
            }
            if (!srcMatch) {
                srcMatch = embedUrl.match(/src\s*=\s*([^\s>]+)/i);
            }
            
            if (srcMatch && srcMatch[1]) {
                embedUrl = srcMatch[1].trim();
                try {
                    embedUrl = decodeURIComponent(embedUrl);
                } catch(e) {
                    // If decoding fails, use original
                }
            } else {
                return { url: '', hasCustomStyling: false };
            }
        }
        
        // Handle OneDrive embeds
        if (embed.server_type === 'onedrive' || isValidOneDriveUrl(embedUrl)) {
            embedUrl = convertOneDriveToEmbed(embedUrl);
        }
        
        // Handle Mixdrop and Dailymotion - ensure proper embed URLs
        const lowerUrl = embedUrl.toLowerCase();
        if (lowerUrl.includes('mixdrop') || lowerUrl.includes('dailymotion') || lowerUrl.includes('bilibili')) {
            // For Mixdrop: ensure it's an embed URL
            if (lowerUrl.includes('mixdrop')) {
                // If it's a watch/share URL, convert to embed
                if (embedUrl.includes('/e/') || embedUrl.includes('/f/')) {
                    // Already an embed URL, keep as is
                } else if (embedUrl.includes('/v/') || embedUrl.includes('/watch/')) {
                    // Convert watch URL to embed format if possible
                    const fileIdMatch = embedUrl.match(/[\/]([a-zA-Z0-9]+)$/) || embedUrl.match(/[\/]([a-zA-Z0-9]+)\?/);
                    if (fileIdMatch && fileIdMatch[1]) {
                        // Try to construct embed URL - this may vary by Mixdrop domain
                        const domain = embedUrl.match(/https?:\/\/([^\/]+)/)?.[1] || 'mixdrop.co';
                        embedUrl = `https://${domain}/e/${fileIdMatch[1]}`;
                    }
                }
            }
            
            // For Dailymotion: ensure it's an embed URL
            if (lowerUrl.includes('dailymotion.com')) {
                // If it's a video URL, convert to embed
                if (embedUrl.includes('/embed/')) {
                    // Already an embed URL, keep as is
                } else if (embedUrl.includes('/video/')) {
                    // Convert video URL to embed format
                    const videoIdMatch = embedUrl.match(/\/video\/([a-zA-Z0-9]+)/);
                    if (videoIdMatch && videoIdMatch[1]) {
                        embedUrl = `https://www.dailymotion.com/embed/video/${videoIdMatch[1]}`;
                    }
                }
            }
            
            // For Bilibili: ensure web version (not mobile/Android) and apply custom styling
            if (lowerUrl.includes('bilibili.tv') || lowerUrl.includes('bilibili.com')) {
                
                // Extract video ID if present
                let videoId = null;
                if (embedUrl.includes('/video/')) {
                    const videoIdMatch = embedUrl.match(/\/video\/([a-zA-Z0-9]+)/);
                    if (videoIdMatch && videoIdMatch[1]) {
                        videoId = videoIdMatch[1];
                    }
                }
                
                // Force web version by:
                // 1. Using bilibili.tv/en/ (English web version)
                // 2. Adding platform=web parameter
                // 3. Removing mobile app redirect parameters
                try {
                    const urlObj = new URL(embedUrl);
                    
                    // Remove mobile/Android specific parameters
                    urlObj.searchParams.delete('platform');
                    urlObj.searchParams.delete('from');
                    urlObj.searchParams.delete('share_source');
                    urlObj.searchParams.delete('share_medium');
                    
                    // Force web platform
                    urlObj.searchParams.set('platform', 'web');
                    
                    // Ensure it's the .tv domain (web version)
                    if (urlObj.hostname.includes('bilibili.com')) {
                        urlObj.hostname = 'www.bilibili.tv';
                        urlObj.pathname = urlObj.pathname.replace(/^\/en/, '/en'); // Ensure /en prefix
                        if (!urlObj.pathname.startsWith('/en/') && !urlObj.pathname.startsWith('/en')) {
                            urlObj.pathname = '/en' + urlObj.pathname;
                        }
                    } else if (!urlObj.hostname.includes('bilibili.tv')) {
                        // If it's not .tv, construct .tv URL
                        if (videoId) {
                            embedUrl = `https://www.bilibili.tv/en/video/${videoId}?platform=web`;
                        } else {
                            embedUrl = urlObj.toString();
                        }
                    } else {
                        embedUrl = urlObj.toString();
                    }
                    
                } catch (e) {
                    // If URL parsing fails, try manual construction
                    if (videoId) {
                        embedUrl = `https://www.bilibili.tv/en/video/${videoId}?platform=web`;
                    }
                }
                
                // Auto-apply Bilibili custom styling if not already present
                if (!hasDivWrapper) {
                    hasCustomStyling = true;
                    divStyle = 'width: 100%; height: 280px; overflow: hidden; position: relative;';
                    iframeStyle = 'width: 100%; height: 330px; position: absolute; top: -60px; left: 0; border: none;';
                    if (!iframeAttributes.scrolling) {
                        iframeAttributes.scrolling = 'no';
                    }
                }
            }
        }
        
        // Ensure URL is properly formatted
        try {
            // If URL doesn't start with http:// or https://, try to fix it
            if (!embedUrl.startsWith('http://') && !embedUrl.startsWith('https://')) {
                try {
                    const decoded = decodeURIComponent(embedUrl);
                    if (decoded.startsWith('http://') || decoded.startsWith('https://')) {
                        embedUrl = decoded;
                    }
                } catch (e) {
                    // Could not decode embed URL
                }
            }
            
            // Validate URL format - this will throw if invalid
            const urlObj = new URL(embedUrl);
            
            // Additional check: make sure we're not getting our own website
            if (urlObj.hostname === window.location.hostname || 
                urlObj.hostname.includes(window.location.hostname.split('.').slice(-2).join('.'))) {
                return { url: '', hasCustomStyling: false };
            }
            
            // Return object with URL and styling information
            return {
                url: embedUrl,
                hasCustomStyling: hasDivWrapper && (divStyle || iframeStyle),
                divStyle: divStyle,
                iframeStyle: iframeStyle,
                iframeAttributes: iframeAttributes
            };
        } catch (error) {
            return { url: '', hasCustomStyling: false };
        }
    }

    // Set active embed by ID
    function setActiveEmbedById(embedId) {
        const embed = embeds.find(e => e.id === embedId);
        if (embed) {
            activeEmbed = embed;
            renderWatch();
            // Update report button after render
            setTimeout(() => {
                updateReportButton();
            }, 100);
        }
    }

    // Update report button
    function updateReportButton() {
        const serverSelection = document.querySelector('.space-y-6 .flex.flex-wrap.gap-2');
        if (!serverSelection || !activeEmbed) return;

        // Remove existing report button if any
        const existingReportBtn = serverSelection.querySelector('.report-embed-btn');
        if (existingReportBtn) {
            existingReportBtn.remove();
        }

        // Add new report button
        const reportBtn = document.createElement('button');
        reportBtn.className = 'report-embed-btn flex items-center space-x-2 px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded text-sm font-medium transition-colors';
        reportBtn.innerHTML = `
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-4 w-4">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <span>Report Problem</span>
        `;
        reportBtn.title = 'Report problem with this embed';
        reportBtn.onclick = () => openReportEmbedModal(movieId, activeEmbed.id, 'movie');
        serverSelection.appendChild(reportBtn);
    }

    // Render download tab - Matching Frontend
    function renderDownload() {
        const downloadContent = document.getElementById('download-content');
        
        downloadContent.innerHTML = `
            <div>
                <h2 class="text-2xl font-bold text-white mb-6">Download ${movie.title || 'Movie'}</h2>
                
                ${downloads.length > 0 ? `
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    ${downloads.map(download => `
                        <a
                            href="${download.download_url || download.url}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="download-card"
                            onclick="trackDownloadClick()"
                        >
                            <div class="flex items-start justify-between mb-2">
                                <h3 class="text-white font-semibold">${download.quality || 'HD'}</h3>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5 text-red-500">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </div>
                            ${download.size ? `<p class="text-gray-400 text-sm">Size: ${download.size}</p>` : ''}
                            ${download.language ? `<p class="text-gray-400 text-sm">Language: ${download.language}</p>` : ''}
                            <p class="text-red-500 text-sm mt-2">Click to download</p>
                        </a>
                    `).join('')}
                </div>
                ` : `
                <div class="bg-gray-900 border border-gray-800 rounded-lg p-8 text-center">
                    <p class="text-gray-400">No download links available for this movie.</p>
                </div>
                `}
            </div>
        `;
    }

    // Render details tab - Matching Frontend
    function renderDetails() {
        const detailsContent = document.getElementById('details-content');
        
        detailsContent.innerHTML = `
            <div>
                <h2 class="text-2xl font-bold text-white mb-6">Movie Details</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Overview -->
                        <div>
                            <h3 class="text-xl font-semibold text-white mb-3">Overview</h3>
                            <p class="text-gray-300 leading-relaxed">
                                ${movie.overview || 'No description available.'}
                            </p>
                        </div>

                        <!-- Countries -->
                        ${movie.countries && movie.countries.length > 0 ? `
                        <div>
                            <h3 class="text-xl font-semibold text-white mb-3 flex items-center space-x-2">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Countries</span>
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                ${movie.countries.map(country => `
                                    <span class="px-3 py-1 bg-gray-800 text-gray-300 rounded">
                                        ${country.name || country}
                                    </span>
                                `).join('')}
                            </div>
                        </div>
                        ` : ''}

                        <!-- Languages -->
                        ${movie.languages && movie.languages.length > 0 ? `
                        <div>
                            <h3 class="text-xl font-semibold text-white mb-3 flex items-center space-x-2">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                                </svg>
                                <span>Languages</span>
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                ${movie.languages.map(language => `
                                    <span class="px-3 py-1 bg-gray-800 text-gray-300 rounded">
                                        ${language.name || language}
                                    </span>
                                `).join('')}
                            </div>
                        </div>
                        ` : ''}
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-4 bg-gray-900 rounded-lg p-6">
                        <h3 class="text-xl font-semibold text-white mb-4">Information</h3>
                        
                        ${movie.release_date ? `
                        <div class="flex justify-between py-2 border-b border-gray-800">
                            <span class="text-gray-400">Release Date</span>
                            <span class="text-white font-medium">
                                ${new Date(movie.release_date).toLocaleDateString()}
                            </span>
                        </div>
                        ` : ''}

                        ${movie.runtime ? `
                        <div class="flex justify-between py-2 border-b border-gray-800">
                            <span class="text-gray-400">Runtime</span>
                            <span class="text-white font-medium">
                                ${Math.floor(movie.runtime / 60)}h ${movie.runtime % 60}m
                            </span>
                        </div>
                        ` : ''}

                        ${movie.vote_average ? `
                        <div class="flex justify-between py-2 border-b border-gray-800">
                            <span class="text-gray-400">Rating</span>
                            <span class="text-white font-medium">
                                ${parseFloat(movie.vote_average).toFixed(1)} / 10
                            </span>
                        </div>
                        ` : ''}

                        ${movie.vote_count ? `
                        <div class="flex justify-between py-2 border-b border-gray-800">
                            <span class="text-gray-400">Votes</span>
                            <span class="text-white font-medium">
                                ${movie.vote_count.toLocaleString()}
                            </span>
                        </div>
                        ` : ''}

                        ${movie.popularity ? `
                        <div class="flex justify-between py-2 border-b border-gray-800">
                            <span class="text-gray-400">Popularity</span>
                            <span class="text-white font-medium">
                                ${parseFloat(movie.popularity).toFixed(1)}
                            </span>
                        </div>
                        ` : ''}

                        <div class="flex justify-between py-2">
                            <span class="text-gray-400">Status</span>
                            <span class="font-medium ${movie.status === 'active' ? 'text-green-400' : 'text-yellow-400'}">
                                ${movie.status === 'active' ? 'Available' : 'Coming Soon'}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Render similar movies - Matching Frontend
    function renderSimilarMovies() {
        if (similarMovies.length === 0) {
            document.getElementById('similar-movies-section').style.display = 'none';
            return;
        }

        document.getElementById('similar-movies-section').style.display = 'block';
        const grid = document.getElementById('similar-movies-grid');
        
        grid.innerHTML = '';
        similarMovies.slice(0, 10).forEach(similarMovie => {
            const card = createMovieCard(similarMovie);
            grid.appendChild(card);
        });
    }

    // Create movie card (stylish version)
    function createMovieCard(movie) {
        const card = document.createElement('div');
        card.className = 'movie-card group';
        card.style.cssText = 'position: relative; flex-shrink: 0; width: 160px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;';
        
        if (window.innerWidth >= 768) card.style.width = '192px';
        if (window.innerWidth >= 1024) card.style.width = '224px';
        
        const imageUrl = movie.poster_path 
            ? `https://image.tmdb.org/t/p/w500${movie.poster_path}`
            : '/images/placeholder.svg';

        const rating = movie.vote_average ? parseFloat(movie.vote_average).toFixed(1) : null;
        const matchPercent = movie.vote_average ? Math.round(parseFloat(movie.vote_average) * 10) : null;
        const year = movie.release_date ? new Date(movie.release_date).getFullYear() : null;
        const runtime = movie.runtime ? `${Math.floor(movie.runtime / 60)}h ${movie.runtime % 60}m` : null;

        card.innerHTML = `
            <div class="movie-card-poster">
                <img src="${imageUrl}" alt="${movie.title || 'Untitled'}" loading="lazy" onerror="this.src='/images/placeholder.svg'" 
                     class="movie-card-image">
                <div class="movie-card-overlay"></div>
                <div class="movie-card-gradient"></div>
                
                ${rating ? `
                <div class="movie-card-badge" style="left: 8px;">
                    <svg fill="currentColor" viewBox="0 0 20 20" style="width: 14px; height: 14px;">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    <span>${rating}</span>
                </div>
                ` : ''}
                
                ${movie.view_count !== undefined && movie.view_count > 0 ? `
                <div style="position: absolute; top: 8px; right: 8px; background: linear-gradient(135deg, rgba(220, 38, 38, 0.95) 0%, rgba(185, 28, 28, 0.95) 100%); color: white; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; display: flex; align-items: center; gap: 4px; z-index: 2; backdrop-filter: blur(4px); box-shadow: 0 2px 8px rgba(220, 38, 38, 0.4);">
                    <svg fill="currentColor" viewBox="0 0 24 24" style="width: 12px; height: 12px;">
                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                    </svg>
                    <span>${movie.view_count}</span>
                </div>
                ` : ''}
            </div>

            <h3 class="movie-card-title">${movie.title || 'Untitled'}</h3>
            
            ${year || runtime ? `
            <div class="movie-card-meta">
                ${year ? `<span>${year}</span>` : ''}
                ${runtime ? `<span>•</span><span>${runtime}</span>` : ''}
            </div>
            ` : ''}

            <div class="movie-card-hover-info">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
                    <button onclick="event.stopPropagation(); window.location.href='/movie/${movie.id}';" 
                            style="background: linear-gradient(135deg, #ffffff 0%, #f3f4f6 100%); color: black; padding: 10px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(255,255,255,0.2);"
                            onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 6px 16px rgba(255,255,255,0.3)'"
                            onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 12px rgba(255,255,255,0.2)'">
                        <svg fill="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </button>
                    <button onclick="event.stopPropagation(); window.location.href='/movie/${movie.id}';" 
                            style="border: 2px solid rgba(255,255,255,0.3); color: white; padding: 10px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; background: rgba(255,255,255,0.1); cursor: pointer; backdrop-filter: blur(4px);"
                            onmouseover="this.style.borderColor='white'; this.style.background='rgba(255,255,255,0.2)'; this.style.transform='scale(1.1)'"
                            onmouseout="this.style.borderColor='rgba(255,255,255,0.3)'; this.style.background='rgba(255,255,255,0.1)'; this.style.transform='scale(1)'">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
                <h4 style="color: white; font-weight: 700; font-size: 16px; margin-bottom: 10px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; line-height: 1.3;">${movie.title || 'Untitled'}</h4>
                <div style="display: flex; align-items: center; gap: 10px; font-size: 13px; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                    ${matchPercent ? `<span style="color: #4ade80; font-weight: 700; background: rgba(74, 222, 128, 0.2); padding: 4px 8px; border-radius: 4px;">${matchPercent}% Match</span>` : ''}
                    ${year ? `<span style="color: #d1d5db;">${year}</span>` : ''}
                    ${runtime ? `<span style="color: #9ca3af;">• ${runtime}</span>` : ''}
                </div>
                ${movie.genres && movie.genres.length > 0 ? `
                <div style="display: flex; flex-wrap: wrap; gap: 6px; font-size: 12px;">
                    ${movie.genres.slice(0, 3).map((genre) => {
                        const genreName = genre.name || genre;
                        return `<span style="background: rgba(220, 38, 38, 0.2); color: #fca5a5; padding: 4px 10px; border-radius: 12px; border: 1px solid rgba(220, 38, 38, 0.3); font-weight: 500;">${genreName}</span>`;
                    }).join('')}
                </div>
                ` : ''}
            </div>
        `;

        card.addEventListener('click', () => {
            window.location.href = `/movie/${movie.id}`;
        });

        return card;
    }

    // Fullscreen event listener
    document.addEventListener('fullscreenchange', () => {
        isFullscreen = !!document.fullscreenElement;
        updateFullscreenButton();
    });

    // Update SEO meta tags dynamically
    function updateSEOTags() {
        if (!movie) return;
        
        const movieTitle = movie.title || 'Movie';
        const movieOverview = movie.overview ? movie.overview.substring(0, 160) : 'Watch ' + movieTitle + ' online in HD quality on Nazaara Box.';
        const defaultImage = '{{ url(asset("website_og_image.png")) }}';
        const movieImage = movie.backdrop_path 
            ? `https://image.tmdb.org/t/p/original${movie.backdrop_path}`
            : (movie.poster_path ? `https://image.tmdb.org/t/p/original${movie.poster_path}` : defaultImage);
        const currentUrl = window.location.href;
        
        // Update meta tags
        document.querySelector('meta[name="title"]')?.setAttribute('content', `${movieTitle} - Watch Online | Nazaara Box`);
        document.querySelector('meta[name="description"]')?.setAttribute('content', movieOverview);
        document.querySelector('meta[property="og:title"]')?.setAttribute('content', `${movieTitle} - Watch Online | Nazaara Box`);
        document.querySelector('meta[property="og:description"]')?.setAttribute('content', movieOverview);
        document.querySelector('meta[property="og:image"]')?.setAttribute('content', movieImage);
        document.querySelector('meta[property="og:url"]')?.setAttribute('content', currentUrl);
        document.querySelector('meta[name="twitter:title"]')?.setAttribute('content', `${movieTitle} - Watch Online | Nazaara Box`);
        document.querySelector('meta[name="twitter:description"]')?.setAttribute('content', movieOverview);
        document.querySelector('meta[name="twitter:image"]')?.setAttribute('content', movieImage);
        document.querySelector('link[rel="canonical"]')?.setAttribute('href', currentUrl);
        
        // Update page title
        document.title = `${movieTitle} - Watch Online | Nazaara Box`;
    }

    // Navigation blocking when video is playing
    let isVideoPlaying = false;
    let navigationBlocked = false;

    // Function to enable navigation blocking
    function enableNavigationBlock() {
        if (navigationBlocked) return;
        navigationBlocked = true;
        isVideoPlaying = true;
        
        // Block page unload with warning
        window.addEventListener('beforeunload', handleBeforeUnload);
        
        // Intercept link clicks to warn before navigation
        document.addEventListener('click', handleLinkClick, true);
        
        // Block browser back/forward buttons (optional - can be annoying)
        // window.addEventListener('popstate', handlePopState);
        
    }

    // Function to disable navigation blocking
    function disableNavigationBlock() {
        if (!navigationBlocked) return;
        navigationBlocked = false;
        isVideoPlaying = false;
        
        window.removeEventListener('beforeunload', handleBeforeUnload);
        document.removeEventListener('click', handleLinkClick, true);
        // window.removeEventListener('popstate', handlePopState);
        
    }

    // Handle beforeunload event (warns user when trying to leave page)
    function handleBeforeUnload(e) {
        if (isVideoPlaying) {
            e.preventDefault();
            e.returnValue = 'You are watching a video. Are you sure you want to leave?';
            return e.returnValue;
        }
    }

    // Handle link clicks (warn before navigating)
    function handleLinkClick(e) {
        if (!isVideoPlaying) return;
        
        const link = e.target.closest('a');
        if (!link) return;
        
        // Allow links that open in new tabs
        if (link.target === '_blank' || link.hasAttribute('download')) {
            return;
        }
        
        // Allow hash links (same page navigation)
        if (link.getAttribute('href') && link.getAttribute('href').startsWith('#')) {
            return;
        }
        
        // Prevent external navigation
        const href = link.getAttribute('href');
        if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
            const shouldNavigate = confirm('You are watching a video. Are you sure you want to leave this page?');
            if (!shouldNavigate) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            } else {
                disableNavigationBlock();
            }
        }
    }

    // Handle browser back/forward buttons (optional - comment out if too aggressive)
    function handlePopState(e) {
        if (isVideoPlaying) {
            const shouldNavigate = confirm('You are watching a video. Are you sure you want to go back?');
            if (!shouldNavigate) {
                history.pushState(null, null, window.location.href);
                e.preventDefault();
                return false;
            } else {
                disableNavigationBlock();
            }
        }
    }

    // Monitor iframe load to detect video playing
    function setupVideoPlayerMonitoring() {
        // Watch for iframe additions/changes
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        const iframes = node.querySelectorAll ? node.querySelectorAll('iframe') : [];
                        if (node.tagName === 'IFRAME') {
                            setupIframeMonitoring(node);
                            
                            // Check if it's vidsrc immediately
                            const src = node.src || '';
                            if (src.includes('vidsrc.icu') || src.includes('vidsrc.io')) {
                                enableNavigationBlock();
                            }
                        }
                        iframes.forEach(iframe => {
                            setupIframeMonitoring(iframe);
                            
                            // Check if it's vidsrc immediately
                            const src = iframe.src || '';
                            if (src.includes('vidsrc.icu') || src.includes('vidsrc.io')) {
                                enableNavigationBlock();
                            }
                        });
                    }
                });
            });
        });

        // Observe the video player container
        const playerContainer = document.getElementById('video-player-container');
        if (playerContainer) {
            observer.observe(playerContainer, { childList: true, subtree: true });
            
            // Also check existing iframes
            const existingIframes = playerContainer.querySelectorAll('iframe');
            existingIframes.forEach(iframe => {
                setupIframeMonitoring(iframe);
                
                // Check if it's vidsrc immediately
                const src = iframe.src || '';
                if (src.includes('vidsrc.icu') || src.includes('vidsrc.io')) {
                    enableNavigationBlock();
                }
            });
        }
        
        // Additional check: Monitor for vidsrc embeds in the embed URL processing
        const checkVidsrcEmbeds = setInterval(() => {
            const allIframes = document.querySelectorAll('iframe[id^="video-iframe-"]');
            allIframes.forEach(iframe => {
                const src = iframe.src || '';
                if ((src.includes('vidsrc.icu') || src.includes('vidsrc.io')) && !iframe.dataset.vidsrcChecked) {
                    iframe.dataset.vidsrcChecked = 'true';
                    enableNavigationBlock();
                }
            });
        }, 1000);
        
        // Stop checking after 30 seconds
        setTimeout(() => {
            clearInterval(checkVidsrcEmbeds);
        }, 30000);
    }

    // Setup monitoring for individual iframe
    function setupIframeMonitoring(iframe) {
        if (iframe.dataset.navigationMonitored) return;
        iframe.dataset.navigationMonitored = 'true';
        
        // Check if this is a vidsrc.icu embed (priority blocking)
        const iframeSrc = iframe.src || '';
        const isVidsrcEmbed = iframeSrc.includes('vidsrc.icu') || iframeSrc.includes('vidsrc.io');
        
        if (isVidsrcEmbed) {
            // Enable blocking immediately for vidsrc embeds
            enableNavigationBlock();
            
            // Also monitor for when iframe actually loads
            if (iframe.complete || iframe.readyState === 'complete') {
                enableNavigationBlock();
            }
        }
        
        // Enable blocking when iframe loads (for all embeds)
        iframe.addEventListener('load', () => {
            enableNavigationBlock();
            
            // For vidsrc embeds, add extra check
            if (isVidsrcEmbed) {
                // Ensure blocking is active
                setTimeout(() => {
                    enableNavigationBlock();
                }, 500);
            }
        });
        
        // Also check on src change (for dynamic embeds)
        let currentSrc = iframe.src;
        const srcObserver = new MutationObserver(() => {
            if (iframe.src !== currentSrc) {
                currentSrc = iframe.src;
                const newIsVidsrc = currentSrc.includes('vidsrc.icu') || currentSrc.includes('vidsrc.io');
                if (newIsVidsrc) {
                    enableNavigationBlock();
                }
            }
        });
        srcObserver.observe(iframe, { attributes: true, attributeFilter: ['src'] });
        
        // Disable blocking when iframe is removed
        const iframeObserver = new MutationObserver(() => {
            if (!document.body.contains(iframe)) {
                disableNavigationBlock();
                iframeObserver.disconnect();
                srcObserver.disconnect();
            }
        });
        iframeObserver.observe(document.body, { childList: true, subtree: true });
    }

    // Ad Management Functions
    let iframeObserver = null;
    
    // Function to disable pointer events on all iframes and videos
    function disableAllIframesAndVideos() {
        // Disable pointer events on all iframes (including nested ones)
        const allIframes = document.querySelectorAll('iframe');
        const allVideos = document.querySelectorAll('video');
        const allVideoContainers = document.querySelectorAll('.video-player-container, .video-player-wrapper');
        
        allIframes.forEach(iframe => {
            iframe.style.pointerEvents = 'none';
            iframe.style.zIndex = '1';
            // Also try to disable nested iframes if accessible
            try {
                if (iframe.contentWindow && iframe.contentDocument) {
                    const nestedIframes = iframe.contentDocument.querySelectorAll('iframe');
                    nestedIframes.forEach(nestedIframe => {
                        nestedIframe.style.pointerEvents = 'none';
                    });
                }
            } catch (e) {
                // Cross-origin iframe, can't access content
            }
        });
        
        allVideos.forEach(video => {
            video.style.pointerEvents = 'none';
            video.style.zIndex = '1';
        });
        
        allVideoContainers.forEach(container => {
            container.style.pointerEvents = 'none';
            container.style.zIndex = '1';
        });
    }
    
    // Function to re-enable pointer events on all iframes and videos
    function enableAllIframesAndVideos() {
        const allIframes = document.querySelectorAll('iframe');
        const allVideos = document.querySelectorAll('video');
        const allVideoContainers = document.querySelectorAll('.video-player-container, .video-player-wrapper');
        
        allIframes.forEach(iframe => {
            iframe.style.pointerEvents = '';
            iframe.style.zIndex = '';
        });
        
        allVideos.forEach(video => {
            video.style.pointerEvents = '';
            video.style.zIndex = '';
        });
        
        allVideoContainers.forEach(container => {
            container.style.pointerEvents = '';
            container.style.zIndex = '';
        });
    }
    
    function showAd() {
        if (adShown || downloadClicked) return;
        
        const adContainer = document.getElementById('ad-container');
        if (adContainer) {
            adContainer.classList.add('show');
            document.body.classList.add('ad-active');
            adShown = true;
            
            // Disable pointer events on all video embeds and iframes
            disableAllIframesAndVideos();
            
            // Monitor for dynamically added iframes (from embed servers)
            if (iframeObserver) {
                iframeObserver.disconnect();
            }
            
            iframeObserver = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) { // Element node
                            // Check if the added node is an iframe
                            if (node.tagName === 'IFRAME') {
                                node.style.pointerEvents = 'none';
                                node.style.zIndex = '1';
                            }
                            // Check for iframes within the added node
                            const iframes = node.querySelectorAll && node.querySelectorAll('iframe');
                            if (iframes) {
                                iframes.forEach(iframe => {
                                    iframe.style.pointerEvents = 'none';
                                    iframe.style.zIndex = '1';
                                });
                            }
                            // Check for video elements
                            if (node.tagName === 'VIDEO') {
                                node.style.pointerEvents = 'none';
                                node.style.zIndex = '1';
                            }
                            const videos = node.querySelectorAll && node.querySelectorAll('video');
                            if (videos) {
                                videos.forEach(video => {
                                    video.style.pointerEvents = 'none';
                                    video.style.zIndex = '1';
                                });
                            }
                        }
                    });
                });
            });
            
            // Observe the entire document for dynamically added iframes
            iframeObserver.observe(document.body, {
                childList: true,
                subtree: true
            });
            
            // Periodically check for new iframes (in case MutationObserver misses some)
            const checkInterval = setInterval(() => {
                if (!adShown || downloadClicked) {
                    clearInterval(checkInterval);
                    return;
                }
                disableAllIframesAndVideos();
            }, 500);
            
            // Store interval ID so we can clear it later
            window.adCheckInterval = checkInterval;
            
            // Load ad content (Google AdSense example - replace with your ad code)
            loadAdContent();
        }
    }

    function closeAd() {
        const adContainer = document.getElementById('ad-container');
        if (adContainer) {
            adContainer.classList.remove('show');
            document.body.classList.remove('ad-active');
            
            // Stop monitoring for new iframes
            if (iframeObserver) {
                iframeObserver.disconnect();
                iframeObserver = null;
            }
            
            // Clear the periodic check interval
            if (window.adCheckInterval) {
                clearInterval(window.adCheckInterval);
                window.adCheckInterval = null;
            }
            
            // Re-enable pointer events on video embeds and iframes
            enableAllIframesAndVideos();
            
        }
    }

    async function loadAdContent() {
        const adContent = document.getElementById('ad-content');
        if (!adContent) return;

        // Download page promotion ad with mobile prototype
        const downloadUrl = 'https://play.google.com/store/apps/details?id=com.pro.name.generator&hl=en';
        const iconUrl = '{{ asset("icon.png") }}';
        
        // Get featured movies for phone mockup
        let featuredMovies = [];
        if (movie && similarMovies && similarMovies.length > 0) {
            featuredMovies = similarMovies.slice(0, 6);
        }
        
        // If no featured movies, fetch trending/popular movies from API
        if (featuredMovies.length === 0) {
            try {
                const moviesResponse = await apiFetch(`${API_BASE_URL}/movies?limit=6`);
                if (moviesResponse.ok) {
                    const moviesResult = await moviesResponse.json();
                    const allMovies = moviesResult.data?.movies || moviesResult.data || [];
                    featuredMovies = allMovies.slice(0, 6);
                }
            } catch (error) {
            }
        }
        
        // Generate phone content - matching TV shows page structure
        let phoneContentHtml = '';
        if (featuredMovies.length > 0) {
            const firstMovie = featuredMovies[0];
            // Format image URLs properly - handle both full URLs and relative paths
            let backdropUrl = '';
            if (firstMovie.backdrop_path) {
                if (firstMovie.backdrop_path.startsWith('http')) {
                    backdropUrl = firstMovie.backdrop_path;
                } else if (firstMovie.backdrop_path.startsWith('/')) {
                    backdropUrl = 'https://image.tmdb.org/t/p/w500' + firstMovie.backdrop_path;
                } else {
                    backdropUrl = 'https://image.tmdb.org/t/p/w500/' + firstMovie.backdrop_path;
                }
            } else if (firstMovie.poster_path) {
                if (firstMovie.poster_path.startsWith('http')) {
                    backdropUrl = firstMovie.poster_path;
                } else if (firstMovie.poster_path.startsWith('/')) {
                    backdropUrl = 'https://image.tmdb.org/t/p/w500' + firstMovie.poster_path;
                } else {
                    backdropUrl = 'https://image.tmdb.org/t/p/w500/' + firstMovie.poster_path;
                }
            }
            
            phoneContentHtml = `
                <a href="${downloadUrl}" target="_blank" rel="noopener noreferrer" class="ad-phone-hero-poster" onclick="trackDownloadClick();">
                    ${backdropUrl ? `<img src="${backdropUrl}" alt="${firstMovie.title || 'Movie'}" onerror="this.style.display='none';">` : '<div style="width: 100%; height: 100%; background: linear-gradient(135deg, #374151 0%, #1f2937 100%);"></div>'}
                    <div class="ad-phone-hero-overlay"></div>
                    <div class="ad-phone-hero-info">
                        <div class="ad-phone-hero-title">${firstMovie.title || 'Featured Movie'}</div>
                    </div>
                </a>
                <div class="ad-phone-content-grid">
                    ${featuredMovies.slice(0, 6).map(m => {
                        let posterUrl = '';
                        if (m.poster_path) {
                            if (m.poster_path.startsWith('http')) {
                                posterUrl = m.poster_path;
                            } else if (m.poster_path.startsWith('/')) {
                                posterUrl = 'https://image.tmdb.org/t/p/w300' + m.poster_path;
                            } else {
                                posterUrl = 'https://image.tmdb.org/t/p/w300/' + m.poster_path;
                            }
                        }
                        return `<a href="${downloadUrl}" target="_blank" rel="noopener noreferrer" class="ad-phone-content-item" onclick="trackDownloadClick();">
                            ${posterUrl ? `<img src="${posterUrl}" alt="${m.title || 'Movie'}" onerror="this.style.display='none';">` : '<div style="width: 100%; height: 100%; background: linear-gradient(135deg, #374151 0%, #1f2937 100%);"></div>'}
                        </a>`;
                    }).join('')}
                </div>
            `;
        } else {
            // Fallback: Show placeholder if API fetch also fails
            phoneContentHtml = `
                <div style="padding: 20px; text-align: center; color: #9ca3af; font-size: 12px;">
                    <p>Stream unlimited content</p>
                </div>
            `;
        }
        
        adContent.innerHTML = `
            <div class="ad-phone-mockup-container">
                <div class="ad-phone-mockup">
                    <div class="ad-phone-screen">
                        <div class="ad-phone-header">
                            <img src="${iconUrl}" alt="Nazaara Box" onerror="this.style.display='none';">
                            <span>Nazaarabox</span>
                        </div>
                        <div class="ad-phone-nav">
                            <button style="color: #dc2626; border-bottom: 2px solid #dc2626;">TV Shows</button>
                            <button style="color: #9ca3af;">Movies</button>
                        </div>
                        <div class="ad-phone-content">
                            ${phoneContentHtml}
                        </div>
                    </div>
                </div>
            </div>
            
            <a href="${downloadUrl}" target="_blank" rel="noopener noreferrer" class="ad-button" onclick="trackDownloadClick();">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Download Now
            </a>
        `;
    }

    function updateCountdown() {
        const countdownEl = document.getElementById('ad-countdown');
        if (countdownEl && adShown && !downloadClicked) {
            countdownEl.textContent = countdownSeconds;
            
            if (countdownSeconds <= 0) {
                closeAd();
            } else {
                countdownSeconds--;
            }
        }
    }

    function startAdTimer() {
        // Clear any existing timers
        if (adTimer) {
            clearTimeout(adTimer);
        }
        if (countdownTimer) {
            clearInterval(countdownTimer);
        }

        // Reset countdown
        countdownSeconds = 20;
        
        // Show ad immediately on page load
        showAd();
        // Update countdown display immediately
        updateCountdown();
        
        // Update countdown every second
        countdownTimer = setInterval(() => {
            updateCountdown();
        }, 1000);
        
        // Auto-hide ad after 20 seconds
        adTimer = setTimeout(() => {
            if (adShown && !downloadClicked) {
                closeAd();
            }
        }, 20000); // 20 seconds = 20000ms
    }

    function stopAdTimer() {
        if (adTimer) {
            clearTimeout(adTimer);
            adTimer = null;
        }
        if (countdownTimer) {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }
        countdownSeconds = 20;
    }
    
    // Check if download has started (file is being downloaded)
    function checkDownloadStart() {
        // Monitor for download start by checking if download link was clicked
        const downloadLinks = document.querySelectorAll('a[href*="/download"], a[href*=".apk"], .download-card');
        downloadLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Small delay to ensure download actually starts
                setTimeout(() => {
                    if (adShown) {
                        downloadClicked = true;
                        stopAdTimer();
                        closeAd();
                    }
                }, 500);
            });
        });
    }

    // Force APK download without navigation
    function startApkDownload(url) {
        // Method 1: Create a temporary anchor element with download attribute
        try {
            const link = document.createElement('a');
            link.href = url;
            link.download = 'nazaarabox.apk';
            link.target = '_self';
            link.style.display = 'none';
            
            // Append to body
            document.body.appendChild(link);
            
            // Trigger click
            link.click();
            
            // Clean up after a short delay
            setTimeout(() => {
                if (document.body.contains(link)) {
                    document.body.removeChild(link);
                }
            }, 200);
            
            // Fallback: If download doesn't start within 1 second, try direct navigation with download attribute
            setTimeout(() => {
                // Check if user is still on the page (download might have failed)
                const fallbackLink = document.createElement('a');
                fallbackLink.href = url;
                fallbackLink.download = 'nazaarabox.apk';
                fallbackLink.style.display = 'none';
                document.body.appendChild(fallbackLink);
                fallbackLink.click();
                setTimeout(() => {
                    if (document.body.contains(fallbackLink)) {
                        document.body.removeChild(fallbackLink);
                    }
                }, 100);
            }, 1000);
        } catch (error) {
            // Final fallback: direct window location
            window.location.href = url;
        }
    }

    // Track download clicks
    function trackDownloadClick() {
        downloadClicked = true;
        stopAdTimer();
        
        // Clean up observers and intervals
        if (iframeObserver) {
            iframeObserver.disconnect();
            iframeObserver = null;
        }
        if (window.adCheckInterval) {
            clearInterval(window.adCheckInterval);
            window.adCheckInterval = null;
        }
        stopAdTimer();
        
        closeAd();
    }

    // Monitor download button clicks
    function setupDownloadTracking() {
        // Monitor download tab clicks
        document.addEventListener('click', (e) => {
            const downloadCard = e.target.closest('.download-card');
            if (downloadCard) {
                trackDownloadClick();
            }
        });

        // Monitor download tab button clicks
        const watchNowBtn = document.getElementById('watch-now-btn');
        if (watchNowBtn) {
            watchNowBtn.addEventListener('click', () => {
                // Check if download tab is clicked
                setTimeout(() => {
                    const downloadTab = document.getElementById('download-tab');
                    if (downloadTab && downloadTab.style.display !== 'none') {
                        // Monitor downloads in the tab
                        const downloadLinks = downloadTab.querySelectorAll('.download-card');
                        downloadLinks.forEach(link => {
                            link.addEventListener('click', trackDownloadClick);
                        });
                    }
                }, 100);
            });
        }
    }

    // Report Embed Modal Functions
    function openReportEmbedModal(contentId, embedId, contentType) {
        document.getElementById('report-content-id').value = contentId;
        document.getElementById('report-embed-id').value = embedId;
        document.getElementById('report-content-type').value = contentType;
        document.getElementById('report-embed-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeReportEmbedModal() {
        document.getElementById('report-embed-modal').classList.add('hidden');
        document.body.style.overflow = '';
        document.getElementById('report-embed-form').reset();
    }

    async function submitEmbedReport(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const data = {
            content_type: formData.get('content_type'),
            content_id: parseInt(formData.get('content_id')),
            embed_id: parseInt(formData.get('embed_id')),
            report_type: formData.get('report_type'),
            description: formData.get('description') || null,
            email: formData.get('email'),
        };

        try {
            const response = await fetch(`${API_BASE_URL}/reports/embed`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            });

            const result = await response.json();

            if (response.ok && result.success) {
                alert('Thank you! Your report has been submitted successfully.');
                closeReportEmbedModal();
            } else {
                alert('Failed to submit report. Please try again.');
            }
        } catch (error) {
            alert('An error occurred. Please try again later.');
        }
    }

    // Request Content Modal Functions
    function openRequestContentModal() {
        // Pre-fill with current movie if available
        if (movie && movie.title) {
            document.getElementById('request-title').value = movie.title;
            document.getElementById('request-type').value = 'movie';
        }
        document.getElementById('request-content-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeRequestContentModal() {
        document.getElementById('request-content-modal').classList.add('hidden');
        document.body.style.overflow = '';
        document.getElementById('request-content-form').reset();
    }

    async function submitContentRequest(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const data = {
            type: formData.get('type'),
            title: formData.get('title'),
            email: formData.get('email'),
            description: formData.get('description') || null,
        };

        // Add TMDB ID if available
        if (movie && movie.tmdb_id) {
            data.tmdb_id = movie.tmdb_id.toString();
        }
        if (movie && movie.release_date) {
            data.year = new Date(movie.release_date).getFullYear().toString();
        }

        try {
            const response = await fetch(`${API_BASE_URL}/requests`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            });

            const result = await response.json();

            if (response.ok && result.success) {
                alert('Thank you! Your request has been submitted successfully.');
                closeRequestContentModal();
            } else {
                alert(result.message || 'Failed to submit request. Please try again.');
            }
        } catch (error) {
            alert('An error occurred. Please try again later.');
        }
    }

    // Close modals when clicking outside
    document.addEventListener('click', (e) => {
        const reportModal = document.getElementById('report-embed-modal');
        const requestModal = document.getElementById('request-content-modal');
        
        if (e.target === reportModal) {
            closeReportEmbedModal();
        }
        if (e.target === requestModal) {
            closeRequestContentModal();
        }
    });

    // Close modals with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeReportEmbedModal();
            closeRequestContentModal();
        }
    });

    // Comments System Functions
    let currentReplyParentId = null;
    let currentReplyParentName = null;

    // Load comments for the movie
    async function loadComments() {
        if (!movie || !movie.id) return;

        const commentsList = document.getElementById('comments-list');
        const commentsLoading = document.getElementById('comments-loading');
        const commentsEmpty = document.getElementById('comments-empty');

        try {
            commentsLoading.style.display = 'block';
            commentsList.innerHTML = '';
            commentsEmpty.style.display = 'none';

            const response = await apiFetch(`${API_BASE_URL}/comments?type=movie&id=${movie.id}`);
            const result = await response.json();

            commentsLoading.style.display = 'none';

            if (result.success && result.data && result.data.comments && result.data.comments.length > 0) {
                // Flatten nested structure for rendering
                const allComments = [];
                result.data.comments.forEach(comment => {
                    allComments.push(comment);
                    if (comment.replies && comment.replies.length > 0) {
                        comment.replies.forEach(reply => {
                            reply.parent_id = comment.id;
                            allComments.push(reply);
                        });
                    }
                });
                renderComments(allComments);
            } else {
                commentsEmpty.style.display = 'block';
            }
        } catch (error) {
            commentsLoading.style.display = 'none';
            commentsList.innerHTML = '<div class="text-center py-8 text-red-400">Error loading comments. Please try again later.</div>';
        }
    }

    // Render comments with nested replies
    function renderComments(comments) {
        const commentsList = document.getElementById('comments-list');
        commentsList.innerHTML = '';

        // Separate top-level comments and replies
        const topLevelComments = comments.filter(c => !c.parent_id);
        const repliesMap = {};
        comments.filter(c => c.parent_id).forEach(reply => {
            if (!repliesMap[reply.parent_id]) {
                repliesMap[reply.parent_id] = [];
            }
            repliesMap[reply.parent_id].push(reply);
        });

        // Render each top-level comment with its replies
        topLevelComments.forEach(comment => {
            const commentHtml = renderComment(comment, repliesMap[comment.id] || []);
            commentsList.innerHTML += commentHtml;
        });
    }

    // Render a single comment with its replies
    function renderComment(comment, replies = []) {
        const date = new Date(comment.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        const adminBadge = comment.is_admin_reply ? '<span class="comment-badge admin">Admin</span>' : '';

        let html = `
            <div class="comment-item" data-comment-id="${comment.id}">
                <div class="comment-header">
                    <span class="comment-author">${escapeHtml(comment.name)}</span>
                    ${adminBadge}
                    <span class="comment-date">${date}</span>
                </div>
                <div class="comment-content">${escapeHtml(comment.comment)}</div>
                <div class="comment-actions">
                    <button onclick="replyToComment(${comment.id}, '${escapeHtml(comment.name)}')" class="comment-action-btn comment-reply-btn">
                        Reply
                    </button>
                </div>
        `;

        // Render replies if any
        if (replies.length > 0) {
            html += '<div class="comment-replies">';
            replies.forEach(reply => {
                html += renderComment(reply, []);
            });
            html += '</div>';
        }

        html += '</div>';
        return html;
    }

    // Reply to a comment
    function replyToComment(parentId, parentName) {
        currentReplyParentId = parentId;
        currentReplyParentName = parentName;

        document.getElementById('comment-parent-id').value = parentId;
        document.getElementById('comment-replying-to').textContent = `Replying to ${parentName}`;
        document.getElementById('comment-replying-to').style.display = 'block';
        document.getElementById('cancel-reply-btn').style.display = 'block';

        // Scroll to comment form
        document.getElementById('comment-form').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        document.getElementById('comment-text').focus();
    }

    // Cancel reply
    function cancelReply() {
        currentReplyParentId = null;
        currentReplyParentName = null;

        document.getElementById('comment-parent-id').value = '';
        document.getElementById('comment-replying-to').style.display = 'none';
        document.getElementById('cancel-reply-btn').style.display = 'none';
        document.getElementById('comment-text').value = '';
    }

    // Submit comment
    async function submitComment(event) {
        event.preventDefault();

        if (!movie || !movie.id) {
            alert('Movie information not loaded. Please refresh the page.');
            return;
        }

        const form = event.target;
        const formData = new FormData(form);
        const submitBtn = document.getElementById('submit-comment-btn');
        const submitText = document.getElementById('submit-comment-text');
        const submitSpinner = document.getElementById('submit-comment-spinner');
        const successMessage = document.getElementById('comment-success-message');

        // Show loading state
        submitBtn.disabled = true;
        submitText.style.display = 'none';
        submitSpinner.style.display = 'block';
        successMessage.style.display = 'none';

        const parentId = formData.get('parent_id');
        const commentData = {
            type: 'movie',
            id: movie.id,
            parent_id: (parentId && String(parentId).trim() !== '') ? parseInt(parentId, 10) : null,
            name: formData.get('name'),
            email: formData.get('email'),
            comment: formData.get('comment')
        };

        try {
            const response = await fetch(`${API_BASE_URL}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(commentData)
            });

            const result = await response.json();

            // Hide loading state
            submitBtn.disabled = false;
            submitText.style.display = 'block';
            submitSpinner.style.display = 'none';

            if (result.success) {
                // Show success message
                successMessage.style.display = 'block';
                
                // Reset form
                form.reset();
                cancelReply();
                
                // Scroll to success message
                successMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                
                // Hide success message after 5 seconds
                setTimeout(() => {
                    successMessage.style.display = 'none';
                }, 5000);
                
                // Reload comments after a short delay
                setTimeout(() => {
                    loadComments();
                }, 1000);
            } else {
                alert(result.message || 'Failed to submit comment. Please try again.');
            }
        } catch (error) {
            
            // Hide loading state
            submitBtn.disabled = false;
            submitText.style.display = 'block';
            submitSpinner.style.display = 'none';
            
            alert('An error occurred. Please try again later.');
        }
    }

    // Escape HTML helper
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

        // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        fetchMovieDetails();

        // Setup video player monitoring after a short delay to ensure DOM is ready
        setTimeout(() => {
            setupVideoPlayerMonitoring();
        }, 1000);

        // Show ad immediately and setup auto-hide after 20 seconds
        startAdTimer();

        // Setup download tracking
        setupDownloadTracking();
        
        // Monitor for download starts
        setTimeout(() => {
            checkDownloadStart();
        }, 500);

        // Remove ad when page unloads
        window.addEventListener('beforeunload', () => {
            stopAdTimer();
        });
    });
</script>
@endpush
@endsection
