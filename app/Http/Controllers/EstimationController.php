<?php

namespace App\Http\Controllers;

use App\Http\Requests\Estimation\CloseEstimationRequest;
use App\Http\Requests\Estimation\FinishEstimationRequest;
use App\Http\Requests\Estimation\StoreEstimationRequest;
use App\Models\Estimation;
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

    public function getEstimationsByGameHashId(string $hashId): JsonResponse
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

    public function finishEstimation(FinishEstimationRequest $request): JsonResponse
    {
        return response()->json(
            $this->estimationService->finishEstimation($request),
            ResponseAlias::HTTP_OK
        );
    }

    public function restartEstimation(Estimation $estimation): JsonResponse
    {
        return response()->json(
            $this->estimationService->resetEstimation($estimation),
            ResponseAlias::HTTP_OK
        );
    }

    public function closeEstimation(CloseEstimationRequest $request): JsonResponse
    {
        return response()->json(
            $this->estimationService->closeEstimation($request),
            ResponseAlias::HTTP_OK
        );
    }
}
