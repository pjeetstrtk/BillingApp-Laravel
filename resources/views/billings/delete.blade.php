@extends('layouts.app')

@section('title', 'Delete Billing')

@section('content')
<h2 class="text-danger">Delete Invoice</h2>
<div class="alert alert-warning">
    Delete invoice <b>{{ $billing->invoice_no }}</b> for customer <b>{{ $billing->customer?->name }}</b>?<br />
    This will remove all associated items and payments.
</div>
<form method="POST" action="{{ route('billings.destroy', $billing) }}">
    @csrf
    @method('DELETE')
    <button class="btn btn-danger">Yes, Delete</button>
    <a href="{{ route('billings.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
