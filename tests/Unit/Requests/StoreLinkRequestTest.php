<?php

use App\Http\Requests\StoreLinkRequest;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

// ============================================
// 1. AUTHORIZATION TESTS
// ============================================

test('it authorizes the request', function () {
    $request = new StoreLinkRequest();
    expect($request->authorize())->toBeTrue();
});

// ============================================
// 2. REQUIRED FIELD TESTS
// ============================================

test('it validates original_url is present', function () {
    $data = [
        'title' => 'Test Link',
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('original_url'))->toBeTrue();
});

// ============================================
// 3. URL FORMAT VALIDATION TESTS
// ============================================

test('it accepts valid http URL', function () {
    $data = [
        'original_url' => 'http://example.com',
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it accepts valid https URL', function () {
    $data = [
        'original_url' => 'https://zaplink.com',
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it accepts URL with path', function () {
    $data = [
        'original_url' => 'https://zaplink.com/path/to/page',
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it accepts URL with query parameters', function () {
    $data = [
        'original_url' => 'https://zaplink.com?query=value&foo=bar',
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it accepts URL with anchor', function () {
    $data = [
        'original_url' => 'https://zaplink.com#section',
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});


test('it rejects file protocol', function () {
    $data = [
        'original_url' => 'file:///etc/passwd',
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('original_url'))->toBeTrue();
});

// ============================================
// 4. URL MAX LENGTH TESTS
// ============================================

test('it accepts URL at max length', function () {
    $longUrl = 'https://zaplink.com/' . str_repeat('a', 2000);
    $data = [
        'original_url' => $longUrl,
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

// ============================================
// 5. ZAPLINK URL VALIDATION TESTS
// ============================================

test('it allows shortening URL with same domain but not a ZapLink code', function () {
    $data = [
        'original_url' => url('/some/random/path'),
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    // Should pass if the path doesn't look like a short code (4-8 alphanumeric)
    expect($validator->passes())->toBeTrue();
});

test('it allows shortening URL with path that is too long to be a short code', function () {
    $data = [
        'original_url' => url('/this-is-a-long-path'),
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

// ============================================
// 6. SELF-REFERENCING VALIDATION TESTS
// ============================================

// ============================================
// 7. TITLE VALIDATION TESTS
// ============================================

test('it allows title to be null', function () {
    $data = [
        'original_url' => 'https://zaplink.com',
        'title' => null,
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it allows title to be empty string', function () {
    $data = [
        'original_url' => 'https://zaplink.com',
        'title' => '',
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it validates title is string', function () {
    $data = [
        'original_url' => 'https://zaplink.com',
        'title' => 123,
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('title'))->toBeTrue();
});

test('it validates title max length', function () {
    $data = [
        'original_url' => 'https://zaplink.com',
        'title' => str_repeat('a', 256),
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('title'))->toBeTrue();
});

test('it accepts title with special characters', function () {
    $data = [
        'original_url' => 'https://zaplink.com',
        'title' => 'My Awesome Link! @#$%',
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

// ============================================
// 8. CUSTOM CODE VALIDATION TESTS
// ============================================

test('it allows custom_code to be null', function () {
    $data = [
        'original_url' => 'https://zaplink.com',
        'custom_code' => null,
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it accepts valid custom_code', function () {
    $data = [
        'original_url' => 'https://zaplink.com',
        'custom_code' => 'valid123',
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it accepts custom_code with uppercase letters', function () {
    $data = [
        'original_url' => 'https://zaplink.com',
        'custom_code' => 'VALID123',
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

// ============================================
// 9. EXPIRATION DATE VALIDATION TESTS
// ============================================

test('it allows expires_at to be null', function () {
    $data = [
        'original_url' => 'https://zaplink.com',
        'expires_at' => null,
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it allows expires_at to be omitted', function () {
    $data = [
        'original_url' => 'https://zaplink.com',
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it validates expires_at is a valid date', function () {
    $data = [
        'original_url' => 'https://zaplink.com',
        'expires_at' => 'not-a-date',
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('expires_at'))->toBeTrue();
});

test('it validates expires_at is after now', function () {
    $data = [
        'original_url' => 'https://zaplink.com',
        'expires_at' => now()->addDay(),
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it validates expires_at with date string', function () {
    $data = [
        'original_url' => 'https://zaplink.com',
        'expires_at' => now()->addWeek()->toDateString(),
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it validates expires_at with datetime string', function () {
    $data = [
        'original_url' => 'https://zaplink.com',
        'expires_at' => now()->addWeek()->toDateTimeString(),
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

// ============================================
// 10. VALID DATA TESTS
// ============================================

test('it passes validation with minimal valid data', function () {
    $data = [
        'original_url' => 'https://zaplink.com',
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it passes validation with custom code only', function () {
    $data = [
        'original_url' => 'https://zaplink.com',
        'custom_code' => 'custom123',
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it passes validation with title only', function () {
    $data = [
        'original_url' => 'https://zaplink.com',
        'title' => 'Test Title',
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it passes validation with expiration only', function () {
    $data = [
        'original_url' => 'https://zaplink.com',
        'expires_at' => now()->addWeek(),
    ];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

// ============================================
// 11. CUSTOM MESSAGE TESTS
// ============================================

test('it returns custom error messages', function () {
    $request = new StoreLinkRequest();
    $messages = $request->messages();

    expect($messages)->toBeArray();
    expect($messages)->toHaveKey('original_url.required', 'Please enter a URL to shorten.');
    expect($messages)->toHaveKey('original_url.url', 'Please enter a valid URL.');
    expect($messages)->toHaveKey('original_url.max', 'URL is too long. Please shorten it further.');
    expect($messages)->toHaveKey('custom_code.alpha_num', 'Custom code can only contain letters and numbers.');
    expect($messages)->toHaveKey('custom_code.min', 'Custom code must be at least 4 characters.');
    expect($messages)->toHaveKey('custom_code.max', 'Custom code cannot exceed 20 characters.');
    expect($messages)->toHaveKey('custom_code.unique', 'This custom code is already taken.');
    expect($messages)->toHaveKey('expires_at.after', 'Expiration date must be in the future.');
});

test('it uses custom required message', function () {
    $data = [];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules(), (new StoreLinkRequest())->messages());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('original_url'))->toBe('Please enter a URL to shorten.');
});

test('it uses custom url format message', function () {
    $data = ['original_url' => 'invalid'];
    $validator = Validator::make($data, (new StoreLinkRequest())->rules(), (new StoreLinkRequest())->messages());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('original_url'))->toBe('Please enter a valid URL.');
});

// ============================================
// 12. RULE STRUCTURE TESTS
// ============================================

test('it returns rules array', function () {
    $request = new StoreLinkRequest();
    $rules = $request->rules();

    expect($rules)->toBeArray();
    expect($rules)->toHaveKeys(['original_url', 'title', 'custom_code', 'expires_at']);
});

test('it validates original_url rule structure', function () {
    $request = new StoreLinkRequest();
    $rules = $request->rules();

    expect($rules['original_url'])->toContain('required');
    expect($rules['original_url'])->toContain('url');
    expect($rules['original_url'])->toContain('max:2048');
});

test('it validates title rule structure', function () {
    $request = new StoreLinkRequest();
    $rules = $request->rules();

    expect($rules['title'])->toContain('nullable');
    expect($rules['title'])->toContain('string');
    expect($rules['title'])->toContain('max:255');
});

test('it validates custom_code rule structure', function () {
    $request = new StoreLinkRequest();
    $rules = $request->rules();

    expect($rules['custom_code'])->toContain('nullable');
    expect($rules['custom_code'])->toContain('alpha_num');
    expect($rules['custom_code'])->toContain('min:4');
    expect($rules['custom_code'])->toContain('max:20');
    expect($rules['custom_code'])->toContain('unique:links,short_code');
});

test('it validates expires_at rule structure', function () {
    $request = new StoreLinkRequest();
    $rules = $request->rules();

    expect($rules['expires_at'])->toContain('nullable');
    expect($rules['expires_at'])->toContain('date');
    expect($rules['expires_at'])->toContain('after:now');
});

// ============================================
// 13. IS ZAPLINK URL METHOD TESTS
// ============================================

test('isZapLinkUrl returns false for non-ZapLink URL', function () {
    $request = new StoreLinkRequest();
    $reflection = new ReflectionMethod($request, 'isZapLinkUrl');
    $reflection->setAccessible(true);

    $result = $reflection->invoke($request, 'https://zaplink.com');
    expect($result)->toBeFalse();
});

test('isZapLinkUrl returns false for different domain', function () {
    $request = new StoreLinkRequest();
    $reflection = new ReflectionMethod($request, 'isZapLinkUrl');
    $reflection->setAccessible(true);

    $result = $reflection->invoke($request, 'https://other-domain.com/abc123');
    expect($result)->toBeFalse();
});

// ============================================
// 14. IS SELF REFERENCING METHOD TESTS
// ============================================

test('isSelfReferencing returns false for non-existing short code', function () {
    $request = new StoreLinkRequest();
    $reflection = new ReflectionMethod($request, 'isSelfReferencing');
    $reflection->setAccessible(true);

    $result = $reflection->invoke($request, url('/xyz999'));
    expect($result)->toBeFalse();
});

test('isSelfReferencing returns false for external URL', function () {
    $request = new StoreLinkRequest();
    $reflection = new ReflectionMethod($request, 'isSelfReferencing');
    $reflection->setAccessible(true);

    $result = $reflection->invoke($request, 'https://zaplink.com/abc123');
    expect($result)->toBeFalse();
});

// ============================================
// HELPER FUNCTIONS
// ============================================

function createUserForStoreLinkTest(array $attributes = []): User
{
    $defaults = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        'email_verified_at' => now(),
        'remember_token' => \Illuminate\Support\Str::random(10),
    ];

    return User::create(array_merge($defaults, $attributes));
}