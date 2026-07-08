<?php

use App\Models\Visit;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ============================================
// 1. DATABASE TESTS
// ============================================

test('it has the correct table name', function () {
    $visit = new Visit();
    expect($visit->getTable())->toBe('visits');
});

test('it has the correct fillable attributes', function () {
    $expectedFillable = [
        'link_id',
        'ip_address',
        'user_agent',
        'referer',
    ];

    $visit = new Visit();
    expect($visit->getFillable())->toBe($expectedFillable);
});

test('it has the correct casts', function () {
    $visit = new Visit();
    $casts = $visit->getCasts();

    expect($casts)->toHaveKey('created_at', 'datetime');
});

// ============================================
// 2. RELATIONSHIP TESTS
// ============================================

test('it belongs to a link', function () {
    $link = createLink();
    $visit = createVisit(['link_id' => $link->id]);

    expect($visit->link)->toBeInstanceOf(Link::class);
    expect($visit->link->id)->toBe($link->id);
});

// ============================================
// 3. MODEL CREATION TESTS
// ============================================

test('it can create a visit', function () {
    $link = createLink();
    $visit = createVisit(['link_id' => $link->id]);

    expect($visit->ip_address)->not->toBeNull();
    expect($visit->user_agent)->not->toBeNull();
    $this->assertDatabaseHas('visits', [
        'id' => $visit->id,
        'link_id' => $link->id,
    ]);
});

test('it can create a visit with custom attributes', function () {
    $link = createLink();
    $visit = createVisit([
        'link_id' => $link->id,
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0 (Custom Browser)',
        'referer' => 'https://example.com',
    ]);

    $this->assertDatabaseHas('visits', [
        'id' => $visit->id,
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0 (Custom Browser)',
        'referer' => 'https://example.com',
    ]);
});

test('it allows null fields', function () {
    $link = createLink();
    $visit = createVisit([
        'link_id' => $link->id,
        'ip_address' => null,
        'user_agent' => null,
        'referer' => null,
    ]);

    expect($visit->ip_address)->toBeNull();
    expect($visit->user_agent)->toBeNull();
    expect($visit->referer)->toBeNull();
});

// ============================================
// 4. ACCESSOR TESTS - DEVICE TYPE
// ============================================

test('it detects mobile device type', function () {
    $link = createLink();
    $visit = createVisit([
        'link_id' => $link->id,
        'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
    ]);

    expect($visit->device_type)->toBe('Mobile');
});

test('it detects bot device type', function () {
    $link = createLink();
    $visit = createVisit([
        'link_id' => $link->id,
        'user_agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
    ]);

    expect($visit->device_type)->toBe('Bot');
});

test('it detects crawler as bot', function () {
    $link = createLink();
    $visit = createVisit([
        'link_id' => $link->id,
        'user_agent' => 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
    ]);

    expect($visit->device_type)->toBe('Bot');
});

test('it detects desktop device type by default', function () {
    $link = createLink();
    $visit = createVisit([
        'link_id' => $link->id,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
    ]);

    expect($visit->device_type)->toBe('Desktop');
});

test('it returns desktop for empty user agent', function () {
    $link = createLink();
    $visit = createVisit([
        'link_id' => $link->id,
        'user_agent' => null,
    ]);

    expect($visit->device_type)->toBe('Desktop');
});

// ============================================
// 5. ACCESSOR TESTS - BROWSER
// ============================================

test('it detects Chrome browser', function () {
    $link = createLink();
    $visit = createVisit([
        'link_id' => $link->id,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
    ]);

    expect($visit->browser)->toBe('Chrome');
});

test('it detects Firefox browser', function () {
    $link = createLink();
    $visit = createVisit([
        'link_id' => $link->id,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
    ]);

    expect($visit->browser)->toBe('Firefox');
});

test('it detects Safari browser', function () {
    $link = createLink();
    $visit = createVisit([
        'link_id' => $link->id,
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
    ]);

    expect($visit->browser)->toBe('Safari');
});

test('it returns Other for unknown browser', function () {
    $link = createLink();
    $visit = createVisit([
        'link_id' => $link->id,
        'user_agent' => 'Unknown Browser/1.0',
    ]);

    expect($visit->browser)->toBe('Other');
});

test('it returns Other for empty user agent', function () {
    $link = createLink();
    $visit = createVisit([
        'link_id' => $link->id,
        'user_agent' => null,
    ]);

    expect($visit->browser)->toBe('Other');
});

