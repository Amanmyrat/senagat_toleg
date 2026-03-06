<?php

use App\Http\Controllers\Api\AstuController;
use App\Http\Controllers\Api\BeletController;
use App\Http\Controllers\Api\CharityController;
use App\Http\Controllers\Api\TelecomController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('/belet')->group(function () {
        Route::get('banks', [BeletController::class, 'banks']);
        Route::get('balances', [BeletController::class, 'balances']);
        Route::post('top-up', [BeletController::class, 'topUp']);
        Route::post('confirm', [BeletController::class, 'confirm']);
        Route::post('/check-phone', [BeletController::class, 'checkPhone']);
        //        Route::get('/orders/{id}/status', [BeletController::class, 'status']);
    });
    Route::post('/charity', [CharityController::class, 'store']);
    //    Route::post('/check-status', [CharityController::class, 'checkStatus']);
    //    Route::get('payments/status/{orderId}', [PaymentStatusController::class, 'checkStatus']);

    Route::prefix('telecom')->group(function () {

        // Balance check

        Route::get('balances', [TelecomController::class, 'handle']);

        // Payment
        Route::post('top-up', [TelecomController::class, 'store']);
    });

    Route::prefix('astu')->group(function () {
        Route::post('topup', [AstuController::class, 'store']);
    });
});
