<?php

namespace App\Services\Belet;

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
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->authToken,
                'Accept' => 'application/json',
            ])->get($this->url.'/api/v2/banks');

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'error' => $response->json('error') ?? [
                    'code' => $response->status(),
                    'message' => $response->body(),
                ],
                'data' => null,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => "No internet connection",
                ],
                'data' => null,
            ];
        }catch (\Exception $e) {
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
