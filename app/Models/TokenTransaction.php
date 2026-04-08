<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenTransaction extends Model
{
    const TYPES = [
        'credit',
        'debit_round',
        'payout_jackpot',
        'payout_season_winner',
        'settlement_refund',
        'settlement_collected',
        'adjustment',
    ];

    protected $fillable = [
        'player_id',
        'amount',
        'type',
        'description',
        'balance_before',
        'balance_after',
        'round_id',
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
        'round_id' => 'integer',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'playerId' => $this->player_id,
            'amount' => $this->amount,
            'type' => $this->type,
            'description' => $this->description,
            'balanceBefore' => $this->balance_before,
            'balanceAfter' => $this->balance_after,
            'roundId' => $this->round_id,
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
}
