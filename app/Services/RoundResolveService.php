<?php

namespace App\Services;

use App\Models\Prediction;
use App\Models\Round;
use App\Models\RoundEntry;
use App\Models\SeasonPoints;
use App\Models\SeasonRoundPoints;
use App\Models\TokenTransaction;
use Illuminate\Support\Facades\DB;

class RoundResolveService
{
    public function __construct(
        private BadgeService $badgeService
    ) {}

    public function resolve(Round $round): array
    {
        $stats = ['predictions_scored' => 0, 'jackpot_winners' => 0];

        DB::transaction(function () use ($round, &$stats) {
            $finishedFixtures = $round->fixtures()
                ->where('status', 'finished')
                ->get();

            // Score each prediction
            foreach ($finishedFixtures as $fixture) {
                $result = $fixture->getResult();
                if ($result === null) continue;

                $count = Prediction::where('fixture_id', $fixture->id)
                    ->update(['is_correct' => DB::raw("CASE WHEN pick = '{$result}' THEN 1 ELSE 0 END")]);

                $stats['predictions_scored'] += $count;
            }

            // Active (non-cancelled/postponed) finished fixtures only
            $activeFinishedIds = $round->fixtures()
                ->where('status', 'finished')
                ->whereNotIn('status', ['postponed', 'cancelled'])
                ->pluck('id');

            $totalActive = $activeFinishedIds->count();

            // Update each player's round entry
            $entries = RoundEntry::where('round_id', $round->id)->with('player')->get();

            foreach ($entries as $entry) {
                $correctCount = Prediction::where('player_id', $entry->player_id)
                    ->whereIn('fixture_id', $activeFinishedIds)
                    ->where('is_correct', true)
                    ->count();

                $isPerfect = $totalActive > 0 && $correctCount === $totalActive;

                $entry->update([
                    'points' => $correctCount,
                    'is_perfect' => $isPerfect,
                ]);
            }

            // Refresh entries after update
            $entries = RoundEntry::where('round_id', $round->id)->with('player')->get();

            // Update season leaderboard
            $season = $round->season;

            foreach ($entries as $entry) {
                SeasonPoints::updateOrCreate(
                    ['season_id' => $season->id, 'player_id' => $entry->player_id],
                    []
                );

                SeasonPoints::where('season_id', $season->id)
                    ->where('player_id', $entry->player_id)
                    ->increment('points', $entry->points);

                SeasonPoints::where('season_id', $season->id)
                    ->where('player_id', $entry->player_id)
                    ->increment('rounds_played');
            }

            // Award jackpot to perfect predictors
            $perfectEntries = $entries->filter(fn ($e) => $e->is_perfect);
            $stats['jackpot_winners'] = $perfectEntries->count();

            if ($perfectEntries->count() > 0 && $season->jackpot > 0) {
                $splitAmount = intdiv($season->jackpot, $perfectEntries->count());

                foreach ($perfectEntries as $entry) {
                    $balanceBefore = $entry->player->token_balance;
                    $entry->player->increment('token_balance', $splitAmount);
                    $balanceAfter = $entry->player->fresh()->token_balance;

                    TokenTransaction::create([
                        'player_id' => $entry->player_id,
                        'amount' => $splitAmount,
                        'type' => 'payout_jackpot',
                        'description' => "Jackpot win - Round {$round->number}",
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter,
                        'round_id' => $round->id,
                    ]);
                }

                $season->update(['jackpot' => 0]);
            }

            // Write SeasonRoundPoints for each entry
            foreach ($entries as $entry) {
                SeasonRoundPoints::updateOrCreate(
                    [
                        'round_id' => $round->id,
                        'player_id' => $entry->player_id,
                    ],
                    [
                        'season_id' => $season->id,
                        'points' => $entry->points,
                        'is_perfect' => $entry->is_perfect,
                    ]
                );
            }

            $round->update(['status' => 'resolved']);
        });

        // Award badges after transaction completes
        $season = $round->season;
        $this->badgeService->awardBadges($round, $season);

        return $stats;
    }
}
