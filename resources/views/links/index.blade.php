{{-- resources/views/links/index.blade.php --}}
@extends('layouts.app')

@section('title', 'My Links - ZapLink')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-white">My Links</h1>
        <a href="{{ route('links.create') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
            + Create New Link
        </a>
    </div>
    
    @if($links->count() > 0)
        <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-white">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Short URL</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Original URL</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Clicks</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($links as $link)
                            <tr class="hover:bg-gray-700 transition">
                                <td class="px-6 py-4">
                                    <a href="{{ route('links.show', $link->short_code) }}" 
                                       class="text-blue-400 hover:text-blue-300">
                                        {{ $link->short_url }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-gray-300 max-w-xs truncate">
                                    {{ $link->original_url }}
                                </td>
                                <td class="px-6 py-4 text-center">{{ $link->clicks }}</td>
                                <td class="px-6 py-4">
                                    @if($link->is_active && !$link->is_expired)
                                        <span class="bg-green-500 text-white px-2 py-1 rounded text-xs">Active</span>
                                    @else
                                        <span class="bg-red-500 text-white px-2 py-1 rounded text-xs">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 space-x-2">
                                    <a href="{{ route('links.show', $link->short_code) }}" 
                                       class="text-blue-400 hover:text-blue-300">View</a>
                                    <form action="#" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-4">
            {{ $links->links() }}
        </div>
    @else
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-12 text-center">
            <div class="text-6xl mb-4">🔗</div>
            <h3 class="text-xl font-semibold text-white mb-2">No Links Yet</h3>
            <p class="text-gray-400 mb-4">Create your first short link and start tracking!</p>
            <a href="{{ route('links.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition inline-block">
                Create Your First Link
            </a>
        </div>
    @endif
</div>
@endsection