<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prediction extends Model
{
    protected $fillable = [
        'player_id',
        'fixture_id',
        'pick',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(Fixture::class);
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'playerId' => $this->player_id,
            'fixtureId' => $this->fixture_id,
            'pick' => $this->pick,
            'isCorrect' => $this->is_correct,
        ];
    }
}
