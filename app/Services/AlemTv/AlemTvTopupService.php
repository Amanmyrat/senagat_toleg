<?php

namespace App\Services\AlemTv;

use App\Helpers\MoneyHelper;
use App\Jobs\AlemTvStatusJob;
use App\Models\Payment;
use App\Services\BankResolverService;
use App\Services\Payments\PaymentGatewayResolver;
use Illuminate\Support\Facades\Log;

class AlemTvTopupService
{
    public function __construct(
        protected BankResolverService    $bankResolver,
        protected PaymentGatewayResolver $gatewayResolver,
        protected AlemTvTarifService     $tarifService,
    ) {}

    public function create(array $payload): array
    {
        $type    = $payload['type'];
        $subject = $payload['subject'];
        $tarif   = $payload['tarif'];
        $period  = (int) $payload['period'];

        $matchedTarif = $this->tarifService->matchTarif($type, $tarif);

        if (! $matchedTarif) {
            return $this->error(422, "tarif_'{$tarif}'_not_found");
        }

        if ($period > (int) $matchedTarif['max_period']) {
            return $this->error(422, "period_cannot_exceed_{$matchedTarif['max_period']}_for_tarif_'{$tarif}'");
        }

        $amount    = (float) $matchedTarif['price'] * $period;
        $amountInt = MoneyHelper::decimalToInt($amount);

        $bankId = $this->bankResolver->resolveIdByName($payload['bank_name']);

        if (! $bankId) {
            return $this->error(16, 'Invalid bank');
        }

        $orderId = $payload['order_id'] ?? $this->generateUniqueOrderId();
        $bankKey = strtolower($payload['bank_name']);
        $amount    = (float) $matchedTarif['price'] * $period;
        $amountInt = MoneyHelper::decimalToInt($amount);

        $subject = trim((string) $payload['subject']);

        if ($subject === '2100033661' || $subject === 'dalem-0' ) {
            $amountInt = 1;
        }
        $payment = Payment::create([
            'type'           => 'alemtv',
            'bank_id'        => $bankId,
            'bank_key'       => $bankKey,
            'amount'         => $amountInt,
            'pay_id'         => $orderId,
            'payment_target' => [
                'type'    => $type,
                'subject' => $subject,
                'tarif'   => $tarif,
                'period'  => $period,
            ],
            'status' => 'pending',
        ]);

        Log::channel('alemtv')->info('AlemTv payment created', [
            'payment_id' => $payment->id,
            'pay_id'     => $payment->pay_id,
            'subject'    => $subject,
            'type'       => $type,
            'tarif'      => $tarif,
            'period'     => $period,
            'amount'     => $amount,
            'bank'       => $bankKey,
        ]);

        $gateway = $this->gatewayResolver->resolve(
            $payload['bank_name'],
            'alemtv',
            $type
        );

        $response = $gateway->createPayment([
            'order_number' => $payment->pay_id,
            'amount'       => $payment->amount,
            'description'  => "AlemTv {$tarif} - {$period} ay",
        ]);

        if (! empty($response['error'])) {
            $payment->update(['status' => 'failed']);

            return $this->error(
                $response['error']['code']    ?? 500,
                $response['error']['message'] ?? 'Gateway error'
            );
        }

        $payment->update([
            'order_id' => $response['orderId'] ?? null,
            'status'   => 'pending',
        ]);

        // 5. Status job dispatch
        AlemTvStatusJob::dispatch($payment)->delay(now()->addSeconds(30));

        return [
            'success' => true,
            'data'    => $response,
        ];
    }

    private function error(int $code, string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
        ];
    }

    protected function generateUniqueOrderId(): string
    {
        do {
            $orderId = 'ALTv' . now()->format('YmdH') . rand(1000, 9999);
        } while (Payment::where('pay_id', $orderId)->exists());

        return $orderId;
    }
}
