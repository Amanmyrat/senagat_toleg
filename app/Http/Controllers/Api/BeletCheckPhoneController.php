<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BeletCheckPhoneRequest;
use App\Services\Belet\BeletUserService;
use Illuminate\Http\JsonResponse;

class BeletCheckPhoneController extends Controller
{
    /**
     * Check User
     *
     * @unauthenticated
     */
    public function checkPhone(BeletCheckPhoneRequest $request, BeletUserService $belet)
    {
        $phone = $request->input('phone');
        $result = $belet->checkPhone($phone);

        return new JsonResponse($result);
    }
}
