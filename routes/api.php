<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AdminProductController;

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

// Admin routes (no auth for development)
// TODO: Add authentication middleware in production
Route::prefix('admin')->group(function () {
    Route::post('/products', [AdminProductController::class, 'store']);
    Route::put('/products/{id}', [AdminProductController::class, 'update']);
    Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);
    Route::post('/products/bulk-update-stock', [AdminProductController::class, 'bulkUpdateStock']);
});

// For production with authentication:
// Route::middleware(['auth:sanctum'])->prefix('admin')->group(function() {
//     Route::post('/products', [AdminProductController::class, 'store']);
//     Route::put('/products/{id}', [AdminProductController::class, 'update']);
//     Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);
//     Route::post('/products/bulk-update-stock', [AdminProductController::class, 'bulkUpdateStock']);
// });