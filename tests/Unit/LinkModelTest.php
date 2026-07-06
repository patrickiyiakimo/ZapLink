<?php
// tests/Unit/LinkModelTest.php

use App\Models\Link;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
    $user = User::factory()->create();
    $link = Link::factory()->create(['user_id' => $user->id]);

    expect($link->user)->toBeInstanceOf(User::class);
    expect($link->user->id)->toBe($user->id);
});

test('it returns null for user when user does not exist', function () {
    $link = Link::factory()->create(['user_id' => 999]);

    expect($link->user)->toBeNull();
});

test('it has many visits', function () {
    $link = Link::factory()->create();
    Visit::factory()->count(3)->create(['link_id' => $link->id]);

    expect($link->visits)->toHaveCount(3);
    expect($link->visits->first())->toBeInstanceOf(Visit::class);
});

test('it returns empty collection for visits when none exist', function () {
    $link = Link::factory()->create();

    expect($link->visits)->toBeEmpty();
    expect($link->visits)->toHaveCount(0);
});

// ============================================
// 3. ACCESSOR TESTS
// ============================================

test('it returns short url attribute', function () {
    $link = Link::factory()->create(['short_code' => 'abc123']);

    $expectedShortUrl = config('app.url') . '/abc123';
    expect($link->short_url)->toBe($expectedShortUrl);
    expect($link->short_url)->toBeString();
});

test('it returns is_expired true when expires_at is in past', function () {
    $link = Link::factory()->create([
        'expires_at' => now()->subDay()
    ]);

    expect($link->is_expired)->toBeTrue();
    expect($link->is_expired)->toBeBool();
});

test('it returns is_expired false when expires_at is in future', function () {
    $link = Link::factory()->create([
        'expires_at' => now()->addDay()
    ]);

    expect($link->is_expired)->toBeFalse();
});

test('it returns is_expired false when expires_at is null', function () {
    $link = Link::factory()->create([
        'expires_at' => null
    ]);

    expect($link->is_expired)->toBeFalse();
});

// ============================================
// 4. SCOPE TESTS
// ============================================

test('it scopes active links only', function () {
    // Create active links
    Link::factory()->count(2)->create([
        'is_active' => true,
        'expires_at' => null
    ]);

    // Create inactive link
    Link::factory()->create([
        'is_active' => false,
        'expires_at' => null
    ]);

    // Create expired link (inactive despite is_active being true)
    Link::factory()->create([
        'is_active' => true,
        'expires_at' => now()->subDay()
    ]);

    $activeLinks = Link::active()->get();

    expect($activeLinks)->toHaveCount(2);
    expect($activeLinks)->each->is_active->toBeTrue();
});

test('it scopes active links excludes expired links', function () {
    $expiredLink = Link::factory()->create([
        'is_active' => true,
        'expires_at' => now()->subDay()
    ]);

    $activeLinks = Link::active()->get();

    expect($activeLinks)->not->toContain($expiredLink);
});

test('it scopes links for specific user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Link::factory()->count(3)->create(['user_id' => $user1->id]);
    Link::factory()->count(2)->create(['user_id' => $user2->id]);

    $user1Links = Link::forUser($user1->id)->get();
    $user2Links = Link::forUser($user2->id)->get();

    expect($user1Links)->toHaveCount(3);
    expect($user2Links)->toHaveCount(2);
    expect($user1Links)->each->user_id->toBe($user1->id);
});

test('it returns empty collection for user with no links', function () {
    $user = User::factory()->create();

    $links = Link::forUser($user->id)->get();

    expect($links)->toBeEmpty();
    expect($links)->toHaveCount(0);
});

// ============================================
// 5. MODEL CREATION TESTS
// ============================================

test('it can create a link with factory', function () {
    $link = Link::factory()->create();

    expect($link->short_code)->not->toBeNull();
    expect($link->short_code)->toBeString();
    expect($link)->toBeInDatabase('links', [
        'id' => $link->id,
        'short_code' => $link->short_code
    ]);
});

test('it can create a link with custom attributes', function () {
    $link = Link::factory()->create([
        'original_url' => 'https://custom-example.com',
        'title' => 'Custom Title',
        'short_code' => 'custom123',
        'clicks' => 100
    ]);

    expect($link)->toBeInDatabase('links', [
        'id' => $link->id,
        'original_url' => 'https://custom-example.com',
        'title' => 'Custom Title',
        'short_code' => 'custom123',
        'clicks' => 100
    ]);
});

test('it creates link with default clicks count', function () {
    $link = Link::factory()->create();

    expect($link->clicks)->toBe(0);
    expect($link)->toBeInDatabase('links', [
        'id' => $link->id,
        'clicks' => 0
    ]);
});

test('it creates link with default active status', function () {
    $link = Link::factory()->create();

    expect($link->is_active)->toBeTrue();
    expect($link)->toBeInDatabase('links', [
        'id' => $link->id,
        'is_active' => 1
    ]);
});

// ============================================
// 6. UPDATE TESTS
// ============================================

