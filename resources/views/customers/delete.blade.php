@extends('layouts.app')

@section('title', 'Delete Customer')

@section('content')
<h2 class="text-danger">Delete Customer</h2>
<div class="alert alert-warning">Are you sure you want to delete <b>{{ $customer->name }}</b>?</div>
<form method="POST" action="{{ route('customers.destroy', $customer) }}">
    @csrf
    @method('DELETE')
    <button class="btn btn-danger">Yes, Delete</button>
    <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
