<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerToken extends Model
{
    protected $fillable = [
        'player_id',
        'token',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
