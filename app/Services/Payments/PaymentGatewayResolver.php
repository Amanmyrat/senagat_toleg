<?php

namespace App\Services\Payments;

use App\Services\Payments\Contracts\PaymentGateway;
use App\Services\Payments\Gateways\SenagatGateway;
use InvalidArgumentException;

class PaymentGatewayResolver
{
    public function resolve(string $bankName): PaymentGateway
    {
        return match ($bankName) {

            'senagat' => app(SenagatGateway::class, ['bankKey' => 'senagat']),
            'altyn_asyr' => app(SenagatGateway::class, ['bankKey' => 'altyn_asyr']),
            'rysgal' => app(SenagatGateway::class, ['bankKey' => 'rysgal']),
            default => throw new InvalidArgumentException(
                "Unsupported payment bank: {$bankName}"
            ),
        };
    }
}
