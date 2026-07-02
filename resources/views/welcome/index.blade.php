{{-- resources/views/welcome/index.blade.php --}}
{{-- 
    Main Welcome Page for ZapLink
    This file orchestrates all the welcome page components
--}}
@extends('layouts.app')

@section('title', 'ZapLink - Shorten Your URLs')

@section('fonts')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
@endsection

@section('content')
<div class="min-h-screen bg-white">
    <div class="container mx-auto px-4 py-16">
        <div class="text-center">
            
            {{-- Hero Section --}}
            @include('welcome.partials.hero')
            
            {{-- URL Shortener Form --}}
            @include('welcome.partials.form')
            
            {{-- How It Works Section --}}
            @include('welcome.partials.how-it-works')
            
            {{-- Features Section --}}
            @include('welcome.partials.features')
            
        </div>
    </div>
</div>

{{-- JavaScript --}}
@include('welcome.partials.scripts')
@endsection