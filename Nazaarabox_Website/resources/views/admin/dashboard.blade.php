@extends('layouts.admin')

@section('title', 'Admin Dashboard - Nazaara Box')

@push('styles')
<style>
    .admin-header {
        background-color: #1a1a1a;
        border-bottom: 1px solid #2a2a2a;
    }
    .stat-card {
        background-color: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 8px;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-dark-900">
    <!-- Header -->
    <header class="admin-header">
        <div style="max-width: 1280px; margin: 0 auto; padding: 0 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; height: 64px;">
                <div style="display: flex; align-items: center;">
                    <h1 style="font-size: 20px; font-weight: bold; color: white; margin: 0;">Nazaara Box Admin</h1>
                </div>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <span id="admin-name" style="color: #9ca3af;">Welcome, Admin</span>
                    <button onclick="handleLogout()" class="flex items-center gap-2 bg-transparent border border-gray-600 text-white px-4 py-2 rounded hover:bg-dark-700 transition-colors text-sm">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span>Logout</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main style="max-width: 1280px; margin: 0 auto; padding: 32px 16px;">
        <div style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="font-size: 24px; font-weight: bold; color: white; margin-bottom: 8px;">Dashboard</h2>
                <p style="color: #9ca3af; margin: 0;">
                    Overview of your Nazaara Box administration
                    <span id="last-updated" style="margin-left: 8px; color: #6b7280;"></span>
                </p>
            </div>
            <button onclick="refreshStats()" id="refresh-btn" class="flex items-center gap-2 bg-transparent border border-gray-600 text-white px-4 py-2 rounded hover:bg-dark-700 transition-colors text-sm">
                <svg id="refresh-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 0020 13a8.001 8.001 0 00-8 8c-2.1 0-4.06-.9-5.5-2.4L4 17"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <span id="refresh-text">Refresh Stats</span>
            </button>
        </div>

        <!-- Main Stats Grid -->
        <div id="loading-state" style="display: none; justify-content: center; align-items: center; min-height: 400px;">
            <div class="spinner"></div>
        </div>

        <div id="dashboard-content">
            <div id="main-stats-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8"></div>

            <!-- Detailed Stats Grid -->
            <div id="detailed-stats-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8"></div>

            <!-- Quick Actions & Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Quick Actions -->
                <div class="stat-card p-6">
                    <h3 style="font-size: 18px; font-weight: 600; color: white; margin-bottom: 16px;">Quick Actions</h3>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <button onclick="window.location.href='/admin/movies'" class="w-full text-left bg-transparent border border-gray-600 text-white px-4 py-2 rounded hover:bg-dark-700 transition-colors flex items-center gap-2">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                            </svg>
                            <span>Manage Movies</span>
                        </button>
                        <button onclick="window.location.href='/admin/tvshows'" class="w-full text-left bg-transparent border border-gray-600 text-white px-4 py-2 rounded hover:bg-dark-700 transition-colors flex items-center gap-2">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span>Manage TV Shows</span>
                        </button>
                        <button onclick="window.location.href='/admin/featured'" class="w-full text-left bg-transparent border border-gray-600 text-white px-4 py-2 rounded hover:bg-dark-700 transition-colors flex items-center gap-2">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                            <span>Featured Content</span>
                        </button>
                        <button onclick="window.location.href='/admin/admins'" class="w-full text-left bg-transparent border border-gray-600 text-white px-4 py-2 rounded hover:bg-dark-700 transition-colors flex items-center gap-2">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span>Manage Admins</span>
                        </button>
                        <button onclick="window.location.href='/admin/requests'" class="w-full text-left bg-transparent border border-gray-600 text-white px-4 py-2 rounded hover:bg-dark-700 transition-colors flex items-center gap-2">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Content Requests</span>
                            <span id="requests-badge" style="margin-left: auto; background-color: #fbbf24; color: #000; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;">0</span>
                        </button>
                        <button onclick="window.location.href='/admin/reports'" class="w-full text-left bg-transparent border border-gray-600 text-white px-4 py-2 rounded hover:bg-dark-700 transition-colors flex items-center gap-2">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span>Embed Reports</span>
                            <span id="reports-badge" style="margin-left: auto; background-color: #ef4444; color: #fff; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;">0</span>
                        </button>
                        <button onclick="window.location.href='/admin/comments'" class="w-full text-left bg-transparent border border-gray-600 text-white px-4 py-2 rounded hover:bg-dark-700 transition-colors flex items-center gap-2">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <span>Manage Comments</span>
                            <span id="comments-badge" style="margin-left: auto; background-color: #3b82f6; color: #fff; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;">0</span>
                        </button>
                        <button onclick="window.location.href='/admin/ads'" class="w-full text-left bg-transparent border border-gray-600 text-white px-4 py-2 rounded hover:bg-dark-700 transition-colors flex items-center gap-2">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                            </svg>
                            <span>Ads Management</span>
                        </button>
                    </div>
                </div>

                <!-- Recent Movies -->
                <div class="stat-card p-6">
                    <h3 style="font-size: 18px; font-weight: 600; color: white; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px; color: #60a5fa;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Recent Movies
                    </h3>
                    <div id="recent-movies" style="display: flex; flex-direction: column; gap: 8px;"></div>
                </div>
            </div>

            <!-- Recent Activity Row 2 -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Recent TV Shows -->
                <div class="stat-card p-6">
                    <h3 style="font-size: 18px; font-weight: 600; color: white; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px; color: #a78bfa;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Recent TV Shows
                    </h3>
                    <div id="recent-tvshows" style="display: flex; flex-direction: column; gap: 8px;"></div>
                </div>

                <!-- Recent Requests -->
                <div class="stat-card p-6">
                    <h3 style="font-size: 18px; font-weight: 600; color: white; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; justify-content: space-between;">
                        <span style="display: flex; align-items: center; gap: 8px;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px; color: #fbbf24;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Recent Requests
                        </span>
                        <a href="/admin/requests" style="color: #60a5fa; font-size: 12px; text-decoration: none;">View All</a>
                    </h3>
                    <div id="recent-requests" style="display: flex; flex-direction: column; gap: 8px;"></div>
                </div>

                <!-- Recent Reports -->
                <div class="stat-card p-6">
                    <h3 style="font-size: 18px; font-weight: 600; color: white; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; justify-content: space-between;">
                        <span style="display: flex; align-items: center; gap: 8px;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px; color: #ef4444;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            Recent Reports
                        </span>
                        <a href="/admin/reports" style="color: #60a5fa; font-size: 12px; text-decoration: none;">View All</a>
                    </h3>
                    <div id="recent-reports" style="display: flex; flex-direction: column; gap: 8px;"></div>
                </div>
            </div>

            <!-- Analytics and Leaderboard -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div id="view-analytics-widget" class="stat-card p-6"></div>
                <div id="leaderboard-widget" class="stat-card p-6"></div>
            </div>
        </div>
    </main>
</div>

@push('scripts')
<script>
    let stats = null;
    let admin = null;

    async function fetchAdminProfile() {
        try {
            const token = localStorage.getItem('adminAccessToken');
            if (!token) {
                window.location.href = '/admin/login';
                return;
            }
            const response = await fetch(`${API_BASE_URL}/admin/auth/profile`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (response.ok) {
                const data = await response.json();
                admin = data.data.admin;
                document.getElementById('admin-name').textContent = `Welcome, ${admin.name || admin.username || 'Admin'}`;
            } else if (response.status === 401) {
                localStorage.removeItem('adminAccessToken');
                localStorage.removeItem('adminRefreshToken');
                localStorage.removeItem('adminUser');
                window.location.href = '/admin/login';
            }
        } catch (error) {
            console.error('Error fetching admin profile:', error);
        }
    }

    async function fetchStats(isRefresh = false) {
        try {
            if (isRefresh) {
                document.getElementById('refresh-icon').style.animation = 'spin 1s linear infinite';
                document.getElementById('refresh-text').textContent = 'Refreshing...';
            }
            
            const token = localStorage.getItem('adminAccessToken');
            const response = await fetch(`${API_BASE_URL}/admin/dashboard/stats`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });

            if (response.ok) {
                const data = await response.json();
                stats = data.data;
                renderDashboard();
                document.getElementById('last-updated').textContent = `• Last updated: ${new Date().toLocaleTimeString()}`;
            } else if (response.status === 401) {
                window.location.href = '/admin/login';
            }
        } catch (error) {
            console.error('Error fetching stats:', error);
        } finally {
            if (isRefresh) {
                document.getElementById('refresh-icon').style.animation = '';
                document.getElementById('refresh-text').textContent = 'Refresh Stats';
            }
        }
    }

    function renderDashboard() {
        if (!stats) return;

        // Main Stats
        const mainStatsHtml = `
            <div class="stat-card p-6">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 14px; font-weight: 500; color: #9ca3af;">Total Admins</p>
                        <p style="font-size: 30px; font-weight: bold; color: white; margin-top: 8px;">${stats.admin?.total || 0}</p>
                        <p style="font-size: 14px; color: #4ade80; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 12px; height: 12px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            ${stats.admin?.active || 0} active
                        </p>
                    </div>
                    <div style="padding: 12px; background-color: #dc2626; border-radius: 8px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 32px; height: 32px; color: white;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="stat-card p-6">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 14px; font-weight: 500; color: #9ca3af;">Total Movies</p>
                        <p style="font-size: 30px; font-weight: bold; color: white; margin-top: 8px;">${stats.movies?.total || 0}</p>
                        <p style="font-size: 14px; color: #4ade80; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 12px; height: 12px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            ${stats.movies?.active || 0} active
                        </p>
                    </div>
                    <div style="padding: 12px; background-color: #2563eb; border-radius: 8px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 32px; height: 32px; color: white;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="stat-card p-6">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 14px; font-weight: 500; color: #9ca3af;">Total TV Shows</p>
                        <p style="font-size: 30px; font-weight: bold; color: white; margin-top: 8px;">${stats.tvShows?.total || 0}</p>
                        <p style="font-size: 14px; color: #4ade80; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 12px; height: 12px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            ${stats.tvShows?.active || 0} active
                        </p>
                    </div>
                    <div style="padding: 12px; background-color: #9333ea; border-radius: 8px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 32px; height: 32px; color: white;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="stat-card p-6">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 14px; font-weight: 500; color: #9ca3af;">Pending Requests</p>
                        <p style="font-size: 30px; font-weight: bold; color: white; margin-top: 8px;">${stats.requests?.pending || 0}</p>
                        <p style="font-size: 14px; color: #fbbf24; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 12px; height: 12px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            ${stats.requests?.total || 0} total
                        </p>
                    </div>
                    <div style="padding: 12px; background-color: #fbbf24; border-radius: 8px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 32px; height: 32px; color: #000;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="stat-card p-6">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 14px; font-weight: 500; color: #9ca3af;">Pending Reports</p>
                        <p style="font-size: 30px; font-weight: bold; color: white; margin-top: 8px;">${stats.reports?.pending || 0}</p>
                        <p style="font-size: 14px; color: #ef4444; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 12px; height: 12px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            ${stats.reports?.total || 0} total
                        </p>
                    </div>
                    <div style="padding: 12px; background-color: #ef4444; border-radius: 8px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 32px; height: 32px; color: white;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        `;
        document.getElementById('main-stats-grid').innerHTML = mainStatsHtml;
        
        // Update badges
        if (stats.requests?.pending) {
            document.getElementById('requests-badge').textContent = stats.requests.pending;
            document.getElementById('requests-badge').style.display = 'inline-block';
        } else {
            document.getElementById('requests-badge').style.display = 'none';
        }
        
        if (stats.reports?.pending) {
            document.getElementById('reports-badge').textContent = stats.reports.pending;
            document.getElementById('reports-badge').style.display = 'inline-block';
        } else {
            document.getElementById('reports-badge').style.display = 'none';
        }
        
        if (stats.comments?.pending) {
            document.getElementById('comments-badge').textContent = stats.comments.pending;
            document.getElementById('comments-badge').style.display = 'inline-block';
        } else {
            const commentsBadge = document.getElementById('comments-badge');
            if (commentsBadge) {
                commentsBadge.style.display = 'none';
            }
        }

        // Detailed Stats (Movie, TV Show, Content Overview)
        const detailedStatsHtml = `
            <div class="stat-card p-6">
                <h3 style="font-size: 18px; font-weight: 600; color: white; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px; color: #60a5fa;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                    </svg>
                    Movie Statistics
                </h3>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    ${createStatRow('Active', stats.movies?.active || 0, 'green')}
                    ${createStatRow('Inactive', stats.movies?.inactive || 0, 'red')}
                    ${createStatRow('Pending', stats.movies?.pending || 0, 'yellow')}
                    ${createStatRow('Featured', stats.movies?.featured || 0, 'yellow')}
                    ${createStatRow('Embeds', stats.movies?.embeds || 0, 'blue')}
                    ${createStatRow('Downloads', stats.movies?.downloads || 0, 'green')}
                </div>
            </div>
            <div class="stat-card p-6">
                <h3 style="font-size: 18px; font-weight: 600; color: white; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px; color: #a78bfa;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    TV Show Statistics
                </h3>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    ${createStatRow('Active', stats.tvShows?.active || 0, 'green')}
                    ${createStatRow('Inactive', stats.tvShows?.inactive || 0, 'red')}
                    ${createStatRow('Pending', stats.tvShows?.pending || 0, 'yellow')}
                    ${createStatRow('Featured', stats.tvShows?.featured || 0, 'yellow')}
                    ${createStatRow('Embeds', stats.tvShows?.embeds || 0, 'purple')}
                    ${createStatRow('Downloads', stats.tvShows?.downloads || 0, 'green')}
                </div>
            </div>
            <div class="stat-card p-6">
                <h3 style="font-size: 18px; font-weight: 600; color: white; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px; color: #4ade80;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    Content Overview
                </h3>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    ${createStatRow('Total Content', (stats.movies?.total || 0) + (stats.tvShows?.total || 0), null)}
                    ${createStatRow('Active Content', (stats.movies?.active || 0) + (stats.tvShows?.active || 0), null)}
                    ${createStatRow('Featured Content', (stats.movies?.featured || 0) + (stats.tvShows?.featured || 0), null)}
                    ${createStatRow('Total Seasons', stats.tvShows?.seasons || 0, null)}
                    ${createStatRow('Total Episodes', stats.tvShows?.episodes || 0, null)}
                    ${createStatRow('Total Embeds', (stats.movies?.embeds || 0) + (stats.tvShows?.embeds || 0), null)}
                </div>
            </div>
            <div class="stat-card p-6">
                <h3 style="font-size: 18px; font-weight: 600; color: white; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px; color: #fbbf24;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Content Requests
                </h3>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    ${createStatRow('Total Requests', stats.requests?.total || 0, null)}
                    ${createStatRow('Pending', stats.requests?.pending || 0, 'yellow')}
                    ${createStatRow('Approved', stats.requests?.approved || 0, 'green')}
                    ${createStatRow('Completed', stats.requests?.completed || 0, 'blue')}
                    ${createStatRow('Movies', stats.requests?.movies || 0, null)}
                    ${createStatRow('TV Shows', stats.requests?.tvshows || 0, null)}
                </div>
            </div>
            <div class="stat-card p-6">
                <h3 style="font-size: 18px; font-weight: 600; color: white; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px; color: #ef4444;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    Embed Reports
                </h3>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    ${createStatRow('Total Reports', stats.reports?.total || 0, null)}
                    ${createStatRow('Pending', stats.reports?.pending || 0, 'yellow')}
                    ${createStatRow('Reviewed', stats.reports?.reviewed || 0, 'blue')}
                    ${createStatRow('Fixed', stats.reports?.fixed || 0, 'green')}
                    ${createStatRow('Movies', stats.reports?.movies || 0, null)}
                    ${createStatRow('Episodes', stats.reports?.episodes || 0, null)}
                </div>
            </div>
        `;
        document.getElementById('detailed-stats-grid').innerHTML = detailedStatsHtml;

        // Recent Activity
        if (stats.recentActivity?.movies?.length > 0) {
            const recentMoviesHtml = stats.recentActivity.movies.map(movie => `
                <div onclick="window.location.href='/admin/movies/${movie.id}'" style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background-color: #2a2a2a; border-radius: 8px; cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#3a3a3a'" onmouseout="this.style.backgroundColor='#2a2a2a'">
                    <div style="flex: 1;">
                        <p style="color: white; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${movie.title}</p>
                        <p style="color: #9ca3af; font-size: 14px;">
                            ${movie.release_date ? new Date(movie.release_date).getFullYear() : 'N/A'}
                        </p>
                    </div>
                    <div style="display: flex; align-items: center;">
                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; ${getStatusBadgeStyle(movie.status)}">
                            ${movie.status}
                        </span>
                    </div>
                </div>
            `).join('');
            document.getElementById('recent-movies').innerHTML = recentMoviesHtml || '<p style="color: #9ca3af; text-align: center; padding: 16px;">No recent movies</p>';
        } else {
            document.getElementById('recent-movies').innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 16px;">No recent movies</p>';
        }

        if (stats.recentActivity?.tvShows?.length > 0) {
            const recentTVShowsHtml = stats.recentActivity.tvShows.map(show => `
                <div onclick="window.location.href='/admin/tvshows/${show.id}'" style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background-color: #2a2a2a; border-radius: 8px; cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#3a3a3a'" onmouseout="this.style.backgroundColor='#2a2a2a'">
                    <div style="flex: 1;">
                        <p style="color: white; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${show.name}</p>
                        <p style="color: #9ca3af; font-size: 14px;">
                            ${show.first_air_date ? new Date(show.first_air_date).getFullYear() : 'N/A'}
                        </p>
                    </div>
                    <div style="display: flex; align-items: center;">
                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; ${getStatusBadgeStyle(show.status)}">
                            ${show.status}
                        </span>
                    </div>
                </div>
            `).join('');
            document.getElementById('recent-tvshows').innerHTML = recentTVShowsHtml || '<p style="color: #9ca3af; text-align: center; padding: 16px;">No recent TV shows</p>';
        } else {
            document.getElementById('recent-tvshows').innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 16px;">No recent TV shows</p>';
        }

        // Recent Requests
        if (stats.recentActivity?.requests?.length > 0) {
            const recentRequestsHtml = stats.recentActivity.requests.map(request => {
                const statusColors = {
                    pending: { bg: '#854d0e', text: '#fde047' },
                    approved: { bg: '#166534', text: '#86efac' },
                    rejected: { bg: '#991b1b', text: '#fca5a5' },
                    completed: { bg: '#1e3a8a', text: '#93c5fd' }
                };
                const statusColor = statusColors[request.status] || { bg: '#374151', text: '#d1d5db' };
                const typeLabel = request.type === 'movie' ? 'Movie' : 'TV Show';
                return `
                    <div onclick="window.location.href='/admin/requests'" style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background-color: #2a2a2a; border-radius: 8px; cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#3a3a3a'" onmouseout="this.style.backgroundColor='#2a2a2a'">
                        <div style="flex: 1; min-width: 0;">
                            <p style="color: white; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 14px;">${escapeHtml(request.title)}</p>
                            <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                                <span style="color: #9ca3af; font-size: 12px;">${typeLabel}</span>
                                ${request.request_count > 1 ? `<span style="color: #fbbf24; font-size: 12px;">• ${request.request_count}x</span>` : ''}
                            </div>
                        </div>
                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 500; background-color: ${statusColor.bg}; color: ${statusColor.text}; margin-left: 8px;">
                            ${request.status}
                        </span>
                    </div>
                `;
            }).join('');
            document.getElementById('recent-requests').innerHTML = recentRequestsHtml;
        } else {
            document.getElementById('recent-requests').innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 16px; font-size: 14px;">No recent requests</p>';
        }

        // Recent Reports
        if (stats.recentActivity?.reports?.length > 0) {
            const recentReportsHtml = stats.recentActivity.reports.map(report => {
                const statusColors = {
                    pending: { bg: '#854d0e', text: '#fde047' },
                    reviewed: { bg: '#1e3a8a', text: '#93c5fd' },
                    fixed: { bg: '#166534', text: '#86efac' },
                    dismissed: { bg: '#374151', text: '#d1d5db' }
                };
                const statusColor = statusColors[report.status] || { bg: '#374151', text: '#d1d5db' };
                const contentTypeLabel = report.content_type === 'movie' ? 'Movie' : 'Episode';
                const reportTypeLabel = report.report_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                return `
                    <div onclick="window.location.href='/admin/reports'" style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background-color: #2a2a2a; border-radius: 8px; cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#3a3a3a'" onmouseout="this.style.backgroundColor='#2a2a2a'">
                        <div style="flex: 1; min-width: 0;">
                            <p style="color: white; font-weight: 500; font-size: 14px;">${contentTypeLabel} #${report.content_id}</p>
                            <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                                <span style="color: #9ca3af; font-size: 12px;">${reportTypeLabel}</span>
                                ${report.report_count > 1 ? `<span style="color: #ef4444; font-size: 12px;">• ${report.report_count}x</span>` : ''}
                            </div>
                        </div>
                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 500; background-color: ${statusColor.bg}; color: ${statusColor.text}; margin-left: 8px;">
                            ${report.status}
                        </span>
                    </div>
                `;
            }).join('');
            document.getElementById('recent-reports').innerHTML = recentReportsHtml;
        } else {
            document.getElementById('recent-reports').innerHTML = '<p style="color: #9ca3af; text-align: center; padding: 16px; font-size: 14px;">No recent reports</p>';
        }

        // Load widgets
        loadViewAnalyticsWidget();
        loadLeaderboardWidget();

        // Show content, hide loading
        document.getElementById('dashboard-content').style.display = 'block';
        document.getElementById('loading-state').style.display = 'none';
    }

    function createStatRow(label, value, color) {
        const colors = {
            green: { icon: '#4ade80', text: 'text-green-400' },
            red: { icon: '#f87171', text: 'text-red-400' },
            yellow: { icon: '#fbbf24', text: 'text-yellow-400' },
            blue: { icon: '#60a5fa', text: 'text-blue-400' },
            purple: { icon: '#a78bfa', text: 'text-purple-400' }
        };
        const colorData = color ? colors[color] : { icon: '#9ca3af', text: 'text-gray-400' };
        
        return `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px; background-color: #2a2a2a; border-radius: 6px;">
                <span style="color: #d1d5db; display: flex; align-items: center; gap: 8px;">
                    ${color ? `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; color: ${colorData.icon};">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>` : ''}
                    ${label}
                </span>
                <span style="color: white; font-weight: 600;">${value}</span>
            </div>
        `;
    }

    function getStatusBadgeStyle(status) {
        const styles = {
            active: 'background-color: #166534; color: #86efac;',
            inactive: 'background-color: #991b1b; color: #fca5a5;',
            pending: 'background-color: #854d0e; color: #fde047;'
        };
        return styles[status] || 'background-color: #374151; color: #d1d5db;';
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    async function loadViewAnalyticsWidget() {
        try {
            const token = localStorage.getItem('adminAccessToken');
            const response = await fetch(`${API_BASE_URL}/admin/analytics/views`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (response.ok) {
                const data = await response.json();
                const analytics = data.data;
                document.getElementById('view-analytics-widget').innerHTML = `
                    <h3 style="font-size: 18px; font-weight: 600; color: white; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px; color: #60a5fa;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        View Analytics
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        ${createAnalyticsStat('Total Views', analytics.total.combined, analytics.total.movies, analytics.total.tvShows, '#60a5fa', '#2563eb')}
                        ${createAnalyticsStat('Today', analytics.today.combined, analytics.today.movies, analytics.today.tvShows, '#4ade80', '#22c55e')}
                        ${createAnalyticsStat('This Week', analytics.week.combined, analytics.week.movies, analytics.week.tvShows, '#fbbf24', '#f59e0b')}
                        ${createAnalyticsStat('This Month', analytics.month.combined, analytics.month.movies, analytics.month.tvShows, '#a78bfa', '#8b5cf6')}
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading view analytics:', error);
        }
    }

    function createAnalyticsStat(label, total, movies, tvShows, color, bgColor) {
        return `
            <div style="background-color: #2a2a2a; border-radius: 8px; padding: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span style="color: white; font-weight: 600; font-size: 14px;">${label}</span>
                    <span style="color: ${color}; font-weight: bold; font-size: 18px;">${total.toLocaleString()}</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <div style="background-color: #1a1a1a; border-radius: 6px; padding: 8px;">
                        <p style="color: #9ca3af; font-size: 12px; margin-bottom: 4px;">Movies</p>
                        <p style="color: white; font-weight: 600; font-size: 14px;">${movies.toLocaleString()}</p>
                    </div>
                    <div style="background-color: #1a1a1a; border-radius: 6px; padding: 8px;">
                        <p style="color: #9ca3af; font-size: 12px; margin-bottom: 4px;">TV Shows</p>
                        <p style="color: white; font-weight: 600; font-size: 14px;">${tvShows.toLocaleString()}</p>
                    </div>
                </div>
            </div>
        `;
    }

    async function loadLeaderboardWidget() {
        try {
            const token = localStorage.getItem('adminAccessToken');
            const response = await fetch(`${API_BASE_URL}/admin/leaderboard/overview?limit=5`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (response.ok) {
                const data = await response.json();
                const leaderboard = data.data;
                const period = 'week';
                const movies = leaderboard.movies[period] || [];
                const tvShows = leaderboard.tvShows[period] || [];
                
                document.getElementById('leaderboard-widget').innerHTML = `
                    <h3 style="font-size: 18px; font-weight: 600; color: white; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px; color: #fbbf24;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        Trending Content
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        <div>
                            <h4 style="color: white; font-weight: 600; font-size: 14px; margin-bottom: 12px;">🎬 Top Movies</h4>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                ${movies.length > 0 ? movies.map((movie, index) => `
                                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px; background-color: #2a2a2a; border-radius: 6px;">
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <span style="color: #fbbf24; font-weight: bold; font-size: 14px; width: 24px;">#${index + 1}</span>
                                            ${movie.poster_path ? `<img src="https://image.tmdb.org/t/p/w92${movie.poster_path}" alt="${movie.title}" style="width: 40px; height: 56px; object-fit: cover; border-radius: 4px;">` : ''}
                                            <div style="flex: 1; min-width: 0;">
                                                <p style="color: white; font-size: 14px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${movie.title}</p>
                                                <p style="color: #9ca3af; font-size: 12px;">⭐ ${movie.vote_average}</p>
                                            </div>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 4px; color: #dc2626; font-size: 14px;">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 12px; height: 12px;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            <span style="font-weight: 500;">${movie.view_count || 0}</span>
                                        </div>
                                    </div>
                                `).join('') : '<p style="color: #9ca3af; font-size: 14px; text-align: center; padding: 16px;">No data available</p>'}
                            </div>
                        </div>
                        <div>
                            <h4 style="color: white; font-weight: 600; font-size: 14px; margin-bottom: 12px;">📺 Top TV Shows</h4>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                ${tvShows.length > 0 ? tvShows.map((show, index) => `
                                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px; background-color: #2a2a2a; border-radius: 6px;">
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <span style="color: #fbbf24; font-weight: bold; font-size: 14px; width: 24px;">#${index + 1}</span>
                                            ${show.poster_path ? `<img src="https://image.tmdb.org/t/p/w92${show.poster_path}" alt="${show.name}" style="width: 40px; height: 56px; object-fit: cover; border-radius: 4px;">` : ''}
                                            <div style="flex: 1; min-width: 0;">
                                                <p style="color: white; font-size: 14px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${show.name}</p>
                                                <p style="color: #9ca3af; font-size: 12px;">⭐ ${show.vote_average}</p>
                                            </div>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 4px; color: #dc2626; font-size: 14px;">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 12px; height: 12px;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            <span style="font-weight: 500;">${show.view_count || 0}</span>
                                        </div>
                                    </div>
                                `).join('') : '<p style="color: #9ca3af; font-size: 14px; text-align: center; padding: 16px;">No data available</p>'}
                            </div>
                        </div>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading leaderboard:', error);
        }
    }

    function refreshStats() {
        fetchStats(true);
    }

    async function handleLogout() {
        try {
            const token = localStorage.getItem('adminAccessToken');
            await fetch(`${API_BASE_URL}/admin/auth/logout`, {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}` }
            });
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            localStorage.removeItem('adminAccessToken');
            localStorage.removeItem('adminRefreshToken');
            localStorage.removeItem('adminUser');
            window.location.href = '/admin/login';
        }
    }

    // Auto-refresh stats every 30 seconds
    let refreshInterval;
    function startAutoRefresh() {
        refreshInterval = setInterval(() => {
            fetchStats(false);
        }, 30000);
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', async () => {
        document.getElementById('loading-state').style.display = 'flex';
        document.getElementById('dashboard-content').style.display = 'none';
        
        await fetchAdminProfile();
        await fetchStats();
        
        startAutoRefresh();
    });
</script>
@endpush
@endsection

