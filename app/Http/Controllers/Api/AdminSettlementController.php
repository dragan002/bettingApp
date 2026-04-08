<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Season;
use App\Models\SeasonSettlement;
use App\Models\TokenTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminSettlementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $season = Season::where('status', 'pending_settlement')->first();

        if (! $season) {
            return response()->json(['message' => 'No season is pending settlement'], 422);
        }

        $players = Player::orderBy('name')->get();
        $settlements = SeasonSettlement::where('season_id', $season->id)
            ->with('player')
            ->get()
            ->keyBy('player_id');

        $unsettled = [];
        $settled = [];

        foreach ($players as $player) {
            if (isset($settlements[$player->id])) {
                $s = $settlements[$player->id];
                $settled[] = [
                    'playerId' => $player->id,
                    'displayName' => $player->displayName(),
                    'settledAmount' => $s->settled_amount,
                    'settledAt' => $s->settled_at?->toISOString(),
                ];
            } else {
                $unsettled[] = [
                    'playerId' => $player->id,
                    'displayName' => $player->displayName(),
                    'tokenBalance' => $player->token_balance,
                ];
            }
        }

        return response()->json([
            'seasonId' => $season->id,
            'unsettled' => $unsettled,
            'settled' => $settled,
        ]);
    }

    public function settle(Request $request, int $playerId): JsonResponse
    {
        $season = Season::where('status', 'pending_settlement')->first();

        if (! $season) {
            return response()->json(['message' => 'No season is pending settlement'], 422);
        }

        $player = Player::findOrFail($playerId);

        $existing = SeasonSettlement::where('season_id', $season->id)
            ->where('player_id', $playerId)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Player already settled'], 422);
        }

        $settlement = null;

        DB::transaction(function () use ($season, $player, &$settlement) {
            $balanceBefore = $player->token_balance;
            $settledAmount = $balanceBefore;

            $settlement = SeasonSettlement::create([
                'season_id' => $season->id,
                'player_id' => $player->id,
                'settled_amount' => $settledAmount,
                'settled_at' => now(),
            ]);

            // Determine transaction type based on balance
            if ($balanceBefore > 0) {
                $type = 'settlement_refund';
            } elseif ($balanceBefore < 0) {
                $type = 'settlement_collected';
            } else {
                $type = 'adjustment';
            }

            TokenTransaction::create([
                'player_id' => $player->id,
                'amount' => -$balanceBefore, // negate to zero out
                'type' => $type,
                'description' => 'Season settlement',
                'balance_before' => $balanceBefore,
                'balance_after' => 0,
            ]);

            $player->update(['token_balance' => 0]);
        });

        return response()->json([
            'settlement' => [
                'playerId' => $player->id,
                'displayName' => $player->displayName(),
                'settledAmount' => $settlement->settled_amount,
                'settledAt' => $settlement->settled_at?->toISOString(),
            ],
        ]);
    }
}
