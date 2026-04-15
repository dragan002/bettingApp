<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FootballDataService
{
    private string $baseUrl = 'https://api.football-data.org/v4';
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.football_data.key', '');
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::timeout(15);
        if ($this->apiKey !== '') {
            $client = $client->withHeaders(['X-Auth-Token' => $this->apiKey]);
        }

        return $client;
    }

    public function getCurrentMatchday(string $leagueId): ?int
    {
        $response = $this->http()
            ->get("{$this->baseUrl}/competitions/{$leagueId}");

        if (! $response->successful()) {
            Log::error('football-data.org getCurrentMatchday error', [
                'leagueId' => $leagueId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        return $response->json('currentSeason.currentMatchday');
    }

    public function getMatches(string $leagueId, int $matchday): array
    {
        try {
            $response = $this->http()
                ->get("{$this->baseUrl}/competitions/{$leagueId}/matches", [
                    'matchday' => $matchday,
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('football-data.org getMatches timeout', [
                'leagueId' => $leagueId,
                'matchday' => $matchday,
                'error' => $e->getMessage(),
            ]);

            return [];
        }

        if (! $response->successful()) {
            Log::error('football-data.org error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        }

        return $response->json('matches', []);
    }

    public function getMatch(int $externalId): ?array
    {
        $response = $this->http()
            ->get("{$this->baseUrl}/matches/{$externalId}");

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    public function getFinishedMatches(string $leagueId, int $matchday): array
    {
        try {
            $response = $this->http()
                ->get("{$this->baseUrl}/competitions/{$leagueId}/matches", [
                    'matchday' => $matchday,
                    'status' => 'FINISHED',
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('football-data.org getFinishedMatches timeout', [
                'leagueId' => $leagueId,
                'matchday' => $matchday,
                'error' => $e->getMessage(),
            ]);

            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        return $response->json('matches', []);
    }

    private function mapStatus(string $apiStatus): string
    {
        return match ($apiStatus) {
            'SCHEDULED', 'TIMED' => 'scheduled',
            'IN_PLAY', 'PAUSED' => 'live',
            'FINISHED' => 'finished',
            'POSTPONED' => 'postponed',
            'CANCELLED', 'SUSPENDED' => 'cancelled',
            default => 'scheduled',
        };
    }

    public function mapMatchToFixture(array $match): array
    {
        $score = $match['score']['fullTime'] ?? ['home' => null, 'away' => null];

        return [
            'external_id' => (string) $match['id'],
            'home_team' => $match['homeTeam']['shortName'] ?? $match['homeTeam']['name'],
            'away_team' => $match['awayTeam']['shortName'] ?? $match['awayTeam']['name'],
            'home_score' => $score['home'],
            'away_score' => $score['away'],
            'status' => $this->mapStatus($match['status']),
            'kickoff_at' => $match['utcDate'] ?? null,
        ];
    }
}
