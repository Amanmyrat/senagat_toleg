<?php

namespace App\Services\AlemTv;

use Illuminate\Support\Facades\Cache;

class AlemTvTarifService
{
    public function __construct(
        private AlemTvTarifClient $client
    ) {}


    public function getTarifs(string $type): array
    {
        $cacheKey = "tarifs:{$type}";
        $ttl      = config('payment.tarif_cache_ttl', 86400);

        return Cache::remember($cacheKey, $ttl, function () use ($type) {
            $response = $this->client->getTarifs($type);

            if (! $response['success']) {
                return [];
            }

            return $response['data'] ?? [];
        });


    }


    public function matchTarif(string $type, string $userTarif): ?array
    {
        $tarifs = $this->getTarifs($type);

        foreach ($tarifs as $tarif) {
            if (isset($tarif['tarif']) && $tarif['tarif'] === $userTarif) {
                return $tarif;
            }
        }

        return null;
    }


    public function refreshCache(string $type): array
    {
        $cacheKey = "tarifs:{$type}";
        $ttl      = config('payment.tarif_cache_ttl', 86400);

        Cache::store('file')->forget($cacheKey);
        $response = $this->client->getTarifs($type);
        $tarifs   = $response['success'] ? ($response['data'] ?? []) : [];

        Cache::store('file')->put($cacheKey, $tarifs, $ttl);
        return $tarifs;
    }
}
