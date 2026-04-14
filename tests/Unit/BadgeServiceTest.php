<?php

namespace Tests\Unit;

use App\Models\Player;
use App\Models\PlayerBadge;
use App\Models\Round;
use App\Models\Season;
use App\Models\SeasonRoundPoints;
use App\Models\TokenTransaction;
use App\Services\BadgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BadgeServiceTest extends TestCase
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

    private function getBadge(Player $player, Season $season, string $category): ?PlayerBadge
    {
        return PlayerBadge::where('player_id', $player->id)
            ->where('season_id', $season->id)
            ->where('category', $category)
            ->first();
    }

    // ================================================================
    // Sniper badge
    // ================================================================

    public function test_awards_sniper_kafa_for_2_rounds_with_4_plus_points(): void
    {
        $service = app(BadgeService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('Sniper1');

        $r1 = $this->makeRound($season, 1);
        $r2 = $this->makeRound($season, 2);
        $this->addPoints($player, $season, $r1, 4);
        $this->addPoints($player, $season, $r2, 5);

        $service->awardBadges($r2, $season);

        $badge = $this->getBadge($player, $season, 'sniper');
        $this->assertNotNull($badge);
        $this->assertSame('kafa', $badge->tier);
    }

    public function test_awards_sniper_rakija_for_3_rounds_with_6_plus_points(): void
    {
        $service = app(BadgeService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('Sniper2');

        foreach (range(1, 3) as $n) {
            $r = $this->makeRound($season, $n);
            $this->addPoints($player, $season, $r, 6);
        }

        $lastRound = Round::where('season_id', $season->id)->orderByDesc('number')->first();
        $service->awardBadges($lastRound, $season);

        $badge = $this->getBadge($player, $season, 'sniper');
        $this->assertNotNull($badge);
        $this->assertSame('rakija', $badge->tier);
    }

    public function test_awards_sniper_zlato_for_3_rounds_with_8_plus_points(): void
    {
        $service = app(BadgeService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('Sniper3');

        foreach (range(1, 3) as $n) {
            $r = $this->makeRound($season, $n);
            $this->addPoints($player, $season, $r, 8);
        }

        $lastRound = Round::where('season_id', $season->id)->orderByDesc('number')->first();
        $service->awardBadges($lastRound, $season);

        $badge = $this->getBadge($player, $season, 'sniper');
        $this->assertNotNull($badge);
        $this->assertSame('zlato', $badge->tier);
    }

    public function test_does_not_award_sniper_for_only_1_round_with_4_plus_points(): void
    {
        $service = app(BadgeService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('NoSniper');

        $r = $this->makeRound($season, 1);
        $this->addPoints($player, $season, $r, 5);

        $service->awardBadges($r, $season);

        $this->assertNull($this->getBadge($player, $season, 'sniper'));
    }

    // ================================================================
    // Perfectionist badge
    // ================================================================

    public function test_awards_perfectionist_kafa_for_1_perfect_round(): void
    {
        $service = app(BadgeService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('Perf1');

        $r = $this->makeRound($season, 1);
        $this->addPoints($player, $season, $r, 10, true);

        $service->awardBadges($r, $season);

        $badge = $this->getBadge($player, $season, 'perfectionist');
        $this->assertSame('kafa', $badge->tier);
    }

    public function test_awards_perfectionist_zlato_for_3_perfect_rounds(): void
    {
        $service = app(BadgeService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('Perf3');

        foreach (range(1, 3) as $n) {
            $r = $this->makeRound($season, $n);
            $this->addPoints($player, $season, $r, 10, true);
        }

        $lastRound = Round::where('season_id', $season->id)->orderByDesc('number')->first();
        $service->awardBadges($lastRound, $season);

        $badge = $this->getBadge($player, $season, 'perfectionist');
        $this->assertSame('zlato', $badge->tier);
    }

    // ================================================================
    // Iron Man badge
    // ================================================================

    public function test_awards_iron_man_kafa_for_3_consecutive_rounds(): void
    {
        $service = app(BadgeService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('Iron1');

        foreach (range(1, 3) as $n) {
            $r = $this->makeRound($season, $n);
            $this->addPoints($player, $season, $r, 3);
        }

        $lastRound = Round::where('season_id', $season->id)->orderByDesc('number')->first();
        $service->awardBadges($lastRound, $season);

        $badge = $this->getBadge($player, $season, 'iron_man');
        $this->assertSame('kafa', $badge->tier);
    }

    public function test_does_not_award_iron_man_when_streak_is_broken(): void
    {
        $service = app(BadgeService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('BrokenIron');

        $r1 = $this->makeRound($season, 1);
        $r2 = $this->makeRound($season, 2);
        $this->makeRound($season, 3); // gap - no points
        $r4 = $this->makeRound($season, 4);
        $r5 = $this->makeRound($season, 5);

        $this->addPoints($player, $season, $r1, 3);
        $this->addPoints($player, $season, $r2, 3);
        // skip round 3
        $this->addPoints($player, $season, $r4, 3);
        $this->addPoints($player, $season, $r5, 3);

        $service->awardBadges($r5, $season);

        // Max consecutive streak = 2, not enough for kafa (needs 3)
        $this->assertNull($this->getBadge($player, $season, 'iron_man'));
    }

    // ================================================================
    // Comeback Kid badge
    // ================================================================

    public function test_awards_comeback_kid_kafa_for_one_comeback(): void
    {
        $service = app(BadgeService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('Comeback');

        $r1 = $this->makeRound($season, 1);
        $r2 = $this->makeRound($season, 2);

        $this->addPoints($player, $season, $r1, 2); // cold round
        $this->addPoints($player, $season, $r2, 5); // hot bounce-back

        $service->awardBadges($r2, $season);

        $badge = $this->getBadge($player, $season, 'comeback_kid');
        $this->assertSame('kafa', $badge->tier);
    }

    // ================================================================
    // Jackpot badge
    // ================================================================

    public function test_awards_jackpot_kafa_for_1_payout_jackpot_transaction(): void
    {
        $service = app(BadgeService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('Jackpotter');

        $r = $this->makeRound($season, 1);
        $this->addPoints($player, $season, $r, 10, true);

        TokenTransaction::create([
            'player_id' => $player->id,
            'amount' => 100,
            'type' => 'payout_jackpot',
            'description' => 'Jackpot win',
            'balance_before' => 0,
            'balance_after' => 100,
        ]);

        $service->awardBadges($r, $season);

        $badge = $this->getBadge($player, $season, 'jackpot');
        $this->assertSame('kafa', $badge->tier);
    }

    // ================================================================
    // Ledeni badge
    // ================================================================

    public function test_awards_ledeni_kafa_for_6_cold_rounds(): void
    {
        $service = app(BadgeService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('Ledeni1');

        foreach (range(1, 6) as $n) {
            $r = $this->makeRound($season, $n);
            $this->addPoints($player, $season, $r, 1);
        }

        $lastRound = Round::where('season_id', $season->id)->orderByDesc('number')->first();
        $service->awardBadges($lastRound, $season);

        $badge = $this->getBadge($player, $season, 'ledeni');
        $this->assertSame('kafa', $badge->tier);
    }

    // ================================================================
    // Upgrade-only
    // ================================================================

    public function test_upgrades_badge_but_never_downgrades(): void
    {
        $service = app(BadgeService::class);
        $season = $this->makeSeason();
        $player = $this->makePlayer('Upgrader');

        PlayerBadge::create([
            'player_id' => $player->id,
            'season_id' => $season->id,
            'category' => 'sniper',
            'tier' => 'kafa',
            'earned_at' => now(),
        ]);

        // Now qualify for rakija
        foreach (range(1, 3) as $n) {
            $r = $this->makeRound($season, $n);
            $this->addPoints($player, $season, $r, 6);
        }

        $lastRound = Round::where('season_id', $season->id)->orderByDesc('number')->first();
        $service->awardBadges($lastRound, $season);

        $badge = $this->getBadge($player, $season, 'sniper');
        $this->assertSame('rakija', $badge->tier);

        // Only one row per category
        $this->assertSame(1, PlayerBadge::where('player_id', $player->id)
            ->where('season_id', $season->id)
            ->where('category', 'sniper')
            ->count());
    }
}
