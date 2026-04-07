<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fixture_id')->constrained()->cascadeOnDelete();
            $table->string('pick');            // '1' | 'X' | '2'
            $table->boolean('is_correct')->nullable(); // null until round resolved
            $table->timestamps();

            $table->unique(['player_id', 'fixture_id']);
            $table->index(['fixture_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
