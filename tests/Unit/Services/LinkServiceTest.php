<?php
// tests/Unit/Services/LinkServiceTest.php

use App\Services\LinkService;
use App\Services\UrlValidatorService;
use App\Models\Link;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

// ============================================
// Setup
// ============================================

beforeEach(function () {
    $this->urlValidator = app(UrlValidatorService::class);
    $this->linkService = app(LinkService::class);
    Cache::flush();
});

// ============================================
// 1. CREATE LINK TESTS
// ============================================

test('it creates a short link', function () {
    $data = [
        'original_url' => 'https://zaplink.com/very/long/url',
        'title' => 'Test Link',
    ];

    $link = $this->linkService->createLink($data);

    expect($link)->toBeInstanceOf(Link::class);
    expect($link->original_url)->toBe('https://zaplink.com/very/long/url');
    expect($link->title)->toBe('Test Link');
    expect($link->short_code)->not->toBeNull();
    expect($link->short_code)->toHaveLength(6);
    expect($link->is_active)->toBeTrue();
    expect($link->clicks)->toBe(0);
});

test('it creates a link with user_id when provided', function () {
    $user = createTestUser();
    $data = [
        'original_url' => 'https://example.com',
    ];

    $link = $this->linkService->createLink($data, $user->id);

    expect($link->user_id)->toBe($user->id);
});

test('it creates a link with custom expiration date', function () {
    $expiresAt = now()->addDays(7);
    $data = [
        'original_url' => 'https://example.com',
        'expires_at' => $expiresAt,
    ];

    $link = $this->linkService->createLink($data);

    expect($link->expires_at->toDateTimeString())->toBe($expiresAt->toDateTimeString());
});

test('it creates a link with metadata', function () {
    $metadata = ['tags' => ['laravel', 'testing'], 'source' => 'api'];
    $data = [
        'original_url' => 'https://example.com',
        'metadata' => $metadata,
    ];

    $link = $this->linkService->createLink($data);

    expect($link->metadata)->toBe($metadata);
    expect($link->metadata)->toBeArray();
});

test('it throws exception for invalid URL', function () {
    $data = [
        'original_url' => 'not-a-valid-url',
    ];

    expect(fn() => $this->linkService->createLink($data))
        ->toThrow(\InvalidArgumentException::class, 'Invalid or unsafe URL provided.');
});

// test('it sanitizes URL before saving', function () {
//     $data = [
//         'original_url' => 'example.com',
//     ];

//     $link = $this->linkService->createLink($data);

//     expect($link->original_url)->toBe('https://example.com');
// });

test('it caches the link after creation', function () {
    $data = [
        'original_url' => 'https://example.com',
    ];

    $link = $this->linkService->createLink($data);
    $cacheKey = "link:{$link->short_code}";

    expect(Cache::has($cacheKey))->toBeTrue();
    $cachedLink = Cache::get($cacheKey);
    expect($cachedLink->id)->toBe($link->id);
});

// ============================================
// 2. SHORT CODE GENERATION TESTS
// ============================================

test('it generates unique short codes', function () {
    $link1 = $this->linkService->createLink([
        'original_url' => 'https://zaplink.com/1'
    ]);
    $link2 = $this->linkService->createLink([
        'original_url' => 'https://zaplink.com/2'
    ]);
    $link3 = $this->linkService->createLink([
        'original_url' => 'https://zaplink.com/3'
    ]);

    expect($link1->short_code)->not->toBe($link2->short_code);
    expect($link2->short_code)->not->toBe($link3->short_code);
    expect($link1->short_code)->not->toBe($link3->short_code);
});

test('it handles custom codes', function () {
    $link = $this->linkService->createLink([
        'original_url' => 'https://example.com',
        'custom_code' => 'my-custom-link'
    ]);

    expect($link->short_code)->toBe('my-custom-link');
});

test('it throws exception for duplicate custom code', function () {
    $this->linkService->createLink([
        'original_url' => 'https://zaplink.com/1',
        'custom_code' => 'taken-code'
    ]);

    expect(fn() => $this->linkService->createLink([
        'original_url' => 'https://zaplink.com/2',
        'custom_code' => 'taken-code'
    ]))->toThrow(\InvalidArgumentException::class, 'This custom code is already taken. Please choose another.');
});

