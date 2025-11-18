@extends('layouts.app')

@section('title', 'Request Content - Nazaara Box')

@section('seo_title', 'Request Movies & TV Shows - Nazaara Box')
@section('seo_description', 'Request your favorite movies, TV shows, and dramas. Let us know what content you want to see on Nazaara Box.')
@section('seo_type', 'website')
@section('seo_url', url('/request'))

@push('styles')
<style>
    .request-wrapper {
        padding-top: 64px; /* Mobile header height */
    }

    @media (min-width: 768px) {
        .request-wrapper {
            padding-top: 80px; /* Desktop header height */
        }
    }

    .request-hero {
        background: linear-gradient(135deg, rgba(220, 38, 38, 0.1) 0%, rgba(17, 24, 39, 0.9) 100%);
        padding: 60px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .request-card {
        background: rgba(31, 41, 55, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 32px;
        backdrop-filter: blur(10px);
    }

    .form-input {
        background: rgba(17, 24, 39, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        padding: 12px 16px;
        color: white;
        transition: all 0.3s;
    }

    .form-input:focus {
        outline: none;
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
    }

    .form-label {
        color: #d1d5db;
        font-weight: 500;
        margin-bottom: 8px;
        display: block;
    }

    .submit-btn {
        background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
        color: white;
        padding: 14px 32px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
    }

    .submit-btn:hover {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(220, 38, 38, 0.3);
    }

    .submit-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .success-message {
        background: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.3);
        color: #4ade80;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 24px;
        display: none;
    }

    .error-message {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #fca5a5;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 24px;
        display: none;
    }

    .info-box {
        background: rgba(59, 130, 246, 0.1);
        border: 1px solid rgba(59, 130, 246, 0.3);
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 24px;
    }

    .info-box p {
        color: #93c5fd;
        margin: 0;
        font-size: 14px;
    }

    .stat-card {
        background: rgba(31, 41, 55, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 20px;
        text-align: center;
    }

    .stat-number {
        font-size: 32px;
        font-weight: 700;
        color: #dc2626;
        margin-bottom: 8px;
    }

    .stat-label {
        color: #9ca3af;
        font-size: 14px;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-black request-wrapper">
    <!-- Hero Section -->
    <div class="request-hero">
        <div class="container mx-auto px-4 md:px-8 lg:px-16">
            <div class="text-center mb-8">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4">
                    Request Content
                </h1>
                <p class="text-gray-300 text-lg md:text-xl max-w-2xl mx-auto">
                    Can't find your favorite movie or TV show? Request it and we'll add it to our collection!
                </p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl mx-auto mb-8">
                <div class="stat-card">
                    <div class="stat-number" id="total-requests">0</div>
                    <div class="stat-label">Total Requests</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="pending-requests">0</div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="completed-requests">0</div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Form Section -->
    <div class="container mx-auto px-4 md:px-8 lg:px-16 py-12">
        <div class="max-w-2xl mx-auto">
            <!-- Success Message -->
            <div id="success-message" class="success-message">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Your request has been submitted successfully! Thank you for your suggestion.</span>
                </div>
            </div>

            <!-- Error Message -->
            <div id="error-message" class="error-message">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <span id="error-text">An error occurred. Please try again.</span>
                </div>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <p>
                    <strong>Note:</strong> We review all requests and prioritize popular content. 
                    While we can't guarantee that every request will be added, we do our best to fulfill as many as possible. 
                    You'll be notified if your requested content becomes available.
                </p>
            </div>

            <!-- Request Form -->
            <div class="request-card">
                <form id="request-form" onsubmit="submitRequest(event)">
                    <!-- Content Type -->
                    <div class="mb-6">
                        <label class="form-label">Content Type *</label>
                        <select 
                            id="content-type" 
                            name="type" 
                            class="form-input w-full" 
                            required
                        >
                            <option value="">Select content type</option>
                            <option value="movie">Movie</option>
                            <option value="tvshow">TV Show / Drama</option>
                        </select>
                    </div>

                    <!-- Title -->
                    <div class="mb-6">
                        <label class="form-label">Title *</label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            class="form-input w-full" 
                            placeholder="Enter the title of the movie or TV show"
                            required
                        />
                    </div>

                    <!-- Email -->
                    <div class="mb-6">
                        <label class="form-label">Email *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input w-full" 
                            placeholder="your@email.com"
                            required
                        />
                        <p class="text-gray-500 text-sm mt-2">
                            We'll notify you when your request is processed.
                        </p>
                    </div>

                    <!-- TMDB ID (Optional) - Commented out for now -->
                    {{-- <div class="mb-6">
                        <label class="form-label">TMDB ID (Optional)</label>
                        <input 
                            type="text" 
                            id="tmdb-id" 
                            name="tmdb_id" 
                            class="form-input w-full" 
                            placeholder="If you know the TMDB ID, enter it here"
                        />
                        <p class="text-gray-500 text-sm mt-2">
                            You can find TMDB ID on <a href="https://www.themoviedb.org/" target="_blank" class="text-blue-400 hover:text-blue-300">themoviedb.org</a>
                        </p>
                    </div> --}}

                    <!-- Year (Optional) - Commented out for now -->
                    {{-- <div class="mb-6">
                        <label class="form-label">Release Year (Optional)</label>
                        <input 
                            type="text" 
                            id="year" 
                            name="year" 
                            class="form-input w-full" 
                            placeholder="e.g., 2023"
                            maxlength="4"
                        />
                    </div> --}}

                    <!-- Description -->
                    <div class="mb-6">
                        <label class="form-label">Additional Details (Optional)</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="5" 
                            class="form-input w-full resize-none" 
                            placeholder="Any additional information about your request (e.g., specific season, language, quality preference, etc.)"
                        ></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-between">
                        <button 
                            type="submit" 
                            id="submit-btn"
                            class="submit-btn flex items-center space-x-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Submit Request</span>
                        </button>
                        
                        <button 
                            type="button" 
                            onclick="resetForm()"
                            class="text-gray-400 hover:text-white transition-colors"
                        >
                            Reset Form
                        </button>
                    </div>
                </form>
            </div>

            <!-- Recent Requests Section -->
            <div class="mt-12">
                <h2 class="text-2xl font-bold text-white mb-6">Recent Requests</h2>
                <div id="recent-requests" class="space-y-4">
                    <!-- Will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Load stats
    async function loadStats() {
        try {
            const response = await fetch(`${API_BASE_URL}/requests?per_page=1`);
            if (response.ok) {
                const result = await response.json();
                if (result.success && result.data) {
                    // Get total from pagination
                    const total = result.data.pagination?.total || 0;
                    document.getElementById('total-requests').textContent = total;
                    
                    // Fetch pending and completed separately
                    const pendingResponse = await fetch(`${API_BASE_URL}/requests?status=pending&per_page=1`);
                    const completedResponse = await fetch(`${API_BASE_URL}/requests?status=completed&per_page=1`);
                    
                    if (pendingResponse.ok) {
                        const pendingResult = await pendingResponse.json();
                        const pending = pendingResult.data?.pagination?.total || 0;
                        document.getElementById('pending-requests').textContent = pending;
                    }
                    
                    if (completedResponse.ok) {
                        const completedResult = await completedResponse.json();
                        const completed = completedResult.data?.pagination?.total || 0;
                        document.getElementById('completed-requests').textContent = completed;
                    }
                }
            }
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }

    // Load recent requests
    async function loadRecentRequests() {
        try {
            const response = await fetch(`${API_BASE_URL}/requests?per_page=10&sort_by=requested_at&sort_order=desc`);
            if (response.ok) {
                const result = await response.json();
                if (result.success && result.data && result.data.requests) {
                    const requests = result.data.requests;
                    const container = document.getElementById('recent-requests');
                    
                    if (requests.length === 0) {
                        container.innerHTML = '<p class="text-gray-400 text-center py-8">No requests yet. Be the first to request content!</p>';
                        return;
                    }
                    
                    container.innerHTML = requests.map(request => {
                        const statusColors = {
                            'pending': 'bg-yellow-600/20 text-yellow-400 border-yellow-600/50',
                            'approved': 'bg-blue-600/20 text-blue-400 border-blue-600/50',
                            'completed': 'bg-green-600/20 text-green-400 border-green-600/50',
                            'rejected': 'bg-red-600/20 text-red-400 border-red-600/50'
                        };
                        const statusColor = statusColors[request.status] || 'bg-gray-600/20 text-gray-400 border-gray-600/50';
                        
                        return `
                            <div class="bg-gray-900 border border-gray-800 rounded-lg p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1">
                                        <h3 class="text-white font-semibold mb-1">${escapeHtml(request.title)}</h3>
                                        <div class="flex items-center space-x-4 text-sm text-gray-400">
                                            <span class="capitalize">${request.type}</span>
                                            <span>•</span>
                                            <span>${new Date(request.requested_at).toLocaleDateString()}</span>
                                            ${request.request_count > 1 ? `<span>•</span><span>${request.request_count} requests</span>` : ''}
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium border ${statusColor} capitalize">
                                        ${request.status}
                                    </span>
                                </div>
                                ${request.description ? `<p class="text-gray-400 text-sm mt-2">${escapeHtml(request.description)}</p>` : ''}
                                ${request.admin_notes ? `
                                    <div class="mt-3 p-3 bg-blue-900/20 border border-blue-600/30 rounded-lg">
                                        <div class="flex items-start gap-2">
                                            <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <div class="flex-1">
                                                <p class="text-blue-300 font-semibold text-sm mb-1">Admin Response:</p>
                                                <p class="text-blue-200 text-sm">${escapeHtml(request.admin_notes)}</p>
                                            </div>
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                        `;
                    }).join('');
                }
            }
        } catch (error) {
            console.error('Error loading recent requests:', error);
            document.getElementById('recent-requests').innerHTML = '<p class="text-gray-400 text-center py-8">Failed to load recent requests.</p>';
        }
    }

    // Submit request
    async function submitRequest(event) {
        event.preventDefault();
        
        const form = event.target;
        const submitBtn = document.getElementById('submit-btn');
        const successMessage = document.getElementById('success-message');
        const errorMessage = document.getElementById('error-message');
        
        // Hide messages
        successMessage.style.display = 'none';
        errorMessage.style.display = 'none';
        
        // Get form data
        const formData = new FormData(form);
        const data = {
            type: formData.get('type'),
            title: formData.get('title'),
            email: formData.get('email'),
            description: formData.get('description') || null,
            tmdb_id: formData.get('tmdb_id') || null,
            year: formData.get('year') || null,
        };

        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Submitting...</span>
        `;

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
                // Show success message
                successMessage.style.display = 'block';
                
                // Reset form
                form.reset();
                
                // Reload stats and recent requests
                loadStats();
                loadRecentRequests();
                
                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                // Show error message
                const errorText = result.message || 'Failed to submit request. Please try again.';
                document.getElementById('error-text').textContent = errorText;
                errorMessage.style.display = 'block';
            }
        } catch (error) {
            console.error('Error submitting request:', error);
            document.getElementById('error-text').textContent = 'An error occurred. Please check your connection and try again.';
            errorMessage.style.display = 'block';
        } finally {
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Submit Request</span>
            `;
        }
    }

    // Reset form
    function resetForm() {
        document.getElementById('request-form').reset();
        document.getElementById('success-message').style.display = 'none';
        document.getElementById('error-message').style.display = 'none';
    }

    // Escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        loadStats();
        loadRecentRequests();
    });
</script>
@endpush
@endsection

