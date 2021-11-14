<?php

namespace App\Services;

use App\Events\FinishEstimationEvent;
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
     * Update estimation status to closed and save story points.
     *
     * @param string $hashId
     * @param int $estimationId
     * @return Estimation
     */
    public function finishEstimation(string $hashId, int $estimationId): Estimation
    {
        $points = request()->input('points');

        $updatedEstimation = tap(Estimation::whereId($estimationId))
            ->update([
                'points' => $points,
                'status' => Estimation::getEstimationStatus(1)
            ])
            ->first();

        broadcast(new FinishEstimationEvent($updatedEstimation));

        return $updatedEstimation;
    }
}
