<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;

Route::get('/', [GameController::class, 'index']);
Route::post('/game/start', [GameController::class, 'start']);
Route::post('/game/place-ship', [GameController::class, 'placeShip']);
Route::post('/game/shoot', [GameController::class, 'shoot']);
Route::post('/game/reset', [GameController::class, 'reset']);
