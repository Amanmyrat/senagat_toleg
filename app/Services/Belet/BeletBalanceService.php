<?php

namespace App\Services\Belet;

use App\Models\BalanceOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BeletBalanceService
{
    protected string $url;

    protected string $authToken;

    public function __construct()
    {
        $this->url = config('belet.url');
        $this->authToken = config('belet.auth_token');
    }

    /**
     *  Balance Recommendations
     */
    public function getBalanceRecommendations(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->authToken,
                'Accept' => 'application/json',
            ])->get($this->url.'/api/v2/balance/recommendations');

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'error' => $response->json('error') ?? [
                    'code' => $response->status(),
                    'message' => $response->body(),
                ],
                'data' => null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => $e->getMessage(),
                ],
                'data' => null,
            ];
        }
    }

    /**
     *  Balance Top Up
     */
    public function topUp(array $payload): array
    {
        $order = BalanceOrder::create([
            'user_id' => $payload['user_id'] ?? null,
            'bank_id' => $payload['bank_id'],
            'amount' => $payload['amount_in_manats'],
            'phone' => $payload['phone'],
            'return_url' => $payload['returnUrl'],
            'client_ip' => $payload['client_ip'],
            'status' => 'pending',
        ]);

        Log::channel('belet')->info('Top-up request created', ['order_id' => $order->id, 'payload' => $payload]);

        $response = Http::withHeaders([
            'Authorization' => $this->authToken,
            'Accept' => 'application/json',
        ])->post($this->url.'/api/v2/balance/top-up', $payload)->json();

        if ($response['success']) {
            $order->update([
                'order_id' => $response['data']['order_id'] ?? null,
            ]);
            Log::channel('belet')->info('Top-up success', ['order_id' => $order->order_id]);
        } else {
            $order->update([
                'status' => 'failed',
                'error_code' => $response['error']['code'] ?? null,
                'error_message' => $response['error']['message'] ?? null,
            ]);
            Log::channel('belet')->error('Top-up failed', ['order_id' => $order->id, 'error' => $response['error']]);
        }

        return $response;
    }
    /**
     *  Balance Confirm
     */
    public function confirm(array $query): array
    {
        $identifier = $query['orderId'] ?? $query['pay_id'] ?? null;
        if (! $identifier) {
            return [
                'success' => false,
                'error' => [
                    'code' => 4,
                    'message' => 'Invalid Query Params'
                ],
                'data' => null,
            ];
        }
        $order = BalanceOrder::where('order_id', $identifier)->first();
        if (! $order) {
            return [
                'success' => false,
                'error' => [
                    'code' => 5,
                    'message' => 'Payment_not_found'
                ],
                'data' => null,
            ];
        }

        if ($order->status === 'confirmed') {
            return [
                'success' => false,
                'error' => [
                    'code' => 6,
                    'message' => 'Payment already activated'
                ],
                'data' => null,
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->authToken,
                'Accept' => 'application/json',
            ])->post($this->url . '/api/v2/balance/confirm?' . http_build_query($query))
                ->throw()
                ->json();
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $response = $e->response->json() ?? [
                    'success' => false,
                    'error' => [
                        'code' => 8,
                        'message' => $e->getMessage()
                    ],
                    'data' => null
                ];
            $order->update([
                'status' => 'failed',
                'error_code' => $response['error']['code'] ?? null,
                'error_message' => $response['error']['message'] ?? null,
            ]);

            Log::channel('belet')->error('Top-up confirm failed', [
                'order_id' => $order->order_id,
                'error' => $response['error'] ?? null
            ]);

            return $response;
        }
        if (($response['success'] ?? false) === true) {
            $order->update(['status' => 'confirmed']);
            Log::info('Top-up confirmed', ['order_id' => $order->order_id]);
        } else {
            $order->update([
                'status' => 'failed',
                'error_code' => $response['error']['code'] ?? null,
                'error_message' => $response['error']['message'] ?? null,
            ]);
            Log::channel('belet')->error('Top-up confirm failed', [
                'order_id' => $order->order_id,
                'error' => $response['error'] ?? null,
            ]);
        }
        return $response;
    }

}
