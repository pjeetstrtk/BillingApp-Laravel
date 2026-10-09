<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);

        $billings = $this->billingsBetween($from, $to);

        return view('reports.index', [
            'billings'     => $billings,
            'from'         => $from->toDateString(),
            'to'           => $to->toDateString(),
            'totalBilled'  => (float) $billings->sum('total_amount'),
            'totalPaid'    => (float) $billings->sum('paid_amount'),
            'totalBalance' => (float) $billings->sum('balance_amount'),
        ]);
    }

    /** Printable HTML invoice. */
    public function invoice(Billing $billing): View
    {
        $billing->load('customer', 'items.product', 'payments');

        return view('reports.invoice', compact('billing'));
    }

    public function downloadInvoice(Billing $billing): Response
    {
        $billing->load('customer', 'items.product', 'payments');

        return Pdf::loadView('pdf.invoice', compact('billing'))
            ->setPaper('a4')
            ->download("Invoice-{$billing->invoice_no}.pdf");
    }

    public function downloadReport(Request $request): Response
    {
        [$from, $to] = $this->dateRange($request);

        $billings = $this->billingsBetween($from, $to);

        return Pdf::loadView('pdf.report', [
            'billings'     => $billings,
            'from'         => $from,
            'to'           => $to,
            'totalBilled'  => (float) $billings->sum('total_amount'),
            'totalPaid'    => (float) $billings->sum('paid_amount'),
            'totalBalance' => (float) $billings->sum('balance_amount'),
        ])
            ->setPaper('a4', 'landscape')
            ->download('SalesReport-' . $from->format('Ymd') . '-to-' . $to->format('Ymd') . '.pdf');
    }

    /** @return array{0: Carbon, 1: Carbon} inclusive start/end of the selected days */
    private function dateRange(Request $request): array
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date'],
        ]);

        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->subMonth()->startOfDay();

        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        return [$from, $to];
    }

    private function billingsBetween(Carbon $from, Carbon $to)
    {
        return Billing::with('customer')
            ->whereBetween('bill_date', [$from, $to])
            ->orderByDesc('bill_date')
            ->get();
    }
}
