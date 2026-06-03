<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\AlemTvCreateRequest;
use App\Http\Requests\AlemTvSearchRequest;
use App\Http\Requests\AlemTvTarifRequest;
use App\Http\Requests\AlemTvTopupRequest;
use App\Services\AlemTv\AlemTvCreateService;
use App\Services\AlemTv\AlemTvSearchService;
use App\Services\AlemTv\AlemTvTarifService;
use App\Services\AlemTv\AlemTvTopupService;
use Illuminate\Http\JsonResponse;

class AlemTvController extends Controller
{
    public function __construct(
    private AlemTvTarifService $alemTvTarifService,
    protected AlemTvSearchService $searchService,
    protected AlemTvCreateService $createService,
    protected AlemTvTopupService  $topupService,

    ) {}
    /**
     * Alem Tv tarifs
     *
     * @unauthenticated
     */
    public function index(AlemTvTarifRequest $request)
    {
        $type = $request->input('type');

        $tarifs = $this->alemTvTarifService->getTarifs($type);

        return response()->json([
            'success' => true,
            'data' => $tarifs,
        ]);
    }

    /**
     * Alem Tv Search
     *
     * @unauthenticated
     */

    public function search(AlemTvSearchRequest $request): JsonResponse
    {
        return new JsonResponse(
            $this->searchService->search($request->validated())
        );
    }
    /**
     * Create and complete payment order
     *
     * @unauthenticated
     */
    public function create(AlemTvCreateRequest $request): JsonResponse
    {
        return new JsonResponse(
            $this->createService->create($request->validated())
        );
    }
    /**
     * Alem Tv Top Up
     *
     * @unauthenticated
     */
    public function topup(AlemTvTopupRequest $request): JsonResponse
    {
        return new JsonResponse(
            $this->topupService->create($request->validated())
        );
    }

}
