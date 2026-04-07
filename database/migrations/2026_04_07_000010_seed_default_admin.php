<?php

use App\Models\Player;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // Only seed if no players exist (fresh install)
        if (Player::count() === 0) {
            Player::create([
                'name' => 'dragan',
                'pin' => Hash::make('1234'),
                'is_admin' => true,
                'token_balance' => 0,
            ]);
        }
    }

    public function down(): void
    {
        Player::where('name', 'dragan')->where('is_admin', true)->delete();
    }
};
