<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckPaymentStatusRequest;
use App\Http\Requests\CheckSenagatPaymentRequest;
use App\Http\Requests\CreateSenagatPaymentRequest;
use App\Services\SenagatBank\SenagatPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SenagatPaymentController extends Controller
{
    public function __construct(
        private SenagatPaymentService $service
    ) {}
    /**
     * Senagat Payment Url
     *
     * @unauthenticated
     */
    public function create(CreateSenagatPaymentRequest $request)
    {
        $result = $this->service->createPayment(
            $request->location_id,
            $request->amount,
            $request->type,
        );

        return new JsonResponse($result);
    }

    /**
     * Senagat Check Payment Url
     *
     * @unauthenticated
     */
    public function checkStatus(CheckSenagatPaymentRequest $request)
    {
        $result = $this->service->checkStatus(
            $request->location_id,
            $request->orderId,
        );

        return new JsonResponse($result);
    }
}
