<?php

use App\Models\User;
use App\Models\Link;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ============================================
// 1. DATABASE TESTS
// ============================================

test('it has the correct table name', function () {
    $user = new User();
    expect($user->getTable())->toBe('users');
});

test('it has the correct fillable attributes', function () {
    $expectedFillable = ['name', 'email', 'password'];
    $user = new User();
    expect($user->getFillable())->toBe($expectedFillable);
});

test('it has the correct casts', function () {
    $user = new User();
    $casts = $user->getCasts();

    expect($casts)->toHaveKey('email_verified_at', 'datetime');
    expect($casts)->toHaveKey('password', 'hashed');
});

test('it has hidden attributes', function () {
    $user = new User();
    $hidden = $user->getHidden();

    expect($hidden)->toContain('password');
    expect($hidden)->toContain('remember_token');
});

// ============================================
// 2. RELATIONSHIP TESTS
// ============================================

test('it returns empty collection for links when none exist', function () {
    $user = User::factory()->create();

    expect($user->links)->toBeEmpty();
    expect($user->links)->toHaveCount(0);
});

test('it returns empty collection for visits when none exist', function () {
    $user = User::factory()->create();

    expect($user->visits)->toBeEmpty();
    expect($user->visits)->toHaveCount(0);
});

// ============================================
// 3. CUSTOM METHOD TESTS
// ============================================

test('it returns false for hasLinks when user has no links', function () {
    $user = User::factory()->create();

    expect($user->hasLinks())->toBeFalse();
});

test('it returns 0 for total_clicks when user has no links', function () {
    $user = User::factory()->create();

    expect($user->total_clicks)->toBe(0);
});

test('it returns 0 for active_links_count when user has no links', function () {
    $user = User::factory()->create();

    expect($user->active_links_count)->toBe(0);
});

// ============================================
// 5. MODEL CREATION TESTS
// ============================================

test('it hashes the password automatically', function () {
    $user = User::factory()->create([
        'password' => 'secret123'
    ]);

    expect($user->password)->not->toBe('secret123');
    expect(password_verify('secret123', $user->password))->toBeTrue();
});

test('it requires unique email addresses', function () {
    User::factory()->create(['email' => 'test@example.com']);

    expect(function () {
        User::factory()->create(['email' => 'test@example.com']);
    })->toThrow(\Illuminate\Database\QueryException::class);
});

// ============================================
// 6. UPDATE TESTS
// ============================================

test('it can update password and hash it', function () {
    $user = User::factory()->create([
        'password' => 'oldpassword'
    ]);

    $user->update([
        'password' => 'newpassword'
    ]);

    expect(password_verify('newpassword', $user->password))->toBeTrue();
});

// ============================================
// 7. DELETE TESTS
// ============================================

test('it can authenticate with correct credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);

    $attempt = auth()->attempt([
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);

    expect($attempt)->toBeTrue();
});

test('it fails authentication with incorrect password', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);

    $attempt = auth()->attempt([
        'email' => 'test@example.com',
        'password' => 'wrongpassword'
    ]);

    expect($attempt)->toBeFalse();
});

test('it fails authentication with non-existent email', function () {
    $attempt = auth()->attempt([
        'email' => 'nonexistent@example.com',
        'password' => 'password123'
    ]);

    expect($attempt)->toBeFalse();
});

// ============================================
// 8. EDGE CASE TESTS
// ============================================

test('it handles very long names', function () {
    $longName = str_repeat('a', 255);
    $user = User::factory()->create(['name' => $longName]);

    expect($user->name)->toBe($longName);
});

test('it handles email with special characters', function () {
    $email = 'john+test@example.com';
    $user = User::factory()->create(['email' => $email]);

    expect($user->email)->toBe($email);
});

test('it handles very long emails', function () {
    $longEmail = str_repeat('a', 50) . '@example.com';
    $user = User::factory()->create(['email' => $longEmail]);

    expect($user->email)->toBe($longEmail);
});

test('it handles empty password after creation', function () {
    // Laravel expects a password, so we need to set it
    $user = User::factory()->make([
        'password' => null
    ]);

    // The user should have a password field but it might be null
    // This is a boundary test - ensure it doesn't crash
    expect($user->password)->toBeNull();
});

// ============================================
// 9. MODEL ATTRIBUTE TESTS
// ============================================

test('it has email as fillable', function () {
    $user = new User();
    expect($user->getFillable())->toContain('email');
});

test('it has name as fillable', function () {
    $user = new User();
    expect($user->getFillable())->toContain('name');
});

test('it has password as fillable', function () {
    $user = new User();
    expect($user->getFillable())->toContain('password');
});

test('it has password hidden', function () {
    $user = new User();
    expect($user->getHidden())->toContain('password');
});

test('it has remember_token hidden', function () {
    $user = new User();
    expect($user->getHidden())->toContain('remember_token');
});

// ============================================
// 10. FACTORY STATE TESTS
// ============================================

test('it can create a user with unverified email', function () {
    $user = User::factory()->unverified()->create();

    expect($user->email_verified_at)->toBeNull();
});
