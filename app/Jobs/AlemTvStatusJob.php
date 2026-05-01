<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Services\AlemTv\AlemTvCreateService;
use App\Services\Payments\PaymentGatewayResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AlemTvStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 30;
    public int $backoff = 10;

    private int $maxCompleteAttempts = 5;

    public function __construct(
        public Payment $payment,
        public int     $completeAttempts = 0,
        public bool    $skipBankCheck = false,
    ) {}

    public function handle(
        PaymentGatewayResolver $gatewayResolver,
        AlemTvCreateService    $createService,
    ): void {
        try {
            $payment = $this->payment->fresh();

            if (in_array($payment->status, ['confirmed', 'failed'])) {
                Log::channel('alemtv')->info("Payment already {$payment->status}, skipping", [
                    'payment_id' => $payment->id,
                ]);
                return;
            }

            if (! $payment->order_id) {
                Log::channel('alemtv')->warning('order_id is null, releasing', [
                    'payment_id' => $payment->id,
                ]);
                $this->release(30);
                return;
            }
            if ($this->skipBankCheck) {
                $this->runCreate($payment, $createService);
                return;
            }

            $type    = $payment->payment_target['type'];
            $gateway = $gatewayResolver->resolve($payment->bank_key, 'alemtv', $type);

            $response    = $gateway->checkPaymentStatus($payment->order_id);
            $errorCode   = (string) ($response['ErrorCode']   ?? '');
            $orderStatus = (int)    ($response['OrderStatus'] ?? -1);

            Log::channel('alemtv')->info('Bank status checked', [
                'payment_id'        => $payment->id,
                'order_id'          => $payment->order_id,
                'error_code'        => $errorCode,
                'order_status'      => $orderStatus,
                'bank_attempt'      => $this->attempts(),
                'complete_attempts' => $this->completeAttempts,
            ]);

            if ($errorCode !== '0' && $errorCode !== '') {
                Log::channel('alemtv')->warning('Bank rejected payment', [
                    'payment_id' => $payment->id,
                    'error_code' => $errorCode,
                ]);
                $payment->update(['status' => 'failed']);
                NotifyMainBackendJob::dispatch($payment->order_id, 'failed', 'alemtv');
                return;
            }

            if ($orderStatus !== 2) {
                if ($this->attempts() >= $this->tries) {
                    Log::channel('alemtv')->error('Max bank check attempts reached', [
                        'payment_id' => $payment->id,
                    ]);
                    $payment->update(['status' => 'failed']);
                    NotifyMainBackendJob::dispatch($payment->order_id, 'failed', 'alemtv');
                    return;
                }

                $this->release(30);
                return;
            }

            $this->runCreate($payment, $createService);

        } catch (\Throwable $e) {
            Log::channel('alemtv')->error('AlemTvStatusJob failed: ' . $e->getMessage(), [
                'payment_id' => $this->payment->id,
                'trace'      => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function runCreate(Payment $payment, AlemTvCreateService $createService): void
    {
        $result = $createService->create([
            'type'    => $payment->payment_target['type'],
            'subject' => $payment->payment_target['subject'],
            'tarif'   => $payment->payment_target['tarif'],
            'period'  => (int) $payment->payment_target['period'],
        ]);

        Log::channel('alemtv')->info('AlemTv create result', [
            'payment_id'        => $payment->id,
            'success'           => $result['success'],
            'data'              => $result['data']  ?? null,
            'error'             => $result['error'] ?? null,
            'complete_attempts' => $this->completeAttempts + 1,
        ]);

        if ($result['success']) {
            $this->markConfirmed($payment);
            return;
        }

        if (($result['error']['code'] ?? '') === 'already_paid') {
            Log::channel('alemtv')->info('AlemTv already paid, marking confirmed', [
                'payment_id' => $payment->id,
            ]);
            $this->markConfirmed($payment);
            return;
        }

        $newAttempts = $this->completeAttempts + 1;

        if ($newAttempts >= $this->maxCompleteAttempts) {
            Log::channel('alemtv')->error('AlemTv max attempts reached', [
                'payment_id'        => $payment->id,
                'complete_attempts' => $newAttempts,
                'error'             => $result['error'] ?? null,
            ]);
            $payment->update(['status' => 'failed']);
            NotifyMainBackendJob::dispatch($payment->order_id, 'failed', 'alemtv');
            return;
        }

        Log::channel('alemtv')->warning('AlemTv create failed, will retry', [
            'payment_id'        => $payment->id,
            'complete_attempts' => $newAttempts,
            'error'             => $result['error'] ?? null,
        ]);

        self::dispatch($payment, $newAttempts, skipBankCheck: true)
            ->delay(now()->addSeconds(30));
    }

    private function markConfirmed(Payment $payment): void
    {
        $payment->update(['status' => 'confirmed']);

        Log::channel('alemtv')->info('AlemTv payment confirmed', [
            'payment_id' => $payment->id,
        ]);

        NotifyMainBackendJob::dispatch($payment->order_id, 'confirmed', 'alemtv');
    }
}
