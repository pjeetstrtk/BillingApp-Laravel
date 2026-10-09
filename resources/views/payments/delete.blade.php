@extends('layouts.app')

@section('title', 'Delete Payment')

@section('content')
<h2 class="text-danger">Delete Payment</h2>
<div class="alert alert-warning">
    Delete payment of <b>Rs. {{ number_format($payment->amount, 2) }}</b> from invoice <b>{{ $payment->billing?->invoice_no }}</b>?
</div>
<form method="POST" action="{{ route('payments.destroy', $payment) }}">
    @csrf
    @method('DELETE')
    <button class="btn btn-danger">Yes, Delete</button>
    <a href="{{ route('payments.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
