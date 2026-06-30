<?php
// app/Services/UrlValidatorService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class UrlValidatorService
{
    /**
     * Validate a URL for safety and accessibility.
     */
    public function validate(string $url): bool
    {
        // Check if URL is empty
        if (empty($url)) {
            return false;
        }

        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Check for blocked domains
        $blockedDomains = config('zap-link.blocked_domains', []);
        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'] ?? '';

        foreach ($blockedDomains as $blocked) {
            if (str_contains($host, $blocked)) {
                return false;
            }
        }

        // Optional: Check if URL is accessible
        if (config('zap-link.check_url_accessibility', false)) {
            return $this->checkAccessibility($url);
        }

        return true;
    }

    /**
     * Check if the URL is accessible.
     */
    protected function checkAccessibility(string $url): bool
    {
        $cacheKey = 'url_check:' . md5($url);
        
        return Cache::remember($cacheKey, 3600, function () use ($url) {
            try {
                $response = Http::timeout(5)
                    ->head($url)
                    ->withoutVerifying(); // For testing only
                
                return $response->successful();
            } catch (\Exception $e) {
                return false;
            }
        });
    }

    /**
     * Sanitize URL.
     */
    public function sanitize(string $url): string
    {
        // Remove whitespace
        $url = trim($url);
        
        // Ensure protocol
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'https://' . $url;
        }
        
        // Remove dangerous characters
        $url = filter_var($url, FILTER_SANITIZE_URL);
        
        return $url;
    }
}