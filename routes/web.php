<?php

use App\Events\VoteEvent;
use App\Http\Controllers\GameController;
use App\Http\Controllers\LobbyController;
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
    'prefix' => 'lobby',
    'middleware' => 'auth'
], function() {
    Route::get('/', [LobbyController::class, 'index'])->name('lobby.index');
    Route::get('/user-games', [GameController::class, 'getUserGamesByCurrentSession'])->name('game.user');
    Route::post('/create-game', [GameController::class, 'startNewGame'])->name('game.create');
});

Route::group([
    'prefix' => 'game',
    'middleware' => 'auth'
], function() {
    Route::get('/', [GameController::class, 'index'])->name('game.index');
    Route::get('/{hashId}', [GameController::class, 'viewSession'])->name('game.session');
});
