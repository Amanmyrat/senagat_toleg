<?php

namespace App\Services\AlemTv;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AlemTvTarifClient
{
    protected string $baseUrl;
    protected string $token;
    protected string $aid;
    protected int $timeout;

    public function __construct()
    {
        $config = config('services.alemtv');
        $this->baseUrl = rtrim($config['base_url'], '/');
        $this->token   = $config['token'];
        $this->aid     = $config['aid'];
        $this->timeout = $config['timeout'] ?? 20;
    }


    private function http()
    {
        return Http::timeout($this->timeout)
            ->withToken($this->token)
            ->withHeaders([
                'AID' => $this->aid,
            ]);
    }


    private function handleRequest($request, string $context): array
    {
        try {
            if ($request->failed()) {
                Log::channel('alemtv')->info("AlemTv [{$context}] HTTP error", [
                    'status' => $request->status(),
                    'body'   => $request->body(),
                ]);

                return [
                    'success' => false,
                    'error'   => [
                        'message' => "HTTP Error {$request->status()}",
                    ],

                ];
            }

            return [
                'success' => true,
                'data'    => $request->json(),
            ];

        } catch (\Exception $e) {
            Log::error("AlemTv [{$context}] exception", [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }


    public function getTarifs($type): array
    {
        $request = $this->http()->get("{$this->baseUrl}/v1/gateway/tarifs", [
            'type' => $type,
        ]);
        Log::channel('cdma')->info('AlemTv RAW Response', [
            'status' => $request->status(),
            'body'   => $request->body(),
            'json'   => $request->json(),
        ]);
        return $this->handleRequest($request, 'tarifs');
    }


    public function searchAccount(string $type, array $params): array
    {
        $endpoint = match ($type) {
            'tv'  => "{$this->baseUrl}/v1/gateway/sat/search",
            'iptv' => "{$this->baseUrl}/v1/gateway/iptv/search",
            default => throw new \InvalidArgumentException("Invalid type: {$type}")
        };

        $request = $this->http()->get($endpoint, $params);

        return $this->handleRequest($request, "search:{$type}");
    }

    public function createOrder(array $payload): array
    {
        $request = $this->http()
            ->acceptJson()
            ->post("{$this->baseUrl}/v1/gateway/order/create", $payload);

        Log::channel('alemtv')->info('AlemTv [create] request', [
            'payload' => $payload,
            'status'  => $request->status(),
            'body'    => $request->body(),
        ]);

        return $this->handleRequest($request, 'order:create');
    }


    public function completeOrder(int $orderId): array
    {
        $request = $this->http()
            ->acceptJson()
            ->post("{$this->baseUrl}/v1/gateway/order/complete", [
                'order_id' => $orderId,
            ]);

        Log::channel('alemtv')->info('AlemTv [complete] request', [
            'order_id' => $orderId,
            'status'   => $request->status(),
            'body'     => $request->body(),
        ]);

        return $this->handleRequest($request, 'order:complete');
    }
}
