<?php

use App\Http\Controllers\Api\PaymentReturnController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {});
Route::get('/payments/return', [PaymentReturnController::class, 'handle']);
