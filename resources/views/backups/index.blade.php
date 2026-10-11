@extends('layouts.app')

@section('title', 'Backups')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h2>Database Backups</h2>
    <form method="POST" action="{{ route('backups.store') }}">
        @csrf
        <button class="btn btn-primary"><i class="bi bi-hdd"></i> Back up now</button>
    </form>
</div>

<div class="card shadow-sm">
    <table class="table mb-0">
        <thead>
            <tr><th>File</th><th>Created</th><th class="text-end">Size</th><th width="180"></th></tr>
        </thead>
        <tbody>
            @forelse ($backups as $b)
                <tr>
                    <td>{{ $b['name'] }}</td>
                    <td>{{ $b['created']->format('d M Y H:i') }}</td>
                    <td class="text-end">{{ number_format($b['size'] / 1024, 1) }} KB</td>
                    <td>
                        <a href="{{ route('backups.download', $b['name']) }}" class="btn btn-sm btn-success">Download</a>
                        <form method="POST" action="{{ route('backups.destroy', $b['name']) }}" class="d-inline"
                              onsubmit="return confirm('Delete this backup?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No backups yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<p class="text-muted small mt-3">
    Stored in <code>storage/app/backups</code>; the newest {{ config('backup.keep') }} are kept.
    Restore: <code>{{ $restoreHint }}</code>
</p>
@endsection
