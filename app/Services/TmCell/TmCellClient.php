<?php

namespace App\Services\TmCell;

use App\Enum\ErrorMessage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class TmCellClient
{
    private string $baseUrl;
    private string $psId;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.tmcell.base_url'), '/');
        $this->psId    = config('services.tmcell.ps_id');
    }

    /**
     * Check Balance
     * GET Balance?ps_id=<ps_id>&phone=<phone>&currency=TMT
     */
    public function getBalance(string $phone): array
    {
        $url = sprintf(
            '%s/xmlinterface.asmx/Balance?ps_id=%s&phone=%s&currency=TMT',
            $this->baseUrl,
            $this->psId,
            $phone
        );

        Log::channel('tmcell')->info('TmCell getBalance request', [
            'phone' => $phone,
        ]);

        try {
            $response = Http::timeout(10)->get($url);

            Log::channel('tmcell')->info('TmCell getBalance response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $this->parseXml($response->body());

        } catch (ConnectionException $e) {
            Log::channel('tmcell')->error('TmCell getBalance connection error', [
                'message' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'result'  => 500,
                'comment' => ErrorMessage::NO_INTERNET_CONNECTION,
            ];
        } catch (\Throwable $e) {
            Log::channel('tmcell')->error('TmCell getBalance exception', [
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
            '%s/xmlinterface.asmx/PaymentPreCheck?ps_id=%s&phone=%s&pt=2',
            $this->baseUrl,
            $this->psId,
            $phone
        );

        Log::channel('tmcell')->info('TmCell paymentPreCheck request', [
            'phone' => $phone,
        ]);

        try {
            $response = Http::timeout(10)->get($url);

            Log::channel('tmcell')->info('TmCell paymentPreCheck response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $this->parseXml($response->body());

        } catch (ConnectionException $e) {
            Log::channel('tmcell')->error('TmCell paymentPreCheck connection error', [
                'message' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'result'  => 500,
                'comment' => ErrorMessage::NO_INTERNET_CONNECTION,
            ];
        } catch (\Throwable $e) {
            Log::channel('tmcell')->error('TmCell paymentPreCheck exception', [
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
            '%s/xmlinterface.asmx/Payment?ps_id=%s&rrn=%s&pt=2&date=%s&time=%s&phone=%s&amount=%s&currency=TMT',
            $this->baseUrl,
            $this->psId,
            $rrn,
            $date,
            $time,
            $phone,
            number_format($amount, 2, '.', '')
        );
        Log::channel('tmcell')->info('TmCell makePayment request', [
            'rrn'    => $rrn,
            'phone'  => $phone,
            'amount' => $amount,
            'date'   => $date,
            'time'   => $time,
        ]);
        try {
            $response = Http::timeout(10)->get($url);

            Log::channel('tmcell')->info('TmCell makePayment response', [
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
            Log::channel('tmcell')->error('TmCell makePayment connection error', [
                'message' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'result'  => 'action_failed',
                'comment' => ErrorMessage::NO_INTERNET_CONNECTION,
            ];
        } catch (\Throwable $e) {
            Log::channel('tmcell')->error('TmCell makePayment exception', [
                'message' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'result'  => 'action_failed',
                'msg'     => $e->getMessage(),
            ];
        }
    }

    /**
     * Parse XML response to array
     */
    private function parseXml(string $body): array
    {
        try {
            $xml = new SimpleXMLElement($body);
            return json_decode(json_encode($xml), true) ?? [];
        } catch (\Throwable $e) {
            Log::channel('tmcell')->error('TmCell XML parse error', [
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
