<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    protected $fillable = [
        'league_id',
        'league_name',
        'status',
        'jackpot',
        'entry_tokens',
    ];

    protected $casts = [
        'jackpot' => 'integer',
        'entry_tokens' => 'integer',
    ];

    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class);
    }

    public function seasonPoints(): HasMany
    {
        return $this->hasMany(SeasonPoints::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function toApiArray(): array
    {
        return [
            'id'                   => $this->id,
            'leagueId'             => $this->league_id,
            'leagueName'           => $this->league_name,
            'status'               => $this->status,
            'jackpot'              => $this->jackpot,
            'entryTokens'          => $this->entry_tokens,
            'isPendingSettlement'  => $this->status === 'pending_settlement',
        ];
    }
}
