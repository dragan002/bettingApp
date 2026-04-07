<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->integer('number');
            $table->string('status')->default('pending'); // pending | active | locked | resolved
            $table->timestamp('locks_at')->nullable();
            $table->timestamps();

            $table->unique(['season_id', 'number']);
            $table->index(['season_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
