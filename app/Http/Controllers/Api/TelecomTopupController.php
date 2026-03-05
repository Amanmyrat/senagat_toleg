<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TelecomTopupRequest;
use App\Services\Telecom\TelecomTopupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelecomTopupController extends Controller
{
    /**
     * Telecom Top Up
     *
     * @unauthenticated
     */
    public function store(TelecomTopupRequest $request)
    {
        return new JsonResponse(
            app(TelecomTopupService::class)->create($request->validated())
        );
    }
}
