<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\Round;
use Illuminate\Support\Facades\Log;

class RoundSyncService
{
    public function __construct(
        private FootballDataService $api,
        private RoundResolveService $resolver,
    ) {}

    public function syncFixtures(Round $round): int
    {
        $season = $round->season;
        $matches = $this->api->getMatches($season->league_id, $round->number);

        $synced = 0;

        foreach ($matches as $match) {
            $data = $this->api->mapMatchToFixture($match);

            Fixture::updateOrCreate(
                ['round_id' => $round->id, 'external_id' => $data['external_id']],
                $data
            );

            $synced++;
        }

        // Activate round if it was pending and we got fixtures
        if ($synced > 0 && $round->status === 'pending') {
            $round->update(['status' => 'active']);
        }

        // Auto-set locks_at from the earliest fixture kickoff
        if ($synced > 0) {
            $earliest = Fixture::where('round_id', $round->id)
                ->whereNotNull('kickoff_at')
                ->min('kickoff_at');

            if ($earliest !== null) {
                $earliestCarbon = \Illuminate\Support\Carbon::parse($earliest);

                if ($round->locks_at === null || ! $round->locks_at->eq($earliestCarbon)) {
                    $round->update(['locks_at' => $earliestCarbon]);
                }
            }
        }

        return $synced;
    }

    public function syncResults(Round $round): int
    {
        $season = $round->season;
        $matches = $this->api->getFinishedMatches($season->league_id, $round->number);

        $updated = 0;

        foreach ($matches as $match) {
            $data = $this->api->mapMatchToFixture($match);

            $affected = Fixture::where('round_id', $round->id)
                ->where('external_id', $data['external_id'])
                ->update([
                    'home_score' => $data['home_score'],
                    'away_score' => $data['away_score'],
                    'status' => $data['status'],
                ]);

            $updated += $affected;
        }

        // Auto-resolve when all non-cancelled/postponed fixtures are finished
        if ($round->status !== 'resolved') {
            $this->maybeAutoResolve($round);
        }

        return $updated;
    }

    private function maybeAutoResolve(Round $round): void
    {
        $activeFixtures = Fixture::where('round_id', $round->id)
            ->whereNotIn('status', ['postponed', 'cancelled'])
            ->get();

        // Nothing to resolve if there are no active fixtures
        if ($activeFixtures->isEmpty()) {
            return;
        }

        // All active fixtures must be finished
        $allFinished = $activeFixtures->every(fn ($f) => $f->status === 'finished');

        if (! $allFinished) {
            return;
        }

        try {
            $this->resolver->resolve($round);
        } catch (\Throwable $e) {
            Log::error('Auto-resolve failed', [
                'round_id' => $round->id,
                'error' => $e->getMessage(),
            ]);

            return;
        }

        // Auto-create the next round
        try {
            $this->createNextRound($round);
        } catch (\Throwable $e) {
            Log::error('Auto-create next round failed', [
                'round_id' => $round->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function createNextRound(Round $round): void
    {
        $nextNumber = $round->number + 1;
        $season = $round->season;

        $exists = Round::where('season_id', $season->id)
            ->where('number', $nextNumber)
            ->exists();

        if ($exists) {
            Log::warning("Auto next-round skipped: round {$nextNumber} already exists for season {$season->id}");
            return;
        }

        $nextRound = Round::create([
            'season_id' => $season->id,
            'number' => $nextNumber,
            'status' => 'pending',
            'locks_at' => null,
        ]);

        $this->syncFixtures($nextRound);
    }
}
