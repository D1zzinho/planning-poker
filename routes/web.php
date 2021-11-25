<?php

use App\Http\Controllers\EstimationController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\LobbyController;
use App\Http\Controllers\VoteController;
use App\Http\Middleware\EnsureRoomEstimationsAreClosed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Auth::routes();

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group([
    'middleware' => 'auth'
], function () {
    Route::group([
        'prefix' => 'lobby'
    ], function () {
        Route::get('/', [LobbyController::class, 'index'])->name('lobby.index');
        Route::get('/user-games', [GameController::class, 'getUserGamesByCurrentSession'])->name('game.user');
        Route::post('/create-game', [GameController::class, 'startNewGame'])->name('game.create');
    });

    Route::group([
        'prefix' => 'game'
    ], function () {
        Route::get('/', [GameController::class, 'index'])->name('game.index');
        Route::get('/{game}', [GameController::class, 'viewGameSession'])->name('game.session');
        Route::patch('/{game}', [GameController::class, 'closeEstimatingRoom'])
            ->middleware(EnsureRoomEstimationsAreClosed::class)
            ->name('game.close');
        Route::get('{hashId}/estimations', [EstimationController::class, 'getEstimationsByGameHashId']);
    });

    Route::group([
        'prefix' => 'estimation'
    ], function() {
        Route::post('/', [EstimationController::class, 'startEstimation']);
        Route::get('/{id}', [EstimationController::class, 'getEstimationById']);
        Route::post('/{estimation}/reset', [EstimationController::class, 'restartEstimation']);
        Route::patch('/{estimation}/finish', [EstimationController::class, 'finishEstimation']);
        Route::patch('/{estimation}/close', [EstimationController::class, 'closeEstimation']);
    });

    Route::group([
        'prefix' => 'vote'
    ], function () {
        Route::get('/', [VoteController::class, 'getVotes']);
        Route::post('/', [VoteController::class, 'sendVote']);
        Route::patch('/{vote}', [VoteController::class, 'changeVote']);
        Route::get('/all-to-user/{userId}', [VoteController::class, 'getVotesByUserId']);
        Route::get('/all-to-estimation/{estimationId}', [VoteController::class, 'getVotesToEstimationByItsId']);
    });
});
