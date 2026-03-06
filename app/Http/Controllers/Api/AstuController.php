<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AstuTopupRequest;
use App\Services\Astu\AstuTopupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class AstuController extends Controller
{

    /**
     * Astu Top Up
     *
     * @unauthenticated
     */
    public function store(AstuTopupRequest $request)
    {
//        $payment = $this->astuService->create(
//            $request->validated()
//        );
        return new JsonResponse(
            app(AstuTopupService::class)->create($request->validated())
        );

    }
}
