{{-- resources/views/links/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Create Short Link - ZapLink')

@section('content')
<div class="container mx-auto px-4 py-8 bg-white min-h-screen">
    <div class="max-w-3xl mx-auto">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Create Short Link</h1>
            <p class="text-gray-600 mt-2">Shorten your long URLs and start tracking clicks instantly</p>
        </div>

        <!-- Main Form Card -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-xl p-6 md:p-8">
            <form action="{{ route('links.store') }}" method="POST" id="createLinkForm">
                @csrf
                
                <!-- Original URL -->
                <div class="mb-6">
                    <label for="original_url" class="block text-sm font-medium text-gray-700 mb-2">
                        Long URL <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                        </div>
                        <input type="url" 
                               id="original_url" 
                               name="original_url" 
                               value="{{ old('original_url') }}"
                               placeholder="Paste your long URL here..."
                               class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('original_url') border-red-500 @enderror"
                               required
                               autofocus>
                    </div>
                    @error('original_url')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">
                        <span class="text-blue-600">Tip:</span> Paste any valid URL (http:// or https://)
                    </p>
                </div>

                <!-- Title (Optional) -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Title <span class="text-gray-500 text-xs">(Optional)</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title') }}"
                           placeholder="My Awesome Link - Give it a memorable name"
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror">
                    @error('title')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Custom Code & Expiration Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Custom Code -->
                    <div>
                        <label for="custom_code" class="block text-sm font-medium text-gray-700 mb-2">
                            Custom Code <span class="text-gray-500 text-xs">(Optional)</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm">{{ config('app.url') }}/</span>
                            </div>
                            <input type="text" 
                                   id="custom_code" 
                                   name="custom_code" 
                                   value="{{ old('custom_code') }}"
                                   placeholder="my-custom-link"
                                   class="w-full pl-32 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('custom_code') border-red-500 @enderror">
                        </div>
                        @error('custom_code')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500">
                             4-20 characters (letters and numbers only)
                        </p>
                    </div>

                    <!-- Expiration Date -->
                    <div>
                        <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">
                            Expiration Date <span class="text-gray-500 text-xs">(Optional)</span>
                        </label>
                        <input type="datetime-local" 
                               id="expires_at" 
                               name="expires_at" 
                               value="{{ old('expires_at') }}"
                               min="{{ now()->addDay()->format('Y-m-d\TH:i') }}"
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('expires_at') border-red-500 @enderror">
                        @error('expires_at')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500">
                            Leave blank for permanent link
                        </p>
                    </div>
                </div>

                <!-- Advanced Options (Collapsible) -->
                <div class="mb-6">
                    <button type="button" 
                            onclick="toggleAdvanced()" 
                            class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        Advanced Options
                    </button>
                    
                    <div id="advancedOptions" class="hidden mt-4 space-y-4">
                        <!-- Tags -->
                        <div>
                            <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">
                                Tags <span class="text-gray-500 text-xs">(Optional)</span>
                            </label>
                            <input type="text" 
                                   id="tags" 
                                   name="tags" 
                                   placeholder="marketing, social, campaign"
                                   class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="mt-2 text-sm text-gray-500">Separate tags with commas</p>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notes <span class="text-gray-500 text-xs">(Optional)</span>
                            </label>
                            <textarea id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="Add any notes about this link..."
                                      class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-gray-200">
                    <button type="submit" 
                            class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3 px-6 transition duration-200 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                        Create Short Link
                    </button>
                    
                    <a href="{{ route('links.index') }}" 
                       class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 transition duration-200 text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Quick Tips Card -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                <div class="flex items-start">
                    <div>
                        <h4 class="text-gray-900 font-semibold">Track Every Click</h4>
                        <p class="text-gray-600 text-sm mt-1">
                            Get detailed analytics including geographic data, referrers, and device information.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                <div class="flex items-start">
                    <div>
                        <h4 class="text-gray-900 font-semibold">Security First</h4>
                        <p class="text-gray-600 text-sm mt-1">
                            All URLs are validated and scanned for malicious content before shortening.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                <div class="flex items-start">
                    <div>
                        <h4 class="text-gray-900 font-semibold">Lightning Fast</h4>
                        <p class="text-gray-600 text-sm mt-1">
                            Shortened links are cached for instant redirects anywhere in the world.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                <div class="flex items-start">
                    <div>
                        <h4 class="text-gray-900 font-semibold">Never Expires</h4>
                        <p class="text-gray-600 text-sm mt-1">
                            Your links are permanent by default. Set expiration only when needed.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Links Preview (if authenticated) -->
        @auth
            @php
                $recentLinks = App\Models\Link::where('user_id', auth()->id())
                                              ->latest()
                                              ->limit(5)
                                              ->get();
            @endphp
            
            @if($recentLinks->count() > 0)
                <div class="mt-8 bg-gray-50 rounded-lg border border-gray-200 p-4">
                    <h4 class="text-gray-900 font-semibold mb-3">Your Recent Links</h4>
                    <div class="space-y-2">
                        @foreach($recentLinks as $recent)
                            <div class="flex justify-between items-center bg-white px-3 py-2 rounded border border-gray-200">
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('links.show', $recent->short_code) }}" 
                                       class="text-blue-600 hover:text-blue-700 text-sm truncate block">
                                        {{ $recent->short_url }}
                                    </a>
                                    <p class="text-gray-500 text-xs truncate">
                                        {{ Str::limit($recent->original_url, 50) }}
                                    </p>
                                </div>
                                <span class="text-gray-600 text-sm ml-4">
                                    {{ $recent->clicks }} clicks
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endauth
    </div>
</div>

<script>
function toggleAdvanced() {
    const options = document.getElementById('advancedOptions');
    const button = event.target.closest('button');
    const svg = button.querySelector('svg');
    
    if (options.classList.contains('hidden')) {
        options.classList.remove('hidden');
        svg.style.transform = 'rotate(180deg)';
        button.innerHTML = `
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transform: rotate(180deg)">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
            Hide Advanced Options
        `;
    } else {
        options.classList.add('hidden');
        svg.style.transform = 'rotate(0deg)';
        button.innerHTML = `
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
            Advanced Options
        `;
    }
}

// Form validation
document.getElementById('createLinkForm').addEventListener('submit', function(e) {
    const urlInput = document.getElementById('original_url');
    const url = urlInput.value.trim();
    
    if (!url) {
        e.preventDefault();
        urlInput.classList.add('border-red-500');
        alert('Please enter a URL to shorten.');
        return;
    }
    
    // Check if URL has protocol
    if (!url.match(/^https?:\/\//i)) {
        // Add https automatically
        urlInput.value = 'https://' + url;
    }
});

// Real-time URL validation
document.getElementById('original_url').addEventListener('input', function() {
    const url = this.value.trim();
    const isValid = url.match(/^https?:\/\/.+/i);
    
    if (url && !isValid) {
        this.classList.add('border-yellow-500');
        this.classList.remove('border-red-500');
    } else if (url && isValid) {
        this.classList.remove('border-yellow-500', 'border-red-500');
        this.classList.add('border-green-500');
    } else {
        this.classList.remove('border-yellow-500', 'border-red-500', 'border-green-500');
    }
});

// Auto-generate custom code from title
document.getElementById('title').addEventListener('input', function() {
    const customCodeInput = document.getElementById('custom_code');
    if (!customCodeInput.value) {
        const title = this.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '');
        
        if (title) {
            customCodeInput.placeholder = title;
        }
    }
});
</script>

<style>
/* Custom scrollbar for the page */
::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    background: #f3f4f6;
}
::-webkit-scrollbar-thumb {
    background: #9ca3af;
    border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
    background: #6b7280;
}
</style>
@endsection