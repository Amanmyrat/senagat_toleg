<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CharityRequest;
use App\Services\Charity\CharityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CharityController extends Controller
{
    /**
     * Charity
     *
     * @unauthenticated
     */

    public function store(
        CharityRequest $request,
        CharityService $service
    ): JsonResponse {
        return new JsonResponse(
            $service->create(
                array_merge(
                    $request->validated(),
                    ['user_id' => $request->user()?->id]
                )
            )
        );
    }
}
