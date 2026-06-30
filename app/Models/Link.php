<?php
// app/Models/Link.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Link extends Model
{
    use HasFactory;
    
    // Remove SoftDeletes trait
    // Remove: use SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'original_url',
        'short_code',
        'title',
        'expires_at',
        'is_active',
        'metadata',
        'clicks'
    ];
    
    protected $casts = [
        'metadata' => 'array',
        'expires_at' => 'datetime',
        'is_active' => 'boolean'
    ];
    
    protected $attributes = [
        'is_active' => true,
        'clicks' => 0
    ];
    
    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function visits()
    {
        return $this->hasMany(Visit::class);
    }
    
    // Accessors
    public function getShortUrlAttribute(): string
    {
        return config('app.url') . '/' . $this->short_code;
    }
    
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expires_at) return false;
        return $this->expires_at->isPast();
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }
    
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}