<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifyMainBackendJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 10;
    public int $backoff = 30;

    public function __construct(
        public string $externalId,
        public string $status,
        public string $provider,
    ) {}

    public function handle(): void
    {
        $response = Http::withToken(config('services.senagat_back.webhook_secret'))
            ->post(config('services.senagat_back.webhook_url'). '/api/v1/webhooks/payment-status', [
                'external_id' => $this->externalId,
                'status'      => $this->status,
                'provider'    => $this->provider,
            ]);

        if ($response->failed()) {
            throw new \Exception('Webhook failed: ' . $response->status());
        }

        Log::channel('telecom')->info('Main backend notified successfully', [
            'external_id' => $this->externalId,
            'status'      => $this->status,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('telecom')->error('Webhook permanently failed after all retries', [
            'external_id' => $this->externalId,
            'status'      => $this->status,
            'error'       => $exception->getMessage(),
        ]);
    }
}
