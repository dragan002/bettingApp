<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonPoints extends Model
{
    protected $table = 'season_points';

    protected $fillable = [
        'season_id',
        'player_id',
        'points',
        'rounds_played',
    ];

    protected $casts = [
        'points' => 'integer',
        'rounds_played' => 'integer',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function toApiArray(): array
    {
        return [
            'playerId' => $this->player_id,
            'playerName' => $this->relationLoaded('player') ? $this->player->name : null,
            'points' => $this->points,
            'roundsPlayed' => $this->rounds_played,
        ];
    }
}
