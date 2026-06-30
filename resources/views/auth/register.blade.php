{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.app')

@section('title', 'Create Account - ZapLink')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div>
            <div class="flex justify-center">
                <a href="{{ route('home') }}" class="text-4xl">⚡</a>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-white">
                Create your account
            </h2>
            <p class="mt-2 text-center text-sm text-gray-400">
                Already have an account?
                <a href="{{ route('login') }}" class="font-medium text-blue-400 hover:text-blue-300">
                    Sign in
                </a>
            </p>
        </div>

        <!-- Registration Form -->
        <div class="bg-gray-800 py-8 px-4 shadow-xl rounded-lg border border-gray-700 sm:px-10">
            <form class="space-y-6" action="{{ route('register') }}" method="POST">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300">
                        Full Name
                    </label>
                    <div class="mt-1">
                        <input id="name" 
                               name="name" 
                               type="text" 
                               autocomplete="name" 
                               value="{{ old('name') }}"
                               required 
                               class="appearance-none block w-full px-3 py-2 border border-gray-600 rounded-lg shadow-sm placeholder-gray-400 text-white bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                               placeholder="John Doe">
                    </div>
                    @error('name')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300">
                        Email Address
                    </label>
                    <div class="mt-1">
                        <input id="email" 
                               name="email" 
                               type="email" 
                               autocomplete="email" 
                               value="{{ old('email') }}"
                               required 
                               class="appearance-none block w-full px-3 py-2 border border-gray-600 rounded-lg shadow-sm placeholder-gray-400 text-white bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                               placeholder="you@example.com">
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300">
                        Password
                    </label>
                    <div class="mt-1">
                        <input id="password" 
                               name="password" 
                               type="password" 
                               autocomplete="new-password"
                               required 
                               class="appearance-none block w-full px-3 py-2 border border-gray-600 rounded-lg shadow-sm placeholder-gray-400 text-white bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror"
                               placeholder="••••••••">
                    </div>
                    <p class="mt-2 text-xs text-gray-400">
                        Must be at least 8 characters with letters, numbers, and symbols
                    </p>
                    @error('password')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-300">
                        Confirm Password
                    </label>
                    <div class="mt-1">
                        <input id="password_confirmation" 
                               name="password_confirmation" 
                               type="password" 
                               autocomplete="new-password"
                               required 
                               class="appearance-none block w-full px-3 py-2 border border-gray-600 rounded-lg shadow-sm placeholder-gray-400 text-white bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror"
                               placeholder="••••••••">
                    </div>
                </div>

                <!-- Terms -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="terms" 
                               name="terms" 
                               type="checkbox" 
                               required
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 rounded bg-gray-700">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="terms" class="text-gray-300">
                            I agree to the
                            <a href="#" class="font-medium text-blue-400 hover:text-blue-300">
                                Terms of Service
                            </a>
                            and
                            <a href="#" class="font-medium text-blue-400 hover:text-blue-300">
                                Privacy Policy
                            </a>
                        </label>
                    </div>
                </div>
                @error('terms')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                @enderror

                <!-- Submit Button -->
                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                        Create Account
                    </button>
                </div>
            </form>

            <!-- Social Login (Optional) -->
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-700"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-gray-800 text-gray-400">
                            Or continue with
                        </span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button type="button" 
                            class="w-full inline-flex justify-center py-2 px-4 border border-gray-600 rounded-lg shadow-sm bg-gray-700 text-sm font-medium text-gray-300 hover:bg-gray-600 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 0C4.477 0 0 4.477 0 10c0 4.42 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.462-1.11-1.462-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.03-2.682-.103-.253-.447-1.27.098-2.646 0 0 .84-.269 2.75 1.025.8-.223 1.65-.334 2.5-.334.85 0 1.7.111 2.5.334 1.91-1.294 2.75-1.025 2.75-1.025.545 1.376.201 2.393.099 2.646.64.698 1.03 1.591 1.03 2.682 0 3.841-2.337 4.687-4.565 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C17.135 18.166 20 14.42 20 10 20 4.477 15.523 0 10 0z"/>
                        </svg>
                    </button>
                    <button type="button" 
                            class="w-full inline-flex justify-center py-2 px-4 border border-gray-600 rounded-lg shadow-sm bg-gray-700 text-sm font-medium text-gray-300 hover:bg-gray-600 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6.29 18.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0020 3.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.073 4.073 0 01.8 7.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 010 16.407a11.616 11.616 0 006.29 1.84"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection