<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckPaymentStatusRequest;
use App\Services\Telecom\TelecomStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelecomStatusController extends Controller
{
    /**
      * Telecom Check Status
      *
      * @unauthenticated
     */
    public function check(CheckPaymentStatusRequest $request)
    {
        return new JsonResponse(
            app(TelecomStatusService::class)->check($request->validated())
        );
    }
}
