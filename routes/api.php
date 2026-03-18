<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TravelOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::middleware('auth:api')->group(function () {
    // Auth
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
        Route::get('/me', [AuthController::class, 'me'])->name('me');
    });

    // Pedidos de Viagem
    Route::prefix('travel-orders')->name('travel-orders.')->group(function () {
        Route::get('/', [TravelOrderController::class, 'index'])->name('index');
        Route::post('/', [TravelOrderController::class, 'store'])->name('store');
        Route::get('/{id}', [TravelOrderController::class, 'show'])->name('show');
        Route::patch('/{id}/status', [TravelOrderController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/cancel', [TravelOrderController::class, 'cancel'])->name('cancel');
    });
});
