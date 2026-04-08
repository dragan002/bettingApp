<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('season_hall_of_fame', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('jackpot_winner_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('leaderboard_winner_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('player_of_season_id')->nullable()->constrained('players')->nullOnDelete();
            $table->integer('total_jackpot');
            $table->integer('total_rounds');
            $table->timestamp('closed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('season_hall_of_fame');
    }
};
