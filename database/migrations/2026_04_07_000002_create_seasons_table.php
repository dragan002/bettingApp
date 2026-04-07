<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('league_id');       // e.g. "PL", "PD", "BL1"
            $table->string('league_name');     // display name
            $table->string('status')->default('active'); // active | ended
            $table->integer('jackpot')->default(0);      // token total
            $table->integer('entry_tokens')->default(5); // cost per round
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
