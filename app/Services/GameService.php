<?php

namespace App\Services;

use App\Models\Game;

class GameService
{

    /**
     * Find all games.
     *
     * @return Game[]
     */
    public function findGames(): array
    {
        return Game::with('estimations')->get();
    }


    /**
     * Find all games created by currently authenticated user.
     *
     * @return Game[]
     */
    public function findUserGamesByActiveSession(): array
    {
        return auth()
            ->user()
            ->games()
            ->with('estimations')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Find game by id.
     *
     * @param int $id
     * @return Game
     */
    public function findGameById(int $id): Game
    {
        return Game::findOrFail($id);
    }

    /**
     * Find game by hash_id property value.
     *
     * @param string $hashId
     * @return Game
     */
    public function findGameByHashId(string $hashId): Game
    {
        return Game::whereHashId($hashId)->firstOrFail();
    }

    /**
     * Create new game.
     *
     * @return array
     */
    public function createGame(): array
    {
        $game = auth()->user()->games()->create();
        $game->load('estimations');

        return [
            'message' => 'Game successfully created',
            'game' => $game
        ];
    }

    /**
     * Close estimating room (update status to closed).
     *
     * @param Game $game
     * @return Game
     */
    public function closeGame(Game $game): Game
    {
        $status = Game::getGameStatus(1);

        $game->update([
            'status' => $status
        ]);

        $updatedGame = $game->refresh();
        $updatedGame->load('estimations');

        return $updatedGame;
    }
}
