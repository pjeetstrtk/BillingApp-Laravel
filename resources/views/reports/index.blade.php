@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<h2 class="mb-3">Reports</h2>

<form method="GET" action="{{ route('reports.index') }}" class="card p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label" for="from">From</label>
            <input id="from" type="date" name="from" class="form-control" value="{{ $from }}" />
        </div>
        <div class="col-md-3">
            <label class="form-label" for="to">To</label>
            <input id="to" type="date" name="to" class="form-control" value="{{ $to }}" />
        </div>
        <div class="col-md-6">
            <button class="btn btn-primary">Filter</button>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Reset</a>
            <button type="submit" formaction="{{ route('reports.pdf') }}" class="btn btn-success">
                <i class="bi bi-file-earmark-pdf"></i> Download PDF
            </button>
        </div>
    </div>
</form>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="stat-card" style="background:#2563eb;">
            <h6>Total Billed</h6>
            <h2>Rs. {{ number_format($totalBilled, 2) }}</h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background:#059669;">
            <h6>Total Paid</h6>
            <h2>Rs. {{ number_format($totalPaid, 2) }}</h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background:#dc2626;">
            <h6>Total Balance</h6>
            <h2>Rs. {{ number_format($totalBalance, 2) }}</h2>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <table class="table mb-0">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Date</th>
                <th>Customer</th>
                <th class="text-end">Total</th>
                <th class="text-end">Paid</th>
                <th class="text-end">Balance</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($billings as $b)
                <tr>
                    <td>{{ $b->invoice_no }}</td>
                    <td>{{ $b->bill_date->format('d M Y') }}</td>
                    <td>{{ $b->customer?->name }}</td>
                    <td class="text-end">Rs. {{ number_format($b->total_amount, 2) }}</td>
                    <td class="text-end">Rs. {{ number_format($b->paid_amount, 2) }}</td>
                    <td class="text-end">Rs. {{ number_format($b->balance_amount, 2) }}</td>
                    <td><span class="badge bg-{{ $b->badgeClass() }}">{{ $b->status }}</span></td>
                    <td>
                        <a href="{{ route('invoices.show', $b) }}" class="btn btn-sm btn-outline-primary">View</a>
                        <a href="{{ route('invoices.pdf', $b) }}" class="btn btn-sm btn-success">PDF</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
