<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('tier');
            $table->timestamp('earned_at');
            $table->timestamps();

            $table->unique(['player_id', 'season_id', 'category']);
            $table->index(['season_id', 'category', 'tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_badges');
    }
};
