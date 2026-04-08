<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SeasonHallOfFame;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HallOfFameController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $seasons = SeasonHallOfFame::with(['season', 'jackpotWinner', 'leaderboardWinner', 'playerOfSeason'])
            ->orderByDesc('closed_at')
            ->get()
            ->map->toApiArray()
            ->values();

        return response()->json(['seasons' => $seasons]);
    }
}
