<?php

namespace App\Services\Belet;

use App\Models\Payment;
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
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => "No internet connection",
                ],
                'data' => null,
            ];
        }catch (\Exception $e) {
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
        $order = Payment::create([
            'user_id'    => $payload['user_id'] ?? null,
            'bank_id'    => $payload['bank_id'],
            'type' => 'belet',
            'amount'     => $payload['amount_in_manats'],
            'payment_target' => [
                'type'  => 'phone',
                'value' => $payload['phone'],
            ],
            'return_url' => $payload['return_url'] ?? null,
            'client_ip'  => $payload['client_ip'] ?? null,
            'status'     => 'pending',
        ]);

        Log::channel('belet')->info('Top-up request created', [
            'order_id' => $order->id,
            'bank_id' => $payload['bank_id'],
            'amount' => $payload['amount_in_manats'],
            'payment_target' => $order->payment_target,
        ]);
        $beletPayload = [
            'bank_id' => $payload['bank_id'],
            'amount_in_manats' => $payload['amount_in_manats'],
            'phone' => $payload['phone'],

        ];
        Log::channel('belet')->info('Top-up request created', [
            'phone' => $payload['phone'],]);
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->authToken,
                'Accept'        => 'application/json',
            ])
                ->post($this->url . '/api/v2/balance/top-up', $beletPayload)
                ->throw()
                ->json();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return [
                'success' => false,
                'error' => [
                    'code'    => 500,
                    'message' => "No internet connection",
                ],
                'data' => null,
            ];
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $response = $e->response->json() ?? [
                    'success' => false,
                    'error'   => [
                        'code'    => 8,
                        'message' => $e->getMessage(),
                    ],
                    'data' => null
                ];

            $order->update([
                'status'        => 'failed',
                'error_code'    => $response['error']['code'] ?? null,
                'error_message' => $response['error']['message'] ?? null,
            ]);

            Log::channel('belet')->error('Top-up failed', [
                'order_id' => $order->id,
                'error'    => $response['error'] ?? null
            ]);

            return $response;
        }

        if (($response['success'] ?? false) === true) {
            $order->update([
                'order_id' => $response['data']['order_id'] ?? null,
                'status'   => 'pending',
            ]);

            Log::channel('belet')->info('Top-up success', ['order_id' => $order->order_id]);
        } else {
            $order->update([
                'status'        => 'failed',
                'error_code'    => $response['error']['code'] ?? null,
                'error_message' => $response['error']['message'] ?? null,
            ]);

            Log::channel('belet')->error('Top-up failed', [
                'order_id' => $order->id,
                'error'    => $response['error'] ?? null
            ]);
        }

        return $response;
    }
    /**
     *  Balance Confirm
     */
    /**
     * Balance Confirm
     */
    public function confirm(array $query): array
    {
        $identifier = $query['orderId'] ?? $query['pay_id'] ?? null;

        if (! $identifier) {
            return [
                'success' => false,
                'error' => [
                    'code' => 4,
                    'message' => 'Invalid Query Params',
                ],
                'data' => null,
            ];
        }
        $order = Payment::where('order_id', $identifier)->first();

        if (! $order) {
            return [
                'success' => false,
                'error' => [
                    'code' => 5,
                    'message' => 'Payment not found',
                ],
                'data' => null,
            ];
        }
        if ($order->status === 'confirmed') {
            return [
                'success' => true,
                'data' => [
                    'status' => 'confirmed',
                    'message' => 'Already confirmed',
                ],
            ];
        }
        $order->update([
            'status' => 'confirming',
        ]);

        try {
            $bankResponse = Http::withHeaders([
                'Authorization' => $this->authToken,
                'Accept' => 'application/json',
            ])->post(
                $this->url . '/api/v2/balance/confirm?' . http_build_query($query)
            )->throw()->json();

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $order->update([
                'status' => 'failed',
                'error_message' => 'No internet connection',
            ]);

            return [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => 'No internet connection',
                ],
                'data' => null,
            ];

        } catch (\Illuminate\Http\Client\RequestException $e) {

            $response = $e->response->json() ?? [];

            $order->update([
                'status' => 'failed',
                'error_code' => $response['data']['code'] ?? null,
                'error_message' => $response['data']['message'] ?? null,
            ]);

            Log::channel('belet')->error('Top-up confirm failed', [
                'order_id' => $order->order_id,
                'response' => $response,
            ]);

            return [
                'success' => false,
                'error' => $response['data'] ?? [
                        'code' => 8,
                        'message' => 'Confirm request failed',
                    ],
                'data' => null,
            ];
        }
        if (($bankResponse['success'] ?? false) === true) {

            $order->update([
                'status' => 'confirmed',
                'error_code' => null,
                'error_message' => null,
            ]);

            Log::info('Top-up confirmed', [
                'order_id' => $order->order_id,
            ]);

            return [
                'success' => true,
                'data' => [
                    'status' => 'confirmed',
                    'message' => 'Balance top-up successful',
                ],
            ];
        }
        $order->update([
            'status' => 'failed',
            'error_code' => $bankResponse['data']['code'] ?? null,
            'error_message' => $bankResponse['data']['message'] ?? null,
        ]);

        return [
            'success' => false,
            'error' => $bankResponse['data'] ?? [
                    'code' => 9,
                    'message' => 'Payment is not completed',
                ],
            'data' => null,
        ];
    }

}
