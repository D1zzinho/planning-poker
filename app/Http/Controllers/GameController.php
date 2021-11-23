<?php

namespace App\Http\Controllers;

use App\Services\GameService;
use Illuminate\Http\JsonResponse;
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
        return redirect()->route('lobby.index');
    }

    public function viewGameSession(string $hashId)
    {
        $game = $this->gameService->findGameByHashId($hashId);
        return view('game', ["game" => $game]);
    }

    /**
     * Create new game and return its data.
     *
     * @return JsonResponse
     */
    public function startNewGame(): JsonResponse
    {
        return response()->json(
            $this->gameService->createGame(),
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Get all games.
     *
     * @return JsonResponse
     */
    public function getGames(): JsonResponse
    {
        return response()->json(
            $this->gameService->findGames(),
            ResponseAlias::HTTP_OK
        );
    }

    /**
     * Get all games created by currently authenticated user.
     *
     * @return JsonResponse
     */
    public function getUserGamesByCurrentSession(): JsonResponse
    {
        return response()->json(
            $this->gameService->findUserGamesByActiveSession(),
            ResponseAlias::HTTP_OK
        );
    }

    /**
     * Close game (update status to closed).
     *
     * @param string $hashId
     * @return JsonResponse
     */
    public function closeEstimatingRoom(string $hashId): JsonResponse
    {
        return response()->json(
            $this->gameService->closeGame($hashId),
            ResponseAlias::HTTP_OK
        );
    }
}
