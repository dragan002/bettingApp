<?php

namespace Tests\Feature;

use App\Models\Player;
use App\Models\PlayerToken;
use App\Models\Season;
use App\Models\SeasonSettlement;
use App\Models\TokenTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class TokenLedgerTest extends TestCase
{
    use RefreshDatabase;

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

    private function makeSeason(string $status = 'active'): Season
    {
        return Season::create([
            'league_id' => 'PL',
            'league_name' => 'Premier League',
            'status' => $status,
            'jackpot' => 0,
            'entry_tokens' => 5,
        ]);
    }

    // ================================================================
    // TokenTransaction ledger
    // ================================================================

    public function test_records_balance_before_and_after_on_admin_credit(): void
    {
        $admin = $this->makeAdmin();
        $adminToken = $this->makeToken($admin);
        $player = $this->makePlayer('Alice', 20);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $adminToken])
            ->postJson('/api/admin/players/' . $player->id . '/credit', [
                'amount' => 15,
                'description' => 'Top-up',
            ]);

        $response->assertStatus(200);

        $tx = TokenTransaction::where('player_id', $player->id)
            ->where('type', 'credit')
            ->firstOrFail();

        $this->assertSame(20, $tx->balance_before);
        $this->assertSame(35, $tx->balance_after);
        $this->assertSame(15, $tx->amount);
        $this->assertSame(35, $player->fresh()->token_balance);
    }

    public function test_returns_transaction_list_from_ledger_endpoint(): void
    {
        $admin = $this->makeAdmin();
        $adminToken = $this->makeToken($admin);
        $player = $this->makePlayer('Bob', 10);

        TokenTransaction::create([
            'player_id' => $player->id,
            'amount' => 10,
            'type' => 'credit',
            'description' => 'Test credit',
            'balance_before' => 0,
            'balance_after' => 10,
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $adminToken])
            ->getJson('/api/players/' . $player->id . '/ledger');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'player' => ['id', 'name'],
                'transactions' => [['id', 'amount', 'type', 'balanceBefore', 'balanceAfter']],
                'meta' => ['currentPage', 'lastPage', 'total'],
            ]);

        $data = $response->json();
        $this->assertSame(0, $data['transactions'][0]['balanceBefore']);
        $this->assertSame(10, $data['transactions'][0]['balanceAfter']);
    }

    public function test_requires_at_least_1_token_for_admin_credit(): void
    {
        $admin = $this->makeAdmin();
        $adminToken = $this->makeToken($admin);
        $player = $this->makePlayer('Carol', 10);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $adminToken])
            ->postJson('/api/admin/players/' . $player->id . '/credit', [
                'amount' => 0,
            ]);

        $response->assertStatus(422);
    }

    // ================================================================
    // Settlement flow
    // ================================================================

    public function test_blocks_new_season_creation_when_season_is_pending_settlement(): void
    {
        $admin = $this->makeAdmin();
        $adminToken = $this->makeToken($admin);
        $this->makeSeason('pending_settlement');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $adminToken])
            ->postJson('/api/admin/season', [
                'league_id' => 'PL',
                'league_name' => 'Premier League',
                'entry_tokens' => 5,
            ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => 'Cannot start a new season while the current season is pending settlement',
        ]);
    }

    public function test_moves_active_season_to_pending_settlement(): void
    {
        $admin = $this->makeAdmin();
        $adminToken = $this->makeToken($admin);
        $season = $this->makeSeason('active');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $adminToken])
            ->postJson('/api/admin/season/pending-settlement');

        $response->assertStatus(200);
        $this->assertSame('pending_settlement', $season->fresh()->status);
    }

    public function test_returns_422_when_no_active_season_for_pending_settlement(): void
    {
        $admin = $this->makeAdmin();
        $adminToken = $this->makeToken($admin);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $adminToken])
            ->postJson('/api/admin/season/pending-settlement');

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'No active season']);
    }

    public function test_settles_a_player_and_zeroes_their_balance(): void
    {
        $admin = $this->makeAdmin();
        $adminToken = $this->makeToken($admin);
        $season = $this->makeSeason('pending_settlement');
        $player = $this->makePlayer('Dave', 30);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $adminToken])
            ->postJson('/api/admin/season/settlements/' . $player->id);

        $response->assertStatus(200)
            ->assertJsonStructure(['settlement' => ['playerId', 'displayName', 'settledAmount', 'settledAt']]);

        $this->assertSame(0, $player->fresh()->token_balance);

        $settlement = SeasonSettlement::where('season_id', $season->id)
            ->where('player_id', $player->id)
            ->firstOrFail();

        $this->assertSame(30, $settlement->settled_amount);

        $tx = TokenTransaction::where('player_id', $player->id)
            ->where('type', 'settlement_refund')
            ->first();
        $this->assertNotNull($tx);
        $this->assertSame(30, $tx->balance_before);
        $this->assertSame(0, $tx->balance_after);
    }

    public function test_records_settlement_collected_for_negative_balance(): void
    {
        $admin = $this->makeAdmin();
        $adminToken = $this->makeToken($admin);
        $this->makeSeason('pending_settlement');
        $player = $this->makePlayer('Erin', -10);

        $this->withHeaders(['Authorization' => 'Bearer ' . $adminToken])
            ->postJson('/api/admin/season/settlements/' . $player->id);

        $tx = TokenTransaction::where('player_id', $player->id)
            ->where('type', 'settlement_collected')
            ->first();
        $this->assertNotNull($tx);
        $this->assertSame(-10, $tx->balance_before);
        $this->assertSame(0, $tx->balance_after);
    }

    public function test_prevents_settling_same_player_twice(): void
    {
        $admin = $this->makeAdmin();
        $adminToken = $this->makeToken($admin);
        $this->makeSeason('pending_settlement');
        $player = $this->makePlayer('Frank', 20);

        $this->withHeaders(['Authorization' => 'Bearer ' . $adminToken])
            ->postJson('/api/admin/season/settlements/' . $player->id);

        $second = $this->withHeaders(['Authorization' => 'Bearer ' . $adminToken])
            ->postJson('/api/admin/season/settlements/' . $player->id);

        $second->assertStatus(422);
        $second->assertJsonFragment(['message' => 'Player already settled']);
    }

    public function test_blocks_season_close_until_all_players_are_settled(): void
    {
        $admin = $this->makeAdmin();
        $adminToken = $this->makeToken($admin);
        $this->makeSeason('pending_settlement');
        $this->makePlayer('Grace', 10); // unsettled

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $adminToken])
            ->postJson('/api/admin/season/close');

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => 'All players must be settled before closing the season',
        ]);
    }

    public function test_closes_season_and_writes_hall_of_fame_when_all_players_settled(): void
    {
        $admin = $this->makeAdmin();
        $adminToken = $this->makeToken($admin);
        $season = $this->makeSeason('pending_settlement');

        // Settle ALL players in the DB (includes the seeded 'dragan' admin from migration)
        $allPlayers = \App\Models\Player::all();
        foreach ($allPlayers as $player) {
            $this->withHeaders(['Authorization' => 'Bearer ' . $adminToken])
                ->postJson('/api/admin/season/settlements/' . $player->id);
        }

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $adminToken])
            ->postJson('/api/admin/season/close');

        $response->assertStatus(200)
            ->assertJsonStructure(['hallOfFame' => ['id', 'seasonId', 'totalRounds', 'closedAt']]);

        $this->assertSame('ended', $season->fresh()->status);
        $this->assertTrue(\App\Models\SeasonHallOfFame::where('season_id', $season->id)->exists());
    }

    public function test_lists_settlement_index_with_unsettled_and_settled_players(): void
    {
        $admin = $this->makeAdmin();
        $adminToken = $this->makeToken($admin);
        $this->makeSeason('pending_settlement');
        $player = $this->makePlayer('Henry', 5);

        // Settle admin only
        $this->withHeaders(['Authorization' => 'Bearer ' . $adminToken])
            ->postJson('/api/admin/season/settlements/' . $admin->id);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $adminToken])
            ->getJson('/api/admin/season/settlements');

        $response->assertStatus(200)
            ->assertJsonStructure(['seasonId', 'unsettled', 'settled']);

        $data = $response->json();
        $settledIds = collect($data['settled'])->pluck('playerId')->toArray();
        $unsettledIds = collect($data['unsettled'])->pluck('playerId')->toArray();

        $this->assertContains($admin->id, $settledIds);
        $this->assertContains($player->id, $unsettledIds);
    }
}
