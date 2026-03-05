<?php

namespace App\Services\Telecom;

use App\Helpers\MoneyHelper;

class TelecomPaymentService
{
    public function __construct(
        private TelecomClient $client
    ) {}
    public function checkBalance(array $params, string $txnId): array
    {
        $response = $this->client->send([
            'command' => 'check_balance',
            'txn_id'  => $txnId,
            'account' => $params['account'],
            'sum'     => '0.00',
            'curr'    => 'TMT',
        ]);

        return $this->parseXml($response->body());
    }

    public function pay(array $params, string $txnId,string $txnDate): array
    {
        $response = $this->client->send([
            'command'  => 'pay',
            'txn_id'   => $txnId,
            'txn_date' => $txnDate,
            'account'  => $params['account'],
            'sum'      => MoneyHelper::decimalToInt($params['amount']) / 100,
            'curr'     => 'TMT',
        ]);

        return $this->parseXml($response->body());
    }
    /**
     * Telecom XML -> array
     */
    private function parseXml(string $xml): array
    {
        $parsed = simplexml_load_string($xml, null, LIBXML_NOCDATA);

        if ($parsed === false) {
            return [
                'result'  => 300,
                'comment' => 'Invalid XML from Telecom',
            ];
        }

        return json_decode(json_encode($parsed), true);
    }
}

//public function forward(array $params)
//{
//    $params['curr'] = 934;
//
//    $params['sum'] = MoneyHelper::decimalToInt($params['amount']);
//
//    unset($params['amount']);
//
//    $response = $this->client->send($params);
//
//    return response(
//        $response->body(),
//        $response->status(),
//        ['Content-Type' => 'application/xml']
//    );
//}
