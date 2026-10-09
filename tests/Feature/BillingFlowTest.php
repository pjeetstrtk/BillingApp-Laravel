<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingFlowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['username' => 'admin', 'password' => 'admin123', 'role' => 'Admin']);
    }

    private function invoice(Customer $customer, float $total = 100): Billing
    {
        return Billing::create([
            'invoice_no'     => 'INV-TEST-' . $customer->id,
            'customer_id'    => $customer->id,
            'bill_date'      => now(),
            'total_amount'   => $total,
            'paid_amount'    => 0,
            'balance_amount' => $total,
            'status'         => Billing::STATUS_PENDING,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_admin_can_log_in_and_wrong_password_is_rejected(): void
    {
        $this->admin();

        $this->from('/login')
            ->post('/login', ['username' => 'admin', 'password' => 'wrong'])
            ->assertRedirect('/login');
        $this->assertGuest();

        $this->post('/login', ['username' => 'admin', 'password' => 'admin123'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_creating_an_invoice_totals_lines_and_reduces_stock(): void
    {
        $this->actingAs($this->admin());
        $customer = Customer::create(['name' => 'Acme']);
        $product = Product::create(['name' => 'Widget', 'price' => 25.50, 'stock' => 10]);

        $response = $this->post(route('billings.store'), [
            'customer_id' => $customer->id,
            'product_ids' => [$product->id],
            'quantities'  => [3],
        ]);

        $billing = Billing::firstOrFail();
        $response->assertRedirect(route('billings.payments.create', $billing));

        $this->assertSame('76.50', $billing->total_amount);
        $this->assertSame('76.50', $billing->balance_amount);
        $this->assertSame('0.00', $billing->paid_amount);
        $this->assertSame('Pending', $billing->status);
        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_invoice_is_rejected_when_stock_is_insufficient(): void
    {
        $this->actingAs($this->admin());
        $customer = Customer::create(['name' => 'Acme']);
        $product = Product::create(['name' => 'Widget', 'price' => 10, 'stock' => 2]);

        $this->post(route('billings.store'), [
            'customer_id' => $customer->id,
            'product_ids' => [$product->id],
            'quantities'  => [5],
        ])->assertSessionHasErrors('product_ids');

        $this->assertSame(0, Billing::count());
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_payments_update_status_and_overpayment_is_rejected(): void
    {
        $this->actingAs($this->admin());
        $billing = $this->invoice(Customer::create(['name' => 'Acme']), 100);

        $this->post(route('billings.payments.store', $billing), ['amount' => 40, 'method' => 'Cash']);
        $billing->refresh();
        $this->assertSame('Partial', $billing->status);
        $this->assertSame('40.00', $billing->paid_amount);
        $this->assertSame('60.00', $billing->balance_amount);

        $this->post(route('billings.payments.store', $billing), ['amount' => 100, 'method' => 'Cash'])
            ->assertSessionHasErrors('amount');
        $this->assertSame(1, Payment::count());

        $this->post(route('billings.payments.store', $billing), ['amount' => 60, 'method' => 'Card']);
        $billing->refresh();
        $this->assertSame('Paid', $billing->status);
        $this->assertSame('0.00', $billing->balance_amount);
    }

    public function test_deleting_a_payment_reverts_the_invoice(): void
    {
        $this->actingAs($this->admin());
        $billing = $this->invoice(Customer::create(['name' => 'Acme']), 100);

        $this->post(route('billings.payments.store', $billing), ['amount' => 100, 'method' => 'Cash']);
        $this->assertSame('Paid', $billing->fresh()->status);

        $this->delete(route('payments.destroy', Payment::firstOrFail()));

        $billing->refresh();
        $this->assertSame('Pending', $billing->status);
        $this->assertSame('0.00', $billing->paid_amount);
        $this->assertSame('100.00', $billing->balance_amount);
    }

    public function test_customer_with_invoices_cannot_be_deleted(): void
    {
        $this->actingAs($this->admin());
        $customer = Customer::create(['name' => 'Acme']);
        $this->invoice($customer);

        $this->delete(route('customers.destroy', $customer))->assertSessionHas('error');
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }
}
