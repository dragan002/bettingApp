<?php

namespace Tests\Unit;

use App\Models\Player;
use App\Models\Round;
use App\Models\Season;
use App\Models\SeasonRoundPoints;
use App\Services\StreakService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StreakServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeSeason(): Season
    {
        return Season::create([
            'league_id' => 'PL',
            'league_name' => 'Premier League',
            'status' => 'active',
            'jackpot' => 0,
            'entry_tokens' => 5,
        ]);
    }

    private function makePlayer(string $name = 'Player'): Player
    {
        return Player::create([
            'name' => $name,
            'pin' => Hash::make('1234'),
            'is_admin' => false,
            'token_balance' => 0,
        ]);
    }

    private function makeRound(Season $season, int $number): Round
    {
        return Round::create([
            'season_id' => $season->id,
            'number' => $number,
            'status' => 'resolved',
            'locks_at' => now()->subHours(2)->toDateTimeString(),
        ]);
    }

    private function addPoints(Player $player, Season $season, Round $round, int $points, bool $isPerfect = false): void
    {
        SeasonRoundPoints::create([
            'season_id' => $season->id,
            'round_id' => $round->id,
            'player_id' => $player->id,
            'points' => $points,
            'is_perfect' => $isPerfect,
        ]);
    }

    // ================================================================
    // onFire
    // ================================================================

    public function test_returns_0_on_fire_when_most_recent_round_has_fewer_than_5_points(): void
    {
        $service = app(StreakService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('P1');

        $r1 = $this->makeRound($season, 1);
        $r2 = $this->makeRound($season, 2);
        $this->addPoints($player, $season, $r1, 6);
        $this->addPoints($player, $season, $r2, 3); // breaks streak

        $result = $service->computeForSeason($season->id);
        $this->assertSame(0, $result[$player->id]['onFire']);
    }

    public function test_returns_correct_on_fire_streak_from_most_recent_rounds(): void
    {
        $service = app(StreakService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('P2');

        $r1 = $this->makeRound($season, 1);
        $r2 = $this->makeRound($season, 2);
        $r3 = $this->makeRound($season, 3);

        $this->addPoints($player, $season, $r1, 3); // not on fire
        $this->addPoints($player, $season, $r2, 7); // on fire
        $this->addPoints($player, $season, $r3, 5); // on fire

        $result = $service->computeForSeason($season->id);
        $this->assertSame(2, $result[$player->id]['onFire']);
    }

    public function test_counts_all_rounds_as_on_fire_when_every_round_has_5_plus_points(): void
    {
        $service = app(StreakService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('P3');

        foreach (range(1, 4) as $n) {
            $r = $this->makeRound($season, $n);
            $this->addPoints($player, $season, $r, 5);
        }

        $result = $service->computeForSeason($season->id);
        $this->assertSame(4, $result[$player->id]['onFire']);
    }

    // ================================================================
    // cold
    // ================================================================

    public function test_returns_correct_cold_streak_from_most_recent_rounds(): void
    {
        $service = app(StreakService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('P4');

        $r1 = $this->makeRound($season, 1);
        $r2 = $this->makeRound($season, 2);
        $r3 = $this->makeRound($season, 3);

        $this->addPoints($player, $season, $r1, 5); // hot
        $this->addPoints($player, $season, $r2, 1); // cold
        $this->addPoints($player, $season, $r3, 0); // cold

        $result = $service->computeForSeason($season->id);
        $this->assertSame(2, $result[$player->id]['cold']);
    }

    public function test_returns_0_cold_streak_when_most_recent_round_has_3_plus_points(): void
    {
        $service = app(StreakService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('P5');

        $r1 = $this->makeRound($season, 1);
        $r2 = $this->makeRound($season, 2);

        $this->addPoints($player, $season, $r1, 0);
        $this->addPoints($player, $season, $r2, 3);

        $result = $service->computeForSeason($season->id);
        $this->assertSame(0, $result[$player->id]['cold']);
    }

    // ================================================================
    // ironMan
    // ================================================================

    public function test_returns_consecutive_rounds_submitted_from_most_recent(): void
    {
        $service = app(StreakService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('P6');

        $r1 = $this->makeRound($season, 1);
        $r2 = $this->makeRound($season, 2);
        $r3 = $this->makeRound($season, 3);
        $r4 = $this->makeRound($season, 4);

        $this->addPoints($player, $season, $r1, 4);
        // round 2 skipped
        $this->addPoints($player, $season, $r3, 4);
        $this->addPoints($player, $season, $r4, 4);

        $result = $service->computeForSeason($season->id);
        // From most recent: r4 submitted, r3 submitted, r2 NOT submitted → streak = 2
        $this->assertSame(2, $result[$player->id]['ironMan']);
    }

    public function test_returns_full_count_when_no_gaps_exist(): void
    {
        $service = app(StreakService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('P7');

        foreach (range(1, 5) as $n) {
            $r = $this->makeRound($season, $n);
            $this->addPoints($player, $season, $r, 3);
        }

        $result = $service->computeForSeason($season->id);
        $this->assertSame(5, $result[$player->id]['ironMan']);
    }

    // ================================================================
    // perfectRounds
    // ================================================================

    public function test_counts_total_perfect_rounds_across_the_season(): void
    {
        $service = app(StreakService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('P8');

        $r1 = $this->makeRound($season, 1);
        $r2 = $this->makeRound($season, 2);
        $r3 = $this->makeRound($season, 3);

        $this->addPoints($player, $season, $r1, 10, true);
        $this->addPoints($player, $season, $r2, 3, false);
        $this->addPoints($player, $season, $r3, 10, true);

        $result = $service->computeForSeason($season->id);
        $this->assertSame(2, $result[$player->id]['perfectRounds']);
    }

    // ================================================================
    // Empty state
    // ================================================================

    public function test_returns_empty_array_when_no_season_round_points_exist(): void
    {
        $service = app(StreakService::class);
        $season = $this->makeSeason();

        $result = $service->computeForSeason($season->id);
        $this->assertEmpty($result);
    }

    public function test_returns_correct_streaks_for_single_cold_round(): void
    {
        $service = app(StreakService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('P9');
        $r = $this->makeRound($season, 1);
        $this->addPoints($player, $season, $r, 2); // cold but only one

        $result = $service->computeForSeason($season->id);
        $this->assertSame(0, $result[$player->id]['onFire']);
        $this->assertSame(1, $result[$player->id]['cold']);
        $this->assertSame(1, $result[$player->id]['ironMan']);
        $this->assertSame(0, $result[$player->id]['perfectRounds']);
    }
}
