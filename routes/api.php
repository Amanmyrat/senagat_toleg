<?php

use App\Http\Controllers\Api\BeletController;
use App\Http\Controllers\Api\BeletCheckPhoneController;
use App\Http\Controllers\Api\OrderStatusController;
use Illuminate\Support\Facades\Route;

//Route::post('/check-phone', [BeletCheckPhoneController::class, 'checkPhone']);
//Route::get('/banks', [BeletController::class, 'banks']);
//Route::get('/balance/recommendations', [BeletBalanceController::class, 'balances']);
//
//Route::post('/balance/top-up', [BeletBalanceController::class, 'topUp']);
//Route::post('/balance/confirm', [BeletBalanceController::class, 'confirm']);
//Route::get('/orders/{id}/status', [OrderStatusController::class, 'status']);
Route::prefix('belet')->group(function () {
    Route::get('banks', [BeletController::class, 'banks']);
    Route::get('balances', [BeletController::class, 'balances']);
    Route::post('top-up', [BeletController::class, 'topUp']);
    Route::post('confirm', [BeletController::class, 'confirm']);
    Route::post('/check-phone', [BeletController::class, 'checkPhone']);
    Route::get('/orders/{id}/status', [BeletController::class, 'status']);
});
