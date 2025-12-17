<?php

use App\Http\Controllers\Api\BeletBalanceController;
use App\Http\Controllers\Api\BeletBanksController;
use App\Http\Controllers\Api\BeletCheckPhoneController;
use App\Http\Controllers\Api\OrderStatusController;
use Illuminate\Support\Facades\Route;

Route::post('/check-phone', [BeletCheckPhoneController::class, 'checkPhone']);
Route::get('/banks', [BeletBanksController::class, 'banks']);
Route::get('/balance/recommendations', [BeletBalanceController::class, 'balances']);

Route::post('/balance/top-up', [BeletBalanceController::class, 'topUp']);
Route::post('/balance/confirm', [BeletBalanceController::class, 'confirm']);
Route::get('/orders/{id}/status', [OrderStatusController::class, 'status']);
