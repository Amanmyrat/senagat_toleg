<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BalanceConfirmRequest;
use App\Http\Requests\BeletBalanceTopUpRequest;
use App\Http\Requests\BeletCheckPhoneRequest;
use App\Services\Belet\BeletBalanceService;
use App\Services\Belet\BeletBankService;
use App\Services\Belet\BeletOrderStatusService;
use App\Services\Belet\BeletUserService;
use Illuminate\Http\JsonResponse;

class BeletController extends Controller
{
    protected BeletBankService $banks;
    protected BeletBalanceService $balances;
    protected BeletOrderStatusService $status;


    public function __construct(
        BeletBankService $banks,
        BeletBalanceService $balances,
        BeletOrderStatusService $status)
    {
        $this->banks = $banks;
        $this->balances = $balances;
        $this->status = $status;

    }

    /**
     * Banks list
     *
     * @unauthenticated
     */
    public function banks()
    {
        $result = $this->banks->getBanks();

        return new JsonResponse($result);
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
        $query = $request->only(['orderId',]);

        return response()->json(
            $balanceService->confirm($query)
        );
    }
    /**
     * Check User
     *
     * @unauthenticated
     */
    public function checkPhone(BeletCheckPhoneRequest $request, BeletUserService $belet)
    {
        $phone = $request->input('phone');
        $result = $belet->checkPhone($phone);

        return new JsonResponse($result);
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
