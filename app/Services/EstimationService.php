<?php

namespace App\Services;

use App\Events\UpdateEstimationEvent;
use App\Events\StartEstimationEvent;
use App\Http\Requests\Estimation\CloseEstimationRequest;
use App\Http\Requests\Estimation\FinishEstimationRequest;
use App\Http\Requests\Estimation\StoreEstimationRequest;
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
            ->get()
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
        return Estimation::whereId($id)->with('votes')->firstOrFail();
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
     * @param FinishEstimationRequest $request
     * @return Estimation
     */
    public function finishEstimation(FinishEstimationRequest $request): Estimation
    {
        $estimation = $request->estimation;

        $validated = $request->safe()->only(['original_result']);
        $validated['status'] = Estimation::getEstimationStatus(1);
        $estimation->update($validated);

        $updatedEstimation = $estimation->refresh();
        $updatedEstimation->load('votes');

        broadcast(new UpdateEstimationEvent($updatedEstimation));

        return $updatedEstimation;
    }

    /**
     * Reset estimation to initial state (no points and status open).
     *
     * @param Estimation $estimation
     * @return Estimation
     */
    public function resetEstimation(Estimation $estimation): Estimation
    {
        $status = Estimation::getEstimationStatus(0);
        $estimation->votes()->delete();
        $estimation->update([
            'original_result' => null,
            'status' => $status
        ]);

        $refreshedEstimation = $estimation->refresh();
        $refreshedEstimation->load('votes');

        broadcast(new UpdateEstimationEvent($refreshedEstimation));

        return $refreshedEstimation;
    }

    /**
     * Update estimation status to closed.
     *
     * @param CloseEstimationRequest $request
     * @return Estimation
     */
    public function closeEstimation(CloseEstimationRequest $request): Estimation
    {
        $estimation = $request->estimation;
        $validated = $request->safe()->only(['points']);
        $validated['status'] = Estimation::getEstimationStatus(2);

        $estimation->update($validated);
        $updatedEstimation = $estimation->refresh();
        $updatedEstimation->load('votes');

        broadcast(new UpdateEstimationEvent($updatedEstimation));

        return $updatedEstimation;
    }
}
