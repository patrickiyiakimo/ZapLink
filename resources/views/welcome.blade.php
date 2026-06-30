{{-- resources/views/welcome.blade.php --}}
@extends('layouts.app')

@section('title', 'ZapLink - Shorten Your URLs')

@section('fonts')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 flex items-center justify-center">
    <div class="container mx-auto px-4 py-16">
        <div class="text-center">
            <!-- Hero Section -->
            <h1 class="text-5xl md:text-6xl font-bold text-white mb-6">
                ⚡ ZapLink
                <span class="block text-2xl md:text-3xl text-blue-400 mt-2">
                    Shorten Your URLs in a Flash
                </span>
            </h1>
            
            <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
                Create short, memorable links that you can share anywhere.
                Track clicks, analyze traffic, and boost your engagement.
            </p>
            
            <!-- Flash Messages - Auto Dismissing -->
            @if(session('success'))
                <div id="success-banner" class="max-w-2xl mx-auto mb-6 bg-green-500/90 backdrop-blur-sm text-white p-4 rounded-lg shadow-lg border border-green-400 transform transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button onclick="dismissBanner('success-banner')" class="text-white hover:text-gray-200 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div id="error-banner" class="max-w-2xl mx-auto mb-6 bg-red-500/90 backdrop-blur-sm text-white p-4 rounded-lg shadow-lg border border-red-400 transform transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button onclick="dismissBanner('error-banner')" class="text-white hover:text-gray-200 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            <!-- URL Shortener Form -->
            <div class="max-w-2xl mx-auto bg-gray-800 rounded-lg shadow-xl p-6 border border-gray-700">
                <form action="{{ route('links.store') }}" method="POST" class="space-y-4" id="shortenForm">
                    @csrf
                    
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1 relative">
                            <input type="url" 
                                   name="original_url" 
                                   id="original_url"
                                   placeholder="Paste your long URL here..."
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 transition @error('original_url') border-red-500 @enderror"
                                   value="{{ old('original_url') }}"
                                   required>
                            @error('original_url')
                                <p class="text-red-400 text-sm mt-1 text-left">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <button type="submit" 
                                id="shortenBtn"
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-200 whitespace-nowrap">
                            Shorten URL
                        </button>
                    </div>
                    
                    <div class="flex flex-col md:flex-row gap-4 text-sm">
                        <div class="flex-1">
                            <label class="text-gray-400 block text-left mb-1">Custom Code (Optional)</label>
                            <input type="text" 
                                   name="custom_code" 
                                   id="custom_code"
                                   placeholder="my-custom-link"
                                   value="{{ old('custom_code') }}"
                                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-blue-500">
                            <p class="text-gray-500 text-xs mt-1 text-left" id="customCodeHint">4-20 characters (letters and numbers only)</p>
                        </div>
                        
                        <div class="flex-1">
                            <label class="text-gray-400 block text-left mb-1">Expires (Optional)</label>
                            <input type="datetime-local" 
                                   name="expires_at"
                                   value="{{ old('expires_at') }}"
                                   min="{{ now()->addDay()->format('Y-m-d\TH:i') }}"
                                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                        </div>
                    </div>
                </form>

                <!-- Validation Rules Info -->
                <div class="mt-4 pt-4 border-t border-gray-700">
                    <div class="flex flex-wrap gap-2 text-xs text-gray-400">
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Valid URLs only
                        </span>
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            No duplicate short URLs
                        </span>
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Cannot shorten ZapLink URLs
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Features Section -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16 max-w-5xl mx-auto">
                <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 hover:border-blue-500 transition duration-300">
                    <div class="text-4xl mb-4">🚀</div>
                    <h3 class="text-white font-semibold text-lg mb-2">Lightning Fast</h3>
                    <p class="text-gray-400">Create short links instantly with our optimized URL shortening engine.</p>
                </div>
                
                <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 hover:border-blue-500 transition duration-300">
                    <div class="text-4xl mb-4">📊</div>
                    <h3 class="text-white font-semibold text-lg mb-2">Real-time Analytics</h3>
                    <p class="text-gray-400">Track every click with detailed statistics and geographic data.</p>
                </div>
                
                <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 hover:border-blue-500 transition duration-300">
                    <div class="text-4xl mb-4">🔒</div>
                    <h3 class="text-white font-semibold text-lg mb-2">Secure & Reliable</h3>
                    <p class="text-gray-400">Your links are safe with built-in security and spam protection.</p>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="mt-16 flex justify-center space-x-8 text-white">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-400">10K+</div>
                    <div class="text-gray-400">Links Created</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-400">50K+</div>
                    <div class="text-gray-400">Clicks Tracked</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-400">99.9%</div>
                    <div class="text-gray-400">Uptime</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-dismiss banners after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    // Success banner auto-dismiss
    const successBanner = document.getElementById('success-banner');
    if (successBanner) {
        setTimeout(() => {
            dismissBanner('success-banner');
        }, 5000);
    }

    // Error banner auto-dismiss
    const errorBanner = document.getElementById('error-banner');
    if (errorBanner) {
        setTimeout(() => {
            dismissBanner('error-banner');
        }, 8000); // Error messages stay a bit longer
    }
});

