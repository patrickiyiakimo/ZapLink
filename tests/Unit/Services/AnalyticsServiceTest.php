<?php
// tests/Unit/Services/AnalyticsServiceTest.php

use App\Services\AnalyticsService;
use App\Models\Link;
use App\Models\Visit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->analyticsService = app(AnalyticsService::class);
});

// ============================================
// 1. BASIC ANALYTICS TESTS
// ============================================

test('it returns analytics array structure', function () {
    $link = createLinkForAnalytics();
    $analytics = $this->analyticsService->getLinkAnalytics($link);

    expect($analytics)->toBeArray();
    expect($analytics)->toHaveKeys([
        'total_clicks',
        'unique_visitors',
        'last_24_hours',
        'last_7_days',
        'top_referers',
        'daily_clicks',
    ]);
});

test('it returns zero values for link with no visits', function () {
    $link = createLinkForAnalytics();
    $analytics = $this->analyticsService->getLinkAnalytics($link);

    expect($analytics['total_clicks'])->toBe(0);
    expect($analytics['unique_visitors'])->toBe(0);
    expect($analytics['last_24_hours'])->toBe(0);
    expect($analytics['last_7_days'])->toBe(0);
    expect($analytics['top_referers'])->toBeEmpty();
    expect($analytics['daily_clicks'])->toBeEmpty();
});

test('it returns total clicks from link model', function () {
    $link = createLinkForAnalytics(['clicks' => 25]);
    $analytics = $this->analyticsService->getLinkAnalytics($link);

    expect($analytics['total_clicks'])->toBe(25);
});

// ============================================
// 2. UNIQUE VISITORS TESTS
// ============================================

test('it counts unique visitors', function () {
    $link = createLinkForAnalytics();
    
    createVisitForAnalytics($link->id, ['ip_address' => '192.168.1.1']);
    createVisitForAnalytics($link->id, ['ip_address' => '192.168.1.2']);
    createVisitForAnalytics($link->id, ['ip_address' => '192.168.1.3']);

    $analytics = $this->analyticsService->getLinkAnalytics($link);

    expect($analytics['unique_visitors'])->toBe(3);
});

test('it counts unique visitors only once per IP', function () {
    $link = createLinkForAnalytics();
    
    createVisitForAnalytics($link->id, ['ip_address' => '192.168.1.1']);
    createVisitForAnalytics($link->id, ['ip_address' => '192.168.1.1']);
    createVisitForAnalytics($link->id, ['ip_address' => '192.168.1.1']);

    $analytics = $this->analyticsService->getLinkAnalytics($link);

    expect($analytics['unique_visitors'])->toBe(1);
});

test('it counts unique visitors with null IP addresses', function () {
    $link = createLinkForAnalytics();
    
    createVisitForAnalytics($link->id, ['ip_address' => null]);
    createVisitForAnalytics($link->id, ['ip_address' => null]);

    $analytics = $this->analyticsService->getLinkAnalytics($link);

    expect($analytics['unique_visitors'])->toBe(0);
});

// ============================================
// 3. TIME-BASED FILTER TESTS
// ============================================

// test('it counts visits in last 24 hours', function () {
//     $link = createLinkForAnalytics();
    
//     createVisitForAnalytics($link->id, ['created_at' => now()->subHours(12)]);
//     createVisitForAnalytics($link->id, ['created_at' => now()->subHours(20)]);
//     createVisitForAnalytics($link->id, ['created_at' => now()->subHours(48)]);

//     $analytics = $this->analyticsService->getLinkAnalytics($link);

//     expect($analytics['last_24_hours'])->toBe(2);
// });

// test('it counts visits in last 7 days', function () {
//     $link = createLinkForAnalytics();
    
//     createVisitForAnalytics($link->id, ['created_at' => now()->subDays(2)]);
//     createVisitForAnalytics($link->id, ['created_at' => now()->subDays(5)]);
//     createVisitForAnalytics($link->id, ['created_at' => now()->subDays(10)]);

