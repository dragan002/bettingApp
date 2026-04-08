<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\TokenTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerLedgerController extends Controller
{
    public function show(Request $request, int $id): JsonResponse
    {
        $player = Player::findOrFail($id);

        $paginated = TokenTransaction::where('player_id', $id)
            ->latest()
            ->paginate(30);

        return response()->json([
            'player' => $player->toApiArray(),
            'transactions' => collect($paginated->items())->map->toApiArray()->values(),
            'meta' => [
                'currentPage' => $paginated->currentPage(),
                'lastPage' => $paginated->lastPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }
}
