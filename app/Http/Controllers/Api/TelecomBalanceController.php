<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TxnIdGenerator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Telecom\TelecomBalanceRequest;
use App\Http\Resources\TelecomBalanceResource;
use App\Services\Telecom\TelecomPaymentService;
use Illuminate\Http\Request;

class TelecomBalanceController extends Controller
{
    /**
     * Telecom balance
     *
     * @unauthenticated
     */
    public function handle(
        TelecomBalanceRequest $request,
        TelecomPaymentService $service
    )
    {
        $txnId = TxnIdGenerator::generate();

        $result = $service->checkBalance(
            $request->validated(),
            $txnId
        );

        return new TelecomBalanceResource(
            array_merge($result, [
                'txn_id' => $txnId,
            ])
        );
    }
}
