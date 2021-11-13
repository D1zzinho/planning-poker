<?php

namespace App\Services;

use App\Events\FinishEstimationEvent;
use App\Events\StartEstimationEvent;
use App\Models\Estimation;
use App\Models\Game;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

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
     * @param int $gameId
     * @return array
     */
    public function findEstimationsByGameHashId(string $hashId): array
    {
        $game = Game::whereHashId($hashId)->firstOrFail();
        return Estimation::whereGameId($game->id)->get()->toArray();
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
     * @return Estimation
     */
    public function createEstimation(string $hashId): Estimation
    {
        $currentUserId = Auth::user()->id;
        $gameId = request()->input('game_id');
        $taskId = request()->input('task');

        $estimation = Estimation::create([
            'game_id' => $gameId,
            'task' => $taskId,
            'status' => Estimation::getEstimationStatus(0)
        ]);
        $estimation->load('game');

        broadcast(new StartEstimationEvent($hashId, $currentUserId));

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
