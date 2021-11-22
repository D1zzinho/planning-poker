<?php

namespace App\Policies;

use App\Models\Estimation;
use App\Models\User;
use App\Services\EstimationService;
use App\Services\GameService;
use Illuminate\Auth\Access\HandlesAuthorization;

class EstimationPolicy
{
    use HandlesAuthorization;

    private GameService $gameService;
    private EstimationService $estimationService;

    public function __construct(GameService $gameService, EstimationService $estimationService)
    {
        $this->gameService = $gameService;
        $this->estimationService = $estimationService;
    }

    /**
     * Check if user is owner of game before starting estimation.
     *
     * @param User $user
     * @return bool
     */
    public function startEstimation(User $user): bool
    {
        $game = $this->gameService->findGameByHashId(request()->route()->parameter('hashId'))->firstOrFail();
        return $user->id === $game->user_id;
    }


    /**
     * Check if user is owner of game and estimation before closing estimation.
     *
     * @param User $user
     * @return bool
     */
    public function finishEstimation(User $user): bool
    {
        $game = $this->gameService->findGameByHashId(request()->route()->parameter('hashId'))->firstOrFail();
        $estimation = $this->estimationService->findEstimationById(request()->route()->parameter('id'));

        return $user->id === $game->user_id && $user->id === $estimation->game->user_id;
    }
}
