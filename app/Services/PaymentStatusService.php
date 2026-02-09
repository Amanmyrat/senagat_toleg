<?php

// namespace App\Services;
//
// use App\Models\Payment;
// use App\Services\Payments\Gateways\BeletGateway;
// use App\Services\Payments\Gateways\CharityGateway;
// use App\Http\Resources\PaymentStatusResource;
//
// class PaymentStatusService
// {
//    protected BeletGateway $beletGateway;
//    protected CharityGateway $charityGateway;
//
//    public function __construct(BeletGateway $beletGateway, CharityGateway $charityGateway)
//    {
//        $this->beletGateway = $beletGateway;
//        $this->charityGateway = $charityGateway;
//    }
//
//    public function checkByOrderId(string $orderId)
//    {
//        $payment = Payment::where('order_id', $orderId)->first();
//
//        if (!$payment) {
//            return [
//                'success' => false,
//                'error' => ['code' => 404, 'message' => 'Payment not found'],
//                'data' => null,
//            ];
//        }
//
//        $response = match ($payment->type) {
//            'belet'   => $this->beletGateway->checkStatus($orderId),
//            'charity' => $this->charityGateway->checkStatus($orderId),
//            default   => [
//                'success' => false,
//                'error' => ['code' => 422, 'message' => 'Unsupported payment type'],
//                'data' => null,
//            ],
//        };
//
//
//        return new PaymentStatusResource($response, $payment->type);
//    }
// }