test('it throws exception for reserved word as custom code', function () {
    expect(fn() => $this->linkService->createLink([
        'original_url' => 'https://example.com',
        'custom_code' => 'register'
    ]))->toThrow(\InvalidArgumentException::class, 'This code is reserved. Please choose another.');
});

test('it converts custom code to lowercase', function () {
    $link = $this->linkService->createLink([
        'original_url' => 'https://example.com',
        'custom_code' => 'MyCustomCode'
    ]);

    expect($link->short_code)->toBe('mycustomcode');
});

test('it trims custom code whitespace', function () {
    $link = $this->linkService->createLink([
        'original_url' => 'https://example.com',
        'custom_code' => '  my-code  '
    ]);

    expect($link->short_code)->toBe('my-code');
});

test('it generates random code when no custom code provided', function () {
    $link = $this->linkService->createLink([
        'original_url' => 'https://example.com'
    ]);

    expect($link->short_code)->toHaveLength(6);
    expect($link->short_code)->toMatch('/^[a-zA-Z0-9]+$/');
});

test('it generates codes without ambiguous characters', function () {
    $link = $this->linkService->createLink([
        'original_url' => 'https://example.com'
    ]);

    expect($link->short_code)->not->toContain('0');
    expect($link->short_code)->not->toContain('O');
    expect($link->short_code)->not->toContain('1');
    expect($link->short_code)->not->toContain('l');
});

// ============================================
// 3. LINK EXISTS TESTS
// ============================================

test('it checks if a short code exists', function () {
    $link = $this->linkService->createLink([
        'original_url' => 'https://example.com'
    ]);

    expect($this->linkService->linkExists($link->short_code))->toBeTrue();
    expect($this->linkService->linkExists('nonexistent'))->toBeFalse();
});

// ============================================
// 4. RESOLVE LINK TESTS
// ============================================

test('it resolves a short link', function () {
    $created = $this->linkService->createLink([
        'original_url' => 'https://example.com'
    ]);

    $resolved = $this->linkService->resolveLink($created->short_code);

    expect($resolved)->not->toBeNull();
    expect($resolved->id)->toBe($created->id);
    expect($resolved->original_url)->toBe('https://example.com');
});

test('it returns null for nonexistent link', function () {
    $result = $this->linkService->resolveLink('nonexistent');
    expect($result)->toBeNull();
});

test('it returns null for expired link', function () {
    $link = $this->linkService->createLink([
        'original_url' => 'https://example.com'
    ]);
    
    $link->update(['expires_at' => now()->subDay()]);

    $result = $this->linkService->resolveLink($link->short_code);

    expect($result)->toBeNull();
});

// test('it returns null for inactive link', function () {
//     $link = $this->linkService->createLink([
//         'original_url' => 'https://example.com'
//     ]);
    
//     $link->update(['is_active' => false]);

//     $result = $this->linkService->resolveLink($link->short_code);

//     expect($result)->toBeNull();
// });

test('it validates short code format before resolving', function () {
    $result = $this->linkService->resolveLink('invalid!@#');
    expect($result)->toBeNull();
});

test('it uses cache when resolving link', function () {
    $link = $this->linkService->createLink([
        'original_url' => 'https://example.com'
    ]);

    $this->linkService->resolveLink($link->short_code);
    
    expect(Cache::has("link:{$link->short_code}"))->toBeTrue();

    $cached = $this->linkService->resolveLink($link->short_code);
    expect($cached->id)->toBe($link->id);
});

// ============================================
// 5. TRACK VISIT TESTS
// ============================================

test('it tracks a visit', function () {
    $link = $this->linkService->createLink([
        'original_url' => 'https://example.com'
    ]);

    $request = createTestRequest();

    $this->linkService->trackVisit($link, $request);

    $this->assertDatabaseHas('visits', [
        'link_id' => $link->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0 (Test Browser)',
        'referer' => 'https://google.com',
    ]);
});

test('it increments clicks when tracking visit', function () {
    $link = $this->linkService->createLink([
        'original_url' => 'https://example.com'
    ]);

    expect($link->clicks)->toBe(0);

    $request = createTestRequest();
    $this->linkService->trackVisit($link, $request);

    expect($link->fresh()->clicks)->toBe(1);
});

test('it clears cache when tracking visit', function () {
    $link = $this->linkService->createLink([
        'original_url' => 'https://example.com'
    ]);

    $this->linkService->resolveLink($link->short_code);
    expect(Cache::has("link:{$link->short_code}"))->toBeTrue();

    $request = createTestRequest();
    $this->linkService->trackVisit($link, $request);

    expect(Cache::has("link:{$link->short_code}"))->toBeFalse();
});

