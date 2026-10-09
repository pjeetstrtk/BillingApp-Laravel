@extends('layouts.app')

@section('title', 'Billing')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h2>Billings</h2>
    <a href="{{ route('billings.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Invoice</a>
</div>

<div class="card shadow-sm">
    <table class="table mb-0">
        <thead>
            <tr><th>Invoice</th><th>Customer</th><th>Date</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th width="220"></th></tr>
        </thead>
        <tbody>
        @foreach ($billings as $b)
            <tr>
                <td>{{ $b->invoice_no }}</td>
                <td>{{ $b->customer?->name }}</td>
                <td>{{ $b->bill_date->format('d M Y') }}</td>
                <td>Rs. {{ number_format($b->total_amount, 2) }}</td>
                <td>Rs. {{ number_format($b->paid_amount, 2) }}</td>
                <td>Rs. {{ number_format($b->balance_amount, 2) }}</td>
                <td><span class="badge bg-{{ $b->badgeClass() }}">{{ $b->status }}</span></td>
                <td>
                    <a href="{{ route('billings.show', $b) }}" class="btn btn-sm btn-info">View</a>
                    <a href="{{ route('invoices.pdf', $b) }}" class="btn btn-sm btn-success">PDF</a>
                    @if ($b->status !== 'Paid')
                        <a href="{{ route('billings.payments.create', $b) }}" class="btn btn-sm btn-warning">Pay</a>
                    @endif
                    <a href="{{ route('billings.delete', $b) }}" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
