{{-- resources/views/links/index.blade.php --}}
@extends('layouts.app')

@section('title', 'My Links - ZapLink')

@section('content')
<div class="container mx-auto px-4 py-8 bg-white min-h-screen">
    <!-- Header with Stats -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">My Links</h1>
                <p class="text-gray-500 mt-1">Manage and track all your shortened URLs</p>
            </div>
            <a href="{{ route('links.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 transition shadow-sm hover:shadow-md flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create New Link
            </a>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500">Total Links</p>
                <p class="text-2xl font-bold text-gray-900">{{ $links->total() }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500">Total Clicks</p>
                <p class="text-2xl font-bold text-gray-900">{{ $links->sum('clicks') }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500">Active Links</p>
                <p class="text-2xl font-bold text-green-600">{{ $links->where('is_active', true)->count() }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500">Most Clicked</p>
                <p class="text-2xl font-bold text-blue-600">{{ $links->max('clicks') ?: 0 }}</p>
            </div>
        </div>
    </div>
    
    @if($links->count() > 0)
        <!-- Table -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-gray-900">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Short URL</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Original URL</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Clicks</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($links as $link)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('links.show', $link->short_code) }}" 
                                           class="text-blue-600 hover:text-blue-800 font-medium">
                                            {{ url($link->short_code) }}
                                        </a>
                                        <button onclick="copyToClipboard('{{ url($link->short_code) }}')" 
                                                class="text-gray-400 hover:text-gray-600 transition" 
                                                title="Copy to clipboard">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 max-w-xs truncate">
                                    <span title="{{ $link->original_url }}">{{ Str::limit($link->original_url, 50) }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-semibold text-gray-900">{{ number_format($link->clicks) }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($link->is_active && !$link->is_expired)
                                        <span class="inline-flex items-center gap-1 bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-medium">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-medium">
                                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('links.show', $link->short_code) }}" 
                                           class="text-blue-600 hover:text-blue-800 font-medium text-sm transition">
                                            View
                                        </a>
                                        <span class="text-gray-300">|</span>
                                        <button type="button" 
                                                onclick="openDeleteModal('{{ $link->short_code }}')"
                                                class="text-red-500 hover:text-red-700 transition" 
                                                title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
            <p class="text-sm text-gray-500">
                Showing <span class="font-medium">{{ $links->firstItem() ?? 0 }}</span> 
                to <span class="font-medium">{{ $links->lastItem() ?? 0 }}</span> 
                of <span class="font-medium">{{ $links->total() }}</span> links
            </p>
            {{ $links->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-gray-50 rounded-xl border border-gray-200 p-16 text-center shadow-sm">
            <div class="max-w-md mx-auto">
                <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No Links Yet</h3>
                <p class="text-gray-500 mb-6">Create your first short link and start tracking clicks in real-time.</p>
                <a href="{{ route('links.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg transition inline-flex items-center gap-2 shadow-sm hover:shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create Your First Link
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
    <!-- Modal Backdrop -->
    <div class="absolute inset-0 bg-gray-900/50" onclick="closeDeleteModal()"></div>
    
    <!-- Modal Content -->
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md">
        <div class="bg-white shadow-xl border border-gray-200">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900">Delete Link</h3>
                <button type="button" onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6 text-center">
                
                <p class="text-gray-700 mb-1">Are you sure you want to delete this link?</p>
                <p class="text-sm text-gray-500">This action cannot be undone.</p>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex justify-center gap-3 p-4 border-t border-gray-200">
                <button type="button" onclick="closeDeleteModal()" 
        class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-sm transition">
    Cancel
</button>
                <form id="deleteForm" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium transition">
                        Delete Link
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================
// COPY TO CLIPBOARD FUNCTION
// ============================================
function copyToClipboard(url) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            showCopyFeedback(event);
        });
    } else {
        const input = document.createElement('input');
        input.value = url;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        showCopyFeedback(event);
    }
}

function showCopyFeedback(event) {
    const button = event.target.closest('button');
    if (!button) return;
    
    const originalHtml = button.innerHTML;
    button.innerHTML = `
        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
    `;
    setTimeout(() => {
        button.innerHTML = originalHtml;
    }, 2000);
}

// ============================================
// DELETE MODAL FUNCTIONS
// ============================================
function openDeleteModal(shortCode) {
    const modal = document.getElementById('deleteModal');
    const deleteForm = document.getElementById('deleteForm');
    
    // Set the form action to the correct delete route
    deleteForm.action = '/links/' + shortCode;
    
    // Show the modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal when pressing Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDeleteModal();
    }
});

// Close modal when clicking outside (already handled by the backdrop)
</script>
