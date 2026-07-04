{{-- resources/views/links/expired.blade.php --}}
{{-- 
    Expired Link Page
    Displayed when a user tries to access an expired short link
--}}
@extends('layouts.app')

@section('title', 'Link Expired - ZapLink')

@section('content')
<div class="min-h-screen bg-white flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full text-center">
     
        {{-- Title --}}
        <h1 class="text-3xl font-bold text-gray-900 mb-3">
            This Link Has Expired
        </h1>
        
        <p class="text-gray-600 mb-2">
            The short URL you're trying to access is no longer active.
        </p>
        
        @if(isset($link) && $link->expires_at)
            <p class="text-sm text-gray-500 mb-6">
                This link expired on 
                <span class="font-medium text-gray-700">
                    {{ $link->expires_at->format('F j, Y') }}
                </span>
            </p>
        @endif

        {{-- Divider --}}
        <div class="border-t border-gray-200 my-6"></div>

        {{-- Original URL (if available) --}}
        @if(isset($link) && $link->original_url)
            <div class="bg-gray-50 border border-gray-200 p-4 mb-6 text-left">
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Original URL</p>
                <p class="text-sm text-gray-700 break-all">
                    {{ $link->original_url }}
                </p>
            </div>
        @endif

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('home') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 transition text-center">
                Go to Homepage
            </a>
            @auth
                <a href="{{ route('links.create') }}" 
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-3 transition text-center">
                    Create New Link
                </a>
            @endauth
        </div>
    </div>
</div>
@endsection