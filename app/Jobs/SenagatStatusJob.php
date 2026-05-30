<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Services\SenagatBank\SenagatPaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SenagatStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 30;
    public int $backoff = 30;

    public function __construct(
        public Payment $payment
    ) {}

    public function handle(SenagatPaymentService $paymentService): void
    {
        $payment = $this->payment->fresh();

        if (! $payment || $payment->status === 'confirmed' || $payment->status === 'failed') {
            return;
        }

        if (! $payment->order_id) {
            Log::channel('senagatPayment')->warning('Payment order_id missing', ['payment_id' => $payment->id]);
            return;
        }

        try {
            $response = $paymentService->checkStatus($payment->bank_id, $payment->order_id);
            $body = $response['data']['body'] ?? [];
        } catch (\Exception $e) {
            Log::channel('senagatPayment')->error('Senagat API connection error', [
                'payment_id' => $payment->id,
                'message' => $e->getMessage()
            ]);

            $this->release(30);
            return;
        }

        $errorCode = (string) ($body['ErrorCode'] ?? '');
        $orderStatus = (int) ($body['OrderStatus'] ?? -1);

        Log::channel('senagatPayment')->info('Senagat status checked', [
            'payment_id' => $payment->id,
            'order_id' => $payment->order_id,
            'error_code' => $errorCode,
            'order_status' => $orderStatus,
            'attempt' => $this->attempts(),
        ]);

        // 1. Durum: Kesin Başarılı Ödeme (OrderStatus = 2)
        if ($errorCode === '0' && $orderStatus === 2) {
            $payment->update(['status' => 'confirmed']);

            NotifyMainBackendSenagatPaymentJob::dispatch($payment->order_id, 'confirmed', $payment->type);

            Log::channel('senagatPayment')->info('Payment confirmed', ['payment_id' => $payment->id]);
            return;
        }


        if ($errorCode !== '0' && $errorCode !== '') {
            $payment->update([
                'status' => 'failed',
                'error_code' => $errorCode,
                'error_message' => $body['ErrorMessage'] ?? null,
            ]);

            NotifyMainBackendSenagatPaymentJob::dispatch($payment->order_id, 'failed', $payment->type);

            Log::channel('senagatPayment')->warning('Payment failed by bank', ['payment_id' => $payment->id]);
            return;
        }

        if ($this->attempts() >= $this->tries) {
            $payment->update(['status' => 'failed']);

            NotifyMainBackendSenagatPaymentJob::dispatch($payment->order_id, 'failed', $payment->type);

            Log::channel('senagatPayment')->error('Payment status timeout', ['payment_id' => $payment->id]);
            return;
        }

        Log::channel('senagatPayment')->info('Payment still pending', [
            'payment_id' => $payment->id,
            'attempt' => $this->attempts(),
        ]);

        $this->release(30);
    }
}
