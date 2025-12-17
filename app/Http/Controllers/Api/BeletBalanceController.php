<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BalanceConfirmRequest;
use App\Http\Requests\BeletBalanceTopUpRequest;
use App\Services\Belet\BeletBalanceService;
use Illuminate\Http\JsonResponse;

class BeletBalanceController extends Controller
{
    protected BeletBalanceService $balances;

    public function __construct(BeletBalanceService $balances)
    {
        $this->balances = $balances;
    }

    /**
     * Balance Recommendations
     *
     * @unauthenticated
     */
    public function balances()
    {
        $result = $this->balances->getBalanceRecommendations();

        return new JsonResponse($result);
    }

    /**
     * Balance Top Up
     *
     * @unauthenticated
     */
    public function topUp(
        BeletBalanceTopUpRequest $request,
        BeletBalanceService $balanceService
    ): JsonResponse {
        $payload = [
            'user_id' => $request->user()->id ?? null,
            'bank_id' => $request->bank_id,
            'amount_in_manats' => $request->amount_in_manats,
            'phone' => $request->phone,
            'returnUrl' => $request->returnUrl,
            'client_ip' => $request->ip(),
        ];

        return response()->json(
            $balanceService->topUp($payload)
        );
    }

    /**
     * Balance Confirm
     *
     * @unauthenticated
     */
    public function confirm(
        BalanceConfirmRequest $request,
        BeletBalanceService $balanceService
    ): JsonResponse {
        $query = $request->only(['orderId', 'pay_id']);

        return response()->json(
            $balanceService->confirm($query)
        );
    }
}
