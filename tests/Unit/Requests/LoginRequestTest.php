<?php

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Validator;

// ============================================
// 1. AUTHORIZATION TESTS
// ============================================

test('it authorizes the request', function () {
    $request = new LoginRequest();
    expect($request->authorize())->toBeTrue();
});

// ============================================
// 2. REQUIRED FIELD TESTS
// ============================================

test('it validates required fields', function () {
    $data = [];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('email'))->toBeTrue();
    expect($validator->errors()->has('password'))->toBeTrue();
});

test('it validates email is string', function () {
    $data = [
        'email' => 12345,
        'password' => 'password123',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('email'))->toBeTrue();
});

test('it accepts valid email format', function () {
    $data = [
        'email' => 'test@example.com',
        'password' => 'password123',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it accepts email with plus sign', function () {
    $data = [
        'email' => 'test+alias@example.com',
        'password' => 'password123',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it accepts email with dot in local part', function () {
    $data = [
        'email' => 'john.doe@example.com',
        'password' => 'password123',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it accepts email with hyphen', function () {
    $data = [
        'email' => 'john-doe@example.com',
        'password' => 'password123',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it accepts email with underscore', function () {
    $data = [
        'email' => 'john_doe@example.com',
        'password' => 'password123',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it validates email with uppercase (should still pass)', function () {
    $data = [
        'email' => 'TEST@EXAMPLE.COM',
        'password' => 'password123',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

// ============================================
// 3. PASSWORD VALIDATION TESTS
// ============================================

test('it validates password is string', function () {
    $data = [
        'email' => 'test@example.com',
        'password' => 12345,
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('password'))->toBeTrue();
});

test('it accepts any password length', function () {
    $data = [
        'email' => 'test@example.com',
        'password' => '123',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it accepts password with spaces', function () {
    $data = [
        'email' => 'test@example.com',
        'password' => 'password with spaces',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it accepts password with special characters', function () {
    $data = [
        'email' => 'test@example.com',
        'password' => 'P@ssw0rd!@#$%^&*()',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it accepts empty password if not required (but it is required)', function () {
    $data = [
        'email' => 'test@example.com',
        'password' => '',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('password'))->toBeTrue();
});

// ============================================
// 4. REMEMBER ME TESTS
// ============================================

test('it accepts remember as boolean', function () {
    $data = [
        'email' => 'test@example.com',
        'password' => 'password123',
        'remember' => true,
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it accepts remember as 1', function () {
    $data = [
        'email' => 'test@example.com',
        'password' => 'password123',
        'remember' => 1,
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it accepts remember as 0', function () {
    $data = [
        'email' => 'test@example.com',
        'password' => 'password123',
        'remember' => 0,
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it accepts remember when not present', function () {
    $data = [
        'email' => 'test@example.com',
        'password' => 'password123',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

// ============================================
// 5. PREPARE FOR VALIDATION TESTS
// ============================================

test('it passes validation with valid data', function () {
    $data = [
        'email' => 'john@example.com',
        'password' => 'password123',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it passes validation with valid data and remember', function () {
    $data = [
        'email' => 'john@example.com',
        'password' => 'password123',
        'remember' => true,
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it passes validation with valid data and uppercase email', function () {
    $data = [
        'email' => 'JOHN@EXAMPLE.COM',
        'password' => 'password123',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

// ============================================
// 6. CUSTOM MESSAGE TESTS
// ============================================

test('it returns custom error messages', function () {
    $request = new LoginRequest();
    $messages = $request->messages();

    expect($messages)->toBeArray();
    expect($messages)->toHaveKey('email.required', 'Email address is required.');
    expect($messages)->toHaveKey('email.email', 'Please enter a valid email address.');
    expect($messages)->toHaveKey('password.required', 'Password is required.');
});

test('it uses custom email required message', function () {
    $data = ['password' => 'password123'];
    $validator = Validator::make($data, (new LoginRequest())->rules(), (new LoginRequest())->messages());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('email'))->toBe('Email address is required.');
});

test('it uses custom email format message', function () {
    $data = [
        'email' => 'invalid-email',
        'password' => 'password123',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules(), (new LoginRequest())->messages());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('email'))->toBe('Please enter a valid email address.');
});

test('it uses custom password required message', function () {
    $data = ['email' => 'test@example.com'];
    $validator = Validator::make($data, (new LoginRequest())->rules(), (new LoginRequest())->messages());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('password'))->toBe('Password is required.');
});

// ============================================
// 7. RULE STRUCTURE TESTS
// ============================================

test('it returns rules array', function () {
    $request = new LoginRequest();
    $rules = $request->rules();

    expect($rules)->toBeArray();
    expect($rules)->toHaveKeys(['email', 'password', 'remember']);
});

test('it validates email rule structure', function () {
    $request = new LoginRequest();
    $rules = $request->rules();

    expect($rules['email'])->toContain('required');
    expect($rules['email'])->toContain('string');
    expect($rules['email'])->toContain('email');
});

test('it validates password rule structure', function () {
    $request = new LoginRequest();
    $rules = $request->rules();

    expect($rules['password'])->toContain('required');
    expect($rules['password'])->toContain('string');
});

test('it validates remember rule structure', function () {
    $request = new LoginRequest();
    $rules = $request->rules();

    expect($rules['remember'])->toContain('boolean');
});

// ============================================
// 8. EDGE CASE TESTS
// ============================================

test('it handles email with trailing spaces', function () {
    $data = [
        'email' => ' test@example.com ',
        'password' => 'password123',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('email'))->toBeTrue();
});

test('it handles email with newline', function () {
    $data = [
        'email' => "test@example.com\n",
        'password' => 'password123',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('email'))->toBeTrue();
});

test('it handles very long email', function () {
    $data = [
        'email' => str_repeat('a', 245) . '@example.com',
        'password' => 'password123',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    // Email max length is not explicitly validated, but should still pass
    expect($validator->passes())->toBeTrue();
});

test('it handles very long password', function () {
    $data = [
        'email' => 'test@example.com',
        'password' => str_repeat('a', 1000),
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->passes())->toBeTrue();
});

test('it validates with email only', function () {
    $data = [
        'email' => 'test@example.com',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('password'))->toBeTrue();
});

test('it validates with password only', function () {
    $data = [
        'password' => 'password123',
    ];
    $validator = Validator::make($data, (new LoginRequest())->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('email'))->toBeTrue();
});