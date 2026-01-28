<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentStatusController extends Controller
{
    protected PaymentStatusService $statusService;

    public function __construct(PaymentStatusService $statusService)
    {
        $this->statusService = $statusService;
    }

    /**
     * Check status by order_id
     */
    public function checkStatus(string $orderId): JsonResponse
    {
        $response = $this->statusService->checkByOrderId($orderId);

        return response()->json($response);
    }
}