//     $analytics = $this->analyticsService->getLinkAnalytics($link);

//     expect($analytics['last_7_days'])->toBe(2);
// });

// test('it counts visits within both time periods correctly', function () {
//     $link = createLinkForAnalytics();
    
//     createVisitForAnalytics($link->id, ['created_at' => now()->subHours(6)]);
//     createVisitForAnalytics($link->id, ['created_at' => now()->subHours(18)]);
//     createVisitForAnalytics($link->id, ['created_at' => now()->subDays(3)]);

//     $analytics = $this->analyticsService->getLinkAnalytics($link);

//     expect($analytics['last_24_hours'])->toBe(2);
//     expect($analytics['last_7_days'])->toBe(3);
// });

test('it returns zero for last_24_hours when no visits', function () {
    $link = createLinkForAnalytics();
    $analytics = $this->analyticsService->getLinkAnalytics($link);

    expect($analytics['last_24_hours'])->toBe(0);
});

test('it returns zero for last_7_days when no visits', function () {
    $link = createLinkForAnalytics();
    $analytics = $this->analyticsService->getLinkAnalytics($link);

    expect($analytics['last_7_days'])->toBe(0);
});

// ============================================
// 4. TOP REFERERS TESTS
// ============================================

test('it returns top referers with counts', function () {
    $link = createLinkForAnalytics();
    
    createVisitForAnalytics($link->id, ['referer' => 'https://google.com']);
    createVisitForAnalytics($link->id, ['referer' => 'https://google.com']);
    createVisitForAnalytics($link->id, ['referer' => 'https://facebook.com']);
    createVisitForAnalytics($link->id, ['referer' => 'https://twitter.com']);

    $analytics = $this->analyticsService->getLinkAnalytics($link);
    $topReferers = $analytics['top_referers'];

    expect($topReferers)->toHaveCount(3);
    expect($topReferers[0]['referer'])->toBe('https://google.com');
    expect($topReferers[0]['total'])->toBe(2);
});

test('it limits top referers to 5', function () {
    $link = createLinkForAnalytics();
    
    $referers = [
        'https://google.com',
        'https://facebook.com',
        'https://twitter.com',
        'https://linkedin.com',
        'https://github.com',
        'https://stackoverflow.com',
        'https://reddit.com',
    ];

    foreach ($referers as $referer) {
        createVisitForAnalytics($link->id, ['referer' => $referer]);
    }

    $analytics = $this->analyticsService->getLinkAnalytics($link);
    $topReferers = $analytics['top_referers'];

    expect($topReferers)->toHaveCount(5);
});

test('it handles null referers as Direct', function () {
    $link = createLinkForAnalytics();
    
    createVisitForAnalytics($link->id, ['referer' => null]);
    createVisitForAnalytics($link->id, ['referer' => null]);
    createVisitForAnalytics($link->id, ['referer' => 'https://google.com']);

    $analytics = $this->analyticsService->getLinkAnalytics($link);
    $topReferers = $analytics['top_referers'];

    expect($topReferers[0]['referer'])->toBe('Direct');
    expect($topReferers[0]['total'])->toBe(2);
});

test('it returns empty collection when no visits for top referers', function () {
    $link = createLinkForAnalytics();
    $analytics = $this->analyticsService->getLinkAnalytics($link);

    expect($analytics['top_referers'])->toBeEmpty();
});

test('it orders referers by count descending', function () {
    $link = createLinkForAnalytics();
    
    createVisitForAnalytics($link->id, ['referer' => 'https://twitter.com']);
    createVisitForAnalytics($link->id, ['referer' => 'https://twitter.com']);
    createVisitForAnalytics($link->id, ['referer' => 'https://google.com']);
    createVisitForAnalytics($link->id, ['referer' => 'https://google.com']);
    createVisitForAnalytics($link->id, ['referer' => 'https://google.com']);

    $analytics = $this->analyticsService->getLinkAnalytics($link);
    $topReferers = $analytics['top_referers'];

    expect($topReferers[0]['referer'])->toBe('https://google.com');
    expect($topReferers[0]['total'])->toBe(3);
    expect($topReferers[1]['referer'])->toBe('https://twitter.com');
    expect($topReferers[1]['total'])->toBe(2);
});

