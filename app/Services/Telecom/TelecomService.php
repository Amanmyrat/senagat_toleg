<?php

namespace App\Services\Telecom;

use App\Helpers\TxnIdGenerator;

class TelecomService
{
    public function __construct(
        private TelecomPaymentService $paymentService
    ) {}

    public function postPayment(array $data): array
    {
        $txnId   = TxnIdGenerator::generate();
        $txnDate = now()->format('YmdHis');

        return $this->paymentService->pay([
            'account' => $data['phone'],
            'amount'  => $data['amount'],
        ], $txnId, $txnDate);
    }

    public function checkBalance(string $phone): array
    {
        $txnId = TxnIdGenerator::generate();

        return $this->paymentService->checkBalance(
            ['account' => $phone],
            $txnId
        );
    }

}
