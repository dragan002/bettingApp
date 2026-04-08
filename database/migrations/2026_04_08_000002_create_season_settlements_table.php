<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('season_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->integer('settled_amount');
            $table->timestamp('settled_at');
            $table->timestamps();

            $table->unique(['season_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('season_settlements');
    }
};
