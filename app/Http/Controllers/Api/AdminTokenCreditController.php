<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\TokenTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminTokenCreditController extends Controller
{
    public function store(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
            'description' => 'nullable|string|max:200',
        ]);

        $player = Player::findOrFail($id);
        $amount = $request->integer('amount');
        $description = $request->input('description');

        $transaction = null;

        DB::transaction(function () use ($player, $amount, $description, &$transaction) {
            $balanceBefore = $player->token_balance;
            $player->increment('token_balance', $amount);
            $balanceAfter = $player->fresh()->token_balance;

            $transaction = TokenTransaction::create([
                'player_id' => $player->id,
                'amount' => $amount,
                'type' => 'credit',
                'description' => $description ?? 'Admin credit',
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
            ]);
        });

        return response()->json([
            'player' => $player->fresh()->toAdminArray(),
            'transaction' => $transaction->toApiArray(),
        ]);
    }
}
