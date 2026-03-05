<?php

namespace App\Services\Telecom;

use App\Models\Payment;
use App\Services\Payments\PaymentGatewayResolver;


class TelecomStatusService
{
    public function __construct(
        protected PaymentGatewayResolver $gatewayResolver,
    ) {}

    public function check(array $payload): array
    {
        $payment = Payment::where('order_id', $payload['orderId'])->first();

        if (! $payment) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 404,
                    'message' => 'Payment not found',
                ],
            ];
        }

        $gateway  = $this->gatewayResolver->resolve($payment->bank_key, 'telecom');
        $response = $gateway->checkPaymentStatus($payment->order_id);

        return [
            'success' => true,
            'data'    => [
                'bank'    => $response,
                'payment' => [
                    'status'         => $payment->status,
                    'telecom_result' => $payment->telecom_result,
                    'telecom_txn_id' => $payment->telecom_txn_id,
                ],
            ],
        ];
    }
}
