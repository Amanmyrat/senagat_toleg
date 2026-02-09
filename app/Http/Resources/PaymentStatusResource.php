<?php

//
// namespace App\Http\Resources;
//
// use Illuminate\Http\Resources\Json\JsonResource;
//
// class PaymentStatusResource extends JsonResource
// {
//    protected string $paymentType;
//
//    public function __construct($resource, string $paymentType)
//    {
//        parent::__construct($resource);
//        $this->paymentType = $paymentType;
//    }
//
//    public function toArray($request): array
//    {
//        if (isset($this['success']) && !$this['success']) {
//            return [
//                'success' => false,
//                'error' => $this['error'] ?? ['code' => 500, 'message' => 'Unknown error'],
//                'data' => $this['data'] ?? null,
//            ];
//        }
//
//        return $this->resource;
//    }
// }
