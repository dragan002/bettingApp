<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\Player;
use App\Models\Prediction;
use App\Models\Round;
use App\Models\RoundEntry;
use App\Models\Season;
use App\Models\SeasonPoints;
use App\Services\FootballDataService;
use App\Services\RoundResolveService;
use App\Services\RoundSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoundSyncAutoResolveTest extends TestCase
{
    use RefreshDatabase;

    private static int $counter = 0;

    private function makePlayer(string $name = 'Player'): Player
    {
        return Player::create([
            'name' => $name,
            'pin' => Hash::make('1234'),
            'is_admin' => false,
            'token_balance' => 10,
        ]);
    }

    private function makeSeason(): Season
    {
        return Season::create([
            'league_id' => 'PL',
            'league_name' => 'Premier League',
            'status' => 'active',
            'jackpot' => 50,
            'entry_tokens' => 5,
        ]);
    }

    private function makeRound(Season $season, string $status = 'active'): Round
    {
        return Round::create([
            'season_id' => $season->id,
            'number' => 1,
            'status' => $status,
            'locks_at' => now()->subHour()->toDateTimeString(),
        ]);
    }

    private function makeFixture(Round $round, string $status, ?int $homeScore = null, ?int $awayScore = null): Fixture
    {
        self::$counter++;
        return Fixture::create([
            'round_id' => $round->id,
            'external_id' => 'sync-ext-' . self::$counter,
            'home_team' => 'Home',
            'away_team' => 'Away',
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'status' => $status,
            'kickoff_at' => now()->subHour()->toDateTimeString(),
        ]);
    }

    private function makeSyncService(): RoundSyncService
    {
        $resolver = app(RoundResolveService::class);
        $api = $this->createMock(FootballDataService::class);
        $api->method('getFinishedMatches')->willReturn([]);
        return new RoundSyncService($api, $resolver);
    }

    // ================================================================
    // maybeAutoResolve
    // ================================================================

    public function test_auto_resolves_round_when_all_active_fixtures_are_finished(): void
    {
        $season = $this->makeSeason();
        $round = $this->makeRound($season);
        $player = $this->makePlayer('AutoAlice');

        $f1 = $this->makeFixture($round, 'finished', 1, 0);
        $f2 = $this->makeFixture($round, 'finished', 0, 0);

        Prediction::create(['player_id' => $player->id, 'fixture_id' => $f1->id, 'pick' => '1']);
        Prediction::create(['player_id' => $player->id, 'fixture_id' => $f2->id, 'pick' => 'X']);
        RoundEntry::create(['round_id' => $round->id, 'player_id' => $player->id, 'is_complete' => true, 'is_perfect' => false, 'points' => 0]);
        SeasonPoints::create(['season_id' => $season->id, 'player_id' => $player->id, 'points' => 0, 'rounds_played' => 0]);

        $syncService = $this->makeSyncService();
        $syncService->syncResults($round);

        $this->assertSame('resolved', $round->fresh()->status);
    }

    public function test_does_not_auto_resolve_when_some_active_fixtures_are_not_finished(): void
    {
        $season = $this->makeSeason();
        $round = $this->makeRound($season);
        $player = $this->makePlayer('AutoBob');

        $f1 = $this->makeFixture($round, 'finished', 2, 1);
        $f2 = $this->makeFixture($round, 'scheduled'); // not finished

        Prediction::create(['player_id' => $player->id, 'fixture_id' => $f1->id, 'pick' => '1']);
        Prediction::create(['player_id' => $player->id, 'fixture_id' => $f2->id, 'pick' => 'X']);
        RoundEntry::create(['round_id' => $round->id, 'player_id' => $player->id, 'is_complete' => true, 'is_perfect' => false, 'points' => 0]);
        SeasonPoints::create(['season_id' => $season->id, 'player_id' => $player->id, 'points' => 0, 'rounds_played' => 0]);

        $syncService = $this->makeSyncService();
        $syncService->syncResults($round);

        $this->assertSame('active', $round->fresh()->status);
    }

    public function test_does_not_auto_resolve_when_only_postponed_or_cancelled_fixtures_exist(): void
    {
        $season = $this->makeSeason();
        $round = $this->makeRound($season);

        $this->makeFixture($round, 'postponed');
        $this->makeFixture($round, 'cancelled');

        $syncService = $this->makeSyncService();
        $syncService->syncResults($round);

        // No active fixtures → no resolve
        $this->assertSame('active', $round->fresh()->status);
    }

    public function test_does_not_auto_resolve_when_round_is_already_resolved(): void
    {
        $season = $this->makeSeason();
        $round = $this->makeRound($season, 'resolved');

        $this->makeFixture($round, 'finished', 1, 0);

        $syncService = $this->makeSyncService();
        $syncService->syncResults($round);

        // syncResults short-circuits for resolved rounds
        $this->assertSame('resolved', $round->fresh()->status);
    }
}
