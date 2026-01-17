<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AdminProductController;
use App\Http\Controllers\Api\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::get('/categories', [ProductController::class, 'categories']);
Route::get('/filter-options', [ProductController::class, 'filterOptions']);

// Order routes
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{orderNumber}', [OrderController::class, 'show']);
Route::post('/orders/{orderNumber}/cancel', [OrderController::class, 'cancel']);

// Admin routes (no auth for development)
// TODO: Add authentication middleware in production
Route::prefix('admin')->group(function () {
    // Products
    Route::post('/products', [AdminProductController::class, 'store']);
    Route::put('/products/{id}', [AdminProductController::class, 'update']);
    Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);
    Route::post('/products/bulk-update-stock', [AdminProductController::class, 'bulkUpdateStock']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
});

// For production with authentication:
// Route::middleware(['auth:sanctum'])->prefix('admin')->group(function() {
//     Route::post('/products', [AdminProductController::class, 'store']);
//     Route::put('/products/{id}', [AdminProductController::class, 'update']);
//     Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);
//     Route::get('/orders', [OrderController::class, 'index']);
//     Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
// });