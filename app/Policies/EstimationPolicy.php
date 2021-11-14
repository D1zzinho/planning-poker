<?php

namespace App\Policies;

use App\Models\User;
use App\Services\GameService;
use Illuminate\Auth\Access\HandlesAuthorization;

class EstimationPolicy
{
    use HandlesAuthorization;

    private GameService $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    /**
     * Check if user that made request, is owner of current game session.
     *
     * @param User $user
     * @return bool
     */
    public function startEstimation(User $user): bool
    {
        $game = $this->gameService->findGameByHashId(request()->route()->parameter('hashId'))->firstOrFail();
        return $user->id === $game->user_id;
    }
}
