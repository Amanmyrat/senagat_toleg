<?php

namespace App\Services\Charity;


use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class CharityService
{
    public function create(array $payload): array
    {
        try {
            $payment = Payment::create([
                'user_id' => $payload['user_id'] ?? null,
                'type' => 'charity',
                'bank_id' => $payload['bank_id'],
                'amount' => $payload['amount'],
                'user_information' => [
                    'name' => $payload['name'],
                    'surname' => $payload['surname'],
                ],
                'payment_target' => [
                    'type' => 'phone',
                    'value' => $payload['phone'],
                ],
                'status' => 'pending',
            ]);

            $url = "https://payments.example.com/charity/{$payment->id}";
            Log::channel('charity')->info('Charity request created', [
                'payment_id' => $payment->id,
                'user_information'=>$payment->user_information,
                'payment_target'=>$payment->payment_target,
                'bank_id' => $payload['bank_id'],
                'amount' => $payload['amount'],
            ]);
            return [
                'success' => true,
                'error' => null,
                'data' => [
                    'payment_url' => $url,

                ],
            ];
        }catch (\Throwable $e) {
            Log::channel('charity')->error('Charity failed', [
                'bank_id' => $payload['bank_id'] ?? null,
                'error' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => 'Charity creation failed',
                ],
                'data' => null,
            ];
        }
        }
}
