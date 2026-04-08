<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('season_round_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->integer('points');
            $table->boolean('is_perfect')->default(false);
            $table->timestamps();

            $table->unique(['round_id', 'player_id']);
            $table->index(['season_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('season_round_points');
    }
};
