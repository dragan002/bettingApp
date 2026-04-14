<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\Player;
use App\Models\PlayerToken;
use App\Models\Prediction;
use App\Models\Round;
use App\Models\RoundEntry;
use App\Models\Season;
use App\Models\SeasonPoints;
use App\Models\SeasonRoundPoints;
use App\Models\TokenTransaction;
use App\Services\RoundResolveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoundResolveTest extends TestCase
{
    use RefreshDatabase;

    private static int $fixtureCounter = 0;

    private function makeAdmin(): Player
    {
        return Player::create([
            'name' => 'Admin',
            'pin' => Hash::make('1234'),
            'is_admin' => true,
            'token_balance' => 0,
        ]);
    }

    private function makePlayer(string $name = 'Player', int $balance = 0): Player
    {
        return Player::create([
            'name' => $name,
            'pin' => Hash::make('1234'),
            'is_admin' => false,
            'token_balance' => $balance,
        ]);
    }

    private function makeToken(Player $player): string
    {
        $token = Str::random(64);
        PlayerToken::create(['player_id' => $player->id, 'token' => $token]);
        return $token;
    }

    private function makeSeason(int $jackpot = 100): Season
    {
        return Season::create([
            'league_id' => 'PL',
            'league_name' => 'Premier League',
            'status' => 'active',
            'jackpot' => $jackpot,
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

    private function makeFinishedFixture(Round $round, int $homeScore, int $awayScore): Fixture
    {
        self::$fixtureCounter++;
        return Fixture::create([
            'round_id' => $round->id,
            'external_id' => 'ext-rr-' . self::$fixtureCounter,
            'home_team' => 'Home',
            'away_team' => 'Away',
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'status' => 'finished',
            'kickoff_at' => now()->subHour()->toDateTimeString(),
        ]);
    }

    private function makePostponedFixture(Round $round): Fixture
    {
        self::$fixtureCounter++;
        return Fixture::create([
            'round_id' => $round->id,
            'external_id' => 'ext-rr-p-' . self::$fixtureCounter,
            'home_team' => 'Home P',
            'away_team' => 'Away P',
            'status' => 'postponed',
            'kickoff_at' => now()->addDays(7)->toDateTimeString(),
        ]);
    }

    private function addPrediction(Player $player, Fixture $fixture, string $pick): void
    {
        Prediction::create([
            'player_id' => $player->id,
            'fixture_id' => $fixture->id,
            'pick' => $pick,
        ]);
    }

    private function addEntry(Player $player, Round $round, bool $isComplete = true): RoundEntry
    {
        return RoundEntry::create([
            'round_id' => $round->id,
            'player_id' => $player->id,
            'is_complete' => $isComplete,
            'is_perfect' => false,
            'points' => 0,
        ]);
    }

    private function addSeasonPoints(Player $player, Season $season): void
    {
        SeasonPoints::create([
            'season_id' => $season->id,
            'player_id' => $player->id,
            'points' => 0,
            'rounds_played' => 0,
        ]);
    }

    // ================================================================
    // Scoring
    // ================================================================

    public function test_scores_predictions_correctly_and_marks_is_correct(): void
    {
        $service = app(RoundResolveService::class);
        $season = $this->makeSeason(0);
        $round = $this->makeRound($season);
        $player = $this->makePlayer('Alice');

        $f = $this->makeFinishedFixture($round, 2, 0); // home wins → '1'
        $this->addPrediction($player, $f, '1');
        $this->addEntry($player, $round);
        $this->addSeasonPoints($player, $season);

        $service->resolve($round);

        $prediction = Prediction::where('player_id', $player->id)->first();
        $this->assertTrue($prediction->is_correct);
    }

    public function test_writes_season_round_points_after_resolution(): void
    {
        $service = app(RoundResolveService::class);
        $season = $this->makeSeason(0);
        $round = $this->makeRound($season);
        $player = $this->makePlayer('Bob');

        $f = $this->makeFinishedFixture($round, 1, 1); // draw → 'X'
        $this->addPrediction($player, $f, 'X');
        $this->addEntry($player, $round);
        $this->addSeasonPoints($player, $season);

        $service->resolve($round);

        $srp = SeasonRoundPoints::where('player_id', $player->id)
            ->where('round_id', $round->id)
            ->first();

        $this->assertNotNull($srp);
        $this->assertSame(1, $srp->points);
    }

    public function test_marks_round_as_resolved_after_service_call(): void
    {
        $service = app(RoundResolveService::class);
        $season = $this->makeSeason(0);
        $round = $this->makeRound($season);
        $player = $this->makePlayer('Carol');

        $f = $this->makeFinishedFixture($round, 1, 0);
        $this->addPrediction($player, $f, '1');
        $this->addEntry($player, $round);
        $this->addSeasonPoints($player, $season);

        $service->resolve($round);

        $this->assertSame('resolved', $round->fresh()->status);
    }

    // ================================================================
    // Jackpot
    // ================================================================

    public function test_awards_jackpot_to_the_perfect_predictor(): void
    {
        $service = app(RoundResolveService::class);
        $season = $this->makeSeason(200);
        $round = $this->makeRound($season);
        $player = $this->makePlayer('Dave', 0);

        $f = $this->makeFinishedFixture($round, 3, 0); // '1'
        $this->addPrediction($player, $f, '1');
        $this->addEntry($player, $round);
        $this->addSeasonPoints($player, $season);

        $service->resolve($round);

        $this->assertSame(200, $player->fresh()->token_balance);
        $this->assertSame(0, $season->fresh()->jackpot);

        $tx = TokenTransaction::where('player_id', $player->id)
            ->where('type', 'payout_jackpot')
            ->first();
        $this->assertNotNull($tx);
        $this->assertSame(200, $tx->amount);
    }

    public function test_splits_jackpot_equally_among_multiple_perfect_predictors(): void
    {
        $service = app(RoundResolveService::class);
        $season = $this->makeSeason(100);
        $round = $this->makeRound($season);

        $p1 = $this->makePlayer('Eve', 0);
        $p2 = $this->makePlayer('Frank', 0);

        $f = $this->makeFinishedFixture($round, 1, 0); // '1'
        $this->addPrediction($p1, $f, '1');
        $this->addPrediction($p2, $f, '1');
        $this->addEntry($p1, $round);
        $this->addEntry($p2, $round);
        $this->addSeasonPoints($p1, $season);
        $this->addSeasonPoints($p2, $season);

        $service->resolve($round);

        $this->assertSame(50, $p1->fresh()->token_balance);
        $this->assertSame(50, $p2->fresh()->token_balance);
        $this->assertSame(0, $season->fresh()->jackpot);
    }

    public function test_does_not_award_jackpot_when_entry_is_not_complete(): void
    {
        $service = app(RoundResolveService::class);
        $season = $this->makeSeason(100);
        $round = $this->makeRound($season);
        $player = $this->makePlayer('Grace', 0);

        $f1 = $this->makeFinishedFixture($round, 1, 0);
        $this->makeFinishedFixture($round, 0, 1);

        $this->addPrediction($player, $f1, '1');
        RoundEntry::create([
            'round_id' => $round->id,
            'player_id' => $player->id,
            'is_complete' => false,
            'is_perfect' => false,
            'points' => 0,
        ]);
        $this->addSeasonPoints($player, $season);

        $service->resolve($round);

        $this->assertSame(0, $player->fresh()->token_balance);
        $this->assertSame(100, $season->fresh()->jackpot); // untouched
    }

    // ================================================================
    // Postponed fixtures excluded
    // ================================================================

    public function test_excludes_postponed_fixtures_from_scoring_totals(): void
    {
        $service = app(RoundResolveService::class);
        $season = $this->makeSeason(0);
        $round = $this->makeRound($season);
        $player = $this->makePlayer('Hank', 0);

        $active = $this->makeFinishedFixture($round, 1, 0); // '1'
        $this->makePostponedFixture($round);

        $this->addPrediction($player, $active, '1');
        $this->addEntry($player, $round);
        $this->addSeasonPoints($player, $season);

        $service->resolve($round);

        $entry = RoundEntry::where('player_id', $player->id)->first();
        $this->assertSame(1, $entry->points);
        $this->assertTrue($entry->is_perfect); // 1 active fixture, predicted correctly
    }

    // ================================================================
    // Admin resolve endpoint
    // ================================================================

    public function test_returns_422_when_round_is_already_resolved(): void
    {
        $admin = $this->makeAdmin();
        $token = $this->makeToken($admin);
        $season = $this->makeSeason(0);
        $round = $this->makeRound($season, 'resolved');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/admin/rounds/' . $round->id . '/resolve');

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Round already resolved']);
    }
}
