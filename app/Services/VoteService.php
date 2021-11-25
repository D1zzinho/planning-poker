<?php

namespace App\Services;

use App\Events\VoteEvent;
use App\Http\Requests\Vote\UpdateVoteRequest;
use App\Models\Vote;
use Illuminate\Support\Facades\Auth;

class VoteService
{

    /**
     * Find all votes.
     *
     * @return Vote[]
     */
    public function findVotes(): array
    {
        return Vote::all()->toArray();
    }

    /**
     * Find all votes made by user by its id.
     *
     * @param int $userId
     * @return Vote[]
     */
    public function findVotesByUserId(int $userId): array
    {
        return Vote::whereUserId($userId)->get()->toArray();
    }

    /**
     * Find all votes made in estimation by its id.
     *
     * @param int $estimationId
     * @return Vote[]
     */
    public function findVotesToEstimationByItsId(int $estimationId): array
    {
        return Vote::whereEstimationId($estimationId)->get()->toArray();
    }

    /**
     * Save vote made in estimating session.
     *
     * @return Vote
     */
    public function save(): Vote
    {
        $validated = request()->validate([
            'estimation_id' => 'required|numeric',
            'points' => 'required|numeric|min:1|max:13'
        ]);

        $vote = auth()->user()->votes()->create($validated);

        broadcast(new VoteEvent($vote));

        return $vote;
    }

    /**
     * Update vote - allows user to change points before estimation is finished.
     *
     * @param UpdateVoteRequest $request
     * @return Vote
     */
    public function updateVote(UpdateVoteRequest $request): Vote
    {
        $vote = $request->vote;
        $validated = $request->safe()->only(['points']);

        $vote->update($validated);
        $updatedVote = $vote->refresh();

        broadcast(new VoteEvent($updatedVote, true));

        return $updatedVote;
    }
}
