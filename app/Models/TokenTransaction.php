<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenTransaction extends Model
{
    protected $fillable = [
        'player_id',
        'amount',
        'type',
        'description',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'playerId' => $this->player_id,
            'amount' => $this->amount,
            'type' => $this->type,
            'description' => $this->description,
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
}
