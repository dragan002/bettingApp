<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonHallOfFame extends Model
{
    protected $table = 'season_hall_of_fame';

    protected $fillable = [
        'season_id',
        'jackpot_winner_id',
        'leaderboard_winner_id',
        'player_of_season_id',
        'total_jackpot',
        'total_rounds',
        'closed_at',
    ];

    protected $casts = [
        'total_jackpot' => 'integer',
        'total_rounds' => 'integer',
        'closed_at' => 'datetime',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function jackpotWinner(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'jackpot_winner_id');
    }

    public function leaderboardWinner(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'leaderboard_winner_id');
    }

    public function playerOfSeason(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_of_season_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'seasonId' => $this->season_id,
            'leagueName' => $this->relationLoaded('season') ? $this->season?->league_name : null,
            'totalJackpot' => $this->total_jackpot,
            'totalRounds' => $this->total_rounds,
            'closedAt' => $this->closed_at?->toISOString(),
            'jackpotWinner' => $this->relationLoaded('jackpotWinner') && $this->jackpotWinner
                ? ['id' => $this->jackpotWinner->id, 'displayName' => $this->jackpotWinner->displayName()]
                : null,
            'leaderboardWinner' => $this->relationLoaded('leaderboardWinner') && $this->leaderboardWinner
                ? ['id' => $this->leaderboardWinner->id, 'displayName' => $this->leaderboardWinner->displayName()]
                : null,
            'playerOfSeason' => $this->relationLoaded('playerOfSeason') && $this->playerOfSeason
                ? ['id' => $this->playerOfSeason->id, 'displayName' => $this->playerOfSeason->displayName()]
                : null,
        ];
    }
}
