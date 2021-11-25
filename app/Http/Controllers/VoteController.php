<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vote\UpdateVoteRequest;
use App\Services\VoteService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class VoteController extends Controller
{

    private VoteService $voteService;

    public function __construct(VoteService $voteService)
    {
        $this->voteService = $voteService;
    }

    /**
     * Get all votes.
     *
     * @return JsonResponse
     */
    public function getVotes(): JsonResponse
    {
        return response()->json($this->voteService->findVotes(), ResponseAlias::HTTP_OK);
    }

    /**
     * Get all votes made by user by its id.
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function getVotesByUserId(int $userId): JsonResponse
    {
        return response()->json($this->voteService->findVotesByUserId($userId), ResponseAlias::HTTP_OK);
    }

    /**
     * Get all votes made in estimation by its id.
     *
     * @param int $estimationId
     * @return JsonResponse
     */
    public function getVotesToEstimationByItsId(int $estimationId): JsonResponse
    {
        return response()
            ->json($this->voteService->findVotesToEstimationByItsId($estimationId), ResponseAlias::HTTP_OK);
    }

    /**
     * Save vote and return data.
     *
     * @return JsonResponse
     */
    public function sendVote(): JsonResponse
    {
        return response()->json($this->voteService->save(), ResponseAlias::HTTP_CREATED);
    }

    /**
     * Update vote during one estimation.
     *
     * @param UpdateVoteRequest $request
     * @return JsonResponse
     */
    public function changeVote(UpdateVoteRequest $request): JsonResponse
    {
        return response()->json(
            $this->voteService->updateVote($request),
            ResponseAlias::HTTP_OK
        );
    }
}
