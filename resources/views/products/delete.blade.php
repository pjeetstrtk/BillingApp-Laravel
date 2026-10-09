@extends('layouts.app')

@section('title', 'Delete Product')

@section('content')
<h2 class="text-danger">Delete Product</h2>
<div class="alert alert-warning">Are you sure you want to delete <b>{{ $product->name }}</b>?</div>
<form method="POST" action="{{ route('products.destroy', $product) }}">
    @csrf
    @method('DELETE')
    <button class="btn btn-danger">Yes, Delete</button>
    <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
