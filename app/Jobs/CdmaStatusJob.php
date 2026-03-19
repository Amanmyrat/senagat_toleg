<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Services\Cdma\CdmaService;
use App\Services\Payments\PaymentGatewayResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CdmaStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 15;
    public int $backoff = 20;

    public function __construct(
        public Payment $payment
    ) {}

    public function handle(
        PaymentGatewayResolver $gatewayResolver,
        CdmaService            $cdmaService
    ): void {
        try {
            $payment = $this->payment->fresh();

            if (! $payment->order_id) {
                Log::channel('cdma')->warning('order_id is null, releasing', [
                    'payment_id' => $payment->id,
                ]);
                $this->release(30);
                return;
            }

            $gateway     = $gatewayResolver->resolve($payment->bank_key, 'cdma');
            $response    = $gateway->checkPaymentStatus($payment->order_id);
            $errorCode   = (string) ($response['ErrorCode'] ?? '');
            $orderStatus = (int)    ($response['OrderStatus'] ?? -1);

            Log::channel('cdma')->info('Bank status checked', [
                'payment_id'   => $payment->id,
                'order_id'     => $payment->order_id,
                'error_code'   => $errorCode,
                'order_status' => $orderStatus,
                'attempt'      => $this->attempts(),
            ]);

            // Banka reddetti
            if ($errorCode !== '0' && $errorCode !== '') {
                Log::channel('cdma')->warning('Bank rejected payment', [
                    'payment_id' => $payment->id,
                    'response'   => $response,
                ]);
                $payment->update(['status' => 'failed']);
                NotifyMainBackendJob::dispatch($payment->order_id, 'failed', 'cdma');
                return;
            }

            // Banka henüz onaylamamış
            if ($orderStatus !== 2) {
                if ($this->attempts() >= $this->tries) {
                    Log::channel('cdma')->error('Bank status max attempts reached', [
                        'payment_id' => $payment->id,
                    ]);
                    $payment->update(['status' => 'failed']);
                    NotifyMainBackendJob::dispatch($payment->order_id, 'failed', 'cdma');
                    return;
                }

                $this->release(30);
                return;
            }

            // Banka onayladı → CDMA API'ye makePayment
            $result = $cdmaService->makePayment($payment);

            Log::channel('cdma')->info('Cdma makePayment result', [
                'payment_id' => $payment->id,
                'success'    => $result['success'],
                'status'     => $result['status'],
                'req_id'     => $result['req_id'] ?? null,
                'rrn'        => $result['rrn'] ?? null,
                'attempt'    => $this->attempts(),
            ]);

            if ($result['success']) {
                Log::channel('cdma')->info('Cdma payment completed', [
                    'payment_id' => $payment->id,
                    'req_id'     => $result['req_id'] ?? null,
                    'rrn'        => $result['rrn'] ?? null,
                ]);
                return;
            }

            $fatalStatuses = [
                'ERR_PHONE',
                'ERR_SUMM',
                'ERR_CURRENCY',
                'ERR_DATE',
                'ERR_ACCESS',
                'ERR_FORBIDEN',
                'ERR_PAYMENT',
                'ERROR',
            ];

            if (in_array($result['status'], $fatalStatuses)) {
                $extras                 = $payment->extras ?? [];
                $extras['cdma_status']  = $result['status'];
                $payment->update(['status' => 'failed', 'extras' => $extras]);

                Log::channel('cdma')->error('Cdma fatal error, stopping', [
                    'payment_id' => $payment->id,
                    'status'     => $result['status'],
                ]);
                NotifyMainBackendJob::dispatch($payment->order_id, 'failed', 'cdma');
                return;
            }

            if ($this->attempts() >= $this->tries) {
                $extras                 = $payment->extras ?? [];
                $extras['cdma_status']  = $result['status'];
                $payment->update(['status' => 'failed', 'extras' => $extras]);

                Log::channel('cdma')->error('Cdma max attempts reached', [
                    'payment_id' => $payment->id,
                    'status'     => $result['status'],
                ]);
                NotifyMainBackendJob::dispatch($payment->order_id, 'failed', 'cdma');
                return;
            }

            Log::channel('cdma')->warning('Cdma payment failed, will retry', [
                'payment_id' => $payment->id,
                'status'     => $result['status'],
                'attempt'    => $this->attempts(),
            ]);

            $this->release(30);

        } catch (\Throwable $e) {
            Log::channel('cdma')->error('CdmaStatusJob failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
