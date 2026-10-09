@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<h2 class="mb-3">Payments</h2>

<div class="card shadow-sm">
    <table class="table mb-0">
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoice</th>
                <th>Customer</th>
                <th>Method</th>
                <th>Reference</th>
                <th class="text-end">Amount</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payments as $p)
                <tr>
                    <td>{{ $p->payment_date->format('d M Y') }}</td>
                    <td>{{ $p->billing?->invoice_no }}</td>
                    <td>{{ $p->billing?->customer?->name }}</td>
                    <td>{{ $p->method }}</td>
                    <td>{{ $p->reference }}</td>
                    <td class="text-end">Rs. {{ number_format($p->amount, 2) }}</td>
                    <td>
                        <a href="{{ route('billings.show', $p->billing_id) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('payments.delete', $p) }}" class="btn btn-sm btn-danger">Delete</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
