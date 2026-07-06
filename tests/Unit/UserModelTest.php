<?php
// tests/Unit/UserModelTest.php

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

test('it has many links', function () {
    $user = User::factory()->create();
    Link::factory()->count(3)->create(['user_id' => $user->id]);

    expect($user->links)->toHaveCount(3);
    expect($user->links->first())->toBeInstanceOf(Link::class);
});

test('it returns empty collection for links when none exist', function () {
    $user = User::factory()->create();

    expect($user->links)->toBeEmpty();
    expect($user->links)->toHaveCount(0);
});

test('it has many visits through links', function () {
    $user = User::factory()->create();
    $link = Link::factory()->create(['user_id' => $user->id]);
    Visit::factory()->count(3)->create(['link_id' => $link->id]);

    expect($user->visits)->toHaveCount(3);
    expect($user->visits->first())->toBeInstanceOf(Visit::class);
});

test('it returns empty collection for visits when none exist', function () {
    $user = User::factory()->create();

    expect($user->visits)->toBeEmpty();
    expect($user->visits)->toHaveCount(0);
});

// ============================================
// 3. CUSTOM METHOD TESTS
// ============================================

test('it checks if user has links', function () {
    $userWithLinks = User::factory()->create();
    Link::factory()->create(['user_id' => $userWithLinks->id]);

    $userWithoutLinks = User::factory()->create();

    expect($userWithLinks->hasLinks())->toBeTrue();
    expect($userWithoutLinks->hasLinks())->toBeFalse();
});

test('it returns false for hasLinks when user has no links', function () {
    $user = User::factory()->create();

    expect($user->hasLinks())->toBeFalse();
});

test('it returns true for hasLinks when user has links', function () {
    $user = User::factory()->create();
    Link::factory()->create(['user_id' => $user->id]);

    expect($user->hasLinks())->toBeTrue();
});

// ============================================
// 4. ACCESSOR TESTS
// ============================================

test('it calculates total clicks correctly', function () {
    $user = User::factory()->create();
    $link1 = Link::factory()->create([
        'user_id' => $user->id,
        'clicks' => 10
    ]);
    $link2 = Link::factory()->create([
        'user_id' => $user->id,
        'clicks' => 5
    ]);

    expect($user->total_clicks)->toBe(15);
});

test('it returns 0 for total_clicks when user has no links', function () {
    $user = User::factory()->create();

    expect($user->total_clicks)->toBe(0);
});

test('it calculates active links count correctly', function () {
    $user = User::factory()->create();
    
    // Active links
    Link::factory()->count(3)->create([
        'user_id' => $user->id,
        'is_active' => true,
        'expires_at' => null
    ]);
    
    // Inactive links
    Link::factory()->count(2)->create([
        'user_id' => $user->id,
        'is_active' => false,
        'expires_at' => null
    ]);

    expect($user->active_links_count)->toBe(3);
});

test('it returns 0 for active_links_count when user has no links', function () {
    $user = User::factory()->create();

    expect($user->active_links_count)->toBe(0);
});

test('it excludes expired links from active_links_count', function () {
    $user = User::factory()->create();
    
    Link::factory()->count(2)->create([
        'user_id' => $user->id,
        'is_active' => true,
        'expires_at' => null
    ]);
    
    Link::factory()->create([
        'user_id' => $user->id,
        'is_active' => true,
        'expires_at' => now()->subDay()
    ]);

    expect($user->active_links_count)->toBe(2);
});

// ============================================
// 5. MODEL CREATION TESTS
// ============================================

test('it can create a user with factory', function () {
    $user = User::factory()->create();

    expect($user)->toBeInDatabase('users', [
        'id' => $user->id,
        'email' => $user->email
    ]);
    expect($user->name)->not->toBeNull();
    expect($user->email)->not->toBeNull();
});

test('it can create a user with custom attributes', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);

    expect($user)->toBeInDatabase('users', [
        'id' => $user->id,
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
});

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

test('it can update user attributes', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com'
    ]);

    $user->update([
        'name' => 'New Name',
        'email' => 'new@example.com'
    ]);

    expect($user)->toBeInDatabase('users', [
        'id' => $user->id,
        'name' => 'New Name',
        'email' => 'new@example.com'
    ]);
});

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

test('it can delete a user', function () {
    $user = User::factory()->create();

    $user->delete();

    expect($user)->not->toBeInDatabase('users', [
        'id' => $user->id
    ]);
});

test('it deletes associated links when user is deleted', function () {
    $user = User::factory()->create();
    Link::factory()->count(3)->create(['user_id' => $user->id]);

    expect(Link::count())->toBe(3);

    $user->delete();

    expect(Link::where('user_id', $user->id)->count())->toBe(0);
    expect(Link::count())->toBe(0);
});

test('it deletes associated visits when user is deleted', function () {
    $user = User::factory()->create();
    $link = Link::factory()->create(['user_id' => $user->id]);
    Visit::factory()->count(3)->create(['link_id' => $link->id]);

    expect(Visit::count())->toBe(3);

    $user->delete();

    // Visits should be deleted through the link cascade
    expect(Visit::count())->toBe(0);
});

// ============================================
// 8. AUTHENTICATION TESTS
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
// 9. EDGE CASE TESTS
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
// 10. MODEL ATTRIBUTE TESTS
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
// 11. FACTORY STATE TESTS
// ============================================

test('it can create a user with verified email', function () {
    $user = User::factory()->verified()->create();

    expect($user->email_verified_at)->not->toBeNull();
    expect($user->email_verified_at)->toBeInstanceOf(\Carbon\Carbon::class);
});

test('it can create a user with unverified email', function () {
    $user = User::factory()->unverified()->create();

    expect($user->email_verified_at)->toBeNull();
});

test('it can create a user with a specific name', function () {
    $user = User::factory()->withName('Custom User')->create();

    expect($user->name)->toBe('Custom User');
});

// ============================================
// 12. STATISTICS TESTS
// ============================================

test('it calculates total clicks across all user links', function () {
    $user = User::factory()->create();
    
    Link::factory()->create([
        'user_id' => $user->id,
        'clicks' => 25
    ]);
    Link::factory()->create([
        'user_id' => $user->id,
        'clicks' => 75
    ]);
    Link::factory()->create([
        'user_id' => $user->id,
        'clicks' => 100
    ]);

    expect($user->total_clicks)->toBe(200);
});

test('it counts only links belonging to the user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    Link::factory()->count(5)->create(['user_id' => $user1->id]);
    Link::factory()->count(3)->create(['user_id' => $user2->id]);

    expect($user1->links()->count())->toBe(5);
    expect($user2->links()->count())->toBe(3);
});

// ============================================
// 13. RELATIONSHIP CASCADE TESTS
// ============================================

test('it cascades delete to links when user is deleted', function () {
    $user = User::factory()->create();
    $links = Link::factory()->count(3)->create(['user_id' => $user->id]);

    $user->delete();

    foreach ($links as $link) {
        expect($link)->not->toBeInDatabase('links', [
            'id' => $link->id
        ]);
    }
});

test('it does not delete links belonging to other users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    Link::factory()->count(3)->create(['user_id' => $user1->id]);
    Link::factory()->count(2)->create(['user_id' => $user2->id]);

    $user1->delete();

    expect(Link::count())->toBe(2);
    expect(Link::where('user_id', $user2->id)->count())->toBe(2);
});