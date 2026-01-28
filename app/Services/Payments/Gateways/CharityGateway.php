<?php

namespace App\Services\Payments\Gateways;

use App\Models\Payment;
use App\Services\Payments\Contracts\PaymentStatusGatewayInterface;
use App\Services\Payments\PaymentGatewayResolver;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class CharityGateway implements PaymentStatusGatewayInterface
{
    protected PaymentGatewayResolver $gatewayResolver;

    public function __construct(PaymentGatewayResolver $gatewayResolver)
    {
        $this->gatewayResolver = $gatewayResolver;
    }

    public function checkStatus(string $orderId): array
    {
        $payment = Payment::where('order_id', $orderId)->first();

        if (!$payment) {
            return [
                'success' => false,
                'error'   => ['code' => 404, 'message' => 'Payment not found'],
                'data'    => null,
            ];
        }

        if (!$payment->bank_key) {
            return [
                'success' => false,
                'error'   => ['code' => 422, 'message' => 'Payment bank_key is missing'],
                'data'    => null,
            ];
        }

        try {
            $gateway = $this->gatewayResolver->resolve($payment->bank_key);
            $response = $gateway->checkPaymentStatus($payment->order_id);

            if (isset($response['success']) && $response['success'] === false) {
                return [
                    'success' => false,
                    'error'   => $response['error'] ?? ['code' => 500, 'message' => 'Unknown error'],
                    'data'    => null,
                ];
            }

            $orderStatus   = $response['OrderStatus'] ?? null;
            $errorCode     = $response['ErrorCode'] ?? null;
            $errorMessage  = $response['ErrorMessage'] ?? null;
            $amount        = $response['Amount'] ?? null;

            $mappedStatus = match ($orderStatus) {
                2 => 'confirmed',
                1 => 'pending',
                0 => 'failed',
                default => 'pending',
            };

            $payment->update([
                'status' => $mappedStatus,
                'error_message' => $errorMessage,
            ]);

            Log::channel('charity')->info('Payment status checked', [
                'order_id' => $payment->order_id,
                'bank_key' => $payment->bank_key,
                'order_status' => $orderStatus,
                'amount' => $amount,
            ]);

            return [
                'success' => true,
                'data' => [
                    'orderStatus' => $orderStatus,
                    'errorCode' => $errorCode,
                    'errorMessage' => $errorMessage,
                    'amount' => $amount,
                ],
            ];

        } catch (ConnectionException | RequestException $e) {
            // Ağ hatası, DNS hatası veya Timeout durumları buraya düşer
            Log::channel('charity')->warning('Network error during payment check', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => [
                    'code' => 503,
                    'message' => 'Service unavailable or connection failed. Please try again later.',
                ],
                'data' => null,
            ];

        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();

            $userFriendlyMessage = str_contains($errorMessage, 'cURL error')
                ? 'Could not connect to the payment provider. Please check your internet connection.'
                : 'An unexpected error occurred.';

            Log::channel('charity')->error('Payment status check failed', [
                'order_id' => $orderId,
                'error' => $errorMessage,
            ]);

            return [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => $userFriendlyMessage,
                ],
                'data' => null,
            ];
        }
    }
}
