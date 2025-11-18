@extends('layouts.admin')

@section('title', 'Admin Login - Nazaara Box')

@section('content')
<div class="min-h-screen bg-dark-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-dark-800 border border-dark-700 rounded-lg shadow-xl">
            <div class="p-6 text-center">
                <h1 class="text-2xl font-bold text-white mb-2">
                    Nazaara Box Admin
                </h1>
                <p class="text-dark-400">
                    Sign in to access the admin panel
                </p>
            </div>
            <div class="p-6">
                <form id="login-form" class="space-y-4">
                    <div id="error-message" class="bg-red-900/20 border border-red-500 text-red-400 px-4 py-3 rounded-md" style="display: none;"></div>
                    
                    <div>
                        <label for="username" class="block text-sm font-medium text-white mb-2">
                            Username or Email
                        </label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            required
                            class="w-full bg-dark-700 text-white border border-dark-600 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-transparent"
                            placeholder="Enter username or email"
                        />
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-white mb-2">
                            Password
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            class="w-full bg-dark-700 text-white border border-dark-600 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-transparent"
                            placeholder="Enter password"
                        />
                    </div>
                    
                    <button
                        type="submit"
                        id="submit-btn"
                        class="w-full bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded transition-colors font-semibold"
                    >
                        <span id="submit-text">Sign In</span>
                        <span id="submit-spinner" class="spinner inline-block" style="display: none; width: 20px; height: 20px; border-width: 2px;"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('login-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submit-btn');
        const submitText = document.getElementById('submit-text');
        const submitSpinner = document.getElementById('submit-spinner');
        const errorMessage = document.getElementById('error-message');
        
        submitBtn.disabled = true;
        submitText.style.display = 'none';
        submitSpinner.style.display = 'inline-block';
        errorMessage.style.display = 'none';
        
        const formData = {
            username: document.getElementById('username').value,
            password: document.getElementById('password').value
        };
        
        try {
            const response = await fetch(`${API_BASE_URL}/admin/auth/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData),
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Store tokens
                if (data.data.tokens) {
                    localStorage.setItem('adminAccessToken', data.data.tokens.access_token);
                    localStorage.setItem('adminRefreshToken', data.data.tokens.refresh_token);
                }
                if (data.data.admin) {
                    localStorage.setItem('adminUser', JSON.stringify(data.data.admin));
                }
                
                // Redirect to dashboard
                window.location.href = '/admin/dashboard';
            } else {
                errorMessage.textContent = data.message || 'Login failed';
                errorMessage.style.display = 'block';
            }
        } catch (error) {
            console.error('Login error:', error);
            errorMessage.textContent = 'Network error. Please try again.';
            errorMessage.style.display = 'block';
        } finally {
            submitBtn.disabled = false;
            submitText.style.display = 'inline';
            submitSpinner.style.display = 'none';
        }
    });
</script>
@endpush
@endsection

