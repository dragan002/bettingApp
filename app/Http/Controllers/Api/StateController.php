<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prediction;
use App\Models\Round;
use App\Models\Season;
use App\Models\SeasonPoints;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $player = $request->attributes->get('player');

        $season = Season::active()->first();
        $round = null;
        $predictions = (object) [];
        $leaderboard = [];
        $history = [];

        if ($season) {
            $round = Round::where('season_id', $season->id)
                ->whereIn('status', ['active', 'locked'])
                ->with('fixtures')
                ->first();

            if ($round) {
                $fixtureIds = $round->fixtures->pluck('id');
                $predictions = (object) Prediction::where('player_id', $player->id)
                    ->whereIn('fixture_id', $fixtureIds)
                    ->get()
                    ->pluck('pick', 'fixture_id')
                    ->toArray();
            }

            $leaderboard = SeasonPoints::where('season_id', $season->id)
                ->with('player')
                ->orderByDesc('points')
                ->orderBy('rounds_played')
                ->get()
                ->map->toApiArray()
                ->values()
                ->all();

            $history = Round::where('season_id', $season->id)
                ->where('status', 'resolved')
                ->orderByDesc('number')
                ->get()
                ->map->toApiArray()
                ->values()
                ->all();
        }

        return response()->json([
            'player' => $player->toApiArray(),
            'season' => $season?->toApiArray(),
            'round' => $round?->toApiArray(),
            'predictions' => $predictions,
            'leaderboard' => $leaderboard,
            'history' => $history,
        ]);
    }
}
