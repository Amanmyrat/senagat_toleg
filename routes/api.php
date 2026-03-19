<?php

use App\Http\Controllers\Api\AstuController;
use App\Http\Controllers\Api\BeletController;
use App\Http\Controllers\Api\CdmaController;
use App\Http\Controllers\Api\CharityController;
use App\Http\Controllers\Api\TelecomController;
use App\Http\Controllers\Api\TmCellController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('/belet')->group(function () {
        Route::get('banks', [BeletController::class, 'banks']);
        Route::get('balances', [BeletController::class, 'balances']);
        Route::post('top-up', [BeletController::class, 'topUp']);
        Route::post('confirm', [BeletController::class, 'confirm']);
        Route::get('checkBalance', [BeletController::class, 'checkBalance']);
        //        Route::get('/orders/{id}/status', [BeletController::class, 'status']);
    });
    Route::post('/charity', [CharityController::class, 'store']);
        Route::post('/check-status', [CharityController::class, 'checkStatus']);
     //   Route::get('payments/status/{orderId}', [PaymentStatusController::class, 'checkStatus']);

    Route::prefix('telecom')->group(function () {

        // Balance check

        Route::get('balances', [TelecomController::class, 'handle']);

        // Payment
        Route::post('top-up', [TelecomController::class, 'store']);
    });
     //ASTU CRUD
    Route::prefix('astu')->group(function () {
        Route::post('topup', [AstuController::class, 'store']);
        Route::post('balance', [AstuController::class, 'balance']);
    });
    //TMCELL CRUD
    Route::prefix('tmcell')->group(function () {

        // Balance check
        Route::get('balance', [TmCellController::class, 'balance']);
        // Payment
        Route::post('pay', [TmCellController::class, 'store']);
    });

    // CDMA CRUD
    Route::prefix('cdma')->group(function () {

    Route::post('/pay', [CdmaController::class, 'store']);
    Route::get('/balance', [CdmaController::class, 'balance']);
    });
});
