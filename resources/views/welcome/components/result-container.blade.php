{{-- resources/views/welcome/components/result-container.blade.php --}}
{{-- 
    Result Container
    Displays the shortened URL after successful creation
--}}

<div id="resultContainer" class="hidden mb-6">
    <div class="bg-green-50 border border-green-200 text-green-800 p-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex-1 w-full">
                <p class="text-sm font-medium mb-2">Your short URL is ready!</p>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                    <input type="text" 
                           id="shortUrlResult" 
                           class="flex-1 bg-gray-50 text-gray-900 px-4 py-2 border border-gray-300 min-w-0" 
                           readonly>
                    <div class="flex gap-2">
                        <button onclick="copyResult()" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 transition flex-1 sm:flex-none">
                            Copy
                        </button>
                        <a href="#" id="resultLink" 
                           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 transition text-center flex-1 sm:flex-none">
                            View
                        </a>
                    </div>
                </div>
            </div>
            <button onclick="dismissResult()" class="text-gray-500 hover:text-gray-700 self-start sm:self-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
</div>