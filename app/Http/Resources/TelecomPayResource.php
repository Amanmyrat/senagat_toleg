<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TelecomPayResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'txn_id'         => $this['txn_id'],
            'provider_txn'   => $this['prv_txn'] ?? null,
            'amount'         => $this['sum'] ?? null,
            'result'         => (int) $this['result'],
            'comment'        => $this['comment'] ?? null,
        ];
    }
}