// ============================================
// 6. UPDATE TESTS
// ============================================

test('it can update visit attributes', function () {
    $link = createLink();
    $visit = createVisit([
        'link_id' => $link->id,
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    ]);

    $visit->update([
        'ip_address' => '10.0.0.1',
        'user_agent' => 'Updated User Agent',
    ]);

    $this->assertDatabaseHas('visits', [
        'id' => $visit->id,
        'ip_address' => '10.0.0.1',
        'user_agent' => 'Updated User Agent',
    ]);
});

// ============================================
// 7. DELETE TESTS
// ============================================

test('it can delete a visit', function () {
    $link = createLink();
    $visit = createVisit(['link_id' => $link->id]);

    $visit->delete();

    $this->assertDatabaseMissing('visits', [
        'id' => $visit->id,
    ]);
});

// ============================================
// 8. TIMESTAMP TESTS
// ============================================

test('it has timestamps', function () {
    $link = createLink();
    $visit = createVisit(['link_id' => $link->id]);

    expect($visit->created_at)->not->toBeNull();
    expect($visit->updated_at)->not->toBeNull();
    expect($visit->created_at)->toBeInstanceOf(\Carbon\Carbon::class);
});

// ============================================
// 9. EDGE CASE TESTS
// ============================================

test('it handles very long user agents', function () {
    $longUserAgent = str_repeat('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 ', 10);
    $link = createLink();
    $visit = createVisit([
        'link_id' => $link->id,
        'user_agent' => $longUserAgent,
    ]);

    expect($visit->user_agent)->toBe($longUserAgent);
});

test('it handles IPv6 addresses', function () {
    $link = createLink();
    $visit = createVisit([
        'link_id' => $link->id,
        'ip_address' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
    ]);

    expect($visit->ip_address)->toBe('2001:0db8:85a3:0000:0000:8a2e:0370:7334');
});

test('it handles multiple visits for same link', function () {
    $link = createLink();
    $visit1 = createVisit(['link_id' => $link->id]);
    $visit2 = createVisit(['link_id' => $link->id]);
    $visit3 = createVisit(['link_id' => $link->id]);

    expect($link->visits)->toHaveCount(3);
    expect($link->visits->pluck('id'))->toContain($visit1->id, $visit2->id, $visit3->id);
});

// ============================================
// 10. VISIT COUNT TESTS
// ============================================

test('it can count visits for a link', function () {
    $link = createLink();
    createVisit(['link_id' => $link->id]);
    createVisit(['link_id' => $link->id]);
    createVisit(['link_id' => $link->id]);

    expect(Visit::where('link_id', $link->id)->count())->toBe(3);
});

test('it returns 0 visits for link with no visits', function () {
    $link = createLink();

    expect(Visit::where('link_id', $link->id)->count())->toBe(0);
});

// ============================================
// 11. IP ADDRESS TESTS
// ============================================

test('it can store IPv4 addresses', function () {
    $link = createLink();
    $visit = createVisit([
        'link_id' => $link->id,
        'ip_address' => '192.168.1.1',
    ]);

    expect($visit->ip_address)->toBe('192.168.1.1');
});

test('it can store localhost IP', function () {
    $link = createLink();
    $visit = createVisit([
        'link_id' => $link->id,
        'ip_address' => '127.0.0.1',
    ]);

    expect($visit->ip_address)->toBe('127.0.0.1');
});

// ============================================
// 12. REFERER TESTS
// ============================================

test('it can store referer URL', function () {
    $link = createLink();
    $visit = createVisit([
        'link_id' => $link->id,
        'referer' => 'https://google.com/search?q=laravel',
    ]);

    expect($visit->referer)->toBe('https://google.com/search?q=laravel');
});

test('it allows null referer', function () {
    $link = createLink();
    $visit = createVisit([
        'link_id' => $link->id,
        'referer' => null,
    ]);

    expect($visit->referer)->toBeNull();
});

// ============================================
// HELPER FUNCTIONS
// ============================================

function createVisit(array $attributes = []): Visit
{
    $defaults = [
        'link_id' => createLink()->id,
        'ip_address' => fake()->ipv4(),
        'user_agent' => fake()->userAgent(),
        'referer' => fake()->url(),
    ];

    return Visit::create(array_merge($defaults, $attributes));
}