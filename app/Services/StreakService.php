<?php

namespace App\Services;

use App\Models\Round;
use App\Models\SeasonRoundPoints;

class StreakService
{
    /**
     * Compute streak data for all players in a season.
     * Returns array keyed by player_id.
     */
    public function computeForSeason(int $seasonId): array
    {
        // One query: all SeasonRoundPoints for season
        $allPoints = SeasonRoundPoints::where('season_id', $seasonId)->get();

        // One query: all rounds for season ordered by number
        $rounds = Round::where('season_id', $seasonId)
            ->orderBy('number')
            ->pluck('id')
            ->toArray();

        // Group records by player_id
        $byPlayer = $allPoints->groupBy('player_id');

        $result = [];

        foreach ($byPlayer as $playerId => $playerPoints) {
            // Build map: round_id => row
            $pointsByRound = [];
            $perfectCount = 0;
            foreach ($playerPoints as $row) {
                $pointsByRound[$row->round_id] = $row;
                if ($row->is_perfect) {
                    $perfectCount++;
                }
            }

            // Walk rounds in reverse order (most recent first)
            $reversedRounds = array_reverse($rounds);

            $onFire = 0;
            $cold = 0;
            $ironMan = 0;
            $onFireDone = false;
            $coldDone = false;
            $ironManDone = false;

            foreach ($reversedRounds as $roundId) {
                $row = $pointsByRound[$roundId] ?? null;

                // onFire: consecutive rounds with points >= 5
                if (! $onFireDone) {
                    if ($row && $row->points >= 5) {
                        $onFire++;
                    } else {
                        $onFireDone = true;
                    }
                }

                // cold: consecutive rounds with points <= 2
                if (! $coldDone) {
                    if ($row && $row->points <= 2) {
                        $cold++;
                    } else {
                        $coldDone = true;
                    }
                }

                // ironMan: consecutive rounds where a SeasonRoundPoints row exists (gap = stop)
                if (! $ironManDone) {
                    if ($row) {
                        $ironMan++;
                    } else {
                        $ironManDone = true;
                    }
                }

                if ($onFireDone && $coldDone && $ironManDone) {
                    break;
                }
            }

            $result[(int) $playerId] = [
                'onFire' => $onFire,
                'cold' => $cold,
                'ironMan' => $ironMan,
                'perfectRounds' => $perfectCount,
            ];
        }

        return $result;
    }
}
