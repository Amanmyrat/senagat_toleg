<?php

use App\Services\BeletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/check-phone', [\App\Http\Controllers\Api\BeletCheckPhoneController::class, 'checkPhone']);

