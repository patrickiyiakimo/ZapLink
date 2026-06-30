{{-- resources/views/links/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Link Details - ZapLink')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Link Details Card -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <h1 class="text-2xl font-bold text-white">Link Details</h1>
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
                    <label class="text-gray-400 text-sm">Short URL</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" 
                               value="{{ $link->short_url }}" 
                               id="shortUrl" 
                               class="flex-1 bg-gray-700 text-white px-4 py-2 rounded border border-gray-600" 
                               readonly>
                        <button onclick="copyToClipboard()" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">
                            Copy
                        </button>
                    </div>
                </div>
                
                <div>
                    <label class="text-gray-400 text-sm">Original URL</label>
                    <div class="bg-gray-700 text-white px-4 py-2 rounded border border-gray-600 break-all">
                        {{ $link->original_url }}
                    </div>
                </div>
                
                @if($link->title)
                    <div>
                        <label class="text-gray-400 text-sm">Title</label>
                        <div class="bg-gray-700 text-white px-4 py-2 rounded border border-gray-600">
                            {{ $link->title }}
                        </div>
                    </div>
                @endif
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="text-gray-400 text-sm">Created</label>
                        <div class="bg-gray-700 text-white px-4 py-2 rounded border border-gray-600">
                            {{ $link->created_at->format('M d, Y') }}
                        </div>
                    </div>
                    <div>
                        <label class="text-gray-400 text-sm">Total Clicks</label>
                        <div class="bg-gray-700 text-white px-4 py-2 rounded border border-gray-600 text-center font-bold text-xl">
                            {{ $analytics['total_clicks'] ?? 0 }}
                        </div>
                    </div>
                    <div>
                        <label class="text-gray-400 text-sm">Expires</label>
                        <div class="bg-gray-700 text-white px-4 py-2 rounded border border-gray-600">
                            {{ $link->expires_at ? $link->expires_at->format('M d, Y') : 'Never' }}
                        </div>
                    </div>
                    <div>
                        <label class="text-gray-400 text-sm">Unique Visitors</label>
                        <div class="bg-gray-700 text-white px-4 py-2 rounded border border-gray-600 text-center font-bold">
                            {{ $analytics['unique_visitors'] ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Analytics Section -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">📊 Analytics</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-gray-700 p-4 rounded">
                    <div class="text-gray-400 text-sm">Total Clicks</div>
                    <div class="text-2xl font-bold text-white">{{ $analytics['total_clicks'] ?? 0 }}</div>
                </div>
                <div class="bg-gray-700 p-4 rounded">
                    <div class="text-gray-400 text-sm">Unique Visitors</div>
                    <div class="text-2xl font-bold text-white">{{ $analytics['unique_visitors'] ?? 0 }}</div>
                </div>
                <div class="bg-gray-700 p-4 rounded">
                    <div class="text-gray-400 text-sm">Last 24 Hours</div>
                    <div class="text-2xl font-bold text-white">{{ $analytics['last_24_hours'] ?? 0 }}</div>
                </div>
            </div>
            
            @if(!empty($analytics['top_referers']) && $analytics['top_referers']->count() > 0)
                <div>
                    <h3 class="text-white font-semibold mb-3">Top Referers</h3>
                    <div class="space-y-2">
                        @foreach($analytics['top_referers'] as $referer)
                            <div class="flex justify-between items-center bg-gray-700 px-4 py-2 rounded">
                                <span class="text-gray-300">{{ $referer['referer'] ?: 'Direct' }}</span>
                                <span class="text-white font-semibold">{{ $referer['total'] }} clicks</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-8 text-gray-400">
                    <p class="text-4xl mb-2">📊</p>
                    <p>No visits recorded yet.</p>
                    <p class="text-sm mt-1">Start sharing your link to see analytics!</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function copyToClipboard() {
    const urlInput = document.getElementById('shortUrl');
    urlInput.select();
    document.execCommand('copy');
    
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Copied!';
    setTimeout(() => {
        button.textContent = originalText;
    }, 2000);
}
</script>
@endsection