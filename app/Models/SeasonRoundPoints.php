<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonRoundPoints extends Model
{
    protected $fillable = [
        'season_id',
        'round_id',
        'player_id',
        'points',
        'is_perfect',
    ];

    protected $casts = [
        'points' => 'integer',
        'is_perfect' => 'boolean',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'seasonId' => $this->season_id,
            'roundId' => $this->round_id,
            'playerId' => $this->player_id,
            'points' => $this->points,
            'isPerfect' => $this->is_perfect,
        ];
    }
}
