<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\CourierController;

// Public Routes - Landing Page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    // Admin Dashboard & Routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/users', [AdminController::class, 'userIndex'])->name('admin.users.index');
        Route::get('/users/{user}/edit', [AdminController::class, 'userEdit'])->name('admin.users.edit');
        Route::put('/users/{user}', [AdminController::class, 'userUpdate'])->name('admin.users.update');
    });

    // Product Routes (Admin only)
    Route::resource('products', ProductController::class)->middleware('admin');

    // User Dashboard Routes
    Route::get('/user/dashboard', [UserDashboardController::class, 'dashboard'])->name('user.dashboard');

    // Courier Dashboard Routes
    Route::get('/courier/dashboard', [CourierController::class, 'dashboard'])->name('courier.dashboard');
});


