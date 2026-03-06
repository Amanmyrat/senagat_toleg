<?php

namespace App\Services\Astu;

use App\Helpers\MoneyHelper;
use App\Models\Payment;
use App\Services\BankResolverService;
use App\Services\Payments\PaymentGatewayResolver;

class AstuTopupService
{
    public function __construct(
        protected BankResolverService $bankResolver,
        protected PaymentGatewayResolver $gatewayResolver,
    ) {}

    public function create(array $payload): array
    {
        $bankId = $this->bankResolver->resolveIdByName($payload['bank_name']);

        if (! $bankId) {
            return $this->error(16, 'Invalid bank');
        }

        $orderId = $payload['order_id'] ?? $this->generateUniqueOrderId();
        $amountInt = MoneyHelper::decimalToInt($payload['amount']);
        $bankKey = strtolower($payload['bank_name']);

        $payment = Payment::create([
            'type' => 'astu',
            'bank_id' => $bankId,
            'bank_key' =>  $bankKey,
            'amount' => $amountInt,
            'pay_id' => $orderId,
            'payment_target' => [
                'type' => $payload['type'],
                'value' => $payload['account'],
            ],
            'status' => 'pending',
        ]);

        $gateway = $this->gatewayResolver->resolve(
            $payload['bank_name'],
            'astu',
            $payload['type']
        );

        $response = $gateway->createPayment([
            'order_number' => $payment->pay_id,
            'amount' => $payment->amount,
            'description' => 'ASTU payment',
        ]);

        if (!empty($response['error'])) {
            $payment->update(['status' => 'failed']);

            return $this->error(
                $response['error']['code'] ?? 500,
                $response['error']['message'] ?? 'Gateway error'
            );
        }


        $payment->update([
            'order_id' => $response['orderId'] ?? null,
            'status' => 'pending',
        ]);

//        return [
//            'success' => true,
//            'data' => [
//                'orderId' => $response['orderId'] ?? null,
//                'payment_url' => $response['formUrl'] ?? null,
//            ],
//        ];
        return [
            'success' => true,
            'data' => $response,
        ];
    }

    private function error(int $code, string $message): array
    {
        return [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
            'data' => null,
        ];
    }
    protected function generateUniqueOrderId(): string
    {
        do {
            $orderId = 'SB'.now()->format('YmdH').rand(1000, 9999);
        } while (Payment::where('order_id', $orderId)->exists());

        return $orderId;
    }

}

