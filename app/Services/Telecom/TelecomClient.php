<?php

namespace App\Services\Telecom;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelecomClient
{
    public function send(array $params)
    {
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
        return $response;
    }

    private function sign(array $params): string
    {
        return md5(
            $params['command'] . ';' .
            $params['account'] . ';' .
            config('services.telecom.secret')
        );
    }
}