test('it tracks multiple visits for same link', function () {
    $link = $this->linkService->createLink([
        'original_url' => 'https://example.com'
    ]);

    for ($i = 0; $i < 5; $i++) {
        $request = createTestRequest(['ip' => "192.168.1.{$i}"]);
        $this->linkService->trackVisit($link, $request);
    }

    expect($link->fresh()->clicks)->toBe(5);
    expect(Visit::where('link_id', $link->id)->count())->toBe(5);
});

// ============================================
// 6. GET USER LINKS TESTS
// ============================================

test('it gets user links with pagination', function () {
    $user = createTestUser();
    
    for ($i = 0; $i < 20; $i++) {
        $this->linkService->createLink([
            'original_url' => "https://zaplink.com/{$i}"
        ], $user->id);
    }

    $links = $this->linkService->getUserLinks($user->id, 10);

    expect($links)->toHaveCount(10);
    expect($links->total())->toBe(20);
    expect($links->currentPage())->toBe(1);
});

test('it orders user links by created_at descending', function () {
    $user = createTestUser();
    
    $link1 = $this->linkService->createLink([
        'original_url' => 'https://zaplink.com/1'
    ], $user->id);
    
    sleep(1);
    
    $link2 = $this->linkService->createLink([
        'original_url' => 'https://zaplink.com/2'
    ], $user->id);

    $links = $this->linkService->getUserLinks($user->id);

    expect($links->first()->id)->toBe($link2->id);
    expect($links->last()->id)->toBe($link1->id);
});

test('it returns empty collection for user with no links', function () {
    $user = createTestUser();

    $links = $this->linkService->getUserLinks($user->id);

    expect($links)->toHaveCount(0);
});

test('it only returns links for the specific user', function () {
    $user1 = createTestUser();
    $user2 = createTestUser();

    $this->linkService->createLink([
        'original_url' => 'https://zaplink.com/1'
    ], $user1->id);
    
    $this->linkService->createLink([
        'original_url' => 'https://zaplink.com/2'
    ], $user2->id);

    $user1Links = $this->linkService->getUserLinks($user1->id);
    $user2Links = $this->linkService->getUserLinks($user2->id);

    expect($user1Links)->toHaveCount(1);
    expect($user2Links)->toHaveCount(1);
    expect($user1Links->first()->user_id)->toBe($user1->id);
    expect($user2Links->first()->user_id)->toBe($user2->id);
});

// ============================================
// 7. EDGE CASE TESTS
// ============================================

test('it handles very long URLs', function () {
    $longUrl = 'https://zaplink.com/' . str_repeat('a', 2000);
    $data = [
        'original_url' => $longUrl,
    ];

    $link = $this->linkService->createLink($data);

    expect($link->original_url)->toBe($longUrl);
});

test('it handles URL with special characters', function () {
    $url = 'https://zaplink.com/path?query=value&foo=bar#fragment';
    $data = [
        'original_url' => $url,
    ];

    $link = $this->linkService->createLink($data);

    expect($link->original_url)->toBe($url);
});

test('it generates enough unique codes', function () {
    $codes = [];
    for ($i = 0; $i < 50; $i++) {
        $link = $this->linkService->createLink([
            'original_url' => "https://zaplink.com/{$i}"
        ]);
        $codes[] = $link->short_code;
    }

    expect(count(array_unique($codes)))->toBe(50);
});

// ============================================
// HELPER FUNCTIONS
// ============================================

function createTestUser(array $attributes = []): User
{
    $defaults = [
        'name' => 'Test User',
        'email' => fake()->unique()->safeEmail(),
        'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        'email_verified_at' => now(),
        'remember_token' => \Illuminate\Support\Str::random(10),
    ];

    return User::create(array_merge($defaults, $attributes));
}

function createTestRequest(array $attributes = []): Request
{
    $defaults = [
        'ip' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0 (Test Browser)',
        'referer' => 'https://google.com',
    ];

    $merged = array_merge($defaults, $attributes);
    
    $request = new Request();
    $request->server->set('REMOTE_ADDR', $merged['ip']);
    $request->headers->set('User-Agent', $merged['user_agent']);
    $request->headers->set('referer', $merged['referer']);
    
    return $request;
}