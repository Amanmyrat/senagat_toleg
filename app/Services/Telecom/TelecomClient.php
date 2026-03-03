<?php

namespace App\Services\Telecom;

use Illuminate\Support\Facades\Http;

class TelecomClient
{
    public function send(array $params)
    {
        $params['md5'] = $this->sign($params);

        return Http::get(
            config('services.telecom.url'),
            $params
        );
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
