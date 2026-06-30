<?php
// routes/web.php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Web\LinkController;
use Illuminate\Support\Facades\Route;

// ============ HOMEPAGE ============
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ============ AUTHENTICATION ROUTES ============
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// ============ PROTECTED ROUTES ============
Route::middleware(['auth'])->group(function () {
    Route::get('/links', [LinkController::class, 'index'])->name('links.index');
    Route::get('/links/create', [LinkController::class, 'create'])->name('links.create');
    Route::post('/links', [LinkController::class, 'store'])->name('links.store');
    Route::get('/links/{shortCode}', [LinkController::class, 'show'])->name('links.show');
});

// ============ REDIRECT ROUTE - MUST BE LAST! ============
// This catches any remaining URLs and tries to redirect them
Route::get('/{shortCode}', [LinkController::class, 'redirect'])
    ->where('shortCode', '^[a-zA-Z0-9]+$')  // Allow any alphanumeric
    ->name('link.redirect');