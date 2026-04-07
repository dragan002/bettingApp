<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Round;
use App\Models\Season;
use App\Services\RoundResolveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminRoundController extends Controller
{
    public function __construct(private RoundResolveService $resolver) {}

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
            'locks_at' => 'required|date|after:now',
        ]);

        $round = Round::create([
            'season_id' => $season->id,
            'number' => $request->integer('number'),
            'status' => 'pending',
            'locks_at' => $request->locks_at,
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

        return response()->json(['message' => 'Round resolved', 'stats' => $stats]);
    }
}