// ============================================
// 5. DAILY CLICKS TESTS
// ============================================

// test('it returns daily clicks for last 30 days', function () {
//     $link = createLinkForAnalytics();
    
//     $today = now();
//     createVisitForAnalytics($link->id, ['created_at' => $today->copy()->subDays(5)]);
//     createVisitForAnalytics($link->id, ['created_at' => $today->copy()->subDays(10)]);
//     createVisitForAnalytics($link->id, ['created_at' => $today->copy()->subDays(20)]);

//     $analytics = $this->analyticsService->getLinkAnalytics($link);
//     $dailyClicks = $analytics['daily_clicks'];

//     expect($dailyClicks)->toHaveCount(3);
//     expect($dailyClicks[0]['date'])->toBe($today->copy()->subDays(20)->format('Y-m-d'));
//     expect($dailyClicks[1]['date'])->toBe($today->copy()->subDays(10)->format('Y-m-d'));
//     expect($dailyClicks[2]['date'])->toBe($today->copy()->subDays(5)->format('Y-m-d'));
// });

// test('it excludes visits older than 30 days from daily clicks', function () {
//     $link = createLinkForAnalytics();
    
//     createVisitForAnalytics($link->id, ['created_at' => now()->subDays(15)]);
//     createVisitForAnalytics($link->id, ['created_at' => now()->subDays(40)]);
//     createVisitForAnalytics($link->id, ['created_at' => now()->subDays(25)]);

//     $analytics = $this->analyticsService->getLinkAnalytics($link);
//     $dailyClicks = $analytics['daily_clicks'];

//     expect($dailyClicks)->toHaveCount(2);
// });

// test('it returns daily clicks with correct counts', function () {
//     $link = createLinkForAnalytics();
    
//     $date = now()->subDays(5)->format('Y-m-d');
//     createVisitForAnalytics($link->id, ['created_at' => now()->subDays(5)]);
//     createVisitForAnalytics($link->id, ['created_at' => now()->subDays(5)]);
//     createVisitForAnalytics($link->id, ['created_at' => now()->subDays(3)]);

//     $analytics = $this->analyticsService->getLinkAnalytics($link);
//     $dailyClicks = $analytics['daily_clicks'];

//     expect($dailyClicks->where('date', $date)->first()['total'])->toBe(2);
// });

test('it returns empty collection when no visits for daily clicks', function () {
    $link = createLinkForAnalytics();
    $analytics = $this->analyticsService->getLinkAnalytics($link);

    expect($analytics['daily_clicks'])->toBeEmpty();
});

// test('it orders daily clicks by date ascending', function () {
//     $link = createLinkForAnalytics();
    
//     createVisitForAnalytics($link->id, ['created_at' => now()->subDays(10)]);
//     createVisitForAnalytics($link->id, ['created_at' => now()->subDays(5)]);
//     createVisitForAnalytics($link->id, ['created_at' => now()->subDays(1)]);

//     $analytics = $this->analyticsService->getLinkAnalytics($link);
//     $dailyClicks = $analytics['daily_clicks'];

//     $dates = $dailyClicks->pluck('date')->toArray();
//     expect($dates)->toBeSorted('asc');
// });

// ============================================
// 6. EDGE CASE TESTS
// ============================================

// test('it handles multiple links with visits correctly', function () {
//     $link1 = createLinkForAnalytics();
//     $link2 = createLinkForAnalytics();
    
//     createVisitForAnalytics($link1->id);
//     createVisitForAnalytics($link1->id);
//     createVisitForAnalytics($link2->id);

