<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fixture extends Model
{
    protected $fillable = [
        'round_id',
        'external_id',
        'home_team',
        'away_team',
        'home_score',
        'away_score',
        'status',
        'kickoff_at',
    ];

    protected $casts = [
        'kickoff_at' => 'datetime',
        'home_score' => 'integer',
        'away_score' => 'integer',
    ];

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    public function isActive(): bool
    {
        return ! in_array($this->status, ['postponed', 'cancelled']);
    }

    public function getResult(): ?string
    {
        if ($this->status !== 'finished' || $this->home_score === null) {
            return null;
        }

        if ($this->home_score > $this->away_score) return '1';
        if ($this->home_score === $this->away_score) return 'X';

        return '2';
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'homeTeam' => $this->home_team,
            'awayTeam' => $this->away_team,
            'homeScore' => $this->home_score,
            'awayScore' => $this->away_score,
            'status' => $this->status,
            'kickoffAt' => $this->kickoff_at?->toISOString(),
            'result' => $this->getResult(),
        ];
    }
}
