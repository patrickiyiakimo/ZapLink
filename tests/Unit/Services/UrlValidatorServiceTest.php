<?php

use App\Services\UrlValidatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->urlValidator = app(UrlValidatorService::class);
    Cache::flush();
});

// ============================================
// 1. VALIDATE METHOD TESTS
// ============================================

test('it validates a valid URL', function () {
    $result = $this->urlValidator->validate('https://zaplink.com');
    expect($result)->toBeTrue();
});

test('it validates a valid URL with www', function () {
    $result = $this->urlValidator->validate('https://www.example.com');
    expect($result)->toBeTrue();
});

test('it validates a valid URL with path', function () {
    $result = $this->urlValidator->validate('https://zaplink.com/path/to/page');
    expect($result)->toBeTrue();
});

test('it validates a valid URL with query parameters', function () {
    $result = $this->urlValidator->validate('https://zaplink.com?query=value&foo=bar');
    expect($result)->toBeTrue();
});

test('it validates a valid URL with anchor', function () {
    $result = $this->urlValidator->validate('https://zaplink.com#section');
    expect($result)->toBeTrue();
});

test('it returns false for empty URL', function () {
    $result = $this->urlValidator->validate('');
    expect($result)->toBeFalse();
});

test('it returns false for invalid URL format', function () {
    $result = $this->urlValidator->validate('not-a-valid-url');
    expect($result)->toBeFalse();
});

test('it returns false for URL without protocol', function () {
    $result = $this->urlValidator->validate('example.com');
    expect($result)->toBeFalse();
});

test('it returns false for malformed URL', function () {
    $result = $this->urlValidator->validate('http:///example.com');
    expect($result)->toBeFalse();
});

test('it returns false for URL with spaces', function () {
    $result = $this->urlValidator->validate('https://example .com');
    expect($result)->toBeFalse();
});

// ============================================
// 2. BLOCKED DOMAIN TESTS
// ============================================

test('it allows domains not in blocked list', function () {
    $result = $this->urlValidator->validate('https://google.com');
    expect($result)->toBeTrue();
});

test('it returns false for timeout when checking accessibility', function () {
    config(['zap-link.check_url_accessibility' => true]);
    
    Http::fake(function () {
        throw new \Exception('Connection timeout');
    });

    $result = $this->urlValidator->validate('https://zaplink.com');
    expect($result)->toBeFalse();
});

test('it uses cached accessibility results', function () {
    config(['zap-link.check_url_accessibility' => true]);
    
    $cacheKey = 'url_check:' . md5('https://zaplink.com');
    Cache::put($cacheKey, true, 3600);
    
    // No HTTP call should be made
    Http::fake(function () {
        throw new \Exception('HTTP call should not be made');
    });

    $result = $this->urlValidator->validate('https://zaplink.com');
    expect($result)->toBeTrue();
});

test('it skips accessibility check when disabled', function () {
    config(['zap-link.check_url_accessibility' => false]);
    
    // No HTTP call should be made
    Http::fake(function () {
        throw new \Exception('HTTP call should not be made');
    });

    $result = $this->urlValidator->validate('https://zaplink.com');
    expect($result)->toBeTrue();
});

// ============================================
// 4. SANITIZE METHOD TESTS
// ============================================

test('it sanitizes URL by trimming whitespace', function () {
    $result = $this->urlValidator->sanitize('  https://zaplink.com  ');
    expect($result)->toBe('https://zaplink.com');
});

test('it adds https protocol to URL without protocol', function () {
    $result = $this->urlValidator->sanitize('zaplink.com');
    expect($result)->toBe('https://zaplink.com');
});

test('it adds https protocol to URL with www', function () {
    $result = $this->urlValidator->sanitize('www.zaplink.com');
    expect($result)->toBe('https://www.zaplink.com');
});

test('it preserves http protocol', function () {
    $result = $this->urlValidator->sanitize('http://zaplink.com');
    expect($result)->toBe('http://zaplink.com');
});

test('it preserves https protocol', function () {
    $result = $this->urlValidator->sanitize('https://zaplink.com');
    expect($result)->toBe('https://zaplink.com');
});

