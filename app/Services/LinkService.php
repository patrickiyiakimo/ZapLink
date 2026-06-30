<?php
// app/Services/LinkService.php

namespace App\Services;

use App\Models\Link;
use App\Models\Visit;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class LinkService
{
    protected UrlValidatorService $urlValidator;
    
    // Reserved words that can't be used as short codes
    protected array $reservedWords = [
        'register', 'login', 'logout', 'links', 'admin', 'api',
        'dashboard', 'settings', 'profile', 'home', 'about',
        'contact', 'terms', 'privacy', 'help', 'support'
    ];
    
    public function __construct(UrlValidatorService $urlValidator)
    {
        $this->urlValidator = $urlValidator;
    }
    
    /**
     * Create a new short link with unique code.
     */
    public function createLink(array $data, ?int $userId = null): Link
    {
        // Validate URL
        if (!$this->urlValidator->validate($data['original_url'])) {
            throw new \InvalidArgumentException('Invalid or unsafe URL provided.');
        }
        
        $originalUrl = $this->urlValidator->sanitize($data['original_url']);
        
        // Generate short code (handles custom code or auto-generation)
        $shortCode = $this->generateShortCode($data['custom_code'] ?? null);
        
        $link = DB::transaction(function () use ($originalUrl, $shortCode, $data, $userId) {
            $link = Link::create([
                'original_url' => $originalUrl,
                'short_code' => $shortCode,
                'user_id' => $userId,
                'title' => $data['title'] ?? null,
                'expires_at' => $data['expires_at'] ?? null,
                'metadata' => $data['metadata'] ?? null,
                'is_active' => true,
                'clicks' => 0,
            ]);
            
            $this->cacheLink($link);
            
            return $link;
        });
        
        return $link;
    }
    
    /**
     * Generate a unique short code.
     */
    public function generateShortCode(?string $customCode = null): string
    {
        // If custom code provided, validate it
        if ($customCode) {
            $customCode = strtolower(trim($customCode));
            
            // Check if it's a reserved word
            if (in_array($customCode, $this->reservedWords)) {
                throw new \InvalidArgumentException('This code is reserved. Please choose another.');
            }
            
            // Check if it's available
            if ($this->linkExists($customCode)) {
                throw new \InvalidArgumentException('This custom code is already taken. Please choose another.');
            }
            
            return $customCode;
        }
        
        // Generate a random code
        return $this->generateRandomCode();
    }
    
    /**
     * Generate a random unique code.
     */
    protected function generateRandomCode(int $length = 6): string
    {
        $maxAttempts = 100;
        $attempts = 0;
        
        do {
            // Use a combination of letters and numbers for better readability
            $code = $this->generateRandomString($length);
            $attempts++;
            
            // If we've tried too many times, increase length
            if ($attempts > $maxAttempts) {
                $length++;
                $attempts = 0;
            }
            
        } while ($this->isCodeUnavailable($code));
        
        return $code;
    }
    
    /**
     * Generate a random string (no ambiguous characters).
     */
    protected function generateRandomString(int $length): string
    {
        // Remove ambiguous characters like 0, O, 1, l, etc.
        $characters = '23456789abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
    
    /**
     * Check if a code is unavailable (exists or reserved).
     */
    protected function isCodeUnavailable(string $code): bool
    {
        // Check if reserved
        if (in_array(strtolower($code), $this->reservedWords)) {
            return true;
        }
        
        // Check if exists in database
        return $this->linkExists($code);
    }
    
    /**
     * Check if a link exists with the given short code.
     */
    public function linkExists(string $shortCode): bool
    {
        return Link::where('short_code', $shortCode)->exists();
    }
    
    /**
     * Resolve a short code to a link.
     */
    public function resolveLink(string $shortCode): ?Link
    {
        // Validate short code format
        if (!preg_match('/^[a-zA-Z0-9]+$/', $shortCode)) {
            return null;
        }
        
        $cacheKey = "link:{$shortCode}";
        
        $link = Cache::remember($cacheKey, 3600, function () use ($shortCode) {
            return Link::where('short_code', $shortCode)
                ->where('is_active', true)
                ->first();
        });
        
        if (!$link) {
            return null;
        }
        
        // Check if expired
        if ($link->expires_at && $link->expires_at->isPast()) {
            return null;
        }
        
        return $link;
    }
    
    /**
     * Track a visit to a link.
     */
    public function trackVisit(Link $link, Request $request): void
    {
        Visit::create([
            'link_id' => $link->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
        ]);
        
        $link->increment('clicks');
        Cache::forget("link:{$link->short_code}");
    }
    
    /**
     * Get user's links.
     */
    public function getUserLinks(int $userId, int $perPage = 15)
    {
        return Link::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
    
    /**
     * Cache a link.
     */
    protected function cacheLink(Link $link): void
    {
        Cache::put("link:{$link->short_code}", $link, 3600);
    }
}