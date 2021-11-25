<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GamePolicy
{
    use HandlesAuthorization;

    /**
     * Check if user is owner of the game before close request.
     *
     * @param User $user
     * @param Game $game
     * @return bool
     */
    public function closeEstimatingRoom(User $user, Game $game): bool
    {
        return $user->id === $game->user_id;
    }
}
