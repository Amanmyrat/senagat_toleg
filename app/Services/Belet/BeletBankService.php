<?php

namespace App\Services\Belet;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BeletBankService
{
    protected string $url;

    protected string $authToken;

    public function __construct()
    {
        $this->url = config('belet.url');
        $this->authToken = config('belet.auth_token');
    }

    /**
     * Confirmed Banks
     */
    public function getBanks(): array
    {
        $cacheKey = 'belet:banks';

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            if (
                is_array($cached) &&
                ($cached['success'] ?? true) === true
            ) {
                return $cached;
            }
            Cache::forget($cacheKey);
        }
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->authToken,
                'Accept' => 'application/json',
            ])->get($this->url.'/api/v2/banks');

            if ($response->successful()) {
                $data = $response->json();
                Cache::put($cacheKey, $data, now()->addDay());

                return $data;
            }

            return [
                'success' => false,
                'error' => $response->json('error') ?? [
                    'code' => $response->status(),
                    'message' => $response->body(),
                ],
                'data' => null,
            ];
        } catch (ConnectionException $e) {
            return [
                'success' => false,
                'error' => ['code' => 500,
                    'message' => 'No internet connection', ],
                'data' => null, ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => $e->getMessage(),
                ],
                'data' => null,
            ];
        }
    }
}
