<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifyMainBackendSenagatPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 30;
    public int $backoff = 30;

    public function __construct(
        public string $externalId,
        public string $status,
        public string $type,
    ) {}

    public function handle(): void
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->withToken(
            config('services.senagat_back.webhook_secret')
        )->post(
            config('services.senagat_back.webhook_url')
            . '/api/v1/senagat/webhooks/payment-status',
            [
                'external_id' => $this->externalId,
                'status' => $this->status,
                'type' => $this->type,
            ]
        );

        if ($response->failed()) {
            throw new \Exception(
                'Webhook failed: ' . $response->status()
            );
        }

        Log::channel('senagatPayment')->info(
            'Main backend payment webhook sent',
            [
                'external_id' => $this->externalId,
                'status' => $this->status,
                'type' => $this->type,
            ]
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('senagatPayment')->error(
            'Payment webhook permanently failed',
            [
                'external_id' => $this->externalId,
                'status' => $this->status,
                'type' => $this->type,
                'error' => $exception->getMessage(),
            ]
        );
    }
}
