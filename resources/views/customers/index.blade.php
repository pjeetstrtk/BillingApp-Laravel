@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h2>Customers</h2>
    <a href="{{ route('customers.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Customer</a>
</div>

<form method="GET" action="{{ route('customers.index') }}" class="mb-3">
    <div class="input-group">
        <input name="search" class="form-control" placeholder="Search by name or phone..." value="{{ $search }}" />
        <button class="btn btn-outline-secondary">Search</button>
    </div>
</form>

<div class="card shadow-sm">
    <table class="table mb-0">
        <thead>
            <tr><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th width="160"></th></tr>
        </thead>
        <tbody>
        @foreach ($customers as $c)
            <tr>
                <td>{{ $c->name }}</td>
                <td>{{ $c->email }}</td>
                <td>{{ $c->phone }}</td>
                <td>{{ $c->address }}</td>
                <td>
                    <a href="{{ route('customers.edit', $c) }}" class="btn btn-sm btn-warning">Edit</a>
                    <a href="{{ route('customers.delete', $c) }}" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
