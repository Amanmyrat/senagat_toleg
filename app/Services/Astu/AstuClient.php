<?php

namespace App\Services\Astu;

use App\Enum\ErrorMessage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AstuClient
{
    private string $baseUrl;
    private string $ip;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.astu.url'), '/');
        $this->ip      = config('services.astu.ip', '127.0.0.1');
    }


    public function getBalance(string $phoneNumber): array
    {
        $url = sprintf(
            '%s/api/v4/getbalance/%s/%s',
            $this->baseUrl,
            $this->ip,
            $phoneNumber
        );

        Log::channel('astu')->info('Astu getBalance request', [
            'phone' => $phoneNumber,
        ]);

        try {
            $response = Http::timeout(10)->get($url);

            Log::channel('astu')->info('Astu getBalance response', [
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);

            return $response->json() ?? [];
        }catch (ConnectionException $e) {
            return [
                'success' => false,
                'result'  => 500,
                'comment' => ErrorMessage::NO_INTERNET_CONNECTION,
            ];
        } catch (\Throwable $e) {
            Log::channel('astu')->error('Astu getBalance exception', [
                'message' => $e->getMessage(),
            ]);
            return ['result' => 'action_fail', 'msg' => $e->getMessage()];
        }
    }
    public function updateBalance(
        string $agrmNum,
        string $receiptNum,
        string $receiptDate,
        float  $amount
    ): array {
        $url = sprintf(
            '%s/api/v1/updatebalance/%s/%s/%s/%s/%s',
            $this->baseUrl,
            $this->ip,
            $agrmNum,
            $receiptNum,
            $receiptDate,
            number_format($amount, 2, '.', '')
        );

        Log::channel('astu')->info('Astu updateBalance request', [
            'agrm_num'    => $agrmNum,
            'receipt_num' => $receiptNum,
            'receipt_date'=> $receiptDate,
            'amount'      => $amount,
        ]);

        try {
            $response = Http::timeout(10)->get($url);

            Log::channel('astu')->info('Astu updateBalance response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            if ($response->failed()) {
                return [
                    'success' => false,
                    'result'  => 'action_failed',
                    'msg'     => 'HTTP error: ' . $response->status(),
                ];
            }

            $json = $response->json();

            if (! is_array($json)) {
                return [
                    'success' => false,
                    'result'  => 'action_failed',
                    'msg'     => 'Invalid JSON response',
                ];
            }

            return $json;

        } catch (ConnectionException $e) {
            Log::channel('astu')->error('Astu updateBalance connection error', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'result'  => 'action_failed',
                'comment' => ErrorMessage::NO_INTERNET_CONNECTION,
            ];
        } catch (\Throwable $e) {
            Log::channel('astu')->error('Astu updateBalance exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'result'  => 'action_failed',
                'msg'     => $e->getMessage(),
            ];
        }
    }

}
