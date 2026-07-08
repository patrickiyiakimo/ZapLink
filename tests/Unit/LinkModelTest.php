<?php

use App\Models\Link;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

// ============================================
// 1. DATABASE TESTS
// ============================================

test('it has the correct table name', function () {
    $link = new Link();
    expect($link->getTable())->toBe('links');
});

test('it has the correct fillable attributes', function () {
    $expectedFillable = [
        'user_id',
        'original_url',
        'short_code',
        'title',
        'expires_at',
        'is_active',
        'metadata',
        'clicks'
    ];

    $link = new Link();
    expect($link->getFillable())->toBe($expectedFillable);
});

test('it has the correct casts', function () {
    $link = new Link();
    $casts = $link->getCasts();

    expect($casts)->toHaveKey('metadata', 'array');
    expect($casts)->toHaveKey('expires_at', 'datetime');
    expect($casts)->toHaveKey('is_active', 'boolean');
});

test('it has default attribute values', function () {
    $link = new Link();
    
    expect($link->is_active)->toBeTrue();
    expect($link->clicks)->toBe(0);
});

// ============================================
// 2. RELATIONSHIP TESTS
// ============================================

test('it belongs to a user', function () {
    // Create a user directly
    $user = createUser();
    
    // Create a link with user_id
    $link = createLink(['user_id' => $user->id]);

    expect($link->user)->toBeInstanceOf(User::class);
    expect($link->user->id)->toBe($user->id);
});

test('it has many visits', function () {
    $link = createLink();
    createVisits($link->id, 3);

    expect($link->visits)->toHaveCount(3);
    expect($link->visits->first())->toBeInstanceOf(Visit::class);
});

test('it returns empty collection for visits when none exist', function () {
    $link = createLink();

    expect($link->visits)->toBeEmpty();
    expect($link->visits)->toHaveCount(0);
});

// ============================================
// 3. ACCESSOR TESTS
// ============================================

test('it returns short url attribute', function () {
    $link = createLink(['short_code' => 'abc123']);

    $expectedShortUrl = config('app.url') . '/abc123';
    expect($link->short_url)->toBe($expectedShortUrl);
    expect($link->short_url)->toBeString();
});

test('it returns is_expired true when expires_at is in past', function () {
    $link = createLink([
        'expires_at' => now()->subDay()
    ]);

    expect($link->is_expired)->toBeTrue();
    expect($link->is_expired)->toBeBool();
});

test('it returns is_expired false when expires_at is in future', function () {
    $link = createLink([
        'expires_at' => now()->addDay()
    ]);

    expect($link->is_expired)->toBeFalse();
});

test('it returns is_expired false when expires_at is null', function () {
    $link = createLink([
        'expires_at' => null
    ]);

    expect($link->is_expired)->toBeFalse();
});

// ============================================
// 4. SCOPE TESTS
// ============================================

test('it scopes active links excludes expired links', function () {
    $expiredLink = createLink([
        'is_active' => true,
        'expires_at' => now()->subDay()
    ]);

    $activeLinks = Link::active()->get();

    expect($activeLinks)->not->toContain($expiredLink);
});

test('it returns empty collection for user with no links', function () {
    $user = createUser();

    $links = Link::forUser($user->id)->get();

    expect($links)->toBeEmpty();
    expect($links)->toHaveCount(0);
});

// ============================================
// 5. MODEL CREATION TESTS
// ============================================

test('it can increment clicks count', function () {
    $link = createLink(['clicks' => 5]);

    $link->increment('clicks');
    $link->refresh();

    expect($link->clicks)->toBe(6);
});

test('it can decrement clicks count', function () {
    $link = createLink(['clicks' => 5]);

    $link->decrement('clicks');
    $link->refresh();

    expect($link->clicks)->toBe(4);
});

test('it can toggle active status', function () {
    $link = createLink(['is_active' => true]);

    $link->update(['is_active' => false]);
    $link->refresh();

    expect($link->is_active)->toBeFalse();

    $link->update(['is_active' => true]);
    $link->refresh();

    expect($link->is_active)->toBeTrue();
});

// ============================================
// 7. DELETE TESTS
// ============================================

