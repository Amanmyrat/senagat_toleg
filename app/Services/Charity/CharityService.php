<?php

namespace App\Services\Charity;

use App\Helpers\MoneyHelper;
use App\Models\Payment;
use App\Services\BankResolverService;
use App\Services\Payments\PaymentGatewayResolver;
use Illuminate\Support\Facades\Log;

class CharityService
{
    public function __construct(
        protected BankResolverService $bankResolver,
        protected PaymentGatewayResolver $gatewayResolver
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
        $payment = Payment::query()->create([
            'user_id' => $payload['user_id'] ?? null,
            'type' => 'charity',
            'bank_id' => $bankId,
            'bank_key' => $bankKey,
            'amount' => $amountInt,
            'user_information' => [
                'name' => $payload['name'],
                'surname' => $payload['surname'],
            ],
            'pay_id' => $orderId,
            'payment_target' => [
                'type' => 'phone',
                'value' => $payload['phone'],
            ],
            'status' => 'pending',
        ]);

        try {
            $gateway = $this->gatewayResolver->resolve($payload['bank_name'],'charity');
            $response = $gateway->createPayment([
                'order_number' => $payment->pay_id,
                'amount' => $payment->amount,
                'description' => $payload['description'] ?? null,
            ]);

            if (! empty($response['error'])) {
                throw new \Exception($response['error']['message']);
            }

            $payment->update([
                'order_id' => $response['orderId'] ?? null,
                'status' => 'pending',
            ]);

            Log::channel('charity')->info('Charity payment created', [
                'payment_id' => $payment->id,
                'bank' => $payload['bank_name'],
                'bank_key' => $bankKey,
                'amount_tyiyn' => $payment->amount,
                'amount_decimal' => MoneyHelper::intToDecimal($payment->amount),
                'pay_id' => $payment->pay_id,
                'gateway_response' => [
                    'orderId' => $response['orderId'] ?? null,
                ],
            ]);

            return [
                'success' => true,
                'error' => null,
                'data' => $response,
            ];

        } catch (\Throwable $e) {
            $payment->update(['status' => 'failed']);

            Log::channel('charity')->error('Charity payment failed', [
                'payment_id' => $payment->id,
                'bank' => $payload['bank_name'],
                'error' => $e->getMessage(),
            ]);

            return $this->error(500, $e->getMessage());
        }
    }
    protected function generateUniqueOrderId(): string
    {
        do {
            $orderId = 'SB'.now()->format('YmdH').rand(1000, 9999);
        } while (Payment::where('order_id', $orderId)->exists());

        return $orderId;
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
}
