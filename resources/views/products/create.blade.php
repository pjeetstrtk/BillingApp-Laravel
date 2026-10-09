@extends('layouts.app')

@section('title', 'New Product')

@section('content')
<h2>New Product</h2>
<form method="POST" action="{{ route('products.store') }}" class="card p-4 shadow-sm mt-3">
    @csrf
    @include('products._form')
    <div>
        <button class="btn btn-primary">Save</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection
