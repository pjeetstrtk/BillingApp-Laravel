<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard.index', [
            'totalCustomers' => Customer::count(),
            'totalProducts'  => Product::count(),
            'totalBillings'  => Billing::count(),
            'pendingAmount'  => (float) Billing::sum('balance_amount'),
            'recent'         => Billing::with('customer')->orderByDesc('bill_date')->limit(5)->get(),
        ]);
    }
}
