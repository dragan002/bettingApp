<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Models\Round;
use App\Models\Season;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupDuplicateFixtures extends Command
{
    protected $signature = 'fixtures:cleanup-duplicates
                            {--round= : Specific round ID to clean up (defaults to current active round)}
                            {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Remove duplicate football-data.org fixtures when a round also has FlashScore fixtures';

    public function handle(): int
    {
        $roundId = $this->option('round');
        $dryRun = $this->option('dry-run');

        if ($roundId) {
            $round = Round::find($roundId);

            if (! $round) {
                $this->error("Round #{$roundId} not found.");

                return self::FAILURE;
            }
        } else {
            $season = Season::where('status', 'active')->first();

            if (! $season) {
                $this->warn('No active season found.');

                return self::SUCCESS;
            }

            $round = Round::where('season_id', $season->id)
                ->whereIn('status', ['pending', 'active', 'locked'])
                ->orderByRaw("CASE status WHEN 'active' THEN 1 WHEN 'pending' THEN 2 WHEN 'locked' THEN 3 ELSE 4 END")
                ->orderByDesc('number')
                ->first();

            if (! $round) {
                $this->warn('No active/pending/locked round found.');

                return self::SUCCESS;
            }
        }

        $this->info("Checking round #{$round->id} (Matchweek {$round->number}, status: {$round->status})");

        $hasFlashScore = Fixture::where('round_id', $round->id)
            ->where('external_id', 'like', 'fs_%')
            ->exists();

        $footballDataCount = Fixture::where('round_id', $round->id)
            ->where('external_id', 'not like', 'fs_%')
            ->count();

        $flashScoreCount = Fixture::where('round_id', $round->id)
            ->where('external_id', 'like', 'fs_%')
            ->count();

        $totalCount = Fixture::where('round_id', $round->id)->count();

        $this->line("  Total fixtures:       {$totalCount}");
        $this->line("  FlashScore (fs_*):    {$flashScoreCount}");
        $this->line("  football-data.org:    {$footballDataCount}");

        if (! $hasFlashScore || $footballDataCount === 0) {
            $this->info('No duplicate mix found — nothing to clean up.');

            return self::SUCCESS;
        }

        $this->warn("Found both FlashScore and football-data.org fixtures in round #{$round->id}.");

        if ($dryRun) {
            $duplicates = Fixture::where('round_id', $round->id)
                ->where('external_id', 'not like', 'fs_%')
                ->get(['id', 'external_id', 'home_team', 'away_team', 'kickoff_at']);

            $this->line('');
            $this->line('Would delete the following football-data.org fixtures (dry run):');

            foreach ($duplicates as $fixture) {
                $this->line("  [{$fixture->id}] {$fixture->home_team} vs {$fixture->away_team} (external_id: {$fixture->external_id})");
            }

            $this->line('');
            $this->info("Dry run complete — {$footballDataCount} fixture(s) would be deleted. Run without --dry-run to apply.");

            return self::SUCCESS;
        }

        $deleted = Fixture::where('round_id', $round->id)
            ->where('external_id', 'not like', 'fs_%')
            ->delete();

        Log::info('fixtures:cleanup-duplicates removed football-data.org fixtures', [
            'round_id' => $round->id,
            'round_number' => $round->number,
            'deleted' => $deleted,
        ]);

        $this->info("Deleted {$deleted} football-data.org fixture(s). Round #{$round->id} now has {$flashScoreCount} FlashScore fixture(s).");

        return self::SUCCESS;
    }
}
