<?php

namespace App\Services\TmCell;

use App\Helpers\MoneyHelper;
use App\Models\Payment;

class TmCellService
{
    public function __construct(
        private TmCellClient $client
    ) {}

    /**
     * Check balance for a given phone number
     */
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

    /**
     * Pre-payment check — validate phone number before payment
     */
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
            'data'    => null,
        ];
    }

    /**
     * Make payment for a given Payment model
     */
    public function makePayment(Payment $payment): array
    {
        $phone  = $payment->payment_target['value'];
        $rrn    = $payment->pay_id;
        $amount = (float) number_format(
            MoneyHelper::intToDecimal($payment->amount),
            2, '.', ''
        );
        $date = now()->format('Ymd');
        $time = now()->format('His');

        $response = $this->client->makePayment($rrn, $phone, $amount, $date, $time);

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

        // OK or ERR_REPEAT (already processed) → success
        $success = in_array($status, ['OK', 'ERR_REPEAT']);

        if ($success) {
            $extras                   = $payment->extras ?? [];
            $extras['tmcell_req_id']  = $response['req_id'] ?? null;
            $extras['tmcell_rrn']     = $response['rrn'] ?? $rrn;
            $extras['tmcell_status']  = $status;

            $payment->update([
                'status' => 'confirmed',
                'extras' => $extras,
            ]);
        }

        return [
            'success' => $success,
            'status'  => $status,
            'req_id'  => $response['req_id'] ?? null,
            'rrn'     => $response['rrn'] ?? $rrn,
            'raw'     => $response,
        ];
    }

    /**
     * Resolve human-readable error message from API status code
     */
    private function resolveErrorMessage(string $code): string
    {
        return match ($code) {
            'ERR_PHONE'      => 'Phone number not found or invalid',
            'ERR_SUMM'       => 'Invalid payment amount',
            'ERR_CURRENCY'   => 'Invalid currency',
            'ERR_DATE'       => 'Invalid date format',
            'ERR_REPEAT'     => 'Payment already processed',
            'ERR_ACCESS'     => 'Access denied for this operation',
            'ERR_FORBIDEN'   => 'Operation forbidden for this subscriber',
            'ERR_NODB'       => 'Temporary database error, please retry',
            'ERR_CALLSLIMIT' => 'Concurrent requests limit exceeded',
            'ERR_VALIDATION' => 'Request validation error',
            'ERROR'          => 'General business error',
            default          => 'Unknown error: ' . $code,
        };
    }
}
