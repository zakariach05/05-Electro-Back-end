<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LocationController;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MagicLinkController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/magic-link', [MagicLinkController::class, 'send']);
Route::post('/auth/magic-verify', [MagicLinkController::class, 'verify']);

Route::get('/locations', [LocationController::class, 'index']);

Route::get('/brands', function() {
    return response()->json([
        ['name' => 'Apple', 'logo' => 'apple.png'],
        ['name' => 'Samsung', 'logo' => 'samsung.png'],
        ['name' => 'Sony', 'logo' => 'sony.png'],
        ['name' => 'LG', 'logo' => 'lg.png'],
        ['name' => 'MSI', 'logo' => 'msi.png'],
        ['name' => 'HP', 'logo' => 'hp.png'],
        ['name' => 'Dell', 'logo' => 'dell.png'],
        ['name' => 'Asus', 'logo' => 'asus.png'],
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::put('/user', [AuthController::class, 'update']);
    Route::post('/user/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/my-orders', [OrderController::class, 'myOrders']);
});

// Public API Routes
Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
Route::post('/contact', [ContactController::class, 'send'])->middleware('throttle:3,1');
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::get('/invoice/{id}', [OrderController::class, 'viewInvoice']);

// Admin Protected Routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        Route::get('/admin/products/export', [ProductController::class, 'exportStock']);

    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    Route::get('/admin/orders', [OrderController::class, 'index']);
    Route::get('/admin/orders/export', [OrderController::class, 'exportOrders']);
    Route::patch('/admin/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::patch('/admin/orders/{id}/assign', [OrderController::class, 'assignAgent']);
    Route::patch('/admin/orders/{id}/payment', [OrderController::class, 'updatePayment']);
    Route::post('/admin/orders/{id}/refund', [OrderController::class, 'refund']);
    Route::get('/admin/orders/{id}/invoice', [OrderController::class, 'generateInvoice']);
    Route::post('/admin/orders/{id}/resend-invoice', [App\Http\Controllers\InvoiceController::class, 'resend']);
    Route::get('/admin/stats', [DashboardController::class, 'stats']);
    Route::get('/admin/users', [AuthController::class, 'index']);
});
