<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerBadge extends Model
{
    const CATEGORIES = [
        'sniper',
        'perfectionist',
        'iron_man',
        'comeback_kid',
        'jackpot',
        'ledeni',
    ];

    const TIERS = [
        'kafa' => 1,
        'rakija' => 2,
        'zlato' => 3,
    ];

    protected $fillable = [
        'player_id',
        'season_id',
        'category',
        'tier',
        'earned_at',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'playerId' => $this->player_id,
            'seasonId' => $this->season_id,
            'category' => $this->category,
            'tier' => $this->tier,
            'earnedAt' => $this->earned_at?->toISOString(),
        ];
    }
}
