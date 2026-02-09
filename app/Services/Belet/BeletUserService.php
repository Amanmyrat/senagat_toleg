<?php

namespace App\Services\Belet;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class BeletUserService
{
    protected string $url;

    protected string $authToken;

    public function __construct()
    {
        $this->url = config('belet.url');
        $this->authToken = config('belet.auth_token');
    }

    public function checkPhone(string $phoneNum): array
    {
        if (empty($phoneNum)) {
            return [
                'success' => false,
                'error' => [
                    'code' => 1,
                    'message' => 'Phone number cannot be empty',
                ],
                'data' => null,
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->authToken,

                'Accept' => 'application/json',
            ])->get($this->url.'/api/v2/users/check', [
                'phone' => $phoneNum,
            ]);

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

        } catch (ConnectionException $e) {
            return [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => 'No internet connection',
                ],
                'data' => null,
            ];
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
