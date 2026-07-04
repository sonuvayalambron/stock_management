<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductStockController;
use App\Http\Controllers\Api\StockMovementController;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Stock movement routes
    Route::get('/stock-movements', [StockMovementController::class, 'index']);
    Route::post('/stock-movements', [StockMovementController::class, 'store']);

    // Product stock routes
    Route::get('/products/{product}/stock', [ProductStockController::class, 'show']);
    Route::get('/products/{product}/stock-movements', [ProductStockController::class, 'movements']);
});