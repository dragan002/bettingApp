<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'pin' => 'required|string',
        ]);

        $player = Player::where('name', $request->name)->first();

        if (! $player || ! $player->verifyPin($request->pin)) {
            return response()->json(['message' => 'Invalid name or PIN'], 401);
        }

        $token = Str::random(64);
        $player->authTokens()->create(['token' => $token]);

        return response()->json([
            'token' => $token,
            'player' => $player->toApiArray(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $bearer = $request->bearerToken();

        if ($bearer) {
            PlayerToken::where('token', $bearer)->delete();
        }

        return response()->json(['message' => 'Logged out']);
    }
}
