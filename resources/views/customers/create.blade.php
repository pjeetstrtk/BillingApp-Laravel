@extends('layouts.app')

@section('title', 'New Customer')

@section('content')
<h2>New Customer</h2>
<form method="POST" action="{{ route('customers.store') }}" class="card p-4 shadow-sm mt-3">
    @csrf
    @include('customers._form')
    <div>
        <button class="btn btn-primary">Save</button>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection
