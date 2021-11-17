<?php

namespace App\Services;

use App\Events\UpdateEstimationEvent;
use App\Events\StartEstimationEvent;
use App\Http\Requests\StoreEstimationRequest;
use App\Models\Estimation;
use App\Models\Game;

class EstimationService
{

    /**
     * Find all estimations.
     *
     * @return Estimation[]
     */
    public function findEstimations(): array
    {
        return Estimation::all()->toArray();
    }

    /**
     * Find all estimations played during one session.
     *
     * @param string $hashId
     * @return array
     */
    public function findEstimationsByGameHashId(string $hashId): array
    {
        $game = Game::whereHashId($hashId)->firstOrFail();

        return Estimation::whereGameId($game->id)
            ->with('votes')
            ->orderBy('created_at', 'desc')
            ->paginate(8)
            ->toArray();
    }

    /**
     * Find estimation by id.
     *
     * @param int $id
     * @return Estimation
     */
    public function findEstimationById(int $id): Estimation
    {
        return Estimation::findOrFail($id);
    }

    /**
     * Create new estimation during session.
     *
     * @param StoreEstimationRequest $request
     * @return Estimation
     */
    public function createEstimation(StoreEstimationRequest $request): Estimation
    {
        $validated = $request->safe()->only(['game_id', 'task']);
        $validated['status'] = Estimation::getEstimationStatus(0);

        $estimation = Estimation::create($validated);
        $estimation->load('game');

        broadcast(new StartEstimationEvent($estimation));

        return $estimation;
    }

    /**
     * Update estimation status to finished and save story points.
     *
     * @param int $estimationId
     * @return Estimation
     */
    public function finishEstimation(int $estimationId): Estimation
    {
        $validated = request()->validate([
            'original_result' => 'required'
        ]);
        $validated['status'] = Estimation::getEstimationStatus(1);

        $estimation = Estimation::findOrFail($estimationId);
        $estimation->update($validated);
        $updatedEstimation = $estimation->refresh();
        $updatedEstimation->load('votes');

        broadcast(new UpdateEstimationEvent($updatedEstimation));

        return $updatedEstimation;
    }

    /**
     * Update estimation status to closed.
     *
     * @param int $estimationId
     * @return Estimation
     */
    public function closeEstimation(int $estimationId): Estimation
    {
        $validated = request()->validate([
            'points' => 'required|numeric'
        ]);
        $validated['status'] = Estimation::getEstimationStatus(2);

        $estimation = Estimation::findOrFail($estimationId);
        $estimation->update($validated);
        $updatedEstimation = $estimation->refresh();
        $updatedEstimation->load('votes');

        broadcast(new UpdateEstimationEvent($updatedEstimation));

        return $updatedEstimation;
    }
}
