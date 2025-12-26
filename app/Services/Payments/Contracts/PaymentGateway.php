<?php
namespace App\Services\Payments\Contracts;

interface PaymentGateway
{
    public function createPayment(array $payload): array;
    public function checkPaymentStatus(string $orderId): array;
}