test('it sanitizes URL with query parameters', function () {
    $url = 'https://zaplink.com?query=value&foo=bar';
    $result = $this->urlValidator->sanitize($url);
    expect($result)->toBe('https://zaplink.com?query=value&foo=bar');
});

test('it sanitizes URL with trailing slash', function () {
    $result = $this->urlValidator->sanitize('https://zaplink.com/');
    expect($result)->toBe('https://zaplink.com/');
});

test('it sanitizes URL with encoded characters', function () {
    $url = 'https://zaplink.com/%20%21%40';
    $result = $this->urlValidator->sanitize($url);
    expect($result)->toBe('https://zaplink.com/%20%21%40');
});

test('it sanitizes empty URL', function () {
    $result = $this->urlValidator->sanitize('');
    expect($result)->toBe('https://');
});

// ============================================
// 5. COMBINED VALIDATION AND SANITIZATION TESTS
// ============================================

test('it validates sanitized URL', function () {
    $url = '  zaplink.com  ';
    $sanitized = $this->urlValidator->sanitize($url);
    $valid = $this->urlValidator->validate($sanitized);
    
    expect($sanitized)->toBe('https://zaplink.com');
    expect($valid)->toBeTrue();
});

test('it validates URL with protocol after sanitization', function () {
    $url = 'zaplink.com';
    $sanitized = $this->urlValidator->sanitize($url);
    expect($sanitized)->toBe('https://zaplink.com');
});

// ============================================
// 6. EDGE CASE TESTS
// ============================================

test('it handles very long URLs', function () {
    $longUrl = 'https://zaplink.com/' . str_repeat('a', 2000);
    $result = $this->urlValidator->validate($longUrl);
    expect($result)->toBeTrue();
});

test('it handles URL with IP address', function () {
    $result = $this->urlValidator->validate('https://127.0.0.1');
    expect($result)->toBeTrue();
});

test('it handles URL with port', function () {
    $result = $this->urlValidator->validate('https://zaplink.com/:8080');
    expect($result)->toBeTrue();
});

test('it handles URL with authentication', function () {
    $result = $this->urlValidator->validate('https://user:pass@example.com');
    expect($result)->toBeTrue();
});

test('it handles URL with unusual TLD', function () {
    $result = $this->urlValidator->validate('https://example.xyz');
    expect($result)->toBeTrue();
});

test('it handles URL with hyphen in domain', function () {
    $result = $this->urlValidator->validate('https://my-example-site.com');
    expect($result)->toBeTrue();
});

test('it handles localhost URL', function () {
    $result = $this->urlValidator->validate('http://localhost');
    expect($result)->toBeTrue();
});

test('it handles URL without TLD', function () {
    $result = $this->urlValidator->validate('https://localhost');
    expect($result)->toBeTrue();
});

// ============================================
// 7. CONFIGURATION TESTS
// ============================================

test('it uses blocked domains from config', function () {
    config(['zap-link.blocked_domains' => ['test.com']]);
    
    $result = $this->urlValidator->validate('https://test.com');
    expect($result)->toBeFalse();
});

test('it allows domains when blocked list is empty', function () {
    config(['zap-link.blocked_domains' => []]);
    
    $result = $this->urlValidator->validate('https://any-domain.com');
    expect($result)->toBeTrue();
});

test('it respects check_url_accessibility config', function () {
    config(['zap-link.check_url_accessibility' => false]);
    
    // Should not make HTTP call
    Http::fake(function () {
        throw new \Exception('HTTP call should not be made');
    });

    $result = $this->urlValidator->validate('https://zaplink.com/');
    expect($result)->toBeTrue();
});

// ============================================
// 8. PERFORMANCE TESTS
// ============================================

test('it validates URLs quickly for simple URLs', function () {
    $start = microtime(true);
    $this->urlValidator->validate('https://zaplink.com');
    $duration = microtime(true) - $start;
    
    expect($duration)->toBeLessThan(0.5);
});

// ============================================
// 9. ERROR HANDLING TESTS
// ============================================

test('it handles malformed URL gracefully', function () {
    $result = $this->urlValidator->validate('http:///invalid');
    expect($result)->toBeFalse();
});
