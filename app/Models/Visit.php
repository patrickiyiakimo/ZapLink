<?php
// app/Models/Visit.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    protected $fillable = [
        'link_id',
        'ip_address',
        'user_agent',
        'referer',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the link that owns the visit.
     */
    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }

    /**
     * Get the user agent details.
     */
    public function getDeviceTypeAttribute(): string
    {
        $userAgent = strtolower($this->user_agent ?? '');
        
        if (str_contains($userAgent, 'mobile')) {
            return 'Mobile';
        } elseif (str_contains($userAgent, 'tablet')) {
            return 'Tablet';
        } elseif (str_contains($userAgent, 'bot') || str_contains($userAgent, 'crawler')) {
            return 'Bot';
        }
        
        return 'Desktop';
    }

    /**
     * Get the browser from user agent.
     */
    public function getBrowserAttribute(): string
    {
        $userAgent = strtolower($this->user_agent ?? '');
        
        if (str_contains($userAgent, 'chrome')) return 'Chrome';
        if (str_contains($userAgent, 'firefox')) return 'Firefox';
        if (str_contains($userAgent, 'safari')) return 'Safari';
        if (str_contains($userAgent, 'edge')) return 'Edge';
        if (str_contains($userAgent, 'opera')) return 'Opera';
        
        return 'Other';
    }
}