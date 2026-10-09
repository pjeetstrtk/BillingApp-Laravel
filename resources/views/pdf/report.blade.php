<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        @page { margin: 30px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #212121; }
        table { width: 100%; border-collapse: collapse; }
        .right { text-align: right; }
        .center { text-align: center; }
        .title { font-size: 20px; font-weight: bold; color: #1976d2; }
        .period { font-size: 11px; color: #757575; }
        .rule { border-top: 1px solid #bdbdbd; margin: 10px 0 15px; }
        .kpi td { padding: 10px; }
        .kpi .cap { font-size: 9px; color: #757575; }
        .kpi .val { font-size: 14px; font-weight: bold; }
        .data th { background: #1976d2; color: #fff; padding: 6px; text-align: left; }
        .data td { padding: 5px; }
        .data tr.alt td { background: #f5f5f5; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 9px; color: #757575; }
    </style>
</head>
<body>
    <div class="title">Sales Report</div>
    <div class="period">Period: {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}</div>
    <div class="rule"></div>

    <table class="kpi" style="border-spacing: 10px; border-collapse: separate; margin: -10px;">
        <tr>
            <td style="background: #bbdefb; width: 33%;">
                <div class="cap">Total Billed</div>
                <div class="val" style="color: #1976d2;">Rs. {{ number_format($totalBilled, 2) }}</div>
            </td>
            <td style="background: #c8e6c9; width: 33%;">
                <div class="cap">Total Paid</div>
                <div class="val" style="color: #388e3c;">Rs. {{ number_format($totalPaid, 2) }}</div>
            </td>
            <td style="background: #ffcdd2; width: 33%;">
                <div class="cap">Balance</div>
                <div class="val" style="color: #d32f2f;">Rs. {{ number_format($totalBalance, 2) }}</div>
            </td>
        </tr>
    </table>

    <br><br>
    <table class="data">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Date</th>
                <th>Customer</th>
                <th class="right">Total</th>
                <th class="right">Paid</th>
                <th class="right">Balance</th>
                <th class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($billings as $b)
                <tr @class(['alt' => $loop->iteration % 2 === 0])>
                    <td>{{ $b->invoice_no }}</td>
                    <td>{{ $b->bill_date->format('d M Y') }}</td>
                    <td>{{ $b->customer?->name ?? '-' }}</td>
                    <td class="right">Rs. {{ number_format($b->total_amount, 2) }}</td>
                    <td class="right">Rs. {{ number_format($b->paid_amount, 2) }}</td>
                    <td class="right">Rs. {{ number_format($b->balance_amount, 2) }}</td>
                    <td class="center">{{ $b->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="right muted" style="margin-top: 10px; font-size: 9px; color: #757575;">Total records: {{ $billings->count() }}</div>

    <div class="footer">Generated on {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
