<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);

// Categories
Route::get('/categories', [ProductController::class, 'categories']);

// Filter options (dynamic based on category)
Route::get('/filter-options', [ProductController::class, 'filterOptions']);
