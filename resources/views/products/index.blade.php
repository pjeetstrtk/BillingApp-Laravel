@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h2>Products</h2>
    <a href="{{ route('products.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Product</a>
</div>

<form method="GET" action="{{ route('products.index') }}" class="mb-3">
    <div class="input-group">
        <input name="search" class="form-control" placeholder="Search products..." value="{{ $search }}" />
        <button class="btn btn-outline-secondary">Search</button>
    </div>
</form>

<div class="card shadow-sm">
    <table class="table mb-0">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Stock</th>
                <th width="160"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $p)
                <tr>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->description }}</td>
                    <td>Rs. {{ number_format($p->price, 2) }}</td>
                    <td>{{ $p->stock }}</td>
                    <td>
                        <a href="{{ route('products.edit', $p) }}" class="btn btn-sm btn-warning">Edit</a>
                        <a href="{{ route('products.delete', $p) }}" class="btn btn-sm btn-danger">Delete</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
