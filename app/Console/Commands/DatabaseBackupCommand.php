<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackup;
use Illuminate\Console\Command;
use Throwable;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'db:backup';

    protected $description = 'Back up the database (SQLite file copy, mysqldump or pg_dump) to storage/app/backups; old backups are pruned';

    public function handle(DatabaseBackup $backup): int
    {
        try {
            $name = $backup->create();
        } catch (Throwable $e) {
            $this->error('Backup failed: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info("Backup created: {$name}");

        return self::SUCCESS;
    }
}
