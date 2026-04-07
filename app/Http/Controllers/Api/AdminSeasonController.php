<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Season;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSeasonController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'league_id' => 'required|string|max:20',
            'league_name' => 'required|string|max:100',
            'entry_tokens' => 'required|integer|min:1',
        ]);

        // End any existing active season
        Season::active()->update(['status' => 'ended']);

        $season = Season::create([
            'league_id' => strtoupper($request->league_id),
            'league_name' => $request->league_name,
            'entry_tokens' => $request->integer('entry_tokens'),
            'jackpot' => 0,
            'status' => 'active',
        ]);

        return response()->json(['season' => $season->toApiArray()], 201);
    }
}
