<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackup;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class BackupController extends Controller
{
    public function index(DatabaseBackup $backup): View
    {
        return view('backups.index', [
            'backups'     => $backup->all(),
            'restoreHint' => $backup->restoreHint(),
        ]);
    }

    public function store(DatabaseBackup $backup): RedirectResponse
    {
        try {
            $name = $backup->create();
        } catch (Throwable $e) {
            return redirect()->route('backups.index')->with('error', 'Backup failed: ' . $e->getMessage());
        }

        return redirect()->route('backups.index')->with('success', "Backup created: {$name}");
    }

    public function download(DatabaseBackup $backup, string $file): BinaryFileResponse
    {
        $path = $backup->pathFor($file);
        abort_if($path === null, 404);

        return response()->download($path);
    }

    public function destroy(DatabaseBackup $backup, string $file): RedirectResponse
    {
        abort_unless($backup->delete($file), 404);

        return redirect()->route('backups.index')->with('success', 'Backup deleted.');
    }
}
