<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Billing App') - Billing App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body { background: #f4f6f9; }
        .sidebar { min-height: 100vh; background: #1f2937; color: #fff; }
        .sidebar a, .sidebar .logout-btn { color: #cbd5e1; text-decoration: none; padding: 12px 20px; display: block; width: 100%; text-align: left; background: none; border: 0; }
        .sidebar a:hover, .sidebar a.active, .sidebar .logout-btn:hover { background: #374151; color: #fff; }
        .sidebar h4 { padding: 20px; border-bottom: 1px solid #374151; }
        .content { padding: 20px; }
        .stat-card { border-radius: 12px; color: #fff; padding: 20px; }
        .stat-card h6 { opacity: 0.9; }
        @@media print {
            .sidebar, .d-print-none { display: none !important; }
            .content { width: 100% !important; max-width: 100% !important; flex: 0 0 100% !important; }
        }
    </style>
</head>
<body>
@auth
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 sidebar p-0 d-print-none">
                <h4><i class="bi bi-receipt"></i> BillingApp</h4>
                <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="{{ route('customers.index') }}" @class(['active' => request()->routeIs('customers.*')])><i class="bi bi-people"></i> Customers</a>
                <a href="{{ route('products.index') }}" @class(['active' => request()->routeIs('products.*')])><i class="bi bi-box-seam"></i> Products</a>
                <a href="{{ route('billings.index') }}" @class(['active' => request()->routeIs('billings.*')])><i class="bi bi-file-earmark-text"></i> Billing</a>
                <a href="{{ route('payments.index') }}" @class(['active' => request()->routeIs('payments.*')])><i class="bi bi-cash-coin"></i> Payments</a>
                <a href="{{ route('reports.index') }}" @class(['active' => request()->routeIs('reports.*', 'invoices.*')])><i class="bi bi-graph-up"></i> Reports</a>
                <a href="{{ route('backups.index') }}" @class(['active' => request()->routeIs('backups.*')])><i class="bi bi-hdd"></i> Backups</a>
                <hr class="text-secondary" />
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Logout</button>
                </form>
            </nav>
            <main class="col-md-10 content">
                @include('partials.alerts')
                @yield('content')
            </main>
        </div>
    </div>
@else
    <main class="container mt-5">
        @yield('content')
    </main>
@endauth
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
