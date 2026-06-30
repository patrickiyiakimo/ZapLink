<?php
// app/Models/User.php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the links for the user.
     */
    public function links()
    {
        return $this->hasMany(Link::class);
    }

    /**
     * Get the visits for the user's links.
     */
    public function visits()
    {
        return $this->hasManyThrough(Visit::class, Link::class);
    }

    /**
     * Check if user has any links.
     */
    public function hasLinks(): bool
    {
        return $this->links()->exists();
    }

    /**
     * Get total clicks for user's links.
     */
    public function getTotalClicksAttribute(): int
    {
        return $this->links()->sum('clicks');
    }

    /**
     * Get active links count.
     */
    public function getActiveLinksCountAttribute(): int
    {
        return $this->links()->active()->count();
    }
}