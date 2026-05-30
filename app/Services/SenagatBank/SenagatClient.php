<?php
namespace App\Services\SenagatBank;

use Illuminate\Support\Facades\Http;

class SenagatClient
{
    public function pay(array $payload): array
    {
        $url = rtrim(config('payments.senagat.base_url'), '/')
            . config('payments.senagat.pay_endpoint');

        $response = Http::timeout(15)
            ->retry(3, 1000)
            ->get($url, $payload);

        return [
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ];
    }

    public function checkStatus(array $payload): array
    {
        $url = rtrim(config('payments.senagat.base_url'), '/')
            . config('payments.senagat.status_endpoint');

        $response = Http::timeout(15)
            ->retry(3, 1000)
            ->get($url, $payload);

        return [
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ];
    }
}
