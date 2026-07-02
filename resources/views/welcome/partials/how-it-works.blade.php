{{-- resources/views/welcome/partials/how-it-works.blade.php --}}
{{-- 
    How It Works Section
    Three-step guide explaining the URL shortening process
--}}

<!-- How It Works Section -->
<div class="mt-20">
    <h2 class="text-3xl font-bold text-gray-900 mb-12">How It Works</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
        
        {{-- Step 1 --}}
        <div class="text-center">
            <div class="w-16 h-16 bg-blue-100 flex items-center justify-center mx-auto mb-4">
                <span class="text-2xl font-bold text-blue-600">1</span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Paste Your URL</h3>
            <p class="text-gray-600">Copy and paste your long URL into the field above</p>
        </div>
        
        {{-- Step 2 --}}
        <div class="text-center">
            <div class="w-16 h-16 bg-blue-100 flex items-center justify-center mx-auto mb-4">
                <span class="text-2xl font-bold text-blue-600">2</span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Customize It</h3>
            <p class="text-gray-600">Add a custom code or set an expiration date if needed</p>
        </div>
        
        {{-- Step 3 --}}
        <div class="text-center">
            <div class="w-16 h-16 bg-blue-100 flex items-center justify-center mx-auto mb-4">
                <span class="text-2xl font-bold text-blue-600">3</span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Share & Track</h3>
            <p class="text-gray-600">Copy your short link and start sharing it with the world</p>
        </div>
        
    </div>
</div>