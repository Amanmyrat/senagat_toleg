<?php

namespace App\Services\Telecom;

use App\Helpers\MoneyHelper;

class TelecomPaymentService
{
    public function __construct(
        private TelecomClient $client
    ) {}

    public function forward(array $params)
    {
        $params['curr'] = 934;

        $params['sum'] = MoneyHelper::decimalToInt($params['amount']);

        unset($params['amount']);

        $response = $this->client->send($params);

        return response(
            $response->body(),
            $response->status(),
            ['Content-Type' => 'application/xml']
        );
    }
}
