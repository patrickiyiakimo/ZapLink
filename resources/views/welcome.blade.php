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
            
            <!-- URL Shortener Form -->
            <div class="max-w-2xl mx-auto bg-gray-800 rounded-lg shadow-xl p-6 border border-gray-700">
                <!-- Results Container -->
                <div id="resultContainer" class="hidden mb-6">
                    <div class="bg-green-500/20 border border-green-400 text-green-300 p-4 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium">Your short URL is ready!</p>
                                <div class="flex items-center mt-2 space-x-2">
                                    <input type="text" 
                                           id="shortUrlResult" 
                                           class="flex-1 bg-gray-700 text-white px-4 py-2 rounded border border-gray-600" 
                                           readonly>
                                    <button onclick="copyResult()" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">
                                        Copy
                                    </button>
                                    <a href="#" id="resultLink" 
                                       class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded transition">
                                        View
                                    </a>
                                </div>
                            </div>
                            <button onclick="dismissResult()" class="ml-4 text-gray-400 hover:text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <form id="shortenForm" class="space-y-4">
                    @csrf
                    
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1 relative">
                            <input type="url" 
                                   name="original_url" 
                                   id="original_url"
                                   placeholder="Paste your long URL here..."
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 transition"
                                   required>
                            <div id="urlError" class="text-red-400 text-sm mt-1 text-left hidden"></div>
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
                                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-blue-500">
                            <p class="text-gray-500 text-xs mt-1 text-left" id="customCodeHint">4-20 characters (letters and numbers only)</p>
                        </div>
                        
                        <div class="flex-1">
                            <label class="text-gray-400 block text-left mb-1">Expires (Optional)</label>
                            <input type="datetime-local" 
                                   name="expires_at"
                                   id="expires_at"
                                   min="{{ now()->addDay()->format('Y-m-d\TH:i') }}"
                                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                        </div>
                    </div>
                </form>

                <!-- Loading Spinner -->
                <div id="loadingSpinner" class="hidden mt-4">
                    <div class="flex items-center justify-center space-x-2">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                        <span class="text-gray-300">Shortening your URL...</span>
                    </div>
                </div>

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
                    <div class="text-3xl font-bold text-blue-400" id="totalLinks">0</div>
                    <div class="text-gray-400">Links Created</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-400" id="totalClicks">0</div>
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
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('shortenForm');
    const submitBtn = document.getElementById('shortenBtn');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const resultContainer = document.getElementById('resultContainer');
    const urlError = document.getElementById('urlError');
    
    // Real-time URL validation
    document.getElementById('original_url').addEventListener('input', function() {
        const url = this.value.trim();
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

    // Form submission via AJAX
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const url = document.getElementById('original_url').value.trim();
        
        // Validate URL
        if (!url) {
            showError('Please enter a URL to shorten.');
            return;
        }
        
        if (!isValidUrl(url)) {
            showError('Please enter a valid URL (http:// or https://).');
            return;
        }
        
        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.textContent = 'Shortening...';
        submitBtn.classList.add('opacity-50');
        loadingSpinner.classList.remove('hidden');
        resultContainer.classList.add('hidden');
        urlError.classList.add('hidden');
        
        try {
            // Get CSRF token
            const token = document.querySelector('input[name="_token"]').value;
            
            // Get form data
            const formData = new FormData(form);
            
            // Make AJAX request
            const response = await fetch('{{ route("links.store") }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Show result
                showResult(data.link.short_url, data.link.short_code);
                
                // Clear form
                document.getElementById('original_url').value = '';
                document.getElementById('custom_code').value = '';
                document.getElementById('expires_at').value = '';
                
                // Update stats
                updateStats();
            } else {
                showError(data.message || 'Something went wrong.');
            }
        } catch (error) {
            showError('An error occurred. Please try again.');
            console.error('Error:', error);
        } finally {
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.textContent = 'Shorten URL';
            submitBtn.classList.remove('opacity-50');
            loadingSpinner.classList.add('hidden');
        }
    });
});

// Helper Functions
function isValidUrl(string) {
    try {
        const url = new URL(string);
        return url.protocol === 'http:' || url.protocol === 'https:';
    } catch (_) {
        return false;
    }
}

function showError(message) {
    const errorEl = document.getElementById('urlError');
    errorEl.textContent = message;
    errorEl.classList.remove('hidden');
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        errorEl.classList.add('hidden');
    }, 5000);
}

function showResult(shortUrl, shortCode) {
    const container = document.getElementById('resultContainer');
    const input = document.getElementById('shortUrlResult');
    const link = document.getElementById('resultLink');
    
    input.value = shortUrl;
    link.href = '/' + shortCode;
    
    container.classList.remove('hidden');
    container.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function copyResult() {
    const input = document.getElementById('shortUrlResult');
    input.select();
    document.execCommand('copy');
    
    const btn = event.target;
    const originalText = btn.textContent;
    btn.textContent = 'Copied!';
    btn.classList.add('bg-green-600');
    setTimeout(() => {
        btn.textContent = originalText;
        btn.classList.remove('bg-green-600');
    }, 2000);
}

function dismissResult() {
    const container = document.getElementById('resultContainer');
    container.classList.add('hidden');
}

async function updateStats() {
    try {
        const response = await fetch('/api/stats');
        const data = await response.json();
        if (data.success) {
            document.getElementById('totalLinks').textContent = data.total_links || 0;
            document.getElementById('totalClicks').textContent = data.total_clicks || 0;
        }
    } catch (error) {
        // Silently fail - stats are cosmetic
    }
}

// Initial stats load
document.addEventListener('DOMContentLoaded', updateStats);
</script>
@endsection