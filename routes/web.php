<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\DeliveryOrderController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WeatherController;

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
    Route::middleware('user')->group(function () {
        Route::get('/user/dashboard', [UserDashboardController::class, 'dashboard'])->name('user.dashboard');

        // User Profile Routes
        Route::prefix('user/profile')->group(function () {
            Route::get('/', [ProfileController::class, 'show'])->name('user.profile');
            Route::put('/update', [ProfileController::class, 'update'])->name('user.profile.update');
            Route::put('/password', [ProfileController::class, 'updatePassword'])->name('user.profile.password');
        });

        // Delivery Order Routes - User
        Route::prefix('user/orders')->group(function () {
            Route::get('/', [DeliveryOrderController::class, 'index'])->name('user.orders.index');
            Route::get('/create', [DeliveryOrderController::class, 'create'])->name('user.orders.create');
            Route::post('/', [DeliveryOrderController::class, 'store'])->name('user.orders.store');
            Route::get('/{order}', [DeliveryOrderController::class, 'show'])->name('user.orders.show');
            Route::put('/{order}', [DeliveryOrderController::class, 'update'])->name('user.orders.update');
            Route::post('/{order}/confirm', [DeliveryOrderController::class, 'confirm'])->name('user.orders.confirm');
            Route::post('/{order}/cancel', [DeliveryOrderController::class, 'cancel'])->name('user.orders.cancel');
            Route::post('/{order}/reorder', [DeliveryOrderController::class, 'reorder'])->name('user.orders.reorder');
        });
    });

    Route::middleware('courier')->group(function () {
        // Courier Dashboard Routes
        Route::get('/courier/dashboard', [CourierController::class, 'dashboard'])->name('courier.dashboard');

        // Courier Profile Routes
        Route::prefix('courier/profile')->group(function () {
            Route::get('/', [CourierController::class, 'showProfile'])->name('courier.profile');
            Route::put('/update', [CourierController::class, 'updateProfile'])->name('courier.profile.update');
            Route::put('/password', [CourierController::class, 'updatePassword'])->name('courier.profile.password');
        });

        // Courier Order Routes
        Route::prefix('courier/orders')->group(function () {
            Route::post('/{order}/accept', [CourierController::class, 'acceptOrder'])->name('courier.accept');
            Route::post('/{order}/arriving-at-pickup', [CourierController::class, 'arrivingAtPickup'])->name('courier.arriving_at_pickup');
            Route::post('/{order}/at-pickup', [CourierController::class, 'atPickup'])->name('courier.at_pickup');
            Route::post('/{order}/pickup', [CourierController::class, 'pickupOrder'])->name('courier.pickup');
            Route::post('/{order}/in-transit', [CourierController::class, 'inTransit'])->name('courier.in_transit');
            Route::post('/{order}/arriving-at-dropoff', [CourierController::class, 'arrivingAtDropoff'])->name('courier.arriving_at_dropoff');
            Route::post('/{order}/at-dropoff', [CourierController::class, 'atDropoff'])->name('courier.at_dropoff');
            Route::post('/{order}/deliver', [CourierController::class, 'deliverOrder'])->name('courier.deliver');
            Route::post('/{order}/delivery-failed', [CourierController::class, 'deliveryFailed'])->name('courier.delivery_failed');
            Route::post('/{order}/cancel', [CourierController::class, 'cancelOrder'])->name('courier.cancel');
        });
    });

    // Chat & Messages Routes
    Route::prefix('orders/{order}/messages')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('orders.chat');
        Route::post('/', [MessageController::class, 'store'])->name('orders.messages.store');
        Route::get('/unread-count', [MessageController::class, 'unreadCount'])->name('orders.messages.unread-count');
    });

    // Notification Routes
    Route::prefix('notifications')->group(function () {
        Route::post('/{notification}/mark-seen', [NotificationController::class, 'markSeen'])->name('notifications.mark-seen');
        Route::post('/mark-all-seen', [NotificationController::class, 'markAllSeen'])->name('notifications.mark-all-seen');
    });

    // API Routes for Address Validation and Fee Calculation
    Route::post('/api/validate-address', function (\Illuminate\Http\Request $request) {
        $request->validate(['address' => 'required|string']);
        $coords = \App\Services\CourierLocationService::geocodeAddress($request->address);
        
        if ($coords) {
            return response()->json([
                'valid' => true,
                'lat' => $coords['lat'],
                'lng' => $coords['lng'],
                'message' => 'Address found'
            ]);
        }
        
        return response()->json(['valid' => false, 'message' => 'Address not found'], 400);
    })->name('api.validate-address');

    Route::post('/api/calculate-fee', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'pickup_address' => 'required|string',
            'delivery_address' => 'required|string',
        ]);
        
        $fee = \App\Services\CourierLocationService::calculateDeliveryFee(
            $request->pickup_address,
            $request->delivery_address
        );
        
        if ($fee !== null) {
            return response()->json([
                'success' => true,
                'fee' => $fee,
                'message' => 'Fee calculated successfully'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Could not calculate fee. Please check addresses.'
        ], 400);
    })->name('api.calculate-fee');

    Route::get('/api/weather', WeatherController::class)->name('api.weather');

});
