<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/account', [AuthController::class, 'destroy']);
    Route::get('/games/active', [GameController::class, 'activeGames']);

    Route::post('/games', [GameController::class, 'store']);
    Route::post('/games/join', [GameController::class, 'join']);
    Route::get('/games/{game}', [GameController::class, 'show']);
    Route::delete('/games/{game}', [GameController::class, 'destroy']);
    Route::post('/games/{game}/moves', [GameController::class, 'move']);
    Route::post('/games/{game}/pass', [GameController::class, 'pass']);

});
