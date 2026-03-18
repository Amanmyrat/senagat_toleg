<?php

namespace App\Http\Controllers\Api;

use App\Enum\ErrorMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\BalanceConfirmRequest;
use App\Http\Requests\BeletBalanceTopUpRequest;
use App\Http\Requests\BeletCheckPhoneRequest;
use App\Http\Resources\BeletCheckUserBalanceResource;
use App\Http\Resources\BeletConfirmResource;
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
        return response()->json(
            $balanceService->topUp(
                $request->validated(),
            )
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
    ) {
        $query = $request->only(['orderId']);
        $result = $balanceService->confirm($query);

        return new BeletConfirmResource($result);
    }



    /**
     * Check User Balance
     *
     * @unauthenticated
     */
    public function checkBalance(BeletCheckPhoneRequest $request, BeletUserService $belet)
    {
        $phone = $request->input('phone');
        $result = $belet->checkBalance($phone);
        if (empty($result['data'])) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => ErrorMessage::USER_NOT_FOUND
                ]
            ], 400);
        }
        return new BeletCheckUserBalanceResource($result['data']);
    }
}
