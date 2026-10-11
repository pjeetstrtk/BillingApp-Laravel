<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Database backups for the active connection:
 *   sqlite -> consistent copy of the database file (.sqlite, VACUUM INTO)
 *   mysql  -> mysqldump  (.sql)
 *   pgsql  -> pg_dump    (.sql)
 */
class DatabaseBackup
{
    private const NAME_PATTERN = '/^[A-Za-z0-9._-]+\.(sql|sqlite)$/';

    private function config(): array
    {
        return config('database.connections.' . config('database.default'));
    }

    private function driver(): string
    {
        return (string) ($this->config()['driver'] ?? '');
    }

    public function directory(): string
    {
        $dir = (string) config('backup.path');
        File::ensureDirectoryExists($dir);

        return $dir;
    }

    /** Create a backup and return its file name. */
    public function create(): string
    {
        $db     = $this->config();
        $driver = $this->driver();

        if (! in_array($driver, ['sqlite', 'mysql', 'pgsql'], true)) {
            throw new RuntimeException("Backups are not supported for the '{$driver}' driver.");
        }

        $name   = $this->newFileName($driver === 'sqlite' ? basename((string) $db['database'], '.sqlite') ?: 'database' : (string) $db['database'], $driver === 'sqlite' ? 'sqlite' : 'sql');
        $target = $this->directory() . DIRECTORY_SEPARATOR . $name;

        try {
            match ($driver) {
                'sqlite' => $this->dumpSqlite($db, $target),
                'mysql'  => $this->runDump($this->mysqlCommand($db, $target), ($db['password'] ?? '') !== '' ? ['MYSQL_PWD' => (string) $db['password']] : null, 'mysqldump', 'MYSQL_DUMP_PATH'),
                'pgsql'  => $this->runDump($this->pgsqlCommand($db, $target), ($db['password'] ?? '') !== '' ? ['PGPASSWORD' => (string) $db['password']] : null, 'pg_dump', 'PG_DUMP_PATH'),
            };
        } catch (Throwable $e) {
            File::delete($target);

            throw $e instanceof RuntimeException ? $e : new RuntimeException($e->getMessage(), 0, $e);
        }

        if (! File::exists($target) || File::size($target) === 0) {
            File::delete($target);

            throw new RuntimeException('The backup file was not created.');
        }

        $this->prune();

        return $name;
    }

    /** Shell command to restore a backup, shown on the Backups page. */
    public function restoreHint(): string
    {
        $db = $this->config();

        return match ($this->driver()) {
            'sqlite' => 'Stop the app, then copy the .sqlite file over ' . basename((string) ($db['database'] ?? 'database.sqlite')) . '.',
            'mysql'  => 'mysql -u ' . ($db['username'] ?? 'root') . ' -p ' . ($db['database'] ?? '') . ' < <file>.sql',
            'pgsql'  => 'psql -U ' . ($db['username'] ?? 'postgres') . ' -d ' . ($db['database'] ?? '') . ' -f <file>.sql',
            default  => '',
        };
    }

    /** @return Collection<int, array{name: string, size: int, created: Carbon}> newest first */
    public function all(): Collection
    {
        $dir = $this->directory() . DIRECTORY_SEPARATOR;

        return collect(array_merge(File::glob($dir . '*.sql'), File::glob($dir . '*.sqlite')))
            ->map(fn (string $path) => [
                'name'    => basename($path),
                'size'    => (int) File::size($path),
                'created' => Carbon::createFromTimestamp(File::lastModified($path)),
            ])
            ->sortByDesc(fn (array $b) => $b['created']->getTimestamp() . $b['name'])
            ->values();
    }

    /** Full path of an existing backup, or null for unknown / unsafe names. */
    public function pathFor(string $name): ?string
    {
        if (! preg_match(self::NAME_PATTERN, $name) || str_contains($name, '..')) {
            return null;
        }

        $path = $this->directory() . DIRECTORY_SEPARATOR . $name;

        return File::isFile($path) ? $path : null;
    }

    public function delete(string $name): bool
    {
        $path = $this->pathFor($name);

        return $path !== null && File::delete($path);
    }

    /** Delete everything except the newest `backup.keep` files. */
    public function prune(): int
    {
        $keep = max(1, (int) config('backup.keep'));
        $old  = $this->all()->slice($keep);

        foreach ($old as $backup) {
            $this->delete($backup['name']);
        }

        return $old->count();
    }

    private function newFileName(string $base, string $extension): string
    {
        $stamp = now()->format('Ymd-His');
        $name  = "{$base}-{$stamp}.{$extension}";

        for ($i = 1; File::exists($this->directory() . DIRECTORY_SEPARATOR . $name); $i++) {
            $name = "{$base}-{$stamp}-{$i}.{$extension}";
        }

        return $name;
    }

    private function dumpSqlite(array $db, string $target): void
    {
        $source = (string) ($db['database'] ?? '');

        if ($source === '' || $source === ':memory:') {
            throw new RuntimeException('An in-memory SQLite database cannot be backed up.');
        }

        // VACUUM INTO writes a consistent, compacted copy even while the app is running.
        // It cannot run inside a transaction, so fall back to a plain file copy.
        try {
            DB::connection()->statement('VACUUM INTO ' . DB::connection()->getPdo()->quote($target));
        } catch (Throwable) {
            File::delete($target);

            if (! File::copy($source, $target)) {
                throw new RuntimeException('Could not copy the SQLite database file.');
            }
        }
    }

    private function mysqlCommand(array $db, string $target): array
    {
        return [
            (string) config('backup.mysqldump'),
            '--host=' . $db['host'],
            '--port=' . $db['port'],
            '--user=' . $db['username'],
            '--default-character-set=utf8mb4',
            '--single-transaction',
            '--no-tablespaces',
            '--result-file=' . $target,
            (string) $db['database'],
        ];
    }

    private function pgsqlCommand(array $db, string $target): array
    {
        return [
            (string) config('backup.pg_dump'),
            '--host=' . $db['host'],
            '--port=' . $db['port'],
            '--username=' . $db['username'],
            '--dbname=' . $db['database'],
            '--no-owner',
            '--no-privileges',
            '--clean',
            '--if-exists',
            '--file=' . $target,
        ];
    }

    private function runDump(array $command, ?array $env, string $tool, string $envKey): void
    {
        $process = new Process($command, null, $env);
        $process->setTimeout((float) config('backup.timeout'));
        $process->run();

        if (! $process->isSuccessful()) {
            $error = trim($process->getErrorOutput() ?: $process->getOutput());

            throw new RuntimeException(
                ($error !== '' ? $error : "{$tool} failed.") . " (Check {$envKey} in .env points to {$tool}.)"
            );
        }
    }
}