test('it deletes associated visits when link is deleted', function () {
    $link = createLink();
    createVisits($link->id, 3);

    expect(Visit::count())->toBe(3);

    $link->delete();

    expect(Visit::where('link_id', $link->id)->count())->toBe(0);
    expect(Visit::count())->toBe(0);
});

// ============================================
// 8. EDGE CASE TESTS
// ============================================

test('it handles special characters in url', function () {
    $url = 'https://zaplink.com/path?query=value&foo=bar#fragment';
    
    $link = createLink([
        'original_url' => $url
    ]);

    expect($link->original_url)->toBe($url);
});

test('it handles unicode urls', function () {
    $url = 'https://例子.测试/路径?参数=值';
    
    $link = createLink([
        'original_url' => $url
    ]);

    expect($link->original_url)->toBe($url);
});

test('it handles empty metadata', function () {
    $link = createLink(['metadata' => null]);

    expect($link->metadata)->toBeNull();
});

test('it handles json metadata', function () {
    $metadata = ['tags' => ['laravel', 'testing'], 'notes' => 'Test link'];
    
    $link = createLink([
        'metadata' => $metadata
    ]);

    expect($link->metadata)->toBe($metadata);
    expect($link->metadata)->toBeArray();
});

// ============================================
// 9. SCOPE CHAINING TESTS
// ============================================

test('it can count links', function () {
    createLink();
    createLink();
    createLink();
    createLink();
    createLink();

    $count = Link::count();

    expect($count)->toBe(5);
});

test('it can count active links', function () {
    createLink(['is_active' => true, 'expires_at' => null]);
    createLink(['is_active' => true, 'expires_at' => null]);
    createLink(['is_active' => true, 'expires_at' => null]);
    createLink(['is_active' => false, 'expires_at' => null]);
    createLink(['is_active' => false, 'expires_at' => null]);

    $count = Link::active()->count();

    expect($count)->toBe(3);
});

// ============================================
// 10. EXPIRED LINK TESTS
// ============================================

test('it can create expired link', function () {
    $link = createLink([
        'expires_at' => now()->subDay()
    ]);

    expect($link->is_expired)->toBeTrue();
    expect($link->expires_at)->toBeLessThan(now());
});

test('it can create inactive link', function () {
    $link = createLink([
        'is_active' => false
    ]);

    expect($link->is_active)->toBeFalse();
});

test('it can create link with custom short code', function () {
    $link = createLink([
        'short_code' => 'custom123'
    ]);

    expect($link->short_code)->toBe('custom123');
});

// ============================================
// 11. UNIQUENESS TESTS
// ============================================

test('it requires unique short codes', function () {
    createLink(['short_code' => 'unique123']);

    expect(function () {
        createLink(['short_code' => 'unique123']);
    })->toThrow(\Illuminate\Database\QueryException::class);
});

// ============================================
// 12. NULLABLE FIELD TESTS
// ============================================

test('it allows null title', function () {
    $link = createLink(['title' => null]);

    expect($link->title)->toBeNull();
});

test('it allows null expires_at', function () {
    $link = createLink(['expires_at' => null]);

    expect($link->expires_at)->toBeNull();
});

test('it allows null metadata', function () {
    $link = createLink(['metadata' => null]);

    expect($link->metadata)->toBeNull();
});

// ============================================
// HELPER FUNCTIONS (No Factories!)
// ============================================

/**
 * Create a user without using factories
 */
function createUser(array $attributes = []): User
{
    $defaults = [
        'name' => 'Test User',
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
        'remember_token' => Str::random(10),
    ];

    return User::create(array_merge($defaults, $attributes));
}

/**
 * Create a link without using factories
 */
function createLink(array $attributes = []): Link
{
    $defaults = [
        'user_id' => createUser()->id,
        'original_url' => 'https://zaplink.com/' . Str::random(10),
        'short_code' => Str::random(6),
        'title' => 'Test Link',
        'expires_at' => null,
        'is_active' => true,
        'metadata' => null,
        'clicks' => 0,
    ];

    return Link::create(array_merge($defaults, $attributes));
}

/**
 * Create visits without using factories
 */
function createVisits(int $linkId, int $count = 1): void
{
    for ($i = 0; $i < $count; $i++) {
        Visit::create([
            'link_id' => $linkId,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'referer' => fake()->url(),
        ]);
    }
}