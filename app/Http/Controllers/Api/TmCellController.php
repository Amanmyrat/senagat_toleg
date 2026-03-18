<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TmCell\BaseTopUpRequest;
use App\Http\Requests\TmCell\TmCellBalanceRequest;
use App\Services\TmCell\TmCellService;
use App\Services\TmCell\TmCellTopupService;
use Illuminate\Http\JsonResponse;

class TmCellController extends Controller
{
    public function __construct(
        protected TmCellService $tmCellService
    ) {}
    /**
     * Tm Cell Pay
     *
     * @unauthenticated
     */
    public function store(BaseTopupRequest $request)
    {
        return new JsonResponse(
            app(TmCellTopupService::class)->create($request->validated())
        );
    }
    /**
     * TmCell Balance
     */
    public function balance(TmCellBalanceRequest $request): JsonResponse
    {
        return new JsonResponse(
            $this->tmCellService->getBalance(
                $request->validated('phone')
            )
        );
    }
}
