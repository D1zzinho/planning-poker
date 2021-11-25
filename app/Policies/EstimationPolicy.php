<?php

namespace App\Policies;

use App\Models\Estimation;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Auth\Access\HandlesAuthorization;

class EstimationPolicy
{
    use HandlesAuthorization;

    private GameService $gameService;

    /**
     * DI of GameService to access stored information.
     *
     * @param GameService $gameService
     */
    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    /**
     * Check if user is owner of game before starting estimation.
     *
     * @param User $user
     * @return bool
     */
    public function startEstimation(User $user): bool
    {
        $game = $this->gameService->findGameById(request()->input('game_id'));
        return $user->id === $game->user_id;
    }

    /**
     * Check if user is owner of game and estimation before finishing estimation.
     *
     * @param User $user
     * @param Estimation $estimation
     * @return bool
     */
    public function finishEstimation(User $user, Estimation $estimation): bool
    {
        return $user->id === $estimation->game->user_id;
    }

    /**
     * Check if user is owner of game and estimation before closing estimation.
     *
     * @param User $user
     * @param Estimation $estimation
     * @return bool
     */
    public function closeEstimation(User $user, Estimation $estimation): bool
    {
        return $user->id === $estimation->game->user_id;
    }
}
