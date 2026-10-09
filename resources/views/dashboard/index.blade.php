@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<h2 class="mb-4">Dashboard</h2>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="background:#2563eb;">
            <h6>Total Customers</h6>
            <h2>{{ $totalCustomers }}</h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background:#059669;">
            <h6>Total Products</h6>
            <h2>{{ $totalProducts }}</h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background:#7c3aed;">
            <h6>Total Billings</h6>
            <h2>{{ $totalBillings }}</h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background:#dc2626;">
            <h6>Pending Amount</h6>
            <h2>Rs. {{ number_format($pendingAmount, 2) }}</h2>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header"><b>Recent Billings</b></div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recent as $b)
                    <tr>
                        <td>{{ $b->invoice_no }}</td>
                        <td>{{ $b->customer?->name }}</td>
                        <td>{{ $b->bill_date->format('d M Y') }}</td>
                        <td>Rs. {{ number_format($b->total_amount, 2) }}</td>
                        <td><span class="badge bg-{{ $b->badgeClass() }}">{{ $b->status }}</span></td>
                        <td><a href="{{ route('billings.show', $b) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
