<?php

use App\Http\Controllers\Api\AdminChargeController;
use App\Http\Controllers\Api\AdminPlayerController;
use App\Http\Controllers\Api\AdminRoundController;
use App\Http\Controllers\Api\AdminSeasonController;
use App\Http\Controllers\Api\AdminSyncController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\PredictionController;
use App\Http\Controllers\Api\ResultsController;
use App\Http\Controllers\Api\StateController;
use Illuminate\Support\Facades\Route;

// SPA entry point
Route::get('/', fn () => view('welcome'));

// Auth (no token required)
Route::prefix('api/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Authenticated player routes
Route::prefix('api')->middleware('auth.token')->group(function () {
    Route::get('/state', [StateController::class, 'index']);
    Route::post('/predictions', [PredictionController::class, 'store']);
    Route::get('/round/{id}/results', [ResultsController::class, 'show']);
    Route::get('/leaderboard', [LeaderboardController::class, 'index']);
    Route::get('/history', [HistoryController::class, 'index']);
    Route::get('/history/{id}', [HistoryController::class, 'show']);

    // Admin routes
    Route::prefix('admin')->middleware('admin.only')->group(function () {
        // Players
        Route::get('/players', [AdminPlayerController::class, 'index']);
        Route::post('/players', [AdminPlayerController::class, 'store']);
        Route::put('/players/{id}', [AdminPlayerController::class, 'update']);
        Route::delete('/players/{id}', [AdminPlayerController::class, 'destroy']);

        // Rounds
        Route::get('/rounds', [AdminRoundController::class, 'index']);
        Route::post('/rounds', [AdminRoundController::class, 'store']);
        Route::put('/rounds/{id}', [AdminRoundController::class, 'update']);
        Route::post('/rounds/{id}/resolve', [AdminRoundController::class, 'resolve']);

        // Sync
        Route::post('/sync/fixtures', [AdminSyncController::class, 'syncFixtures']);
        Route::post('/sync/results', [AdminSyncController::class, 'syncResults']);

        // Season
        Route::post('/season', [AdminSeasonController::class, 'store']);

        // Charge
        Route::post('/charge-round', [AdminChargeController::class, 'charge']);
    });
});