test('it can update link attributes', function () {
    $link = Link::factory()->create();

    $link->update([
        'title' => 'Updated Title',
        'original_url' => 'https://updated-example.com'
    ]);

    expect($link)->toBeInDatabase('links', [
        'id' => $link->id,
        'title' => 'Updated Title',
        'original_url' => 'https://updated-example.com'
    ]);
});

test('it can increment clicks count', function () {
    $link = Link::factory()->create(['clicks' => 5]);

    $link->increment('clicks');
    $link->refresh();

    expect($link->clicks)->toBe(6);
});

test('it can decrement clicks count', function () {
    $link = Link::factory()->create(['clicks' => 5]);

    $link->decrement('clicks');
    $link->refresh();

    expect($link->clicks)->toBe(4);
});

test('it can toggle active status', function () {
    $link = Link::factory()->create(['is_active' => true]);

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

test('it can delete a link', function () {
    $link = Link::factory()->create();

    $link->delete();

    expect($link)->not->toBeInDatabase('links', [
        'id' => $link->id
    ]);
});

test('it deletes associated visits when link is deleted', function () {
    $link = Link::factory()->create();
    Visit::factory()->count(3)->create(['link_id' => $link->id]);

    expect(Visit::count())->toBe(3);

    $link->delete();

    expect(Visit::where('link_id', $link->id)->count())->toBe(0);
    expect(Visit::count())->toBe(0);
});

// ============================================
// 8. MASS ASSIGNMENT TESTS
// ============================================

test('it can mass assign attributes', function () {
    $user = User::factory()->create();
    
    $data = [
        'user_id' => $user->id,
        'original_url' => 'https://mass-assign.com',
        'short_code' => 'mass123',
        'title' => 'Mass Assign Test',
        'expires_at' => now()->addDays(7),
        'is_active' => false,
        'clicks' => 50
    ];

    $link = Link::create($data);

    expect($link)->toBeInDatabase('links', [
        'id' => $link->id,
        'original_url' => 'https://mass-assign.com',
        'short_code' => 'mass123',
        'title' => 'Mass Assign Test'
    ]);
});

// ============================================
// 9. EDGE CASE TESTS
// ============================================

test('it handles very long urls', function () {
    $longUrl = 'https://example.com/' . str_repeat('a', 1000);
    
    $link = Link::factory()->create([
        'original_url' => $longUrl
    ]);

    expect($link->original_url)->toBe($longUrl);
    expect($link)->toBeInDatabase('links', [
        'id' => $link->id,
        'original_url' => $longUrl
    ]);
});

test('it handles special characters in url', function () {
    $url = 'https://example.com/path?query=value&foo=bar#fragment';
    
    $link = Link::factory()->create([
        'original_url' => $url
    ]);

    expect($link->original_url)->toBe($url);
});

test('it handles unicode urls', function () {
    $url = 'https://例子.测试/路径?参数=值';
    
    $link = Link::factory()->create([
        'original_url' => $url
    ]);

    expect($link->original_url)->toBe($url);
});

test('it handles empty metadata', function () {
    $link = Link::factory()->create(['metadata' => null]);

    expect($link->metadata)->toBeNull();
});

test('it handles json metadata', function () {
    $metadata = ['tags' => ['laravel', 'testing'], 'notes' => 'Test link'];
    
    $link = Link::factory()->create([
        'metadata' => $metadata
    ]);

    expect($link->metadata)->toBe($metadata);
    expect($link->metadata)->toBeArray();
});

// ============================================
// 10. SCOPE CHAINING TESTS
// ============================================

test('it can chain scopes', function () {
    $user = User::factory()->create();
    
    Link::factory()->count(3)->create([
        'user_id' => $user->id,
        'is_active' => true,
        'expires_at' => null
    ]);
    
    Link::factory()->count(2)->create([
        'user_id' => $user->id,
        'is_active' => false,
        'expires_at' => null
    ]);

    $links = Link::forUser($user->id)->active()->get();

    expect($links)->toHaveCount(3);
    expect($links)->each->user_id->toBe($user->id);
    expect($links)->each->is_active->toBeTrue();
});

// ============================================
// 11. COUNT TESTS
// ============================================

test('it can count links', function () {
    Link::factory()->count(5)->create();

    $count = Link::count();

    expect($count)->toBe(5);
});

test('it can count active links', function () {
    Link::factory()->count(3)->create(['is_active' => true, 'expires_at' => null]);
    Link::factory()->count(2)->create(['is_active' => false]);

    $count = Link::active()->count();

    expect($count)->toBe(3);
});

// ============================================
// 12. FACTORY STATE TESTS
// ============================================

test('it can create expired link using factory state', function () {
    $link = Link::factory()->expired()->create();

    expect($link->is_expired)->toBeTrue();
    expect($link->expires_at)->toBeLessThan(now());
});

test('it can create inactive link using factory state', function () {
    $link = Link::factory()->inactive()->create();

    expect($link->is_active)->toBeFalse();
});

test('it can create link with custom short code using factory state', function () {
    $link = Link::factory()->withShortCode('custom123')->create();

    expect($link->short_code)->toBe('custom123');
});