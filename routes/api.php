<?php

use App\Http\Controllers\Api\BeletController;
use App\Http\Controllers\Api\CharityController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('/belet')->group(function () {
        Route::get('banks', [BeletController::class, 'banks']);
        Route::get('balances', [BeletController::class, 'balances']);
        Route::post('top-up', [BeletController::class, 'topUp']);
        Route::post('confirm', [BeletController::class, 'confirm']);
        Route::post('/check-phone', [BeletController::class, 'checkPhone']);
        Route::get('/orders/{id}/status', [BeletController::class, 'status']);
    });
    Route::post('/charity', [CharityController::class, 'store']);

});
