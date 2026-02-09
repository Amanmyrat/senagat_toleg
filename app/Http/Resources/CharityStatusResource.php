<?php

namespace App\Http\Resources;

use App\Support\Money;
use Illuminate\Http\Resources\Json\JsonResource;

class CharityStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'success' => true,
            'data' => [
                'orderStatus' => $this['OrderStatus'] ?? null,
                'errorCode' => $this['ErrorCode'] ?? null,
                'errorMessage' => $this['ErrorMessage'] ?? null,
                'amount' => Money::fromCents($this['Amount'] ?? null),
            ],
        ];
    }
}
