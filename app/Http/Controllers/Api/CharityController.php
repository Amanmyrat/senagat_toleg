<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CharityRequest;
use App\Services\Charity\CharityService;
use App\Services\Payments\PaymentGatewayResolver;
use Illuminate\Http\JsonResponse;

class CharityController extends Controller
{
    /**
     * Charity
     *
     * @unauthenticated
     */
    protected CharityService $charityService;

    protected PaymentGatewayResolver $gatewayResolver;

    public function __construct(CharityService $charityService, PaymentGatewayResolver $gatewayResolver)
    {
        $this->charityService = $charityService;
        $this->gatewayResolver = $gatewayResolver;
    }
    /**
     * Send Charity payment
     */
    public function store(CharityRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $response = $this->charityService->create($payload);

        return new JsonResponse($response);
    }
    /**
     * Check payment status
     *
     * @unauthenticated
     */
}
