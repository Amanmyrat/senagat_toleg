<?php

namespace App\Services\SenagatBank;
use App\Jobs\SenagatStatusJob;
use App\Models\Merchant;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * @method error(int $int, mixed $message)
 */
class SenagatPaymentService
{
    public function __construct(
        private SenagatClient $client
    ) {}

    public function createPayment(int $locationId, float $amount,string $type): array
    {
        $merchant = Merchant::where('location_id', $locationId)
            ->firstOrFail();

        $orderNumber = 'ORD-' . now()->format('YmdHis') . '-' . Str::random(6);
        $payment = Payment::create([
            'type' => $type,
            'bank_id' => $merchant->location_id,
            'amount' => $amount,
            'pay_id' => $orderNumber,
            'status' => 'pending',
        ]);
        $payload = [
            'userName' => $merchant->username,
            'password' => $merchant->password,
            'orderNumber' => $orderNumber,
            'amount' => $amount,
            'currency' => config('payments.senagat.currency'),
            'language' => 'tk',
            'returnUrl' => config('payments.senagat.base_url') . '/return',
        ];

        $response = $this->client->pay($payload);
        $errorCode = $response['body']['errorCode'] ?? null;
        if ($errorCode != 0) {
            $payment->update(['status' => 'failed']);
            return $this->error(500, $response['error']['message']);
        }
        $payment->update([
            'order_id' => $response['body']['orderId'] ?? null,
            'status' => 'pending',
        ]);
        SenagatStatusJob::dispatch($payment)
            ->delay(now()->addSeconds(30));
        return [
            'success' => true,
            'data'    => $response,
        ];
    }

    public function checkStatus(string $locationId, string $orderId): array
    {
        $merchant = Merchant::where('location_id', $locationId)
            ->firstOrFail();


        $payload = [
            'userName' => $merchant->username,
            'password' => $merchant->password,
            'orderId' => $orderId,
        ];

        $response = $this->client->checkStatus($payload);

        return [
            'data' => $response,
        ];
    }

}
