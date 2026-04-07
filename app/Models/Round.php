<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Round extends Model
{
    protected $fillable = [
        'season_id',
        'number',
        'status',
        'locks_at',
    ];

    protected $casts = [
        'locks_at' => 'datetime',
        'number' => 'integer',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function fixtures(): HasMany
    {
        return $this->hasMany(Fixture::class);
    }

    public function roundEntries(): HasMany
    {
        return $this->hasMany(RoundEntry::class);
    }

    public function isLocked(): bool
    {
        if ($this->status === 'locked' || $this->status === 'resolved') {
            return true;
        }

        return $this->locks_at !== null && $this->locks_at->isPast();
    }

    public function activeFixtures()
    {
        return $this->fixtures()->whereNotIn('status', ['postponed', 'cancelled']);
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status,
            'locksAt' => $this->locks_at?->toISOString(),
            'isLocked' => $this->isLocked(),
            'fixtures' => $this->relationLoaded('fixtures')
                ? $this->fixtures->map->toApiArray()->values()->all()
                : [],
        ];
    }
}
