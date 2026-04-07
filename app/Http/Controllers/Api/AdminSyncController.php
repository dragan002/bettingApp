<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Round;
use App\Services\RoundSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSyncController extends Controller
{
    public function __construct(private RoundSyncService $sync) {}

    public function syncFixtures(Request $request): JsonResponse
    {
        $request->validate(['round_id' => 'required|integer|exists:rounds,id']);

        $round = Round::with('season')->findOrFail($request->integer('round_id'));

        $count = $this->sync->syncFixtures($round);

        return response()->json(['message' => "Synced {$count} fixtures", 'count' => $count]);
    }

    public function syncResults(Request $request): JsonResponse
    {
        $request->validate(['round_id' => 'required|integer|exists:rounds,id']);

        $round = Round::with('season')->findOrFail($request->integer('round_id'));

        $count = $this->sync->syncResults($round);

        return response()->json(['message' => "Updated {$count} results", 'count' => $count]);
    }
}
