@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div class="d-flex justify-content-between mb-3 d-print-none">
    <h2>Invoice: {{ $billing->invoice_no }}</h2>
    <div>
        <a href="{{ route('invoices.pdf', $billing) }}" class="btn btn-success">
            <i class="bi bi-file-earmark-pdf"></i> Download PDF
        </a>
        <a href="{{ route('invoices.show', $billing) }}" class="btn btn-info">View Invoice</a>
        @if ($billing->status !== 'Paid')
            <a href="{{ route('billings.payments.create', $billing) }}" class="btn btn-warning">Add Payment</a>
        @endif
        <a href="{{ route('billings.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>

<div class="card shadow-sm p-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h5>Customer</h5>
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
                <b>Date:</b> {{ $billing->bill_date->format('d M Y H:i') }}<br />
                <b>Status:</b>
                <span class="badge bg-{{ $billing->badgeClass() }}">{{ $billing->status }}</span>
            </p>
        </div>
    </div>

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Qty</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($billing->items as $item)
                <tr>
                    <td>{{ $item->product?->name }}</td>
                    <td>Rs. {{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td class="text-end">Rs. {{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Subtotal</th>
                <th class="text-end">Rs. {{ number_format($billing->total_amount, 2) }}</th>
            </tr>
            <tr>
                <th colspan="3" class="text-end">Paid</th>
                <th class="text-end">Rs. {{ number_format($billing->paid_amount, 2) }}</th>
            </tr>
            <tr class="table-warning">
                <th colspan="3" class="text-end">Balance</th>
                <th class="text-end">Rs. {{ number_format($billing->balance_amount, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    @if ($billing->payments->isNotEmpty())
        <h5>Payments</h5>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th class="text-end">Amount</th>
                    <th class="d-print-none"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($billing->payments as $p)
                    <tr>
                        <td>{{ $p->payment_date->format('d M Y') }}</td>
                        <td>{{ $p->method }}</td>
                        <td>{{ $p->reference }}</td>
                        <td class="text-end">Rs. {{ number_format($p->amount, 2) }}</td>
                        <td class="d-print-none"><a href="{{ route('payments.delete', $p) }}" class="btn btn-sm btn-danger">Delete</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
