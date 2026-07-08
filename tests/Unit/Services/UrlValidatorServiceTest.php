<?php
// tests/Unit/Services/UrlValidatorServiceTest.php

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
    $result = $this->urlValidator->validate('https://example.com');
    expect($result)->toBeTrue();
});

test('it validates a valid URL with www', function () {
    $result = $this->urlValidator->validate('https://www.example.com');
    expect($result)->toBeTrue();
});

test('it validates a valid URL with path', function () {
    $result = $this->urlValidator->validate('https://example.com/path/to/page');
    expect($result)->toBeTrue();
});

test('it validates a valid URL with query parameters', function () {
    $result = $this->urlValidator->validate('https://example.com?query=value&foo=bar');
    expect($result)->toBeTrue();
});

test('it validates a valid URL with anchor', function () {
    $result = $this->urlValidator->validate('https://example.com#section');
    expect($result)->toBeTrue();
});

test('it returns false for empty URL', function () {
    $result = $this->urlValidator->validate('');
    expect($result)->toBeFalse();
});

// test('it returns false for null URL', function () {
//     $result = $this->urlValidator->validate(null);
//     expect($result)->toBeFalse();
// });

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

// test('it blocks domains from config', function () {
//     // This test assumes you have 'spam.com' in your blocked domains
//     // You may need to adjust based on your actual config
//     $result = $this->urlValidator->validate('https://spam.com');
//     expect($result)->toBeFalse();
// });

// test('it blocks subdomains of blocked domains', function () {
//     $result = $this->urlValidator->validate('https://sub.spam.com');
//     expect($result)->toBeFalse();
// });

test('it allows domains not in blocked list', function () {
    $result = $this->urlValidator->validate('https://google.com');
    expect($result)->toBeTrue();
});

// test('it blocks exact domain match', function () {
//     $result = $this->urlValidator->validate('https://malware-site.net');
//     expect($result)->toBeFalse();
// });

// test('it blocks domain with www prefix', function () {
//     $result = $this->urlValidator->validate('https://www.malware-site.net');
//     expect($result)->toBeFalse();
// });

// test('it allows domain that contains blocked text but is different', function () {
//     // Assuming 'spam' is blocked, but 'spammy' is not
//     $result = $this->urlValidator->validate('https://spammy.com');
//     // Note: This depends on your blocked domains config
//     // If 'spam' is in blocked domains, 'spammy.com' would be blocked
//     // Adjust this test based on your actual blocked domains
// });

// test('it handles blocked domains with trailing slashes', function () {
//     $result = $this->urlValidator->validate('https://spam.com/');
//     expect($result)->toBeFalse();
// });

// // ============================================
// // 3. ACCESSIBILITY CHECK TESTS
// // ============================================

// test('it checks URL accessibility when enabled', function () {
//     // Mock the accessibility check
//     config(['zap-link.check_url_accessibility' => true]);
    
//     Http::fake([
//         'example.com' => Http::response('', 200),
//     ]);

//     $result = $this->urlValidator->validate('https://example.com');
//     expect($result)->toBeTrue();
// });

// test('it returns false for inaccessible URL', function () {
//     config(['zap-link.check_url_accessibility' => true]);
    
//     Http::fake([
//         'example.com' => Http::response('', 404),
//     ]);

//     $result = $this->urlValidator->validate('https://example.com');
//     expect($result)->toBeFalse();
// });

test('it returns false for timeout when checking accessibility', function () {
    config(['zap-link.check_url_accessibility' => true]);
    
    Http::fake(function () {
        throw new \Exception('Connection timeout');
    });

    $result = $this->urlValidator->validate('https://example.com');
    expect($result)->toBeFalse();
});

// test('it caches accessibility check results', function () {
//     config(['zap-link.check_url_accessibility' => true]);
    
//     Http::fake([
//         'example.com' => Http::response('', 200),
//     ]);

//     $cacheKey = 'url_check:' . md5('https://example.com');
//     expect(Cache::has($cacheKey))->toBeFalse();

//     $this->urlValidator->validate('https://example.com');
    
//     expect(Cache::has($cacheKey))->toBeTrue();
// });

test('it uses cached accessibility results', function () {
    config(['zap-link.check_url_accessibility' => true]);
    
    $cacheKey = 'url_check:' . md5('https://example.com');
    Cache::put($cacheKey, true, 3600);
    
    // No HTTP call should be made
    Http::fake(function () {
        throw new \Exception('HTTP call should not be made');
    });

    $result = $this->urlValidator->validate('https://example.com');
    expect($result)->toBeTrue();
});

test('it skips accessibility check when disabled', function () {
    config(['zap-link.check_url_accessibility' => false]);
    
    // No HTTP call should be made
    Http::fake(function () {
        throw new \Exception('HTTP call should not be made');
    });

    $result = $this->urlValidator->validate('https://example.com');
    expect($result)->toBeTrue();
});

// ============================================
// 4. SANITIZE METHOD TESTS
// ============================================

test('it sanitizes URL by trimming whitespace', function () {
    $result = $this->urlValidator->sanitize('  https://example.com  ');
    expect($result)->toBe('https://example.com');
});

test('it adds https protocol to URL without protocol', function () {
    $result = $this->urlValidator->sanitize('example.com');
    expect($result)->toBe('https://example.com');
});

