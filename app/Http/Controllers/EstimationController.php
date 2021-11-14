<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEstimationRequest;
use App\Services\EstimationService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class EstimationController extends Controller
{

    private EstimationService $estimationService;

    public function __construct(EstimationService $estimationService)
    {
        $this->estimationService = $estimationService;
    }

    public function getEstimationsToGame(string $hashId): JsonResponse
    {
        return response()->json(
            $this->estimationService->findEstimationsByGameHashId($hashId),
            ResponseAlias::HTTP_OK
        );
    }

    public function getEstimationById(int $id): JsonResponse
    {
        return response()->json(
            $this->estimationService->findEstimationById($id),
            ResponseAlias::HTTP_OK
        );
    }

    public function startEstimation(StoreEstimationRequest $request): JsonResponse
    {
        return response()->json(
            $this->estimationService->createEstimation($request),
            ResponseAlias::HTTP_CREATED
        );
    }

    public function closeEstimation(string $hashId, int $id): JsonResponse
    {
        return response()->json(
            $this->estimationService->finishEstimation($hashId, $id),
            ResponseAlias::HTTP_OK
        );
    }
}
