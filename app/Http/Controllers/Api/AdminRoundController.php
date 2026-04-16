<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Round;
use App\Models\Season;
use App\Models\SeasonPoints;
use App\Models\SeasonRoundPoints;
use App\Services\RoundResolveService;
use App\Services\RoundSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminRoundController extends Controller
{
    public function __construct(
        private RoundResolveService $resolver,
        private RoundSyncService $sync,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $season = Season::active()->first();

        if (! $season) {
            return response()->json(['rounds' => []]);
        }

        $rounds = Round::where('season_id', $season->id)
            ->orderByDesc('number')
            ->get()
            ->map->toApiArray()
            ->values();

        return response()->json(['rounds' => $rounds]);
    }

    public function store(Request $request): JsonResponse
    {
        $season = Season::active()->firstOrFail();

        $request->validate([
            'number' => 'required|integer|min:1|unique:rounds,number,NULL,id,season_id,' . $season->id,
        ]);

        $round = Round::create([
            'season_id' => $season->id,
            'number' => $request->integer('number'),
            'status' => 'pending',
            'locks_at' => null,
        ]);

        return response()->json(['round' => $round->toApiArray()], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $round = Round::findOrFail($id);

        $request->validate([
            'status' => 'sometimes|string|in:pending,active,locked,resolved',
            'locks_at' => 'sometimes|nullable|date',
        ]);

        $round->update($request->only(['status', 'locks_at']));

        return response()->json(['round' => $round->fresh()->toApiArray()]);
    }

    public function resolve(Request $request, int $id): JsonResponse
    {
        $round = Round::with('fixtures', 'season')->findOrFail($id);

        if ($round->status === 'resolved') {
            return response()->json(['message' => 'Round already resolved'], 422);
        }

        $stats = $this->resolver->resolve($round);

        try {
            $this->sync->createNextRound($round);
        } catch (\Throwable $e) {
            Log::warning('Manual resolve: next round creation failed', [
                'round_id' => $round->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['message' => 'Round resolved', 'stats' => $stats]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $round = Round::findOrFail($id);

        // If the round was resolved, reverse its contribution to season_points
        if ($round->status === 'resolved') {
            $roundPoints = SeasonRoundPoints::where('round_id', $round->id)->get();

            foreach ($roundPoints as $srp) {
                SeasonPoints::where('season_id', $round->season_id)
                    ->where('player_id', $srp->player_id)
                    ->each(function ($sp) use ($srp) {
                        $sp->decrement('points', $srp->points);
                        $sp->decrement('rounds_played', 1);
                    });
            }
        }

        Log::info('Admin deleted round', [
            'round_id' => $round->id,
            'round_number' => $round->number,
            'status' => $round->status,
            'admin_id' => $request->attributes->get('player')?->id,
        ]);

        $round->delete();

        return response()->json(['message' => 'Round deleted']);
    }
}
