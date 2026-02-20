<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::prefix('v1')->group(function () {
    // Auth routes
    Route::apiResource('auth', AuthController::class)->only('store');
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logout']);

    // Email verification
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])
        ->middleware(['auth:sanctum', 'signed'])
        ->name('verification.verify');

    // User CRUD (protected)
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('users', UserController::class);
    });
});