// Function to dismiss banner with animation
function dismissBanner(id) {
    const banner = document.getElementById(id);
    if (banner) {
        banner.style.opacity = '0';
        banner.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            banner.style.display = 'none';
        }, 300);
    }
}

// Real-time URL validation
document.getElementById('original_url').addEventListener('input', function() {
    const url = this.value.trim();
    const errorMessage = document.querySelector('.text-red-400');
    
    if (url && !isValidUrl(url)) {
        this.classList.add('border-yellow-500');
        this.classList.remove('border-red-500', 'border-green-500');
    } else if (url && isValidUrl(url)) {
        this.classList.remove('border-yellow-500', 'border-red-500');
        this.classList.add('border-green-500');
    } else {
        this.classList.remove('border-yellow-500', 'border-red-500', 'border-green-500');
    }
});

// Custom code validation
document.getElementById('custom_code').addEventListener('input', function() {
    const code = this.value.trim();
    const hint = document.getElementById('customCodeHint');
    
    if (code && !/^[a-zA-Z0-9]+$/.test(code)) {
        hint.textContent = '❌ Only letters and numbers allowed';
        hint.classList.add('text-red-400');
        hint.classList.remove('text-gray-500');
        this.classList.add('border-red-500');
    } else if (code && (code.length < 4 || code.length > 20)) {
        hint.textContent = '❌ Must be 4-20 characters';
        hint.classList.add('text-red-400');
        hint.classList.remove('text-gray-500');
        this.classList.add('border-red-500');
    } else if (code) {
        hint.textContent = '✅ Valid custom code';
        hint.classList.remove('text-red-400', 'text-gray-500');
        hint.classList.add('text-green-400');
        this.classList.remove('border-red-500');
        this.classList.add('border-green-500');
    } else {
        hint.textContent = '4-20 characters (letters and numbers only)';
        hint.classList.remove('text-red-400', 'text-green-400');
        hint.classList.add('text-gray-500');
        this.classList.remove('border-red-500', 'border-green-500');
    }
});

// Helper function to validate URL
function isValidUrl(string) {
    try {
        const url = new URL(string);
        return url.protocol === 'http:' || url.protocol === 'https:';
    } catch (_) {
        return false;
    }
}

// Form submission with validation
document.getElementById('shortenForm').addEventListener('submit', function(e) {
    const urlInput = document.getElementById('original_url');
    const url = urlInput.value.trim();
    
    if (!url) {
        e.preventDefault();
        urlInput.classList.add('border-red-500');
        showTemporaryError('Please enter a URL to shorten.');
        return;
    }
    
    if (!isValidUrl(url)) {
        e.preventDefault();
        urlInput.classList.add('border-red-500');
        showTemporaryError('Please enter a valid URL (http:// or https://).');
        return;
    }
    
    // Disable button to prevent double submission
    const btn = document.getElementById('shortenBtn');
    btn.disabled = true;
    btn.textContent = 'Shortening...';
    btn.classList.add('opacity-50');
});

// Show temporary error message
function showTemporaryError(message) {
    // Check if error banner already exists
    let errorBanner = document.getElementById('temp-error');
    if (errorBanner) {
        errorBanner.remove();
    }
    
    // Create error banner
    const banner = document.createElement('div');
    banner.id = 'temp-error';
    banner.className = 'max-w-2xl mx-auto mb-4 bg-red-500/90 backdrop-blur-sm text-white p-4 rounded-lg shadow-lg border border-red-400 transform transition-all duration-300';
    banner.innerHTML = `
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>${message}</span>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    
    // Insert before the form
    const form = document.getElementById('shortenForm');
    form.parentNode.insertBefore(banner, form);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (banner) {
            banner.style.opacity = '0';
            banner.style.transform = 'translateY(-20px)';
            setTimeout(() => banner.remove(), 300);
        }
    }, 5000);
}

// Re-enable button on page load if there was an error
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('shortenBtn');
    btn.disabled = false;
    btn.textContent = 'Shorten URL';
    btn.classList.remove('opacity-50');
});

// Clear form fields on successful submission
@if(session('success'))
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('original_url').value = '';
        document.getElementById('custom_code').value = '';
    });
@endif
</script>

<style>
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    background: #1a1a1a;
}
::-webkit-scrollbar-thumb {
    background: #4a5568;
    border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
    background: #718096;
}

/* Banner animations */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

#success-banner, #error-banner, #temp-error {
    animation: slideDown 0.5s ease-out forwards;
}

/* Form input focus styles */
input:focus {
    outline: none;
    ring: 2px solid #3b82f6;
}
</style>
@endsection