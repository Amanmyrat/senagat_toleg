<?php

namespace App\Services\Telecom;

use App\Enum\ErrorMessage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelecomClient
{
    public function send(array $params)
    {
        try {

        $params['md5'] = $this->sign($params);
        Log::channel('telecom')->info('Telecom request', [
            'params' => $params,
        ]);


        $response = Http::timeout(10)
            ->accept('application/xml')
            ->get(config('services.telecom.url'), $params);

        Log::channel('telecom')->info('Telecom response', [
            'status'  => $response->status(),
            'headers' => $response->headers(),
            'body'    => $response->body(),
        ]);
            return [
                'success' => true,
                'body' => $response->body()
            ];
        }catch (ConnectionException $e) {

            return [
                'success' => false,
                'result'  => 500,
                'comment' => ErrorMessage::NO_INTERNET_CONNECTION,
            ];
        }
        }

    private function sign(array $params): string
    {
        return md5(
            $params['command'] . ';' .
            $params['account'] . ';' .
            $params['txn_id'] . ';' .
            config('services.telecom.secret')
        );
    }
}
