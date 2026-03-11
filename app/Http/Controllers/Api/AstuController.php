<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AstuBalanceRequest;
use App\Http\Requests\AstuTopupRequest;
use App\Models\Payment;
use App\Services\Astu\AstuService;
use App\Services\Astu\AstuTopupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AstuController extends Controller
{
    public function __construct(
        protected AstuService $astuService

    ) {}

    /**
     * Astu Top Up
     *
     * @unauthenticated
     */
    public function store(AstuTopupRequest $request)
    {
        return new JsonResponse(
            app(AstuTopupService::class)->create($request->validated())
        );
    }
    /**
     * Astu balance
     */
    public function balance(AstuBalanceRequest $request): JsonResponse
    {
        return new JsonResponse(
            $this->astuService->getBalance(
                $request->validated('account'),
                $request->validated('type'),
            )
        );
    }


}
