<?php

namespace App\Providers;

use App\Services\BadgeService;
use App\Services\FootballDataService;
use App\Services\RoundResolveService;
use App\Services\RoundSyncService;
use App\Services\StreakService;
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
        $this->app->singleton(BadgeService::class);
        $this->app->singleton(StreakService::class);
        $this->app->singleton(RoundResolveService::class);
    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Enable WAL mode for SQLite concurrency
        if (config('database.default') === 'sqlite' && file_exists(config('database.connections.sqlite.database'))) {
            DB::statement('PRAGMA journal_mode=WAL;');
        }
    }
}
