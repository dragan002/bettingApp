<?php

use App\Http\Controllers\Api\AdminChargeController;
use App\Http\Controllers\Api\AdminPlayerController;
use App\Http\Controllers\Api\AdminRoundController;
use App\Http\Controllers\Api\AdminSeasonController;
use App\Http\Controllers\Api\AdminSettlementController;
use App\Http\Controllers\Api\AdminSyncController;
use App\Http\Controllers\Api\AdminTokenCreditController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HallOfFameController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\PlayerBadgesController;
use App\Http\Controllers\Api\PlayerBalancesController;
use App\Http\Controllers\Api\PlayerLedgerController;
use App\Http\Controllers\Api\PredictionController;
use App\Http\Controllers\Api\ResultsController;
use App\Http\Controllers\Api\RoundPotController;
use App\Http\Controllers\Api\StateController;
use Illuminate\Support\Facades\Route;

// SPA entry point
Route::get('/', fn () => view('welcome'));

// Privacy policy (required for Play Store)
Route::get('/privacy', fn () => view('privacy'));

// Temporary DB diagnostic — remove after confirming PostgreSQL works
Route::get('/db-check', function () {
    $conn = config('database.default');
    $db   = config("database.connections.{$conn}.database");
    $host = config("database.connections.{$conn}.host", 'n/a');
    $players = \App\Models\Player::count();
    return response()->json([
        'connection' => $conn,
        'database'   => $db,
        'host'       => $host,
        'player_count' => $players,
        'database_url_set' => !empty(env('DATABASE_URL')),
    ]);
});

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

    // Player data
    Route::get('/players/balances', [PlayerBalancesController::class, 'index']);
    Route::get('/players/{id}/ledger', [PlayerLedgerController::class, 'show']);
    Route::get('/players/{id}/badges', [PlayerBadgesController::class, 'show']);
    Route::get('/rounds/{id}/pot', [RoundPotController::class, 'show']);
    Route::get('/hall-of-fame', [HallOfFameController::class, 'index']);

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
        Route::delete('/rounds/{id}', [AdminRoundController::class, 'destroy']);

        // Sync
        Route::post('/sync/fixtures', [AdminSyncController::class, 'syncFixtures']);
        Route::post('/sync/results', [AdminSyncController::class, 'syncResults']);
        Route::get('/api-status', [AdminSyncController::class, 'apiStatus']);

        // Season
        Route::post('/season', [AdminSeasonController::class, 'store']);
        Route::post('/season/pending-settlement', [AdminSeasonController::class, 'pendingSettlement']);
        Route::post('/season/close', [AdminSeasonController::class, 'close']);

        // Charge
        Route::post('/charge-round', [AdminChargeController::class, 'charge']);

        // Token credits
        Route::post('/players/{id}/credit', [AdminTokenCreditController::class, 'store']);

        // Nickname
        Route::put('/players/{id}/nickname', [AdminPlayerController::class, 'updateNickname']);

        // Settlements
        Route::get('/season/settlements', [AdminSettlementController::class, 'index']);
        Route::post('/season/settlements/{playerId}', [AdminSettlementController::class, 'settle']);
    });
});
