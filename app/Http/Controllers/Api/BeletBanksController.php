<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Belet\BeletBankService;
use Illuminate\Http\JsonResponse;

class BeletBanksController extends Controller
{
    protected BeletBankService $banks;

    public function __construct(BeletBankService $banks)
    {
        $this->banks = $banks;
    }

    /**
     * Banks list
     *
     * @unauthenticated
     */
    public function banks()
    {
        $result = $this->banks->getBanks();

        return new JsonResponse($result);
    }
}
