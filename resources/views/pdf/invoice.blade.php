<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $billing->invoice_no }}</title>
    <style>
        @page { margin: 40px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #212121; }
        table { width: 100%; border-collapse: collapse; }
        .muted { color: #757575; }
        .small { font-size: 9px; }
        .right { text-align: right; }
        .center { text-align: center; }
        .brand { font-size: 20px; font-weight: bold; color: #1976d2; }
        .title { font-size: 24px; font-weight: bold; color: #1976d2; }
        .rule { border-top: 1px solid #bdbdbd; margin: 10px 0 15px; }
        .label { font-size: 9px; font-weight: bold; color: #616161; }
        .items th { background: #1976d2; color: #fff; padding: 6px; }
        .items td { padding: 6px; }
        .items tr.alt td { background: #f5f5f5; }
        .history th { background: #e0e0e0; padding: 5px; text-align: left; }
        .history td { padding: 5px; }
        .totals { width: 250px; margin-left: auto; margin-top: 15px; }
        .totals td { padding: 3px 0; }
        .balance td { border-top: 1px solid #9e9e9e; padding-top: 5px; font-weight: bold; color: #d32f2f; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 9px; font-style: italic; border-top: 1px solid #bdbdbd; padding-top: 5px; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td>
                <div class="brand">BillingApp Pvt Ltd</div>
                <div class="small muted">123 Business Street, Dhaka 1207</div>
                <div class="small muted">Phone: +880 1700-000000 | Email: info@billingapp.com</div>
            </td>
            <td class="right" style="width: 190px;">
                <div class="title">INVOICE</div>
                <div># {{ $billing->invoice_no }}</div>
                <div>Date: {{ $billing->bill_date->format('d M Y') }}</div>
                <div><b>Status: {{ $billing->status }}</b></div>
            </td>
        </tr>
    </table>
    <div class="rule"></div>

    <div class="label">BILL TO</div>
    <div style="font-size: 12px; font-weight: bold;">{{ $billing->customer?->name ?? '-' }}</div>
    @if ($billing->customer?->email)<div>{{ $billing->customer->email }}</div>@endif
    @if ($billing->customer?->phone)<div>{{ $billing->customer->phone }}</div>@endif
    @if ($billing->customer?->address)<div>{{ $billing->customer->address }}</div>@endif

    <br>
    <table class="items">
        <thead>
            <tr>
                <th style="width: 30px; text-align: left;">#</th>
                <th style="text-align: left;">Product</th>
                <th class="right">Unit Price</th>
                <th class="center">Qty</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($billing->items as $item)
                <tr @class(['alt' => $loop->iteration % 2 === 0])>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->product?->name ?? '-' }}</td>
                    <td class="right">Rs. {{ number_format($item->unit_price, 2) }}</td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="right">Rs. {{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td><b>Subtotal</b></td><td class="right">Rs. {{ number_format($billing->total_amount, 2) }}</td></tr>
        <tr><td><b>Paid</b></td><td class="right">Rs. {{ number_format($billing->paid_amount, 2) }}</td></tr>
        <tr class="balance"><td>Balance Due</td><td class="right">Rs. {{ number_format($billing->balance_amount, 2) }}</td></tr>
    </table>

    @if ($billing->payments->isNotEmpty())
        <div style="margin-top: 20px; font-weight: bold;">Payment History</div>
        <table class="history" style="margin-top: 5px;">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th class="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($billing->payments as $p)
                    <tr>
                        <td>{{ $p->payment_date->format('d M Y') }}</td>
                        <td>{{ $p->method }}</td>
                        <td>{{ $p->reference ?? '-' }}</td>
                        <td class="right">Rs. {{ number_format($p->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">Thank you for your business!</div>
</body>
</html>
