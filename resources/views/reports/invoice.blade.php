@extends('layouts.app')

@section('title', 'Invoice Print')

@section('content')
<div class="card shadow-sm p-4">
    <div class="text-center mb-4">
        <h2>INVOICE</h2>
        <p class="text-muted">BillingApp Pvt Ltd</p>
    </div>
    <div class="row mb-4">
        <div class="col-md-6">
            <h5>Bill To:</h5>
            <p>
                <b>{{ $billing->customer?->name }}</b><br />
                {{ $billing->customer?->email }}<br />
                {{ $billing->customer?->phone }}<br />
                {{ $billing->customer?->address }}
            </p>
        </div>
        <div class="col-md-6 text-end">
            <p>
                <b>Invoice #:</b> {{ $billing->invoice_no }}<br />
                <b>Date:</b> {{ $billing->bill_date->format('d M Y') }}<br />
                <b>Status:</b> {{ $billing->status }}
            </p>
        </div>
    </div>
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Unit Price</th>
                <th>Qty</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($billing->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->product?->name }}</td>
                    <td>Rs. {{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td class="text-end">Rs. {{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-end">Total</th>
                <th class="text-end">Rs. {{ number_format($billing->total_amount, 2) }}</th>
            </tr>
            <tr>
                <th colspan="4" class="text-end">Paid</th>
                <th class="text-end">Rs. {{ number_format($billing->paid_amount, 2) }}</th>
            </tr>
            <tr class="table-warning">
                <th colspan="4" class="text-end">Balance</th>
                <th class="text-end">Rs. {{ number_format($billing->balance_amount, 2) }}</th>
            </tr>
        </tfoot>
    </table>
    <div class="text-center mt-4 d-print-none">
        <a href="{{ route('invoices.pdf', $billing) }}" class="btn btn-success">
            <i class="bi bi-file-earmark-pdf"></i> Download PDF
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Print
        </button>
        <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>
@endsection
