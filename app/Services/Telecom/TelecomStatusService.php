<?php

namespace App\Services\Telecom;

use App\Models\Payment;
use App\Services\Payments\PaymentGatewayResolver;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class TelecomStatusService
{
    protected PaymentGatewayResolver $gatewayResolver;

    public function __construct(PaymentGatewayResolver $gatewayResolver)
    {
        $this->gatewayResolver = $gatewayResolver;
    }

    public function check(string $orderId): array
    {
        $payment = Payment::where('order_id', $orderId)->first();

        if (! $payment) {
            return [
                'success' => false,
                'error' => [
                    'code' => 404,
                    'message' => 'Payment not found',
                ],
                'data' => null,
            ];
        }

        if (! $payment->bank_key) {
            return [
                'success' => false,
                'error' => [
                    'code' => 422,
                    'message' => 'Payment bank_key is missing',
                ],
                'data' => null,
            ];
        }

        try {

            $gateway = $this->gatewayResolver->resolve($payment->bank_key, 'telecom');

            $response = $gateway->checkPaymentStatus($payment->order_id);

            $orderStatus = $response['OrderStatus'] ?? null;
            $errorCode = $response['ErrorCode'] ?? null;
            $errorMessage = $response['ErrorMessage'] ?? null;
            $amount = $response['Amount'] ?? null;

            $mappedStatus = match ($orderStatus) {
                2 => 'confirmed',
                1 => 'confirming',
                0 => 'failed',
                default => 'pending',
            };

            Log::channel('telecom')->info('Telecom payment status checked', [
                'order_id' => $payment->order_id,
                'bank_key' => $payment->bank_key,
                'order_status' => $orderStatus,
                'amount' => $amount,
            ]);

            return [
                'success' => true,
                'data' => [
                    'bank' => $response,
                    'payment' => [
                        'status' => $mappedStatus,
                        'amount' => $amount,
                        'error_message' => $errorMessage,
                    ],
                ],
            ];

        } catch (ConnectionException | RequestException $e) {

            Log::channel('telecom')->warning('Network error during telecom payment check', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => [
                    'code' => 503,
                    'message' => 'Service unavailable. Please try again later.',
                ],
                'data' => null,
            ];

        } catch (\Throwable $e) {

            Log::channel('telecom')->error('Telecom payment check failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => 'Unexpected error occurred.',
                ],
                'data' => null,
            ];
        }
    }
}
