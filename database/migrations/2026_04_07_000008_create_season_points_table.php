<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('season_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->integer('points')->default(0);
            $table->integer('rounds_played')->default(0);
            $table->timestamps();

            $table->unique(['season_id', 'player_id']);
            $table->index(['season_id', 'points']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('season_points');
    }
};
