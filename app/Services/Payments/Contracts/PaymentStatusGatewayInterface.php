<?php

namespace App\Services\Payments\Contracts;

interface PaymentStatusGatewayInterface
{
    public function checkStatus(string $orderId): array;
}
