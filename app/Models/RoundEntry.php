<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoundEntry extends Model
{
    protected $fillable = [
        'round_id',
        'player_id',
        'is_complete',
        'is_perfect',
        'points',
    ];

    protected $casts = [
        'is_complete' => 'boolean',
        'is_perfect' => 'boolean',
        'points' => 'integer',
    ];

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
            'roundId' => $this->round_id,
            'playerId' => $this->player_id,
            'playerName' => $this->relationLoaded('player') ? $this->player->name : null,
            'isComplete' => $this->is_complete,
            'isPerfect' => $this->is_perfect,
            'points' => $this->points,
        ];
    }
}
