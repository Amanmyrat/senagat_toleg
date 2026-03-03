<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Telecom\TelecomPaymentRequest;
use App\Services\Telecom\TelecomPaymentService;
use Illuminate\Http\Request;

class TelecomPaymentController extends Controller
{
    /**
     * Telecom payment
     *
     * @unauthenticated
     */
    public function handle(
        TelecomPaymentRequest $request,
        TelecomPaymentService $service
    ) {
        return $service->forward($request->validated());
    }
}
