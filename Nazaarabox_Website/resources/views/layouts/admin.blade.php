<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel - Nazaara Box')</title>

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

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --primary-red: #dc2626;
            --primary-red-dark: #b91c1c;
            --dark-900: #0a0a0a;
            --dark-800: #1a1a1a;
            --dark-700: #2a2a2a;
            --dark-600: #3a3a3a;
            --dark-500: #4a4a4a;
            --dark-400: #9ca3af;
            --dark-300: #d1d5db;
        }

        body {
            background-color: var(--dark-900);
            color: #ffffff;
            font-family: 'Inter', sans-serif;
        }

        .bg-dark-900 { background-color: var(--dark-900); }
        .bg-dark-800 { background-color: var(--dark-800); }
        .bg-dark-700 { background-color: var(--dark-700); }
        .bg-dark-600 { background-color: var(--dark-600); }
        .bg-primary-600 { background-color: var(--primary-red); }
        .border-dark-700 { border-color: var(--dark-700); }
        .text-dark-400 { color: var(--dark-400); }
        .text-dark-300 { color: var(--dark-300); }

        /* Spinner */
        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-top-color: #dc2626;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-dark-900">
    @yield('content')
    
    <script>
        const API_BASE_URL = "{{ url('/api/v1') }}";
        
        // API Key for protected endpoints (embeds, downloads, etc.)
        // This key is required for API routes protected by api.key middleware
        // Note: We can't retrieve the full key from DB (it's hashed), so using the known key
        const API_KEY = '{{ env("APP_API_KEY", "nzb_api_qfUxBMPiu3aqeXjgdqKCO4KqTDJB31m4") }}';
        
        // CSRF Token setup for AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Helper function to get headers for API requests
        function getApiHeaders(includeAuth = true) {
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-API-Key': API_KEY
            };
            
            if (includeAuth) {
                const token = localStorage.getItem('adminAccessToken');
                if (token) {
                    headers['Authorization'] = `Bearer ${token}`;
                }
            }
            
            return headers;
        }
        
        // Admin auth check
        function checkAdminAuth() {
            // This will be handled by middleware in Laravel
            return true;
        }
    </script>
    @stack('scripts')
</body>
</html>

