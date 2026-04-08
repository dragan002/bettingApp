<?php

namespace App\Services;

use App\Models\PlayerBadge;
use App\Models\Round;
use App\Models\Season;
use App\Models\SeasonRoundPoints;
use App\Models\TokenTransaction;
use Illuminate\Support\Carbon;

class BadgeService
{
    public function awardBadges(Round $round, Season $season): void
    {
        // Load all SeasonRoundPoints for the season in one query
        $allPoints = SeasonRoundPoints::where('season_id', $season->id)->get();

        // Get all rounds for this season ordered by number, to know sequence
        $seasonRoundIds = Round::where('season_id', $season->id)
            ->orderBy('number')
            ->pluck('id')
            ->toArray();

        // Group records by player_id
        $byPlayer = $allPoints->groupBy('player_id');

        foreach ($byPlayer as $playerId => $playerPoints) {
            $this->evaluatePlayer(
                (int) $playerId,
                $season,
                $playerPoints->all(),
                $seasonRoundIds
            );
        }
    }

    private function evaluatePlayer(
        int $playerId,
        Season $season,
        array $playerPoints,
        array $seasonRoundIds
    ): void {
        // Build a map: round_id => points row
        $pointsByRound = [];
        $perfectCount = 0;
        foreach ($playerPoints as $row) {
            $pointsByRound[$row->round_id] = $row;
            if ($row->is_perfect) {
                $perfectCount++;
            }
        }

        // Sort by season round order
        $orderedRows = [];
        foreach ($seasonRoundIds as $roundId) {
            if (isset($pointsByRound[$roundId])) {
                $orderedRows[] = $pointsByRound[$roundId];
            }
        }

        $badges = [];

        // sniper: rounds with high points
        $sniperCount4 = 0;
        $sniperCount6 = 0;
        $sniperCount8 = 0;
        foreach ($orderedRows as $row) {
            if ($row->points >= 4) $sniperCount4++;
            if ($row->points >= 6) $sniperCount6++;
            if ($row->points >= 8) $sniperCount8++;
        }
        $sniperTier = null;
        if ($sniperCount8 >= 3) $sniperTier = 'zlato';
        elseif ($sniperCount6 >= 3) $sniperTier = 'rakija';
        elseif ($sniperCount4 >= 2) $sniperTier = 'kafa';
        $badges['sniper'] = $sniperTier;

        // perfectionist: perfect rounds
        $perfTier = null;
        if ($perfectCount >= 3) $perfTier = 'zlato';
        elseif ($perfectCount >= 2) $perfTier = 'rakija';
        elseif ($perfectCount >= 1) $perfTier = 'kafa';
        $badges['perfectionist'] = $perfTier;

        // iron_man: max consecutive rounds (by season round sequence)
        $maxStreak = 0;
        $currentStreak = 0;
        foreach ($seasonRoundIds as $roundId) {
            if (isset($pointsByRound[$roundId])) {
                $currentStreak++;
                if ($currentStreak > $maxStreak) {
                    $maxStreak = $currentStreak;
                }
            } else {
                $currentStreak = 0;
            }
        }
        $ironTier = null;
        if ($maxStreak >= 8) $ironTier = 'zlato';
        elseif ($maxStreak >= 5) $ironTier = 'rakija';
        elseif ($maxStreak >= 3) $ironTier = 'kafa';
        $badges['iron_man'] = $ironTier;

        // comeback_kid: count instances where ≤2 pts round is followed by ≥5 pts round
        $comebackCount = 0;
        for ($i = 0; $i < count($orderedRows) - 1; $i++) {
            if ($orderedRows[$i]->points <= 2 && $orderedRows[$i + 1]->points >= 5) {
                $comebackCount++;
            }
        }
        $comebackTier = null;
        if ($comebackCount >= 3) $comebackTier = 'zlato';
        elseif ($comebackCount >= 2) $comebackTier = 'rakija';
        elseif ($comebackCount >= 1) $comebackTier = 'kafa';
        $badges['comeback_kid'] = $comebackTier;

        // jackpot: count payout_jackpot TokenTransactions across ALL seasons
        $jackpotWins = TokenTransaction::where('player_id', $playerId)
            ->where('type', 'payout_jackpot')
            ->count();
        $jackpotTier = null;
        if ($jackpotWins >= 3) $jackpotTier = 'zlato';
        elseif ($jackpotWins >= 2) $jackpotTier = 'rakija';
        elseif ($jackpotWins >= 1) $jackpotTier = 'kafa';
        $badges['jackpot'] = $jackpotTier;

        // ledeni: total rounds with ≤2 pts this season
        $coldRounds = 0;
        foreach ($orderedRows as $row) {
            if ($row->points <= 2) $coldRounds++;
        }
        $ledeniTier = null;
        if ($coldRounds >= 12) $ledeniTier = 'zlato';
        elseif ($coldRounds >= 9) $ledeniTier = 'rakija';
        elseif ($coldRounds >= 6) $ledeniTier = 'kafa';
        $badges['ledeni'] = $ledeniTier;

        // Upsert badges — only upgrade, never downgrade
        foreach ($badges as $category => $tier) {
            if ($tier === null) {
                continue;
            }

            $existing = PlayerBadge::where('player_id', $playerId)
                ->where('season_id', $season->id)
                ->where('category', $category)
                ->first();

            if ($existing) {
                $existingRank = PlayerBadge::TIERS[$existing->tier] ?? 0;
                $newRank = PlayerBadge::TIERS[$tier] ?? 0;
                if ($newRank <= $existingRank) {
                    continue; // don't downgrade
                }
            }

            PlayerBadge::updateOrCreate(
                [
                    'player_id' => $playerId,
                    'season_id' => $season->id,
                    'category' => $category,
                ],
                [
                    'tier' => $tier,
                    'earned_at' => Carbon::now(),
                ]
            );
        }
    }
}
