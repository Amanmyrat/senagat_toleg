<?php

namespace App\Jobs;

use App\Helpers\MoneyHelper;
use App\Helpers\TxnIdGenerator;
use App\Models\Payment;
use App\Services\Payments\PaymentGatewayResolver;
use App\Services\Telecom\TelecomPaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TelecomStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public int $tries = 20;
    public int $backoff = 10;

    public function __construct(
        public Payment $payment
    ) {}

    public function handle(
        PaymentGatewayResolver $gatewayResolver,
        TelecomPaymentService $telecomService
    ): void {
        $payment = $this->payment->fresh();
        $gateway = $gatewayResolver->resolve(
            $this->payment->bank_key,
            'telecom'
        );

        $response    = $gateway->checkPaymentStatus($this->payment->order_id);
        $errorCode   = (string) ($response['ErrorCode'] ?? '');
        $orderStatus = (int)    ($response['OrderStatus'] ?? -1);

        Log::channel('telecom')->info('Bank status checked', [
            'payment_id'   => $this->payment->id,
            'order_id'     => $this->payment->order_id,
            'error_code'   => $errorCode,
            'order_status' => $orderStatus,
            'attempt'      => $this->attempts(),
        ]);

        if ($errorCode !== '0' && $errorCode !== '') {
            Log::channel('telecom')->warning('Bank rejected payment', [
                'payment_id' => $this->payment->id,
                'response'   => $response,
            ]);

            $this->payment->update(['status' => 'failed']);
            return;
        }

        if ($orderStatus !== 2) {
            if ($this->attempts() >= $this->tries) {
                Log::channel('telecom')->error('Bank status max attempts reached', [
                    'payment_id' => $this->payment->id,
                ]);

                $this->payment->update(['status' => 'failed']);
                return;
            }

            $this->release(30);
            return;
        }

        $extras = $this->payment->extras ?? [];
        $txnId = $extras['telecom_txn_id'] ?? TxnIdGenerator::generate();
        if (!isset($extras['telecom_txn_id'])) {

            $extras['telecom_txn_id'] = $txnId;

            $payment->update([
                'extras' => $extras
            ]);

            $payment->refresh();
        }

        $txnDate         = now()->format('YmdHis');
        $telecomResponse = $telecomService->pay([
            'account' => $this->payment->payment_target['value'],
            'amount'  => number_format(
                MoneyHelper::intToDecimal($payment->amount),
                2, '.', ''
            ),
        ], $txnId, $txnDate);

        $result = (int) ($telecomResponse['result'] ?? -1);

        Log::channel('telecom')->info('Telecom pay response', [
            'payment_id' => $this->payment->id,
            'txn_id'     => $txnId,
            'result'     => $result,
            'comment'    => $telecomResponse['comment'] ?? '',
            'attempt'    => $this->attempts(),
        ]);

        if (in_array($result, [0, 8])) {
            $extras['telecom_result'] = $result;
            $this->payment->update([
                'status'         => 'confirmed',
                'extras' => $extras
            ]);

            Log::channel('telecom')->info('Telecom payment completed', [
                'payment_id' => $this->payment->id,
                'result'     => $result,
                'txn_id'     => $txnId,
            ]);

            return;
        }

        if ($this->attempts() >= $this->tries) {
            $extras['telecom_result'] = $result;

            $this->payment->update([
                'status'         => 'failed',
                'extras' => $extras
            ]);

            Log::channel('telecom')->error('Telecom payment max attempts reached', [
                'payment_id' => $this->payment->id,
                'result'     => $result,
                'txn_id'     => $txnId,
            ]);

            return;
        }

        Log::channel('telecom')->warning('Telecom payment failed, will retry', [
            'payment_id' => $this->payment->id,
            'result'     => $result,
            'txn_id'     => $txnId,
            'comment'    => $telecomResponse['comment'] ?? '',
            'attempt'    => $this->attempts(),
        ]);

        $this->release(10);
    }
}
