<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;

/** Plain-SQL PostgreSQL backups made with pg_dump (restore with psql). */
class DatabaseBackup
{
    public function directory(): string
    {
        $dir = (string) config('backup.path');
        File::ensureDirectoryExists($dir);

        return $dir;
    }

    /** Create a backup and return its file name. */
    public function create(): string
    {
        $connection = config('database.default');
        $db = config("database.connections.{$connection}");

        if (($db['driver'] ?? null) !== 'pgsql') {
            throw new RuntimeException('Backups use pg_dump and only work with the pgsql connection.');
        }

        $name   = sprintf('%s-%s.sql', $db['database'], now()->format('Ymd-His'));
        $target = $this->directory() . DIRECTORY_SEPARATOR . $name;

        $process = new Process(
            [
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
            ],
            null,
            ($db['password'] ?? '') !== '' ? ['PGPASSWORD' => (string) $db['password']] : null,
        );
        $process->setTimeout((float) config('backup.timeout'));
        $process->run();

        if (! $process->isSuccessful() || ! File::exists($target)) {
            File::delete($target);

            $error = trim($process->getErrorOutput() ?: $process->getOutput());

            throw new RuntimeException(
                ($error !== '' ? $error : 'pg_dump failed.') . ' (Check PG_DUMP_PATH in .env points to pg_dump and that its version is not older than the server.)'
            );
        }

        $this->prune();

        return $name;
    }

    /** @return Collection<int, array{name: string, size: int, created: Carbon}> newest first */
    public function all(): Collection
    {
        return collect(File::glob($this->directory() . DIRECTORY_SEPARATOR . '*.sql'))
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
        if (! preg_match('/^[A-Za-z0-9._-]+\.sql$/', $name) || str_contains($name, '..')) {
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
}
