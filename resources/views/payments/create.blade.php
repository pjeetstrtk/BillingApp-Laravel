@extends('layouts.app')

@section('title', 'Add Payment')

@section('content')
<h2>Add Payment</h2>

<div class="card shadow-sm p-4 mt-3">
    <div class="alert alert-info">
        <b>Invoice:</b> {{ $billing->invoice_no }}<br />
        <b>Customer:</b> {{ $billing->customer?->name }}<br />
        <b>Total:</b> Rs. {{ number_format($billing->total_amount, 2) }} &nbsp; | &nbsp;
        <b>Paid:</b> Rs. {{ number_format($billing->paid_amount, 2) }} &nbsp; | &nbsp;
        <b class="text-danger">Balance:</b> Rs. {{ number_format($billing->balance_amount, 2) }}
    </div>

    <form method="POST" action="{{ route('billings.payments.store', $billing) }}">
        @csrf
        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input id="amount" name="amount" type="number" step="0.01" min="0.01" class="form-control"
                   value="{{ old('amount', $billing->balance_amount) }}" required />
        </div>
        <div class="mb-3">
            <label for="method" class="form-label">Method</label>
            <select id="method" name="method" class="form-select">
                @foreach ($methods as $m)
                    <option @selected(old('method', 'Cash') === $m)>{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="reference" class="form-label">Reference</label>
            <input id="reference" name="reference" class="form-control" value="{{ old('reference') }}"
                   maxlength="100" placeholder="Txn ID / Cheque No (optional)" />
        </div>
        <button class="btn btn-success">Save Payment</button>
        <a href="{{ route('billings.show', $billing) }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
