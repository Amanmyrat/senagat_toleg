<?php

namespace App\Services\Cdma;

use App\Enum\ErrorMessage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class CdmaClient
{
    private string $baseUrl;
    private string $psId;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.cdma.base_url'), '/');
        $this->psId    = config('services.cdma.ps_id');
    }

    private function client()
    {
        return Http::withBasicAuth(
            config('services.cdma.pfx_username'),
            config('services.cdma.pfx_password')
        )->withOptions([
            'curl' => [
                CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=0',
                CURLOPT_SSL_VERIFYPEER  => false,
                CURLOPT_SSL_VERIFYHOST  => false,
            ],
        ])->timeout(10);
    }


    /**
     * Check Balance
     * GET Balance?ps_id=<ps_id>&phone=<phone>&currency=TMT
     */
    public function getBalance(string $phone): array
    {
        $url = sprintf(
            '%s/Balance?ps_id=%s&phone=%s&currency=TMT',
            $this->baseUrl,
            $this->psId,
            $phone
        );

        Log::channel('cdma')->info('Cdma getBalance request', [
            'phone' => $phone,
        ]);

        try {
            $response = $this->client()->get($url);

            Log::channel('cdma')->info('Cdma getBalance response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $this->parseXml($response->body());

        } catch (ConnectionException $e) {
            Log::channel('cdma')->error('Cdma getBalance connection error', [
                'message' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'result'  => 500,
                'comment' => ErrorMessage::NO_INTERNET_CONNECTION,
            ];
        } catch (\Throwable $e) {
            Log::channel('cdma')->error('Cdma getBalance exception', [
                'message' => $e->getMessage(),
            ]);
            return ['result' => 'action_fail', 'msg' => $e->getMessage()];
        }
    }

    /**
     * Pre-Payment Check
     * GET PaymentPreCheck?ps_id=<ps_id>&phone=<phone>&pt=2
     */
    public function paymentPreCheck(string $phone): array
    {
        $url = sprintf(
            '%s/PaymentPreCheck?ps_id=%s&phone=%s&pt=2',
            $this->baseUrl,
            $this->psId,
            $phone
        );

        Log::channel('cdma')->info('Cdma paymentPreCheck request', [
            'phone' => $phone,
        ]);

        try {
            $response = $this->client()->get($url);
            Log::channel('cdma')->info('Cdma paymentPreCheck response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $this->parseXml($response->body());

        } catch (ConnectionException $e) {
            Log::channel('cdma')->error('Cdma paymentPreCheck connection error', [
                'message' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'result'  => 500,
                'comment' => ErrorMessage::NO_INTERNET_CONNECTION,
            ];
        } catch (\Throwable $e) {
            Log::channel('cdma')->error('Cdma paymentPreCheck exception', [
                'message' => $e->getMessage(),
            ]);
            return ['result' => 'action_fail', 'msg' => $e->getMessage()];
        }
    }

    /**
     * Make Payment
     * GET Payment?ps_id=<ps_id>&rrn=<rrn>&pt=2&date=<date>&time=<time>&phone=<phone>&amount=<amount>&currency=TMT
     */
    public function makePayment(
        string $rrn,
        string $phone,
        float  $amount,
        string $date,
        string $time
    ): array {
        $url = sprintf(
            '%s/Payment?ps_id=%s&rrn=%s&pt=2&date=%s&time=%s&phone=%s&amount=%s&currency=TMT',
            $this->baseUrl,
            $this->psId,
            $rrn,
            $date,
            $time,
            $phone,
            number_format($amount, 2, '.', '')
        );

        Log::channel('cdma')->info('Cdma makePayment request', [
            'rrn'    => $rrn,
            'phone'  => $phone,
            'amount' => $amount,
            'date'   => $date,
            'time'   => $time,
        ]);

        try {
            $response = $this->client()->get($url);

            Log::channel('cdma')->info('Cdma makePayment response', [
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

            return $this->parseXml($response->body());

        } catch (ConnectionException $e) {
            Log::channel('cdma')->error('Cdma makePayment connection error', [
                'message' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'result'  => 'action_failed',
                'comment' => ErrorMessage::NO_INTERNET_CONNECTION,
            ];
        } catch (\Throwable $e) {
            Log::channel('cdma')->error('Cdma makePayment exception', [
                'message' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'result'  => 'action_failed',
                'msg'     => $e->getMessage(),
            ];
        }
    }

    private function parseXml(string $body): array
    {
        try {
            $xml = new SimpleXMLElement($body);
            return json_decode(json_encode($xml), true) ?? [];
        } catch (\Throwable $e) {
            Log::channel('cdma')->error('Cdma XML parse error', [
                'message' => $e->getMessage(),
                'body'    => $body,
            ]);
            return [
                'success' => false,
                'result'  => 'action_failed',
                'msg'     => 'XML parse error: ' . $e->getMessage(),
            ];
        }
    }
}
