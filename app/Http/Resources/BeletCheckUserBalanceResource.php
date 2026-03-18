<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeletCheckUserBalanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray($request)
    {
        $data = $this->resource;

        return [
            'success' => true,
            'data' => [
                'balance' => number_format((float) ($data['balance_in_manats'] ?? 0), 2, '.', ''),
                'max_possible_amount' => $data['max_possible_amount'] ?? 0,
            ]
        ];
    }
}
