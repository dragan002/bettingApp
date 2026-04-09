<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\Round;

class RoundSyncService
{
    public function __construct(private FootballDataService $api) {}

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

        return $updated;
    }
}
