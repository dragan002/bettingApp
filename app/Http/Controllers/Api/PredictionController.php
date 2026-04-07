<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prediction;
use App\Models\Round;
use App\Models\RoundEntry;
use App\Models\Season;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PredictionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'predictions' => 'required|array|min:1',
            'predictions.*' => 'required|string|in:1,X,2',
        ]);

        $player = $request->attributes->get('player');

        $season = Season::active()->first();

        if (! $season) {
            return response()->json(['message' => 'No active season'], 422);
        }

        $round = Round::where('season_id', $season->id)
            ->where('status', 'active')
            ->first();

        if (! $round) {
            return response()->json(['message' => 'No active round'], 422);
        }

        if ($round->isLocked()) {
            return response()->json(['message' => 'Round is locked'], 422);
        }

        $activeFixtures = $round->activeFixtures()->get();
        $activeFixtureIds = $activeFixtures->pluck('id')->flip();

        try {
            DB::transaction(function () use ($player, $round, $activeFixtures, $activeFixtureIds, $request) {
                // Re-check lock inside transaction
                $round->refresh();
                if ($round->isLocked()) {
                    throw new \RuntimeException('Round is locked');
                }

                foreach ($request->predictions as $fixtureId => $pick) {
                    if (! $activeFixtureIds->has((int) $fixtureId)) {
                        continue;
                    }

                    Prediction::updateOrCreate(
                        ['player_id' => $player->id, 'fixture_id' => (int) $fixtureId],
                        ['pick' => $pick]
                    );
                }

                // Update round entry completeness
                $totalActive = $activeFixtures->count();
                $predictedCount = Prediction::where('player_id', $player->id)
                    ->whereIn('fixture_id', $activeFixtures->pluck('id'))
                    ->count();

                RoundEntry::updateOrCreate(
                    ['round_id' => $round->id, 'player_id' => $player->id],
                    ['is_complete' => $predictedCount === $totalActive]
                );
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Predictions saved']);
    }
}
