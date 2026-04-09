<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerBadge;
use App\Models\Prediction;
use App\Models\Round;
use App\Models\RoundEntry;
use App\Models\Season;
use App\Models\SeasonPoints;
use App\Services\StreakService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StateController extends Controller
{
    public function __construct(private StreakService $streakService) {}

    public function index(Request $request): JsonResponse
    {
        $player = $request->attributes->get('player');

        $season = Season::where('status', 'active')
            ->orWhere('status', 'pending_settlement')
            ->orderByDesc('id')
            ->first();

        $round = null;
        $predictions = (object) [];
        $leaderboard = [];
        $history = [];
        $balances = [];

        if ($season) {
            $round = Round::where('season_id', $season->id)
                ->whereIn('status', ['active', 'locked'])
                ->with('fixtures')
                ->first();

            if ($round) {
                $fixtureIds = $round->fixtures->pluck('id');
                $predictions = (object) Prediction::where('player_id', $player->id)
                    ->whereIn('fixture_id', $fixtureIds)
                    ->get()
                    ->pluck('pick', 'fixture_id')
                    ->toArray();
            }

            // Streaks — computed once, used in leaderboard
            $streaks = $this->streakService->computeForSeason($season->id);

            // Badge counts per player (gold badges) — single grouped query
            $goldBadgeCounts = PlayerBadge::where('season_id', $season->id)
                ->where('tier', 'zlato')
                ->select('player_id')
                ->selectRaw('COUNT(*) as badge_count')
                ->groupBy('player_id')
                ->pluck('badge_count', 'player_id')
                ->toArray();

            $leaderboard = SeasonPoints::where('season_id', $season->id)
                ->with('player')
                ->orderByDesc('points')
                ->orderBy('rounds_played')
                ->get()
                ->map(function ($entry) use ($streaks, $goldBadgeCounts) {
                    $arr = $entry->toApiArray();
                    $pid = (int) $entry->player_id;
                    $arr['displayName'] = $entry->relationLoaded('player')
                        ? ($entry->player?->displayName() ?? $arr['playerName'] ?? '')
                        : ($arr['playerName'] ?? '');
                    $arr['streaks'] = $streaks[$pid] ?? ['onFire' => 0, 'cold' => 0, 'ironMan' => 0, 'perfectRounds' => 0];
                    $arr['badgeCount'] = $goldBadgeCounts[$pid] ?? 0;

                    return $arr;
                })
                ->values()
                ->all();

            $history = Round::where('season_id', $season->id)
                ->where('status', 'resolved')
                ->orderByDesc('number')
                ->get()
                ->map->toApiArray()
                ->values()
                ->all();
        }

        $balances = Player::orderByDesc('token_balance')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'displayName' => $p->displayName(),
                'tokenBalance' => $p->token_balance,
            ])
            ->values()
            ->all();

        $seasonData = $season?->toApiArray();

        $roundData = $round?->toApiArray();
        if ($round) {
            $roundData['completedCount'] = RoundEntry::where('round_id', $round->id)
                ->where('is_complete', true)
                ->count();
            $roundData['totalPlayers'] = Player::where('is_admin', false)->count();
        }

        return response()->json([
            'player' => $player->toApiArray(),
            'season' => $seasonData,
            'round' => $roundData,
            'predictions' => $predictions,
            'leaderboard' => $leaderboard,
            'history' => $history,
            'balances' => $balances,
        ]);
    }
}