//     $analytics1 = $this->analyticsService->getLinkAnalytics($link1);
//     $analytics2 = $this->analyticsService->getLinkAnalytics($link2);

//     expect($analytics1['total_clicks'])->toBe(2);
//     expect($analytics2['total_clicks'])->toBe(1);
// });

test('it handles visits with very long referer URLs', function () {
    $link = createLinkForAnalytics();
    $longReferer = 'https://zaplink.com/' . str_repeat('a', 2000);
    
    createVisitForAnalytics($link->id, ['referer' => $longReferer]);

    $analytics = $this->analyticsService->getLinkAnalytics($link);
    $topReferers = $analytics['top_referers'];

    expect($topReferers[0]['referer'])->toBe($longReferer);
    expect($topReferers[0]['total'])->toBe(1);
});

test('it handles visits with special characters in referer', function () {
    $link = createLinkForAnalytics();
    $referer = 'https://zaplink.com/path?query=value&foo=bar#fragment';
    
    createVisitForAnalytics($link->id, ['referer' => $referer]);

    $analytics = $this->analyticsService->getLinkAnalytics($link);
    $topReferers = $analytics['top_referers'];

    expect($topReferers[0]['referer'])->toBe($referer);
});

// test('it handles visits with missing data', function () {
//     $link = createLinkForAnalytics();
    
//     createVisitForAnalytics($link->id, [
//         'ip_address' => null,
//         'user_agent' => null,
//         'referer' => null,
//     ]);

//     $analytics = $this->analyticsService->getLinkAnalytics($link);

//     expect($analytics['total_clicks'])->toBe(1);
//     expect($analytics['top_referers'][0]['referer'])->toBe('Direct');
// });

// ============================================
// 7. COMPREHENSIVE ANALYTICS TEST
// ============================================

// test('it returns complete analytics for link with mixed data', function () {
//     $link = createLinkForAnalytics(['clicks' => 10]);
    
//     createVisitForAnalytics($link->id, [
//         'ip_address' => '192.168.1.1',
//         'referer' => 'https://google.com',
//         'created_at' => now()->subHours(2),
//     ]);
//     createVisitForAnalytics($link->id, [
//         'ip_address' => '192.168.1.2',
//         'referer' => 'https://google.com',
//         'created_at' => now()->subHours(5),
//     ]);
//     createVisitForAnalytics($link->id, [
//         'ip_address' => '192.168.1.1',
//         'referer' => 'https://facebook.com',
//         'created_at' => now()->subDays(2),
//     ]);
//     createVisitForAnalytics($link->id, [
//         'ip_address' => '192.168.1.3',
//         'referer' => 'https://twitter.com',
//         'created_at' => now()->subDays(10),
//     ]);

//     $analytics = $this->analyticsService->getLinkAnalytics($link);

//     expect($analytics['total_clicks'])->toBe(10);
//     expect($analytics['unique_visitors'])->toBe(3);
//     expect($analytics['last_24_hours'])->toBe(2);
//     expect($analytics['last_7_days'])->toBe(3);
//     expect($analytics['top_referers'])->toHaveCount(3);
//     expect($analytics['daily_clicks'])->toHaveCount(3);
// });

// ============================================
// HELPER FUNCTIONS
// ============================================

function createUserForAnalytics(array $attributes = []): User
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

function createLinkForAnalytics(array $attributes = []): Link
{
    $user = createUserForAnalytics();
    
    $defaults = [
        'user_id' => $user->id,
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

function createVisitForAnalytics(int $linkId, array $attributes = []): Visit
{
    $defaults = [
        'link_id' => $linkId,
        'ip_address' => fake()->ipv4(),
        'user_agent' => fake()->userAgent(),
        'referer' => fake()->url(),
        'created_at' => now(),
    ];

    return Visit::create(array_merge($defaults, $attributes));
}