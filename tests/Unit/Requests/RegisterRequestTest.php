<?php
// tests/Unit/Requests/RegisterRequestTest.php

use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

test('it authorizes the request', function () {
    $request = new RegisterRequest();
    expect($request->authorize())->toBeTrue();
});

test('it validates required fields', function () {
    $data = [];
    $validator = Validator::make($data, (new RegisterRequest())->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('name'))->toBeTrue();
    expect($validator->errors()->has('email'))->toBeTrue();
    expect($validator->errors()->has('password'))->toBeTrue();
    expect($validator->errors()->has('terms'))->toBeTrue();
});

test('it validates email max length', function () {
    $data = [
        'name' => 'Test User',
        'email' => str_repeat('a', 245) . '@example.com', // 256+ characters
        'password' => 'Test@1234',
        'password_confirmation' => 'Test@1234',
        'terms' => 'accepted',
    ];
    $validator = Validator::make($data, (new RegisterRequest())->rules());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('email'))->toBeTrue();
});

test('it handles email with trailing spaces', function () {
    $data = [
        'name' => 'Test User',
        'email' => ' testuser@example.com ',
        'password' => 'Test@1234',
        'password_confirmation' => 'Test@1234',
        'terms' => 'accepted',
    ];
    $validator = Validator::make($data, (new RegisterRequest())->rules());

    // The email validator will handle this
    expect($validator->fails())->toBeTrue(); // Should fail because of spaces
});

test('it returns custom error messages', function () {
    $request = new RegisterRequest();
    $messages = $request->messages();

    expect($messages)->toBeArray();
    expect($messages)->toHaveKey('name.required', 'Please enter your full name.');
    expect($messages)->toHaveKey('email.required', 'Email address is required.');
    expect($messages)->toHaveKey('email.email', 'Please enter a valid email address.');
    expect($messages)->toHaveKey('email.unique', 'This email is already registered.');
    expect($messages)->toHaveKey('password.required', 'Password is required.');
    expect($messages)->toHaveKey('password.confirmed', 'Passwords do not match.');
    expect($messages)->toHaveKey('password.min', 'Password must be at least 8 characters.');
    expect($messages)->toHaveKey('terms.required', 'You must agree to the terms and conditions.');
    expect($messages)->toHaveKey('terms.accepted', 'You must accept the terms to continue.');
});

test('it validates all rules return array', function () {
    $request = new RegisterRequest();
    $rules = $request->rules();

    expect($rules)->toBeArray();
    expect($rules)->toHaveKeys(['name', 'email', 'password', 'terms']);
});

test('it validates name rule structure', function () {
    $request = new RegisterRequest();
    $rules = $request->rules();

    expect($rules['name'])->toBe(['required', 'string', 'max:255']);
});

test('it validates email rule structure', function () {
    $request = new RegisterRequest();
    $rules = $request->rules();

    expect($rules['email'])->toContain('required');
    expect($rules['email'])->toContain('string');
    expect($rules['email'])->toContain('email');
    expect($rules['email'])->toContain('max:255');
    expect($rules['email'])->toContain('unique:users');
});

test('it validates password rule structure', function () {
    $request = new RegisterRequest();
    $rules = $request->rules();

    expect($rules['password'])->toContain('required');
    expect($rules['password'])->toContain('confirmed');
});

test('it validates terms rule structure', function () {
    $request = new RegisterRequest();
    $rules = $request->rules();

    expect($rules['terms'])->toBe(['required', 'accepted']);
});

// ============================================
// HELPER FUNCTIONS
// ============================================

function createUserForRegisterTest(array $attributes = []): User
{
    $defaults = [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password' => \Illuminate\Support\Facades\Hash::make('Test@1234'),
        'email_verified_at' => now(),
        'remember_token' => \Illuminate\Support\Str::random(10),
    ];

    return User::create(array_merge($defaults, $attributes));
}