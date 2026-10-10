<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Generates realistic random data with Faker: customers, products, invoices
 * (with line items) and payments whose totals, balances and statuses are
 * consistent.
 *
 *   php artisan db:seed --class=FakeDataSeeder
 *   php artisan fake:data --customers=50 --products=60 --invoices=200
 *
 * Faker is a dev dependency, so this needs `composer install` without --no-dev.
 */
class FakeDataSeeder extends Seeder
{
    private int $customers = 30;
    private int $products = 40;
    private int $invoices = 60;

    public function counts(int $customers, int $products, int $invoices): static
    {
        $this->customers = max(1, $customers);
        $this->products  = max(1, $products);
        $this->invoices  = max(0, $invoices);

        return $this;
    }

    public function run(): void
    {
        if (! class_exists(FakerFactory::class)) {
            throw new RuntimeException('Faker is not installed. Run "composer install" without --no-dev.');
        }

        DB::transaction(function () {
            $customers = Customer::factory()->count($this->customers)->create();
            $products  = Product::factory()->count($this->products)->create();

            for ($i = 1; $i <= $this->invoices; $i++) {
                $this->createInvoice($i, $customers->random(), $products);
            }
        });
    }

    private function createInvoice(int $n, Customer $customer, $products): void
    {
        $billDate = now()->subDays(fake()->numberBetween(0, 90))
            ->setTime(fake()->numberBetween(8, 20), fake()->numberBetween(0, 59), fake()->numberBetween(0, 59));

        $billing = Billing::create([
            // Sequence number keeps the invoice no unique even for identical timestamps
            'invoice_no'     => 'INV-' . $billDate->format('YmdHis') . '-F' . str_pad((string) $n, 4, '0', STR_PAD_LEFT) . '-' . $customer->id,
            'customer_id'    => $customer->id,
            'bill_date'      => $billDate,
            'total_amount'   => 0,
            'paid_amount'    => 0,
            'balance_amount' => 0,
            'status'         => Billing::STATUS_PENDING,
        ]);

        $total = 0.0;

        foreach ($products->random(min($products->count(), fake()->numberBetween(1, 5))) as $product) {
            $qty       = fake()->numberBetween(1, 5);
            $unitPrice = (float) $product->price;
            $lineTotal = round($unitPrice * $qty, 2);

            $billing->items()->create([
                'product_id' => $product->id,
                'quantity'   => $qty,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ]);

            $product->stock = max(0, $product->stock - $qty);
            $product->save();

            $total += $lineTotal;
        }

        $total = round($total, 2);

        // 30% unpaid, 30% partly paid, 40% fully paid (in one or two payments)
        $roll = fake()->numberBetween(1, 100);
        $paid = 0.0;

        if ($roll > 30) {
            $target   = $roll <= 60 ? round($total * fake()->numberBetween(20, 80) / 100, 2) : $total;
            $payments = $target > 1 && fake()->boolean(40) ? 2 : 1;
            $first    = $payments === 2 ? round($target / 2, 2) : $target;

            foreach (array_filter([$first, $payments === 2 ? round($target - $first, 2) : null]) as $amount) {
                Payment::create([
                    'billing_id'   => $billing->id,
                    'payment_date' => $billDate->copy()->addDays(fake()->numberBetween(0, 20))->min(now()),
                    'amount'       => $amount,
                    'method'       => fake()->randomElement(['Cash', 'Card', 'UPI', 'Bank']),
                    'reference'    => fake()->boolean(50) ? strtoupper(fake()->bothify('TXN-########')) : null,
                ]);
                $paid += $amount;
            }
        }

        $billing->total_amount = $total;
        $billing->paid_amount  = $paid;
        $billing->refreshBalanceAndStatus();
        $billing->save();
    }
}
