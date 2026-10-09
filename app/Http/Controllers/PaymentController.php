<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public const METHODS = ['Cash', 'Card', 'UPI', 'Bank'];

    public function index(): View
    {
        $payments = Payment::with('billing.customer')->orderByDesc('payment_date')->get();

        return view('payments.index', compact('payments'));
    }

    public function create(Billing $billing): View
    {
        $billing->load('customer');

        return view('payments.create', [
            'billing' => $billing,
            'methods' => self::METHODS,
        ]);
    }

    public function store(Request $request, Billing $billing): RedirectResponse
    {
        $data = $request->validate([
            'amount'    => ['required', 'numeric', 'gt:0'],
            'method'    => ['required', 'string', 'max:30'],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($billing, $data) {
            $locked = Billing::lockForUpdate()->findOrFail($billing->id);
            $amount = round((float) $data['amount'], 2);

            if ($amount > (float) $locked->balance_amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Amount must be between 0 and ' . number_format((float) $locked->balance_amount, 2) . '.',
                ]);
            }

            Payment::create([
                'billing_id'   => $locked->id,
                'payment_date' => now(),
                'amount'       => $amount,
                'method'       => $data['method'],
                'reference'    => $data['reference'] ?? null,
            ]);

            $locked->paid_amount = (float) $locked->paid_amount + $amount;
            $locked->refreshBalanceAndStatus();
            $locked->save();
        });

        return redirect()->route('billings.show', $billing)->with('success', 'Payment recorded.');
    }

    /** Delete confirmation page. */
    public function delete(Payment $payment): View
    {
        $payment->load('billing');

        return view('payments.delete', compact('payment'));
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        DB::transaction(function () use ($payment) {
            $billing = Billing::lockForUpdate()->find($payment->billing_id);

            if ($billing) {
                $billing->paid_amount = max(0, (float) $billing->paid_amount - (float) $payment->amount);
                $billing->refreshBalanceAndStatus();
                $billing->save();
            }

            $payment->delete();
        });

        return redirect()->route('payments.index')->with('success', 'Payment deleted.');
    }
}
