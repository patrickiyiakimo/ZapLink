<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class RateLimit
{
    public function handle(Request $request, Closure $next, $limit = 60, $minutes = 1)
    {
        $key = $request->user()?->id ?? $request->ip();
        
        $executed = RateLimiter::attempt(
            "url-shortener:{$key}",
            $limit,
            function () {
                // No action needed, just tracking
            },
            $minutes * 60
        );
        
        if (!$executed) {
            abort(429, 'Too many requests. Please wait a moment.');
        }
        
        return $next($request);
    }
}