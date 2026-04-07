<?php

namespace App\Providers;

use App\Services\FootballDataService;
use App\Services\RoundResolveService;
use App\Services\RoundSyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FootballDataService::class);
        $this->app->singleton(RoundSyncService::class);
        $this->app->singleton(RoundResolveService::class);
    }

    public function boot(): void
    {
        // Enable WAL mode for SQLite concurrency
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA journal_mode=WAL;');
        }
    }
}
