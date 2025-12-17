<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Belet\BeletOrderStatusService;
use Illuminate\Http\JsonResponse;

class OrderStatusController extends Controller
{
    protected BeletOrderStatusService $status;

    public function __construct(BeletOrderStatusService $status)
    {
        $this->status = $status;
    }

    /**
     * Order status
     *
     * @unauthenticated
     */
    public function status(string $id): JsonResponse
    {
        $response = $this->status->checkStatus($id);

        return new JsonResponse($response);
    }
}
