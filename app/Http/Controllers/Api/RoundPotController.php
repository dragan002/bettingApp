<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Round;
use App\Models\TokenTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoundPotController extends Controller
{
    public function show(Request $request, int $id): JsonResponse
    {
        $round = Round::findOrFail($id);

        $transactions = TokenTransaction::where('round_id', $id)
            ->where('type', 'debit_round')
            ->get();

        $tokensCollected = $transactions->sum(fn ($t) => abs($t->amount));
        $playerCount = $transactions->pluck('player_id')->unique()->count();

        return response()->json([
            'roundId' => $round->id,
            'roundNumber' => $round->number,
            'tokensCollected' => $tokensCollected,
            'playerCount' => $playerCount,
        ]);
    }
}
