<?php

namespace App\Services\Payments;

use App\Services\Payments\Contracts\PaymentGateway;
use App\Services\Payments\Gateways\SenagatGateway;
use InvalidArgumentException;

class PaymentGatewayResolver
{

    public function resolve(string $bankName, string $service,?string $type = null): PaymentGateway
    {
        return match ($bankName) {
            'senagat' => app(SenagatGateway::class, [
                'bankKey' => 'senagat',
                'service' => $service,
                'type' => $type
            ]),
            'altyn_asyr' => app(SenagatGateway::class, [
                'bankKey' => 'altyn_asyr',
                'service' => $service,
                'type' => $type
            ]),
            'rysgal' => app(SenagatGateway::class, [
                'bankKey' => 'rysgal',
                'service' => $service,
                'type' => $type
            ]),
            default => throw new \InvalidArgumentException("Unsupported bank"),
        };
    }
}
//    public function resolve(string $bankName): PaymentGateway
//    {
//        return match ($bankName) {
//
//            'senagat' => app(SenagatGateway::class, ['bankKey' => 'senagat']),
//            'altyn_asyr' => app(SenagatGateway::class, ['bankKey' => 'altyn_asyr']),
//            'rysgal' => app(SenagatGateway::class, ['bankKey' => 'rysgal']),
//            default => throw new InvalidArgumentException(
//                "Unsupported payment bank: {$bankName}"
//            ),
//        };
//    }
