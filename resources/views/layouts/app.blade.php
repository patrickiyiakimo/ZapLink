{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'ZapLink'))</title>
    
    <!-- Fonts -->
    @yield('fonts')
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="bg-gray-800 antialiased">
    <div id="app">
        <!-- Navigation -->
        <nav class="bg-gray-900 border-b border-gray-700">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center h-16">
                    <a href="{{ route('home') }}" class="text-white text-xl font-bold">
                         ZapLink
                    </a>
                    
                    <div class="flex space-x-4 items-center">
                        @auth
                            <a href="{{ route('links.index') }}" class="text-gray-300 hover:text-white">My Links</a>
                            <a href="{{ route('links.create') }}" class="text-gray-300 hover:text-white">Create</a>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-300 hover:text-white">Logout</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-300 hover:text-white">Login</a>
                            <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Register</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-500 text-white p-4 text-center">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="bg-red-500 text-white p-4 text-center">
                {{ session('error') }}
            </div>
        @endif
        
        <!-- Main Content -->
        <main>
            @yield('content')
        </main>
        
        <!-- Footer -->
        <footer class="bg-gray-900 border-t border-gray-700 mt-12">
            <div class="container mx-auto px-4 py-6">
                <p class="text-center text-gray-400">
                    &copy; {{ date('Y') }} ZapLink. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
    
    @stack('scripts')
</body>
</html>