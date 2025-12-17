<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BeletCheckPhoneRequest;
use App\Services\BeletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BeletCheckPhoneController extends Controller
{
    /**
     * Check User
     * @unauthenticated
     *
     */

    public function checkPhone(BeletCheckPhoneRequest $request, BeletService $belet)
    {
        $phone = $request->input('phone');
        $result = $belet->checkPhone($phone);
        return new JsonResponse($result);
    }
}
