@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<h2>Edit Product</h2>
<form method="POST" action="{{ route('products.update', $product) }}" class="card p-4 shadow-sm mt-3">
    @csrf
    @method('PUT')
    @include('products._form')
    <div>
        <button class="btn btn-primary">Update</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection
