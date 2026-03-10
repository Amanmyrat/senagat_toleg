<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Services\Astu\AstuService;
use App\Services\Payments\PaymentGatewayResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AstuStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 15;
    public int $backoff = 20;

    public function __construct(
        public Payment $payment
    )
    {
    }

    /**
     * @throws \Throwable
     */
    public function handle(
        PaymentGatewayResolver $gatewayResolver,
        AstuService            $astuService
    ): void
    {

        try {

            $payment = $this->payment->fresh();
            if (! $payment->order_id) {
                Log::channel('astu')->warning('order_id is null, releasing', [
                    'payment_id' => $payment->id,
                ]);
                $this->release(30);
                return;
            }
            $serviceType = $payment->payment_target['type'];
            $gateway = $gatewayResolver->resolve($payment->bank_key, 'astu',$serviceType);
            $response = $gateway->checkPaymentStatus($payment->order_id);
            $errorCode = (string)($response['ErrorCode'] ?? '');
            $orderStatus = (int)($response['OrderStatus'] ?? -1);

            Log::channel('astu')->info('Bank status checked', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'error_code' => $errorCode,
                'order_status' => $orderStatus,
                'attempt' => $this->attempts(),
            ]);

            if ($errorCode !== '0' && $errorCode !== '') {
                Log::channel('astu')->warning('Bank rejected payment', [
                    'payment_id' => $payment->id,
                ]);
                $payment->update(['status' => 'failed']);
                return;
            }

            if ($orderStatus !== 2) {
                if ($this->attempts() >= $this->tries) {
                    Log::channel('astu')->error('Bank status max attempts reached', [
                        'payment_id' => $payment->id,
                    ]);
                    $payment->update(['status' => 'failed']);
                    return;
                }

                $this->release(30);
                return;
            }

            $result = $astuService->updateBalance($payment);

            Log::channel('astu')->info('Astu updateBalance result', [
                'payment_id' => $payment->id,
                'success' => $result['success'],
                'result' => $result['result'],
                'receipt' => $result['receipt'] ?? null,
                'user_notification' => $result['user_notification'] ?? null,
                'attempt' => $this->attempts(),
            ]);

            if ($result['success']) {
                Log::channel('astu')->info('Astu payment completed', [
                    'payment_id' => $payment->id,
                    'receipt' => $result['receipt'] ?? null,
                ]);
                return;
            }

            $fatalResults = [
                'access_denied',
                'wrong_agreement_number',
                'customer_not_found',
                'wrong_payment_amount',
                'wrong_payment_number',
                'wrong_date',
            ];

            if (in_array($result['result'], $fatalResults)) {
                $extras = $payment->extras ?? [];
                $extras['astu_result'] = $result['result'];
                $payment->update(['status' => 'failed', 'extras' => $extras]);

                Log::channel('astu')->error('Astu fatal error, stopping', [
                    'payment_id' => $payment->id,
                    'result' => $result['result'],
                ]);
                return;
            }

            if ($this->attempts() >= $this->tries) {
                $extras = $payment->extras ?? [];
                $extras['astu_result'] = $result['result'];
                $payment->update(['status' => 'failed', 'extras' => $extras]);

                Log::channel('astu')->error('Astu max attempts reached', [
                    'payment_id' => $payment->id,
                    'result' => $result['result'],
                ]);
                return;
            }

            Log::channel('astu')->warning('Astu payment failed, will retry', [
                'payment_id' => $payment->id,
                'result' => $result['result'],
                'attempt' => $this->attempts(),
            ]);

            $this->release(30);
        } catch (\Throwable $e) {

            Log::channel('astu')->error('AstuStatusJob failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}
