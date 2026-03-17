<?php

namespace App\Services\Astu;

use App\Helpers\MoneyHelper;
use App\Jobs\NotifyMainBackendJob;
use App\Models\Payment;

class AstuService
{
    public function __construct(
        private AstuClient $client
    ) {}


    public function formatPhoneNumber(string $account, string $type): string
    {
        return match ($type) {
            'phone' => $account . '-12',
            'iptv'  => 'iptv-' . $account,
            'internet' => 'inet-' . $account,
            'cdma'     => 'cdma-' . $account,
            default    => throw new \InvalidArgumentException(
                "unsupported_account_type: '{$type}'"
            ),
        };
    }

    public function getBalance(string $account, string $type): array
    {
        $phoneNumber = $this->formatPhoneNumber($account, $type);
        $response    = $this->client->getBalance($phoneNumber);

        if (isset($response['success']) && $response['success'] === false) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'connection_error',
                    'message' => $response['comment'],
                ],
                'data'    => null,
            ];
        }
        $result = $response['result'] ?? '';
        if ($result !== 'action_success') {
            return [
                'success' => false,
                'error'   => [
                    'code'    => $result,
                    'message' => $response['msg'] ?? 'Unknown error',
                ],
                'data'    => null,
            ];
        }

        return [
            'success' => true,
            'data'    => [
                'number'  => $response['number'] ?? null,
                'balance' => $response['balance'] ?? null,
                'type'    => $type,
            ],
        ];
    }
    public function updateBalance(Payment $payment): array
    {
        $type    = $payment->payment_target['type'] ?? 'phone';
        $account = $payment->payment_target['value'];
        $agrmNum = $this->formatPhoneNumber($account, $type);

        $receiptNum  = $payment->pay_id;
        $receiptDate = now()->format('YmdHis');
        $amount      = (float) number_format(
            MoneyHelper::intToDecimal($payment->amount),
            2, '.', ''
        );

        $response = $this->client->updateBalance(
            $agrmNum,
            $receiptNum,
            $receiptDate,
            $amount
        );

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
        $result = $response['result'] ?? '';
        $success = in_array($result, ['action_success', 'payment_already_exists']);
        if ($success) {
            $extras = $payment->extras ?? [];
            $extras['astu_receipt']   = $response['receipt'] ?? $receiptNum;
            $extras['astu_result']    = $result;
            $extras['astu_agrm_num']  = $agrmNum;

            $payment->update([
                'status' => 'confirmed',
                'extras' => $extras,
            ]);
            NotifyMainBackendJob::dispatch($payment->order_id, 'confirmed', 'astu');
        }
        return [
            'success'          => $success,
            'result'           => $result,
            'receipt'          => $response['receipt'] ?? null,
            'user_notification'=> $response['userNotification'] ?? null,
            'raw'              => $response,
        ];
    }
}
