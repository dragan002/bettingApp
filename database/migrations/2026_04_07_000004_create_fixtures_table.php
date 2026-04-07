<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained()->cascadeOnDelete();
            $table->string('external_id')->nullable();   // football-data.org match id
            $table->string('home_team');
            $table->string('away_team');
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->string('status')->default('scheduled'); // scheduled|live|finished|postponed|cancelled
            $table->timestamp('kickoff_at')->nullable();
            $table->timestamps();

            $table->index(['round_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};
