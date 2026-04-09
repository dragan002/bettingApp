<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\TokenTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminPlayerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $players = Player::orderBy('name')->get()->map->toAdminArray()->values();

        return response()->json(['players' => $players]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:players,name',
            'pin' => 'required|string|min:4|max:8',
            'is_admin' => 'boolean',
        ]);

        $player = Player::create([
            'name' => $request->name,
            'pin' => Hash::make($request->pin),
            'is_admin' => $request->boolean('is_admin', false),
            'token_balance' => 0,
        ]);

        return response()->json(['player' => $player->toAdminArray()], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $player = Player::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:50|unique:players,name,' . $id,
            'pin' => 'sometimes|string|min:4|max:8',
            'is_admin' => 'sometimes|boolean',
            'token_balance' => 'sometimes|integer',
        ]);

        $data = $request->only(['name', 'is_admin', 'token_balance']);

        if ($request->filled('pin')) {
            $data['pin'] = Hash::make($request->pin);
        }

        // Record token adjustment if balance changed
        if ($request->has('token_balance') && $request->integer('token_balance') !== $player->token_balance) {
            $diff = $request->integer('token_balance') - $player->token_balance;
            $balanceBefore = $player->token_balance;
            $player->update(['token_balance' => $request->integer('token_balance')]);
            unset($data['token_balance']);

            TokenTransaction::create([
                'player_id'      => $player->id,
                'amount'         => abs($diff),
                'type'           => $diff > 0 ? 'credit' : 'adjustment',
                'description'    => 'Admin adjustment',
                'balance_before' => $balanceBefore,
                'balance_after'  => $player->fresh()->token_balance,
            ]);
        }

        $player->update($data);

        return response()->json(['player' => $player->fresh()->toAdminArray()]);
    }

    public function updateNickname(Request $request, int $id): JsonResponse
    {
        $request->validate(['nickname' => 'nullable|string|max:30']);
        $player = Player::findOrFail($id);
        $player->update(['nickname' => $request->input('nickname')]);

        return response()->json(['player' => $player->toAdminArray()]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $player = Player::findOrFail($id);

        // Prevent deleting yourself
        $currentPlayer = $request->attributes->get('player');
        if ($currentPlayer->id === $player->id) {
            return response()->json(['message' => 'Cannot delete your own account'], 422);
        }

        $player->delete();

        return response()->json(['message' => 'Player deleted']);
    }
}
