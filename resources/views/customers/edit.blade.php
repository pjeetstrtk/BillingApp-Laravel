@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<h2>Edit Customer</h2>
<form method="POST" action="{{ route('customers.update', $customer) }}" class="card p-4 shadow-sm mt-3">
    @csrf
    @method('PUT')
    @include('customers._form')
    <div>
        <button class="btn btn-primary">Update</button>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection
