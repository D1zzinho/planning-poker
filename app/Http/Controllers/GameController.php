<?php

namespace App\Http\Controllers;

use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class GameController extends Controller
{

    private GameService $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }


    public function index()
    {
        return view('game');
    }

    public function viewSession(string $hashId)
    {
        $game = $this->gameService->findGameByHashId($hashId);
        return view('game', ["game" => $game]);
    }

    /**
     * Create new game.
     *
     * @return array
     */
    public function startNewGame(): array
    {
        return $this->gameService->createGame();
    }

    /**
     * Get all games.
     *
     * @return JsonResponse
     */
    public function getGames(): JsonResponse
    {
        return response()->json($this->gameService->findGames(), ResponseAlias::HTTP_OK);
    }


    /**
     * Get all games created by currently authenticated user.
     *
     * @return JsonResponse
     */
    public function getUserGamesByCurrentSession(): JsonResponse
    {
        return response()->json($this->gameService->findUserGamesByCurrentSession(), ResponseAlias::HTTP_OK);
    }
}
