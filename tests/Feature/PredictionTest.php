<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\Player;
use App\Models\PlayerToken;
use App\Models\Prediction;
use App\Models\Round;
use App\Models\RoundEntry;
use App\Models\Season;
use App\Models\TokenTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class PredictionTest extends TestCase
{
    use RefreshDatabase;

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    private function makeAdmin(): Player
    {
        return Player::create([
            'name' => 'Admin',
            'pin' => Hash::make('1234'),
            'is_admin' => true,
            'token_balance' => 100,
        ]);
    }

    private function makePlayer(string $name = 'Player', int $balance = 50): Player
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

    private function makeSeason(int $entryTokens = 5): Season
    {
        return Season::create([
            'league_id' => 'PL',
            'league_name' => 'Premier League',
            'status' => 'active',
            'jackpot' => 0,
            'entry_tokens' => $entryTokens,
        ]);
    }

    private function makeRound(Season $season, string $status = 'active', ?string $locksAt = null): Round
    {
        return Round::create([
            'season_id' => $season->id,
            'number' => 1,
            'status' => $status,
            'locks_at' => $locksAt ?? now()->addHours(2)->toDateTimeString(),
        ]);
    }

    private static int $fixtureCounter = 0;

    private function makeFixture(Round $round, string $status = 'scheduled'): Fixture
    {
        self::$fixtureCounter++;
        return Fixture::create([
            'round_id' => $round->id,
            'external_id' => 'ext-' . self::$fixtureCounter,
            'home_team' => 'Home ' . self::$fixtureCounter,
            'away_team' => 'Away ' . self::$fixtureCounter,
            'status' => $status,
            'kickoff_at' => now()->addHours(3)->toDateTimeString(),
        ]);
    }

    // ================================================================
    // Auto-charge on prediction submit
    // ================================================================

    public function test_charges_player_exactly_once_when_predictions_completed(): void
    {
        $player = $this->makePlayer('Alice', 50);
        $token = $this->makeToken($player);
        $season = $this->makeSeason(5);
        $round = $this->makeRound($season);
        $fixture = $this->makeFixture($round);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/predictions', [
                'predictions' => [(string) $fixture->id => '1'],
            ]);

        $response->assertStatus(200);

        $this->assertSame(45, $player->fresh()->token_balance);

        $this->assertSame(1, TokenTransaction::where('player_id', $player->id)
            ->where('type', 'debit_round')
            ->where('round_id', $round->id)
            ->count());

        $this->assertSame(5, $season->fresh()->jackpot);
    }

    public function test_does_not_charge_again_when_predictions_updated_after_first_complete(): void
    {
        $player = $this->makePlayer('Bob', 50);
        $token = $this->makeToken($player);
        $season = $this->makeSeason(5);
        $round = $this->makeRound($season);
        $fixture1 = $this->makeFixture($round);
        $fixture2 = $this->makeFixture($round);

        // First complete submit
        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/predictions', [
                'predictions' => [
                    (string) $fixture1->id => '1',
                    (string) $fixture2->id => 'X',
                ],
            ]);

        $this->assertSame(45, $player->fresh()->token_balance);

        // Second submit — update picks (still complete)
        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/predictions', [
                'predictions' => [
                    (string) $fixture1->id => '2',
                    (string) $fixture2->id => 'X',
                ],
            ]);

        // No second charge
        $this->assertSame(45, $player->fresh()->token_balance);
        $this->assertSame(1, TokenTransaction::where('player_id', $player->id)
            ->where('type', 'debit_round')
            ->where('round_id', $round->id)
            ->count());
    }

    public function test_records_correct_balance_before_and_after_on_auto_charge(): void
    {
        $player = $this->makePlayer('Charlie', 30);
        $token = $this->makeToken($player);
        $season = $this->makeSeason(10);
        $round = $this->makeRound($season);
        $fixture = $this->makeFixture($round);

        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/predictions', [
                'predictions' => [(string) $fixture->id => '1'],
            ]);

        $tx = TokenTransaction::where('player_id', $player->id)
            ->where('type', 'debit_round')
            ->firstOrFail();

        $this->assertSame(30, $tx->balance_before);
        $this->assertSame(20, $tx->balance_after);
        $this->assertSame(-10, $tx->amount);
    }

    public function test_returns_422_with_debt_cap_exceeded_when_balance_at_cap(): void
    {
        $season = $this->makeSeason(5);
        $player = $this->makePlayer('Debtor', -15); // exactly -(5 * 3)
        $token = $this->makeToken($player);
        $round = $this->makeRound($season);
        $fixture = $this->makeFixture($round);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/predictions', [
                'predictions' => [(string) $fixture->id => '1'],
            ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['debtCapExceeded' => true]);
    }

    public function test_allows_prediction_when_balance_is_one_above_debt_cap(): void
    {
        $season = $this->makeSeason(5);
        $player = $this->makePlayer('BoundaryPlayer', -14); // one above cap of -15
        $token = $this->makeToken($player);
        $round = $this->makeRound($season);
        $fixture = $this->makeFixture($round);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/predictions', [
                'predictions' => [(string) $fixture->id => '1'],
            ]);

        $response->assertStatus(200);
    }

    public function test_returns_422_when_round_is_locked(): void
    {
        $player = $this->makePlayer('Dave', 50);
        $token = $this->makeToken($player);
        $season = $this->makeSeason(5);
        $round = $this->makeRound($season, 'active', now()->subMinute()->toDateTimeString());
        $fixture = $this->makeFixture($round);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/predictions', [
                'predictions' => [(string) $fixture->id => '1'],
            ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Round is locked']);
    }

    public function test_returns_422_when_no_active_season(): void
    {
        $player = $this->makePlayer('Eve', 50);
        $token = $this->makeToken($player);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/predictions', [
                'predictions' => ['1' => '1'],
            ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'No active season']);
    }

    public function test_skips_postponed_fixtures_when_evaluating_completeness(): void
    {
        $player = $this->makePlayer('Frank', 50);
        $token = $this->makeToken($player);
        $season = $this->makeSeason(5);
        $round = $this->makeRound($season);
        $active = $this->makeFixture($round, 'scheduled');
        $this->makeFixture($round, 'postponed'); // should be ignored

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/predictions', [
                'predictions' => [(string) $active->id => '1'],
            ]);

        $response->assertStatus(200);

        $entry = RoundEntry::where('round_id', $round->id)
            ->where('player_id', $player->id)
            ->firstOrFail();

        $this->assertTrue($entry->is_complete);
        $this->assertSame(1, TokenTransaction::where('player_id', $player->id)
            ->where('type', 'debit_round')
            ->count());
    }

    // ================================================================
    // Debt cap enforcement
    // ================================================================

    public function test_blocks_player_whose_balance_equals_the_negative_cap_exactly(): void
    {
        $season = $this->makeSeason(10);
        $round = $this->makeRound($season);
        $fixture = $this->makeFixture($round);
        $player = $this->makePlayer('Broke', -30); // exactly -(10 * 3)
        $token = $this->makeToken($player);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/predictions', [
                'predictions' => [(string) $fixture->id => '1'],
            ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['debtCapExceeded' => true]);
    }

    public function test_blocks_player_whose_balance_is_below_the_negative_cap(): void
    {
        $season = $this->makeSeason(10);
        $round = $this->makeRound($season);
        $fixture = $this->makeFixture($round);
        $player = $this->makePlayer('Deep Broke', -50);
        $token = $this->makeToken($player);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/predictions', [
                'predictions' => [(string) $fixture->id => '1'],
            ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['debtCapExceeded' => true]);
    }
}
