<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlashScoreService
{
    /**
     * Map from football-data.org league codes to FlashScore tournament identifiers.
     */
    private const LEAGUE_MAP = [
        'PL' => ['template_id' => 'dYlOSQOD', 'season_id' => 187],
    ];

    private string $apiKey;
    private string $apiHost = 'flashscore4.p.rapidapi.com';
    private string $baseUrl = 'https://flashscore4.p.rapidapi.com/api/flashscore/v2';

    public function __construct()
    {
        $this->apiKey = config('services.flashscore.key', '');
    }

    public function isConfigured(string $leagueId): bool
    {
        return $this->apiKey !== '' && isset(self::LEAGUE_MAP[$leagueId]);
    }

    /**
     * Fetch the next upcoming matchday fixtures for the given league.
     *
     * FlashScore does not expose matchday numbers, so we detect gameweek
     * boundaries by watching for the first repeated team — in a round-robin
     * league no team appears twice in the same matchday.
     */
    public function getNextMatchdayFixtures(string $leagueId): array
    {
        if (! $this->isConfigured($leagueId)) {
            return [];
        }

        $ids = self::LEAGUE_MAP[$leagueId];

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'x-rapidapi-host' => $this->apiHost,
                    'x-rapidapi-key' => $this->apiKey,
                ])
                ->get("{$this->baseUrl}/tournaments/fixtures", [
                    'tournament_template_id' => $ids['template_id'],
                    'season_id' => $ids['season_id'],
                    'page' => 1,
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('FlashScore getNextMatchdayFixtures timeout', [
                'leagueId' => $leagueId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }

        if (! $response->successful()) {
            Log::error('FlashScore API error', [
                'leagueId' => $leagueId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        }

        return $this->extractNextMatchday($response->json() ?? []);
    }

    /**
     * Extract the first matchday cluster from a list of upcoming fixtures.
     *
     * Stop at the first fixture whose home or away team already appeared —
     * that signals the start of the next matchday.
     */
    private function extractNextMatchday(array $fixtures): array
    {
        $seenTeams = [];
        $matchday = [];

        foreach ($fixtures as $fixture) {
            $homeId = $fixture['home_team']['team_id'] ?? null;
            $awayId = $fixture['away_team']['team_id'] ?? null;

            if ($homeId === null || $awayId === null) {
                continue;
            }

            if (isset($seenTeams[$homeId]) || isset($seenTeams[$awayId])) {
                break;
            }

            $seenTeams[$homeId] = true;
            $seenTeams[$awayId] = true;
            $matchday[] = $fixture;
        }

        return $matchday;
    }

    public function mapMatchToFixture(array $match): array
    {
        $kickoff = isset($match['timestamp'])
            ? Carbon::createFromTimestamp($match['timestamp'])->toDateTimeString()
            : null;

        return [
            'external_id' => 'fs_' . $match['match_id'],
            'home_team' => $match['home_team']['name'],
            'away_team' => $match['away_team']['name'],
            'home_score' => null,
            'away_score' => null,
            'status' => 'scheduled',
            'kickoff_at' => $kickoff,
        ];
    }
}
