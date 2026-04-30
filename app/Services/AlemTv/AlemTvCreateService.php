<?php

namespace App\Services\AlemTv;

class AlemTvCreateService
{
    public function __construct(
        private AlemTvTarifService  $tarifService,
        private AlemTvTarifClient $client,

    ) {}

    public function create(array $validated): array
    {
        $type    = $validated['type'];
        $subject = $validated['subject'];
        $tarif   = $validated['tarif'];
        $period  = (int) $validated['period'];

        $matchedTarif = $this->tarifService->matchTarif($type, $tarif);

        if (! $matchedTarif) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'tarif_not_found',
                    'message' => "Tarif '{$tarif}' not found",
                ],
                'data' => null,
            ];
        }

        $amount = (float) $matchedTarif['price'] * $period;

        $createResponse = $this->client->createOrder([
            'subject' => $subject,
            'tarif'   => $tarif,
            'period'  => $period,
            'amount'  => $amount,
        ]);

        if (! $createResponse['success']) {
            return [
                'success' => false,
                'error'   => $createResponse['error'],
                'data'    => null,
            ];
        }

        $orderId    = $createResponse['data']['order_id']    ?? null;
        $orderToken = $createResponse['data']['order_token'] ?? null;

        if (! $orderId) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'missing_order_id',
                    'message' => 'order_id not returned from gateway',
                ],
                'data' => null,
            ];
        }

        $completeResponse = $this->client->completeOrder($orderId);

        return $this->normalizeCompleteResponse($completeResponse, $orderId, $orderToken, $amount, $period, $tarif);
    }

    private function normalizeCompleteResponse(
        array   $completeResponse,
        int     $orderId,
        ?string $orderToken,
        float   $amount,
        int     $period,
        string  $tarif,
    ): array {
        if (! $completeResponse['success']) {
            $statusCode = $completeResponse['status_code'];
            $message    = $completeResponse['error']['message'] ?? 'Unknown error';

            $code = match ($statusCode) {
                302     => 'already_paid',
                404     => 'not_found',
                default => 'gateway_error',
            };

            return [
                'success' => false,
                'error'   => [
                    'code'    => $code,
                    'message' => $message,
                ],
                'data' => null,
            ];
        }

        $status = $completeResponse['data']['status'] ?? null;

        if ($status === null) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'unexpected_response',
                    'message' => 'Unexpected response from gateway complete',
                ],
                'data' => ['raw' => $completeResponse['data']],
            ];
        }

        return [
            'success' => (bool) $status,
            'data'    => [
                'order_id'    => $orderId,
                'order_token' => $orderToken,
                'tarif'       => $tarif,
                'period'      => $period,
                'amount'      => $amount,
            ],
        ];
    }
}
