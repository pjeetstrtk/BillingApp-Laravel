<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\Customer;
use App\Models\Product;
use Database\Seeders\FakeDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FakeDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_consistent_data(): void
    {
        app(FakeDataSeeder::class)->counts(5, 8, 15)->run();

        $this->assertSame(5, Customer::count());
        $this->assertSame(8, Product::count());
        $this->assertSame(15, Billing::count());

        Billing::with(['items', 'payments'])->get()->each(function (Billing $b) {
            $this->assertEqualsWithDelta((float) $b->items->sum('line_total'), (float) $b->total_amount, 0.001);
            $this->assertEqualsWithDelta((float) $b->payments->sum('amount'), (float) $b->paid_amount, 0.001);
            $this->assertEqualsWithDelta((float) $b->total_amount - (float) $b->paid_amount, (float) $b->balance_amount, 0.001);
        });
    }
}
