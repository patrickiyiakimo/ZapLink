{{-- resources/views/links/index.blade.php --}}
@extends('layouts.app')

@section('title', 'My Links - ZapLink')

@section('content')
<div class="container mx-auto px-4 py-8 bg-white min-h-screen">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">My Links</h1>
        <a href="{{ route('links.create') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 transition">
            + Create New Link
        </a>
    </div>
    
    @if($links->count() > 0)
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-gray-900">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Short URL</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Original URL</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clicks</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($links as $link)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <a href="{{ route('links.show', $link->short_code) }}" 
                                       class="text-blue-600 hover:text-blue-800 font-medium">
                                        {{ url($link->short_code) }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-gray-600 max-w-xs truncate">
                                    {{ $link->original_url }}
                                </td>
                                <td class="px-6 py-4 text-center font-medium">{{ $link->clicks }}</td>
                                <td class="px-6 py-4">
                                    @if($link->is_active && !$link->is_expired)
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Active</span>
                                    @else
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 space-x-3">
                                    <a href="{{ route('links.show', $link->short_code) }}" 
                                       class="text-blue-600 hover:text-blue-800 font-medium">View</a>
                                    <form action="{{ route('links.destroy', $link->short_code) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 transition" title="Delete">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
    </svg>
</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-6">
            {{ $links->links() }}
        </div>
    @else
        <div class="bg-gray-50 border border-gray-200 p-12 text-center shadow-sm">
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Links Yet</h3>
            <p class="text-gray-600 mb-4">Create your first short link and start tracking!</p>
            <a href="{{ route('links.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 transition inline-block">
                Create Your First Link
            </a>
        </div>
    @endif
</div>
@endsection