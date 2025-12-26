<?php

namespace App\Services\Payments\Gateways;

use App\Services\Payments\Contracts\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SenagatGateway implements PaymentGateway
{
    protected array $config;

    public function __construct(protected string $bankKey)
    {
        $this->config = config("payments.{$this->bankKey}");
    }

//    protected function getBankKey(): string
//    {
//        return $this->config['key'] ?? 'senagat';
//    }
    public function createPayment(array $payload): array
    {
        $formData =
            [
                'userName' => $this->config['userName'],
                'password' => $this->config['password'],
                'orderNumber' => $payload['order_number'],
                'amount' => $payload['amount'] ,
                'currency' => $this->config['currency'],
                'returnUrl' => $this->config['return_url'],
                'description' => $payload['description'] ?? 'Charity payment',
            ];
        if ($this->bankKey === 'senagat') {
            $formData['language'] = 'tk';
        }

        try {
            $response = Http::asForm()
                ->post($this->config['base_url'] . $this->config['pay_endpoint'], $formData);

            Log::info('Gateway resolve', [
                'bankKey' => $bankKey ?? null,
            ]);
            return $response->json() ?? [
                    'success' => false,
                    'error' => [
                        'code' => $response->status(),
                        'message' => $response->body(),
                    ],
                    'data' => null,
                ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => 'Senagat gateway unreachable',
                ],
                'data' => null,
            ];
        }
    }

        public function checkPaymentStatus(string $orderId): array
    {

        $formData = [
            'userName' => $this->config['userName'],
            'password' => $this->config['password'],
            'orderId'  => $orderId,
        ];

        try {
            $response = Http::asForm()->post($this->config['base_url'] . $this->config['status_endpoint'], $formData)->json();

            return $response ?? [
                    'success' => false,
                    'data' => null,
                    'error' => ['message' => 'Empty response from gateway'],
                ];

        } catch (\Throwable $e) {
            return ['success' => false, 'data' => null, 'error' => ['message' => $e->getMessage()]];
        }
    }

}
