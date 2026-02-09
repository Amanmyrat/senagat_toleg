<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Charity\CharityService;
use Illuminate\Http\Request;

class PaymentReturnController extends Controller
{
    public function handle(Request $request)
    {
        $orderId = $request->get('orderId');

        if (! $orderId) {
            return response()->json([
                'success' => false,
                'message' => 'OrderId missing',
            ], 400);
        }

        return app(CharityService::class)->checkPaymentStatus($orderId);
    }
}
