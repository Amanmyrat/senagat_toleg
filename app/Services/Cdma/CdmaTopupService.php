<?php

namespace App\Services\Cdma;

use App\Enum\ErrorMessage;
use App\Helpers\MoneyHelper;
use App\Jobs\CdmaStatusJob;
use App\Models\Payment;
use App\Services\BankResolverService;
use App\Services\Payments\PaymentGatewayResolver;
use Illuminate\Support\Facades\Log;

class CdmaTopupService
{
    public function __construct(
        protected BankResolverService    $bankResolver,
        protected PaymentGatewayResolver $gatewayResolver,
        protected CdmaService            $cdmaService,
    ) {}

    public function create(array $payload): array
    {
        $preCheck = $this->cdmaService->paymentPreCheck($payload['phone']);

        if (! $preCheck['success']) {
            Log::channel('cdma')->warning('Cdma paymentPreCheck failed', [
                'phone' => $payload['phone'],
                'error' => $preCheck['error'] ?? null,
            ]);

            return $this->error(
                422,
                $preCheck['error']['message'] ?? ErrorMessage::INVALID_PHONE
            );
        }

        $bankId = $this->bankResolver->resolveIdByName($payload['bank_name']);

        if (! $bankId) {
            return $this->error(16, 'Invalid bank');
        }

        $orderId   = $payload['order_id'] ?? $this->generateUniqueOrderId();
        $amountInt = MoneyHelper::decimalToInt($payload['amount']);
        $bankKey   = strtolower($payload['bank_name']);

        $payment = Payment::create([
            'type'           => 'cdma',
            'bank_id'        => $bankId,
            'bank_key'       => $bankKey,
            'amount'         => $amountInt,
            'pay_id'         => $orderId,
            'payment_target' => [
                'value' => $payload['phone'],
            ],
            'status'         => 'pending',
        ]);

        Log::channel('cdma')->info('Cdma payment created', [
            'payment_id' => $payment->id,
            'pay_id'     => $payment->pay_id,
            'phone'      => $payload['phone'],
            'amount'     => $payload['amount'],
            'bank'       => $bankKey,
        ]);

        $gateway = $this->gatewayResolver->resolve(
            $payload['bank_name'],
            'cdma',
        );

        $response = $gateway->createPayment([
            'order_number' => $payment->pay_id,
            'amount'       => $payment->amount,
            'description'  => 'CDMA payment',
        ]);

        if (! empty($response['error'])) {
            $payment->update(['status' => 'failed']);

            return $this->error(
                $response['error']['code'] ?? 500,
                $response['error']['message'] ?? 'Gateway error'
            );
        }

        $payment->update([
            'order_id' => $response['orderId'] ?? null,
            'status'   => 'pending',
        ]);

        CdmaStatusJob::dispatch($payment)->delay(now()->addSeconds(30));

        return [
            'success' => true,
            'data'    => $response,
        ];
    }

    private function error(int $code, string $message): array
    {
        return [
            'success' => false,
            'error'   => [
                'code'    => $code,
                'message' => $message,
            ],
            'data'    => null,
        ];
    }

    protected function generateUniqueOrderId(): string
    {
        do {
            $orderId = 'CD' . now()->format('YmdH') . rand(1000, 9999);
        } while (Payment::where('pay_id', $orderId)->exists());

        return $orderId;
    }
}
