{{-- resources/views/welcome/partials/form.blade.php --}}
{{-- 
    URL Shortener Form
    Main form for shortening URLs with validation and AJAX submission
--}}

<!-- URL Shortener Form -->
<div class="max-w-2xl mx-auto bg-white p-6 border border-gray-200">
    
    {{-- Guest Notification --}}
    @guest
        @include('welcome.components.guest-notification')
    @endauth

    {{-- Result Container --}}
    @include('welcome.components.result-container')

    {{-- Main Form --}}
    <form id="shortenForm" class="space-y-4">
        @csrf
        
        {{-- URL Input and Submit Button --}}
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
                <input type="url" 
                       name="original_url" 
                       id="original_url"
                       placeholder="Paste your long URL here..."
                       class="w-full px-4 py-3 bg-gray-50 border border-gray-300 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                       @auth required @else disabled @endauth
                       @guest style="cursor: not-allowed; opacity: 0.7;" @endguest>
                
                {{-- Error Message --}}
                <div id="urlError" class="text-red-600 text-sm mt-1 text-left hidden"></div>
                
                {{-- Guest Overlay --}}
                @guest
                    <div class="absolute inset-0 flex items-center justify-center bg-white/80">
                        <span class="text-gray-500 text-sm font-medium">Sign in to shorten URLs</span>
                    </div>
                @endguest
            </div>
            
            <button type="submit" 
                    id="shortenBtn"
                    @guest disabled @endguest
                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold transition duration-200 whitespace-nowrap @guest opacity-50 cursor-not-allowed @endguest">
                Shorten URL
            </button>
        </div>
        
        {{-- Optional Fields --}}
        <div class="flex flex-col md:flex-row gap-4 text-sm">
            {{-- Custom Code --}}
            <div class="flex-1">
                <label class="text-gray-700 block text-left mb-1">Custom Code (Optional)</label>
                <input type="text" 
                       name="custom_code" 
                       id="custom_code"
                       placeholder="my-custom-link"
                       @auth @else disabled @endauth
                       class="w-full px-4 py-2 bg-gray-50 border border-gray-300 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @guest opacity-50 cursor-not-allowed @endguest">
                <p class="text-gray-500 text-xs mt-1 text-left" id="customCodeHint">
                    4-20 characters (letters and numbers only)
                </p>
            </div>
            
            {{-- Expiration Date --}}
            <div class="flex-1">
                <label class="text-gray-700 block text-left mb-1">Expires (Optional)</label>
                <input type="datetime-local" 
                       name="expires_at"
                       id="expires_at"
                       @auth @else disabled @endauth
                       min="{{ now()->addDay()->format('Y-m-d\TH:i') }}"
                       class="w-full px-4 py-2 bg-gray-50 border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @guest opacity-50 cursor-not-allowed @endguest">
            </div>
        </div>
    </form>

    {{-- Loading Spinner --}}
    <div id="loadingSpinner" class="hidden mt-4">
        <div class="flex items-center justify-center space-x-2">
            <span class="text-gray-600">Shortening your URL...</span>
        </div>
    </div>
</div>
