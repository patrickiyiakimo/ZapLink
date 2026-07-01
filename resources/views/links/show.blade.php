{{-- resources/views/links/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Link Details - ZapLink')

@section('content')
<div class="container mx-auto px-4 py-8 bg-white min-h-screen">
    <div class="max-w-4xl mx-auto">
        <!-- Link Details Card -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <h1 class="text-2xl font-bold text-gray-900">Link Details</h1>
                <div class="flex space-x-2">
                    @if($link->is_active && !$link->is_expired)
                        <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm">Active</span>
                    @else
                        <span class="bg-red-500 text-white px-3 py-1 rounded-full text-sm">Inactive</span>
                    @endif
                </div>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="text-gray-600 text-sm font-medium">Short URL</label>
                    <div class="flex items-center space-x-2 mt-1">
                        <input type="text" 
                               value="{{ url('/' . $link->short_code) }}" 
                               id="shortUrl" 
                               class="flex-1 bg-gray-50 text-gray-900 px-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               readonly>
                        <button onclick="copyToClipboard()" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">
                            Copy
                        </button>
                        <a href="{{ url('/' . $link->short_code) }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition inline-flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            Visit
                        </a>
                    </div>
                </div>
                
                <div>
                    <label class="text-gray-600 text-sm font-medium">Original URL</label>
                    <div class="flex items-center space-x-2 mt-1">
                        <div class="flex-1 bg-gray-50 text-gray-900 px-4 py-2 rounded border border-gray-300 break-all">
                            {{ $link->original_url }}
                        </div>
                        <a href="{{ $link->original_url }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded transition inline-flex items-center whitespace-nowrap">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            Open
                        </a>
                    </div>
                </div>
                
                @if($link->title)
                    <div>
                        <label class="text-gray-600 text-sm font-medium">Title</label>
                        <div class="bg-gray-50 text-gray-900 px-4 py-2 rounded border border-gray-300 mt-1">
                            {{ $link->title }}
                        </div>
                    </div>
                @endif
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="text-gray-600 text-sm font-medium">Created</label>
                        <div class="bg-gray-50 text-gray-900 px-4 py-2 rounded border border-gray-300 mt-1">
                            {{ $link->created_at->format('M d, Y') }}
                        </div>
                    </div>
                    <div>
                        <label class="text-gray-600 text-sm font-medium">Total Clicks</label>
                        <div class="bg-gray-50 text-gray-900 px-4 py-2 rounded border border-gray-300 text-center font-bold text-xl mt-1">
                            {{ $analytics['total_clicks'] ?? 0 }}
                        </div>
                    </div>
                    <div>
                        <label class="text-gray-600 text-sm font-medium">Expires</label>
                        <div class="bg-gray-50 text-gray-900 px-4 py-2 rounded border border-gray-300 mt-1">
                            {{ $link->expires_at ? $link->expires_at->format('M d, Y') : 'Never' }}
                        </div>
                    </div>
                    <div>
                        <label class="text-gray-600 text-sm font-medium">Unique Visitors</label>
                        <div class="bg-gray-50 text-gray-900 px-4 py-2 rounded border border-gray-300 text-center font-bold mt-1">
                            {{ $analytics['unique_visitors'] ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Analytics Section -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900 mb-4">📊 Analytics</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                    <div class="text-gray-600 text-sm">Total Clicks</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $analytics['total_clicks'] ?? 0 }}</div>
                </div>
                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                    <div class="text-gray-600 text-sm">Unique Visitors</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $analytics['unique_visitors'] ?? 0 }}</div>
                </div>
                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                    <div class="text-gray-600 text-sm">Last 24 Hours</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $analytics['last_24_hours'] ?? 0 }}</div>
                </div>
            </div>
            
            @if(!empty($analytics['top_referers']) && $analytics['top_referers']->count() > 0)
                <div>
                    <h3 class="text-gray-900 font-semibold mb-3">Top Referers</h3>
                    <div class="space-y-2">
                        @foreach($analytics['top_referers'] as $referer)
                            <div class="flex justify-between items-center bg-gray-50 px-4 py-2 rounded border border-gray-200">
                                <span class="text-gray-700">{{ $referer['referer'] ?: 'Direct' }}</span>
                                <span class="text-gray-900 font-semibold">{{ $referer['total'] }} clicks</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p class="text-4xl mb-2">📊</p>
                    <p>No visits recorded yet.</p>
                    <p class="text-sm mt-1">Start sharing your link to see analytics!</p>
                </div>
            @endif
        </div>
        
        <!-- Action Buttons -->
        <div class="mt-6 flex flex-wrap gap-4">
            <a href="{{ route('links.index') }}" 
               class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to My Links
            </a>
            
            <a href="{{ url('/' . $link->short_code) }}" 
               target="_blank" 
               rel="noopener noreferrer"
               class="inline-flex items-center text-green-600 hover:text-green-700 font-medium ml-auto">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
                Visit Short URL
            </a>
            
            <a href="{{ $link->original_url }}" 
               target="_blank" 
               rel="noopener noreferrer"
               class="inline-flex items-center text-gray-600 hover:text-gray-700 font-medium">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
                Visit Original URL
            </a>
        </div>
    </div>
</div>

<script>
function copyToClipboard() {
    const urlInput = document.getElementById('shortUrl');
    urlInput.select();
    urlInput.setSelectionRange(0, 99999); // For mobile devices
    navigator.clipboard.writeText(urlInput.value).then(function() {
        const button = event.target;
        const originalText = button.textContent;
        const originalClass = button.className;
        button.textContent = 'Copied! ✓';
        button.className = 'bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition';
        setTimeout(() => {
            button.textContent = originalText;
            button.className = originalClass;
        }, 2000);
    }).catch(function() {
        // Fallback for older browsers
        document.execCommand('copy');
        const button = event.target;
        const originalText = button.textContent;
        const originalClass = button.className;
        button.textContent = 'Copied! ✓';
        button.className = 'bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition';
        setTimeout(() => {
            button.textContent = originalText;
            button.className = originalClass;
        }, 2000);
    });
}
</script>
@endsection