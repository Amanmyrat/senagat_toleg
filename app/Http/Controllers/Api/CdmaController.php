<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TmCell\BaseTopUpRequest;
use App\Http\Requests\TmCell\TmCellBalanceRequest;
use App\Services\Cdma\CdmaService;
use App\Services\Cdma\CdmaTopupService;
use Illuminate\Http\JsonResponse;

class CdmaController extends Controller
{
    public function __construct(
        protected CdmaService $cdmaService
    ) {}

    /**
     * CDMA Pay
     *
     * @unauthenticated
     */
    public function store(BaseTopUpRequest $request): JsonResponse
    {
        return new JsonResponse(
            app(CdmaTopupService::class)->create($request->validated())
        );
    }

    /**
     * CDMA Balance
     */
    public function balance(TmCellBalanceRequest $request): JsonResponse
    {
        return new JsonResponse(
            $this->cdmaService->getBalance(
                $request->validated('phone')
            )
        );
    }
}
