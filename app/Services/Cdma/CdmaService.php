<?php

namespace App\Services\Cdma;

use App\Helpers\MoneyHelper;
use App\Jobs\NotifyMainBackendJob;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class CdmaService
{
    public function __construct(
        private CdmaClient $client
    ) {}

    public function getBalance(string $phone): array
    {
        $response = $this->client->getBalance($phone);

        if (isset($response['success']) && $response['success'] === false) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'connection_error',
                    'message' => $response['comment'] ?? $response['msg'] ?? 'Connection error',
                ],
                'data'    => null,
            ];
        }

        $status = $response['status'] ?? '';

        if ($status !== 'OK') {
            return [
                'success' => false,
                'error'   => [
                    'code'    => $status,
                    'message' => $this->resolveErrorMessage($status),
                ],
                'data'    => null,
            ];
        }

        return [
            'success' => true,
            'data'    => [
                'phone'    => $phone,
                'balance'  => $response['amount'] ?? null,
                'currency' => $response['currency'] ?? 'TMT',
            ],
        ];
    }

    public function paymentPreCheck(string $phone): array
    {
        $response = $this->client->paymentPreCheck($phone);

        if (isset($response['success']) && $response['success'] === false) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'connection_error',
                    'message' => $response['comment'] ?? $response['msg'] ?? 'Connection error',
                ],
            ];
        }

        $status = $response['status'] ?? '';

        if ($status !== 'OK') {
            return [
                'success' => false,
                'error'   => [
                    'code'    => $status,
                    'message' => $this->resolveErrorMessage($status),
                ],
                'data'    => null,
            ];
        }

        return [
            'success' => true,
        ];
    }

    public function makePayment(Payment $payment): array
    {
        $phone  = '993' . $payment->payment_target['value'];
        $rrn    = $payment->pay_id;
        $amount = MoneyHelper::intToDecimal($payment->amount);
        $date = now()->format('Ymd');
        $time = now()->format('His');
        Log::channel('cdma')->info('makePayment params', [
            'phone'  => $phone,
            'amount' => $amount,
            'rrn'    => $rrn,
        ]);
        $response = $this->client->makePayment($rrn, $phone, $amount, $date, $time);

        Log::channel('cdma')->info('Cdma makePayment response send', [
            'status' => $response['status'] ?? null,
            'body'   => $response,
        ]);
        if (isset($response['success']) && $response['success'] === false) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'connection_error',
                    'message' => $response['comment'] ?? $response['msg'] ?? 'Connection error',
                ],
            ];
        }

        $status  = $response['status'] ?? '';
        $success = in_array($status, ['OK', 'ERR_REPEAT']);

        if ($success) {
            $extras                  = $payment->extras ?? [];
            $extras['cdma_req_id']   = $response['req_id'] ?? null;
            $extras['cdma_rrn']      = $response['rrn'] ?? $rrn;
            $extras['cdma_status']   = $status;

            $payment->update([
                'status' => 'confirmed',
                'extras' => $extras,
            ]);
            NotifyMainBackendJob::dispatch($payment->order_id, 'confirmed', 'cdma');
        }

        return [
            'success' => $success,
            'status'  => $status,
            'req_id'  => $response['req_id'] ?? null,
            'rrn'     => $response['rrn'] ?? $rrn,
            'raw'     => $response,
        ];
    }

    private function resolveErrorMessage(string $code): string
    {
        return match ($code) {
            'ERR_PHONE'    => 'Phone number not found or invalid',
            'ERR_SUMM'     => 'Invalid payment amount',
            'ERR_CURRENCY' => 'Invalid currency',
            'ERR_DATE'     => 'Invalid date format',
            'ERR_REPEAT'   => 'Payment already processed',
            'ERR_ACCESS'   => 'Access denied for this operation',
            'ERR_FORBIDEN' => 'Operation forbidden for this subscriber',
            'ERR_NODB'     => 'Temporary database error, please retry',
            'ERR_PAYMENT'  => 'Payment not found',
            'ERROR'        => 'General business error',
            default        => 'Unknown error: ' . $code
    };
}
}
