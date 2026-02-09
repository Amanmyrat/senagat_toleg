<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeletConfirmResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'data' => [
                'success' => (bool) ($this['success'] ?? false),
                'error' => $this['error'] ?? null,
                'message' => $this['data']['message']
                    ?? $this['data']['errorMessage']
                    ?? null,
            ],
        ];
    }
}
