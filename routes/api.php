<?php

use App\Http\Controllers\Api\BeletController;
use App\Http\Controllers\Api\CharityController;
use App\Http\Controllers\Api\TelecomBalanceController;
use App\Http\Controllers\Api\TelecomPaymentController;
use App\Http\Controllers\Api\TelecomStatusController;
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

        Route::get('balances', [TelecomBalanceController::class, 'handle']);

        // Payment
        Route::get('pay', [TelecomPaymentController::class, 'handle']);
        Route::post('top-up', [\App\Http\Controllers\Api\TelecomTopupController::class, 'store']);
        Route::post('status', [TelecomStatusController::class, 'check']);
    });
});
