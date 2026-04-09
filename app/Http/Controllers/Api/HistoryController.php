<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prediction;
use App\Models\Round;
use App\Models\RoundEntry;
use App\Models\Season;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $season = Season::active()->first();

        if (! $season) {
            return response()->json(['rounds' => []]);
        }

        $rounds = Round::where('season_id', $season->id)
            ->where('status', 'resolved')
            ->orderByDesc('number')
            ->get()
            ->map->toApiArray()
            ->values();

        return response()->json(['rounds' => $rounds]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $round = Round::with('fixtures')->findOrFail($id);

        $fixtureIds = $round->fixtures->pluck('id');

        $predictions = Prediction::whereIn('fixture_id', $fixtureIds)
            ->get()
            ->groupBy('player_id');

        $entries = RoundEntry::where('round_id', $round->id)
            ->with('player')
            ->get()
            ->map(function ($entry) use ($predictions) {
                $playerPredictions = $predictions->get($entry->player_id, collect());

                return [
                    'playerId'    => $entry->player_id,
                    'playerName'  => $entry->player->name,
                    'displayName' => $entry->player->displayName(),
                    'isComplete'  => $entry->is_complete,
                    'isPerfect'   => $entry->is_perfect,
                    'points'      => $entry->points,
                    'predictions' => $playerPredictions->pluck('pick', 'fixture_id'),
                ];
            });

        return response()->json([
            'round' => $round->toApiArray(),
            'entries' => $entries->values(),
        ]);
    }
}
