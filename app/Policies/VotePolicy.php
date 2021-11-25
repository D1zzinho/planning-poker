<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vote;
use Illuminate\Auth\Access\HandlesAuthorization;

class VotePolicy
{
    use HandlesAuthorization;

    public function changeVote(User $user, Vote $vote): bool
    {
        return $user->id === $vote->user_id;
    }
}
