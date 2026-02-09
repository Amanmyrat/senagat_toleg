<?php

namespace App\Services\Payments\Gateways;

use App\Services\Payments\Contracts\PaymentStatusGatewayInterface;
use Illuminate\Support\Facades\Http;

class BeletGateway implements PaymentStatusGatewayInterface
{
    protected string $url;

    protected string $authToken;

    public function __construct()
    {
        $this->url = config('belet.url');
        $this->authToken = config('belet.auth_token');
    }

    public function checkStatus(string $orderId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->authToken,
                'Accept' => 'application/json',
            ])->get($this->url.'/api/v2/orders/'.$orderId.'/status');

            return $response->json() ?? [
                'success' => false,
                'error' => ['code' => 500, 'message' => 'Empty response from Belet'],
                'data' => null,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => 'No internet connection',
                ],
                'data' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => ['code' => 500, 'message' => $e->getMessage()],
                'data' => null,
            ];
        }
    }
}
