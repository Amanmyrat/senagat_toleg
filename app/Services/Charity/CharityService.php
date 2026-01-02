<?php

namespace App\Services\Charity;

use App\Helpers\MoneyHelper;
use App\Http\Resources\CharityStatusResource;
use App\Models\Payment;
use App\Services\Payments\PaymentGatewayResolver;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Services\BankResolverService;

class CharityService
{
    public function __construct(
        protected BankResolverService $bankResolver,
        protected PaymentGatewayResolver $gatewayResolver
    ) {}

    public function create(array $payload): array
    {
        $bankId = $this->bankResolver->resolveIdByName($payload['bank_name']);

        if (!$bankId) {
            return $this->error(16, 'Invalid bank');
        }
        $orderId = $payload['order_id'] ?? $this->generateUniqueOrderId();
        $amountInt = MoneyHelper::decimalToInt($payload['amount']);
        $bankKey = strtolower($payload['bank_name']);
        $payment = Payment::create([
            'user_id' => $payload['user_id'] ?? null,
            'type' => 'charity',
            'bank_id' => $bankId,
            'bank_key' => $bankKey,
            'amount' =>  $amountInt,
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
            $gateway = $this->gatewayResolver->resolve($payload['bank_name']);
            $response = $gateway->createPayment([
                'order_number' => $payment->pay_id,
                'amount' => $payment->amount,
                'description' => $payload['description'] ?? null,
            ]);

            if (!empty($response['error'])) {
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
                'amount_decimal'=>  MoneyHelper::intToDecimal($payment->amount),
                'pay_id'=>$payment->pay_id,
                'gateway_response' => [
                    'orderId' => $response['orderId']?? null,
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

    /**
     * Check payment status and update DB
     */

    public function checkPaymentStatus(string $orderId)
    {
        $payment = Payment::where('order_id', $orderId)->first();
        if (!$payment) {
            return new JsonResponse([
                'success' => false,
                'error'   => ['code' => 404, 'message' => 'Payment not found'],
                'data'    => null,
            ]);
        }
        if (!$payment->bank_key) {
            return new JsonResponse([
                'success' => false,
                'error' => ['code' => 422, 'message' => 'Payment bank_key is missing'],
                'data' => null,
            ]);
        }
        try {
            $gateway = $this->gatewayResolver->resolve($payment->bank_key);
            $response = $gateway->checkPaymentStatus($payment->order_id);
            $orderStatus  = $response['OrderStatus'] ?? null;
            $errorMessage = $response['ErrorMessage'] ?? null;
            $amountDecimal =  Money::fromCents($response['Amount']);
            if ($orderStatus) {
                $payment->update([
                    'status' => match ($orderStatus) {
                        'APPROVED', 'CONFIRMED' => 'confirmed',
                        'DECLINED', 'FAILED'    => 'failed',
                        default                  => 'pending',
                    },
                    'error_message' => $errorMessage,
                ]);
            }
            Log::channel('charity')->info('Payment status checked', [
                'order_id' => $payment->order_id,
                'bank_key' => $payment->bank_key,
                'order_status' => $orderStatus,
                'amount_decimal'=>$amountDecimal,
            ]);
            return  new CharityStatusResource($response);
        } catch (\Throwable $e) {
            Log::channel('charity')->error('Payment status check failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return new JsonResponse( [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => 'Payment status check failed',
                ],
                'data' => null,
            ]);
        }
    }


    protected function generateUniqueOrderId(): string
    {
        do {
            $orderId = 'SB' . now()->format('YmdH') . rand(1000, 9999);
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
