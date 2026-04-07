<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->timestamps();

            $table->index(['player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_tokens');
    }
};
