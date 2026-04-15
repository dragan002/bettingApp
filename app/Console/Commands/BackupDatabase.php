<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database';

    protected $description = 'Backup database to Cloudflare R2 (SQLite or PostgreSQL)';

    public function handle(): int
    {
        $driver = config('database.default');

        if ($driver === 'sqlite') {
            return $this->backupSqlite();
        }

        if ($driver === 'pgsql') {
            return $this->backupPostgres();
        }

        $this->error("Unsupported database driver for backup: {$driver}");

        return 1;
    }

    private function backupSqlite(): int
    {
        $dbPath = config('database.connections.sqlite.database');

        if (! file_exists($dbPath)) {
            Log::error('Backup failed: SQLite database file not found', ['path' => $dbPath]);
            $this->error('Database file not found: '.$dbPath);

            return 1;
        }

        $filename = 'backup_'.now()->format('Y-m-d_H-i-s').'.sqlite';

        try {
            $contents = file_get_contents($dbPath);
            Storage::disk('r2')->put($filename, $contents);
            $this->pruneOldBackups('.sqlite');
            Log::info('SQLite backup successful', ['file' => $filename]);
            $this->info("Backup created: {$filename}");

            return 0;
        } catch (\Throwable $e) {
            Log::error('SQLite backup failed', ['error' => $e->getMessage()]);
            $this->error('Backup failed: '.$e->getMessage());

            return 1;
        }
    }

    private function backupPostgres(): int
    {
        $conn     = config('database.connections.pgsql');
        $url      = env('DATABASE_URL');

        // Build connection parts — prefer DATABASE_URL if set
        if ($url) {
            $parsed   = parse_url($url);
            $host     = $parsed['host'] ?? '127.0.0.1';
            $port     = $parsed['port'] ?? 5432;
            $dbname   = ltrim($parsed['path'] ?? 'railway', '/');
            $user     = $parsed['user'] ?? '';
            $password = $parsed['pass'] ?? '';
        } else {
            $host     = $conn['host'];
            $port     = $conn['port'];
            $dbname   = $conn['database'];
            $user     = $conn['username'];
            $password = $conn['password'];
        }

        $tmpFile  = sys_get_temp_dir().'/db_backup_'.now()->format('Y-m-d_H-i-s').'.sql';
        $filename = 'backup_'.now()->format('Y-m-d_H-i-s').'.sql';

        // pg_dump uses PGPASSWORD env var to avoid interactive password prompt
        $cmd = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %d -U %s -d %s --no-owner --no-acl -f %s 2>&1',
            escapeshellarg($password),
            escapeshellarg($host),
            (int) $port,
            escapeshellarg($user),
            escapeshellarg($dbname),
            escapeshellarg($tmpFile)
        );

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            $detail = implode("\n", $output);
            Log::error('pg_dump failed', ['exit' => $exitCode, 'output' => $detail]);
            $this->error("pg_dump failed (exit {$exitCode}): {$detail}");

            return 1;
        }

        try {
            $contents = file_get_contents($tmpFile);
            @unlink($tmpFile);

            Storage::disk('r2')->put($filename, $contents);
            $this->pruneOldBackups('.sql');

            Log::info('PostgreSQL backup successful', ['file' => $filename]);
            $this->info("Backup created: {$filename}");

            return 0;
        } catch (\Throwable $e) {
            @unlink($tmpFile);
            Log::error('PostgreSQL backup upload failed', ['error' => $e->getMessage()]);
            $this->error('Backup upload failed: '.$e->getMessage());

            return 1;
        }
    }

    private function pruneOldBackups(string $ext): void
    {
        try {
            $files = collect(Storage::disk('r2')->files())
                ->filter(fn ($f) => str_ends_with($f, $ext))
                ->sort()
                ->values();

            if ($files->count() > 30) {
                $toDelete = $files->slice(0, $files->count() - 30)->all();
                Storage::disk('r2')->delete($toDelete);
                Log::info('Old backups pruned', ['deleted' => count($toDelete)]);
            }
        } catch (\Throwable $e) {
            Log::warning('Backup pruning failed', ['error' => $e->getMessage()]);
        }
    }
}
