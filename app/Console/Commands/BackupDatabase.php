<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database';

    protected $description = 'Backup SQLite database to Cloudflare R2';

    public function handle(): int
    {
        $dbPath = config('database.connections.sqlite.database');

        if (! file_exists($dbPath)) {
            Log::error('Backup failed: database file not found', ['path' => $dbPath]);
            $this->error('Database file not found: '.$dbPath);

            return 1;
        }

        $filename = 'backup_'.now()->format('Y-m-d_H-i-s').'.sqlite';

        try {
            $contents = file_get_contents($dbPath);
            Storage::disk('r2')->put($filename, $contents);

            // Keep only the last 30 backups
            $files = Storage::disk('r2')->files();
            sort($files);

            if (count($files) > 30) {
                $toDelete = array_slice($files, 0, count($files) - 30);
                Storage::disk('r2')->delete($toDelete);
                Log::info('Old backups pruned', ['deleted' => count($toDelete)]);
            }

            Log::info('Database backup successful', ['file' => $filename]);
            $this->info("Backup created: {$filename}");

            return 0;
        } catch (\Throwable $e) {
            Log::error('Backup failed', ['error' => $e->getMessage()]);
            $this->error('Backup failed: '.$e->getMessage());

            return 1;
        }
    }
}
