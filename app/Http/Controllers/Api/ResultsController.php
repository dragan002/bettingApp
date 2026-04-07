<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prediction;
use App\Models\Round;
use App\Models\RoundEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResultsController extends Controller
{
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
            ->map(function ($entry) use ($predictions, $round) {
                $playerPredictions = $predictions->get($entry->player_id, collect());

                return [
                    'playerId' => $entry->player_id,
                    'playerName' => $entry->player->name,
                    'isComplete' => $entry->is_complete,
                    'isPerfect' => $entry->is_perfect,
                    'points' => $entry->points,
                    'predictions' => $playerPredictions->pluck('pick', 'fixture_id'),
                ];
            });

        return response()->json([
            'round' => $round->toApiArray(),
            'entries' => $entries->values(),
        ]);
    }
}
