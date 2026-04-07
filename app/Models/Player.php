<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class Player extends Model
{
    protected $fillable = [
        'name',
        'pin',
        'is_admin',
        'token_balance',
    ];

    protected $hidden = ['pin'];

    protected $casts = [
        'is_admin' => 'boolean',
        'token_balance' => 'integer',
    ];

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    public function roundEntries(): HasMany
    {
        return $this->hasMany(RoundEntry::class);
    }

    public function tokenTransactions(): HasMany
    {
        return $this->hasMany(TokenTransaction::class);
    }

    public function seasonPoints(): HasMany
    {
        return $this->hasMany(SeasonPoints::class);
    }

    public function authTokens(): HasMany
    {
        return $this->hasMany(PlayerToken::class);
    }

    public function verifyPin(string $pin): bool
    {
        return Hash::check($pin, $this->pin);
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'isAdmin' => $this->is_admin,
            'tokenBalance' => $this->token_balance,
        ];
    }

    public function toAdminArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'isAdmin' => $this->is_admin,
            'tokenBalance' => $this->token_balance,
        ];
    }
}
