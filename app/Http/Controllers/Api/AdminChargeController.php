<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Round;
use App\Models\RoundEntry;
use App\Models\Season;
use App\Models\TokenTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminChargeController extends Controller
{
    public function charge(Request $request): JsonResponse
    {
        $request->validate(['round_id' => 'required|integer|exists:rounds,id']);

        $round = Round::findOrFail($request->integer('round_id'));
        $season = Season::active()->firstOrFail();

        if ($round->season_id !== $season->id) {
            return response()->json(['message' => 'Round does not belong to active season'], 422);
        }

        $charged = 0;
        $entryTokens = $season->entry_tokens;

        DB::transaction(function () use ($round, $season, $entryTokens, &$charged) {
            $completedEntries = RoundEntry::where('round_id', $round->id)
                ->where('is_complete', true)
                ->with('player')
                ->get();

            foreach ($completedEntries as $entry) {
                if ($entry->player->token_balance < $entryTokens) {
                    continue; // skip players with insufficient tokens
                }

                $entry->player->decrement('token_balance', $entryTokens);

                TokenTransaction::create([
                    'player_id' => $entry->player_id,
                    'amount' => $entryTokens,
                    'type' => 'debit',
                    'description' => "Entry fee - Round {$round->number}",
                ]);

                $charged++;
            }

            // Add collected tokens to jackpot
            $season->increment('jackpot', $charged * $entryTokens);
        });

        return response()->json(['message' => "Charged {$charged} players", 'charged' => $charged]);
    }
}
