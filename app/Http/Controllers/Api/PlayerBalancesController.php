<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerBalancesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $players = Player::orderByDesc('token_balance')
            ->get()
            ->map->toApiArray()
            ->values();

        return response()->json(['players' => $players]);
    }
}
