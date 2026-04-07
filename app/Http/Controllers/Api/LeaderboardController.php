<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Season;
use App\Models\SeasonPoints;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $season = Season::active()->first();

        if (! $season) {
            return response()->json(['leaderboard' => [], 'season' => null]);
        }

        $leaderboard = SeasonPoints::where('season_id', $season->id)
            ->with('player')
            ->orderByDesc('points')
            ->orderBy('rounds_played')
            ->get()
            ->map->toApiArray()
            ->values();

        return response()->json([
            'season' => $season->toApiArray(),
            'leaderboard' => $leaderboard,
        ]);
    }
}
