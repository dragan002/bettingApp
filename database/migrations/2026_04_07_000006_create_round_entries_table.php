<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('round_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_complete')->default(false);
            $table->boolean('is_perfect')->default(false);
            $table->integer('points')->default(0);
            $table->timestamps();

            $table->unique(['round_id', 'player_id']);
            $table->index(['round_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('round_entries');
    }
};
