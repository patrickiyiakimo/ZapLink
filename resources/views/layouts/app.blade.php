<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'ZapLink'))</title>
    
    <!-- Preconnect for faster font loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Inter Font - Clean, modern, highly readable -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="bg-white antialiased font-sans">
    <div id="app">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-200 shadow-sm">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center h-16">
                    <a href="{{ route('home') }}" class="text-xl font-extrabold text-gray-900 tracking-tight">
                         ZapLink
                    </a>
                    
                    <div class="flex space-x-6 items-center">
                        @auth
                            <a href="{{ route('links.index') }}" class="text-gray-600 hover:text-gray-900 font-medium transition">My Links</a>
                            <a href="{{ route('links.create') }}" class="text-gray-600 hover:text-gray-900 font-medium transition">Create</a>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-600 hover:text-gray-900 font-medium transition">Logout</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 font-medium transition">Log In</a>
                            <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg transition shadow-sm hover:shadow-md">
                                Sign Up Free
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 text-center font-medium">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 text-center font-medium">
                {{ session('error') }}
            </div>
        @endif
        
        <!-- Main Content -->
        <main>
            @yield('content')
        </main>
        
        <!-- Footer -->
        <footer class="bg-gray-50 border-t border-gray-200 mt-12">
            <div class="container mx-auto px-4 py-8">
                <p class="text-center text-gray-500 text-sm font-medium">
                    &copy; {{ date('Y') }} ZapLink. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
    
    @stack('scripts')
</body>
</html>