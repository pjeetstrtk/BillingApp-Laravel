<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(): View
    {
        $billings = Billing::with('customer')->orderByDesc('bill_date')->get();

        return view('billings.index', compact('billings'));
    }

    public function create(): View
    {
        return view('billings.create', [
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'products'  => Product::where('stock', '>', 0)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id'     => ['required', 'integer', 'exists:customers,id'],
            'product_ids'     => ['required', 'array', 'min:1'],
            'product_ids.*'   => ['required', 'integer', 'exists:products,id'],
            'quantities'      => ['required', 'array'],
            'quantities.*'    => ['required', 'integer', 'min:1'],
        ], [
            'customer_id.required' => 'Select a customer and at least one product.',
            'product_ids.required' => 'Select a customer and at least one product.',
            'product_ids.min'      => 'Select a customer and at least one product.',
        ]);

        $productIds = array_values($data['product_ids']);
        $quantities = array_values($data['quantities']);

        if (count($productIds) !== count($quantities)) {
            throw ValidationException::withMessages(['quantities' => 'Every product needs a quantity.']);
        }

        $billing = DB::transaction(function () use ($data, $productIds, $quantities) {
            $billing = Billing::create([
                'invoice_no'     => Billing::generateInvoiceNo(),
                'customer_id'    => $data['customer_id'],
                'bill_date'      => now(),
                'total_amount'   => 0,
                'paid_amount'    => 0,
                'balance_amount' => 0,
                'status'         => Billing::STATUS_PENDING,
            ]);

            $total = 0.0;

            foreach ($productIds as $i => $productId) {
                $qty = (int) $quantities[$i];

                // Lock the row so concurrent invoices can't oversell the same stock.
                $product = Product::lockForUpdate()->findOrFail($productId);

                if ($product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'product_ids' => "Not enough stock for {$product->name} (available: {$product->stock}).",
                    ]);
                }

                $unitPrice = (float) $product->price;
                $lineTotal = round($unitPrice * $qty, 2);

                $billing->items()->create([
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);

                $product->decrement('stock', $qty);
                $total += $lineTotal;
            }

            $total = round($total, 2);
            $billing->update([
                'total_amount'   => $total,
                'paid_amount'    => 0,
                'balance_amount' => $total,
            ]);

            return $billing;
        });

        return redirect()->route('billings.payments.create', $billing);
    }

    public function show(Billing $billing): View
    {
        $billing->load('customer', 'items.product', 'payments');

        return view('billings.show', compact('billing'));
    }

    /** Delete confirmation page. */
    public function delete(Billing $billing): View
    {
        $billing->load('customer');

        return view('billings.delete', compact('billing'));
    }

    public function destroy(Billing $billing): RedirectResponse
    {
        // Items and payments are removed by the ON DELETE CASCADE foreign keys.
        $billing->delete();

        return redirect()->route('billings.index')->with('success', 'Invoice deleted.');
    }
}
