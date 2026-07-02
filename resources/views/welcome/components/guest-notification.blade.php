{{-- resources/views/welcome/components/guest-notification.blade.php --}}
{{-- 
    Guest User Notification
    Shows a call-to-action for unauthenticated users
--}}

<div class="mb-6 bg-blue-50 border border-blue-200 p-4">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div class="flex items-center">
            <span class="text-blue-800 font-medium">
                Create a free account to start shortening URLs
            </span>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('register') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-medium transition">
                Sign Up Free
            </a>
            <a href="{{ route('login') }}" 
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 text-sm font-medium transition">
                Log In
            </a>
        </div>
    </div>
</div>