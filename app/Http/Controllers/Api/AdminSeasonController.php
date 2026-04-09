<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerBadge;
use App\Models\Round;
use App\Models\Season;
use App\Models\SeasonHallOfFame;
use App\Models\SeasonPoints;
use App\Models\SeasonSettlement;
use App\Models\TokenTransaction;
use App\Services\FootballDataService;
use App\Services\RoundSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminSeasonController extends Controller
{
    public function __construct(
        private FootballDataService $footballData,
        private RoundSyncService $roundSync,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'league_id' => 'required|string|max:20',
            'league_name' => 'required|string|max:100',
            'entry_tokens' => 'required|integer|min:1',
        ]);

        // Guard: reject if any season is pending settlement
        if (Season::where('status', 'pending_settlement')->exists()) {
            return response()->json([
                'message' => 'Cannot start a new season while the current season is pending settlement',
            ], 422);
        }

        // End any existing active season
        Season::active()->update(['status' => 'ended']);

        $leagueId = strtoupper($request->league_id);

        $season = Season::create([
            'league_id' => $leagueId,
            'league_name' => $request->league_name,
            'entry_tokens' => $request->integer('entry_tokens'),
            'jackpot' => 0,
            'status' => 'active',
        ]);

        // Auto-create first round from current matchday
        try {
            $matchday = $this->footballData->getCurrentMatchday($leagueId);

            if ($matchday !== null) {
                $round = Round::create([
                    'season_id' => $season->id,
                    'number' => $matchday,
                    'status' => 'pending',
                    'locks_at' => null,
                ]);

                $this->roundSync->syncFixtures($round);
            }
        } catch (\Throwable $e) {
            Log::warning('Auto-create first round failed after season creation', [
                'season_id' => $season->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['season' => $season->toApiArray()], 201);
    }

    public function pendingSettlement(Request $request): JsonResponse
    {
        $season = Season::active()->first();

        if (! $season) {
            return response()->json(['message' => 'No active season'], 422);
        }

        $season->update(['status' => 'pending_settlement']);

        return response()->json(['season' => $season->fresh()->toApiArray()]);
    }

    public function close(Request $request): JsonResponse
    {
        $season = Season::where('status', 'pending_settlement')->first();

        if (! $season) {
            return response()->json(['message' => 'No season pending settlement'], 422);
        }

        $totalPlayers = Player::count();
        $settledCount = SeasonSettlement::where('season_id', $season->id)->count();

        if ($settledCount < $totalPlayers) {
            return response()->json([
                'message' => 'All players must be settled before closing the season',
                'settledCount' => $settledCount,
                'totalPlayers' => $totalPlayers,
            ], 422);
        }

        // Determine leaderboard winner
        $leaderboardTop = SeasonPoints::where('season_id', $season->id)
            ->with('player')
            ->orderByDesc('points')
            ->orderBy('rounds_played')
            ->first();

        $leaderboardWinnerId = $leaderboardTop?->player_id;

        // Determine jackpot winner: find player with a payout_jackpot transaction for this season's rounds
        $jackpotWinnerId = TokenTransaction::join('rounds', 'rounds.id', '=', 'token_transactions.round_id')
            ->where('rounds.season_id', $season->id)
            ->where('token_transactions.type', 'payout_jackpot')
            ->value('token_transactions.player_id');

        // Determine player of season: PlayerBadge where season_id, tier='zlato',
        // group by player_id, order by count desc, take first — tie goes to leaderboard leader
        $playerOfSeasonRow = PlayerBadge::where('season_id', $season->id)
            ->where('tier', 'zlato')
            ->select('player_id')
            ->selectRaw('COUNT(*) as badge_count')
            ->groupBy('player_id')
            ->orderByDesc('badge_count')
            ->first();

        $playerOfSeasonId = null;
        if ($playerOfSeasonRow) {
            // Check for tie — if multiple players have same count, pick leaderboard leader
            $topCount = $playerOfSeasonRow->badge_count;
            $tiedPlayers = PlayerBadge::where('season_id', $season->id)
                ->where('tier', 'zlato')
                ->select('player_id')
                ->selectRaw('COUNT(*) as badge_count')
                ->groupBy('player_id')
                ->having('badge_count', '=', $topCount)
                ->pluck('player_id')
                ->toArray();

            if (count($tiedPlayers) === 1) {
                $playerOfSeasonId = $tiedPlayers[0];
            } else {
                // Tie — pick leaderboard leader if they're in the tied set
                if ($leaderboardWinnerId && in_array($leaderboardWinnerId, $tiedPlayers)) {
                    $playerOfSeasonId = $leaderboardWinnerId;
                } else {
                    $playerOfSeasonId = $tiedPlayers[0];
                }
            }
        }

        $totalRounds = $season->rounds()->where('status', 'resolved')->count();

        $hof = SeasonHallOfFame::updateOrCreate(
            ['season_id' => $season->id],
            [
                'jackpot_winner_id' => $jackpotWinnerId,
                'leaderboard_winner_id' => $leaderboardWinnerId,
                'player_of_season_id' => $playerOfSeasonId,
                'total_jackpot' => $season->jackpot,
                'total_rounds' => $totalRounds,
                'closed_at' => now(),
            ]
        );

        $season->update(['status' => 'ended']);

        $hof->load(['season', 'jackpotWinner', 'leaderboardWinner', 'playerOfSeason']);

        return response()->json(['hallOfFame' => $hof->toApiArray()]);
    }
}
