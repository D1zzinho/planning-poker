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
    public function findUserGamesByCurrentSession(): array
    {
        return auth()->user()->games()->get()->toArray();
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

        return [
            'message' => 'Game successfully created',
            'game' => $game
        ];
    }

}
