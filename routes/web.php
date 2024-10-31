<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeaderboardController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', [GameController::class, 'index']);
Route::post('/game/start', [GameController::class, 'start']);
Route::post('/game/place-ship', [GameController::class, 'placeShip']);
Route::post('/game/shoot', [GameController::class, 'shoot']);
Route::post('/game/reset', [GameController::class, 'reset']);
Route::get('/game/history', [GameController::class, 'history']);

Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');