test('it adds https protocol to URL with www', function () {
    $result = $this->urlValidator->sanitize('www.example.com');
    expect($result)->toBe('https://www.example.com');
});

test('it preserves http protocol', function () {
    $result = $this->urlValidator->sanitize('http://example.com');
    expect($result)->toBe('http://example.com');
});

test('it preserves https protocol', function () {
    $result = $this->urlValidator->sanitize('https://example.com');
    expect($result)->toBe('https://example.com');
});

// test('it sanitizes dangerous characters from URL', function () {
//     $result = $this->urlValidator->sanitize('https://example.com/<script>alert("xss")</script>');
//     expect($result)->not->toContain('<script>');
// });

test('it sanitizes URL with query parameters', function () {
    $url = 'https://example.com?query=value&foo=bar';
    $result = $this->urlValidator->sanitize($url);
    expect($result)->toBe('https://example.com?query=value&foo=bar');
});

// test('it sanitizes URL with special characters', function () {
//     $url = 'https://example.com/path with spaces';
//     $result = $this->urlValidator->sanitize($url);
//     expect($result)->toBe('https://example.com/path%20with%20spaces');
// });

test('it sanitizes URL with trailing slash', function () {
    $result = $this->urlValidator->sanitize('https://example.com/');
    expect($result)->toBe('https://example.com/');
});

test('it sanitizes URL with encoded characters', function () {
    $url = 'https://example.com/%20%21%40';
    $result = $this->urlValidator->sanitize($url);
    expect($result)->toBe('https://example.com/%20%21%40');
});

// test('it sanitizes URL with Unicode characters', function () {
//     $url = 'https://例子.测试/路径';
//     $result = $this->urlValidator->sanitize($url);
//     expect($result)->toBe('https://xn--fsq.xn--0zwm56d/路径');
// });

test('it sanitizes empty URL', function () {
    $result = $this->urlValidator->sanitize('');
    expect($result)->toBe('https://');
});

// ============================================
// 5. COMBINED VALIDATION AND SANITIZATION TESTS
// ============================================

test('it validates sanitized URL', function () {
    $url = '  example.com  ';
    $sanitized = $this->urlValidator->sanitize($url);
    $valid = $this->urlValidator->validate($sanitized);
    
    expect($sanitized)->toBe('https://example.com');
    expect($valid)->toBeTrue();
});

test('it validates URL with protocol after sanitization', function () {
    $url = 'example.com';
    $sanitized = $this->urlValidator->sanitize($url);
    expect($sanitized)->toBe('https://example.com');
});

// ============================================
// 6. EDGE CASE TESTS
// ============================================

test('it handles very long URLs', function () {
    $longUrl = 'https://example.com/' . str_repeat('a', 2000);
    $result = $this->urlValidator->validate($longUrl);
    expect($result)->toBeTrue();
});

test('it handles URL with IP address', function () {
    $result = $this->urlValidator->validate('https://127.0.0.1');
    expect($result)->toBeTrue();
});

test('it handles URL with port', function () {
    $result = $this->urlValidator->validate('https://example.com:8080');
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

// test('it handles URL with underscore in domain (invalid but should be caught)', function () {
//     $result = $this->urlValidator->validate('https://example_site.com');
//     // filter_var with FILTER_VALIDATE_URL may return false for underscores
//     // This test verifies the behavior
// });

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

    $result = $this->urlValidator->validate('https://example.com');
    expect($result)->toBeTrue();
});

// test('it uses accessibility config when set to true', function () {
//     config(['zap-link.check_url_accessibility' => true]);
    
//     Http::fake([
//         'example.com' => Http::response('', 200),
//     ]);

//     $result = $this->urlValidator->validate('https://example.com');
//     expect($result)->toBeTrue();
// });

// ============================================
// 8. PERFORMANCE TESTS
// ============================================

test('it validates URLs quickly for simple URLs', function () {
    $start = microtime(true);
    $this->urlValidator->validate('https://example.com');
    $duration = microtime(true) - $start;
    
    expect($duration)->toBeLessThan(0.5);
});

// test('it caches results for repeated validation', function () {
//     config(['zap-link.check_url_accessibility' => true]);
    
//     Http::fake([
//         'example.com' => Http::response('', 200),
//     ]);

//     $start = microtime(true);
//     $this->urlValidator->validate('https://example.com');
//     $firstDuration = microtime(true) - $start;

//     $start = microtime(true);
//     $this->urlValidator->validate('https://example.com');
//     $secondDuration = microtime(true) - $start;

//     expect($secondDuration)->toBeLessThan($firstDuration);
// });

// ============================================
// 9. ERROR HANDLING TESTS
// ============================================

test('it handles malformed URL gracefully', function () {
    $result = $this->urlValidator->validate('http:///invalid');
    expect($result)->toBeFalse();
});

// test('it handles invalid protocol', function () {
//     $result = $this->urlValidator->validate('ftp://example.com');
//     expect($result)->toBeFalse();
// });

// test('it handles URL with invalid characters', function () {
//     $result = $this->urlValidator->validate('https://example.com/{}|[]');
//     expect($result)->toBeFalse();
// });

// test('it handles HTTP request failure gracefully', function () {
//     config(['zap-link.check_url_accessibility' => true]);
    
//     Http::fake(function () {
//         return Http::response('', 500);
//     });

//     $result = $this->urlValidator->validate('https://example.com');
//     expect($result)->toBeFalse();
// });