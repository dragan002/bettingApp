<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Round;
use App\Models\Season;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sync fixtures for active round daily at 08:00 UTC
Schedule::call(function () {
    $season = Season::where('status', 'active')->first();
    if (! $season) {
        return;
    }
    $round = Round::where('season_id', $season->id)
        ->whereIn('status', ['pending', 'active'])
        ->first();
    if (! $round) {
        return;
    }
    $round->load('season');
    app(\App\Services\RoundSyncService::class)->syncFixtures($round);
})->dailyAt('08:00')->name('sync-fixtures')->withoutOverlapping();

// Daily database backup to Cloudflare R2 at 03:00 UTC
Schedule::command('backup:database')->dailyAt('03:00')->name('backup-database')->withoutOverlapping();

// Sync results for active/locked round every 3 hours
Schedule::call(function () {
    $season = Season::where('status', 'active')->first();
    if (! $season) {
        return;
    }
    $round = Round::where('season_id', $season->id)
        ->whereIn('status', ['active', 'locked'])
        ->first();
    if (! $round) {
        return;
    }
    $round->load('season');
    app(\App\Services\RoundSyncService::class)->syncResults($round);
})->everyThreeHours()->name('sync-results')->withoutOverlapping();
