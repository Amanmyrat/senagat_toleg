<?php

namespace App\Services\Telecom;

use App\Helpers\MoneyHelper;
use App\Helpers\TxnIdGenerator;
use App\Jobs\TelecomStatusJob;
use App\Models\Payment;
use App\Services\BankResolverService;
use App\Services\Payments\PaymentGatewayResolver;

class TelecomTopupService
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

        $payment = Payment::query()->create([
            'type' => 'telecom',
            'bank_id' => $bankId,
            'bank_key' => $bankKey,
            'amount' => $amountInt,
            'pay_id' => $orderId,
            'payment_target' => [
                'type' => 'home_phone',
                'value' => $payload['phone'],
            ],
            'status' => 'pending',
        ]);

        $gateway = $this->gatewayResolver->resolve($payload['bank_name'], 'telecom');

        $response = $gateway->createPayment([
            'order_number' => $payment->pay_id,
            'amount' => $payment->amount,
            'description' => 'Telecom top-up',
        ]);

        if (! empty($response['error'])) {
            $payment->update(['status' => 'failed']);
            return $this->error(500, $response['error']['message']);
        }

        $payment->update([
            'order_id' => $response['orderId'] ?? null,
            'status' => 'pending',
        ]);
        TelecomStatusJob::dispatch($payment)->delay(now()->addSeconds(30));
        return [
            'success' => true,
            'data' => $response,
        ];
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
