<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Round;
use App\Services\RoundSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminSyncController extends Controller
{
    public function __construct(private RoundSyncService $sync) {}

    public function syncFixtures(Request $request): JsonResponse
    {
        $request->validate(['round_id' => 'required|integer|exists:rounds,id']);

        $round = Round::with('season')->findOrFail($request->integer('round_id'));

        try {
            $count = $this->sync->syncFixtures($round);
        } catch (\Throwable $e) {
            Log::error('syncFixtures failed', ['round_id' => $round->id, 'error' => $e->getMessage()]);
            $message = app()->environment('production') ? 'Sync failed. Check server logs.' : $e->getMessage();
            return response()->json(['message' => $message], 500);
        }

        return response()->json(['message' => "Synced {$count} fixtures", 'count' => $count]);
    }

    public function syncResults(Request $request): JsonResponse
    {
        $request->validate(['round_id' => 'required|integer|exists:rounds,id']);

        $round = Round::with('season')->findOrFail($request->integer('round_id'));

        try {
            $count = $this->sync->syncResults($round);
        } catch (\Throwable $e) {
            Log::error('syncResults failed', ['round_id' => $round->id, 'error' => $e->getMessage()]);
            $message = app()->environment('production') ? 'Sync failed. Check server logs.' : $e->getMessage();
            return response()->json(['message' => $message], 500);
        }

        return response()->json(['message' => "Updated {$count} results", 'count' => $count]);
    }
}
