<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\DeliveryOrderController;
use App\Http\Controllers\MessageController;

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
    // General Dashboard Route - Redirects based on role
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        return match($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'courier' => redirect()->route('courier.dashboard'),
            default => redirect()->route('user.dashboard'),
        };
    })->name('dashboard');

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

    // Delivery Order Routes - User
    Route::prefix('user/orders')->group(function () {
        Route::get('/', [DeliveryOrderController::class, 'index'])->name('user.orders.index');
        Route::get('/create', [DeliveryOrderController::class, 'create'])->name('user.orders.create');
        Route::post('/', [DeliveryOrderController::class, 'store'])->name('user.orders.store');
        Route::get('/{order}', [DeliveryOrderController::class, 'show'])->name('user.orders.show');
        Route::post('/{order}/cancel', [DeliveryOrderController::class, 'cancel'])->name('user.orders.cancel');
    });

    // Courier Dashboard Routes
    Route::get('/courier/dashboard', [CourierController::class, 'dashboard'])->name('courier.dashboard');

    // Courier Order Routes
    Route::prefix('courier/orders')->group(function () {
        Route::post('/{order}/accept', [CourierController::class, 'acceptOrder'])->name('courier.accept');
        Route::post('/{order}/pickup', [CourierController::class, 'pickupOrder'])->name('courier.pickup');
        Route::post('/{order}/deliver', [CourierController::class, 'deliverOrder'])->name('courier.deliver');
        Route::post('/{order}/cancel', [CourierController::class, 'cancelOrder'])->name('courier.cancel');
    });

    // Chat & Messages Routes
    Route::prefix('orders/{order}/messages')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('orders.chat');
        Route::post('/', [MessageController::class, 'store'])->name('orders.messages.store');
        Route::get('/unread-count', [MessageController::class, 'unreadCount'])->name('orders.messages.unread-count');
    });

});
