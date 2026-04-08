<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('token_transactions', function (Blueprint $table) {
            $table->integer('balance_before')->nullable()->after('description');
            $table->integer('balance_after')->nullable()->after('balance_before');
            $table->foreignId('round_id')->nullable()->after('balance_after')
                ->constrained()->nullOnDelete();

            $table->index('round_id');
        });
    }

    public function down(): void
    {
        Schema::table('token_transactions', function (Blueprint $table) {
            $table->dropForeign(['round_id']);
            $table->dropIndex(['round_id']);
            $table->dropColumn(['balance_before', 'balance_after', 'round_id']);
        });
    }
};
