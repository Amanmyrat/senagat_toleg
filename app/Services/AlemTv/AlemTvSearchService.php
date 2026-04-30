<?php

namespace App\Services\AlemTv;

use App\Services\AlemTv\AlemTvTarifClient;
use App\Services\AlemTv\AlemTvTarifService;
use App\Services\AlemTv\PaymentCalculatorService;
use Carbon\Carbon;

class AlemTvSearchService
{
    public function __construct(
        private AlemTvTarifClient       $client,
        private AlemTvTarifService      $tarifService,
        private PaymentCalculatorService $calculator,
    ) {}

    public function search(array $validated): array
    {
        $type = $validated['type'];

        $params = match ($type) {
            'tv'  => ['cardnumber' => $validated['account']],
            'iptv' => ['login'      => $validated['account']],
        };

        $response = $this->client->searchAccount($type, $params);

        if (! $response['success']) {
            return [
                'success' => false,
                'error'   => $response['error'],
                'data'    => null,
            ];
        }

        $raw = $response['data'];

        $userTarif = $raw['tarif'] ?? null;
        $start     = $raw['start'] ?? null;
        $end       = $raw['end']   ?? null;

        if (! $userTarif || ! $end) {
            return [
                'success' => false,
                'message' => 'Tarif or end date missing from API response',
            ];
        }

        $matchedTarif = $this->tarifService->matchTarif($type, $userTarif);

        if (! $matchedTarif) {
            $matchedTarif = [
                'tarif' => $userTarif,
                'price' => 0,
                'max_period' => 0,
            ];
        }

        $paymentOptions = $this->calculator->calculate($matchedTarif, $end);

        return [
            'success' => true,
            'data'    => [
                'account'         => $validated['account'],
                'type'            => $type,
                'tarif'           => $userTarif,
                'end'             => Carbon::createFromTimestamp($end)->format('d.m.Y'),
                'payment_options' => $paymentOptions,
//                'payment_options' => empty($paymentOptions) ? [
//                    [
//                        "months" => 1,
//                        "amount" => 1
//                    ]
//                ] : $paymentOptions,
            ],
        ];
    }
}
