<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonSettlement extends Model
{
    protected $fillable = [
        'season_id',
        'player_id',
        'settled_amount',
        'settled_at',
    ];

    protected $casts = [
        'settled_amount' => 'integer',
        'settled_at' => 'datetime',
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
            'id' => $this->id,
            'seasonId' => $this->season_id,
            'playerId' => $this->player_id,
            'displayName' => $this->relationLoaded('player')
                ? ($this->player?->displayName() ?? '')
                : '',
            'settledAmount' => $this->settled_amount,
            'settledAt' => $this->settled_at?->toISOString(),
        ];
    }
}
