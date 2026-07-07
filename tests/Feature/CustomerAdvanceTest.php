<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAdvance;
use App\Models\Group;
use App\Models\Product;
use App\Models\Unit;
use App\Models\ProductUnit;
use App\Models\User;
use App\Services\AdvanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAdvanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Customer $customer;
    protected Product $product;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::factory()->create();
        $superGroup = Group::where('is_super_admin', true)->first();
        $superGroup->users()->attach($this->admin);

        $this->customer = Customer::factory()->create();

        $this->product = Product::factory()->create();
        $this->unit = Unit::factory()->create();
        ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'conversion_factor' => 1,
            'is_default_sale' => true,
            'is_default_purchase' => true,
        ]);
    }

    public function test_advance_can_be_recorded(): void
    {
        $response = $this->actingAs($this->admin)->post(route('customer-advances.store'), [
            'customer_id' => $this->customer->id,
            'amount' => 500000,
            'payment_method' => 'cash',
            'advance_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('customer-advances.index'));
        $this->assertDatabaseHas('customer_advances', [
            'customer_id' => $this->customer->id,
            'amount' => 500000,
            'balance' => 500000,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);
    }

    public function test_advance_index_is_accessible(): void
    {
        CustomerAdvance::factory()->create([
            'customer_id' => $this->customer->id,
            'amount' => 300000,
            'balance' => 300000,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('customer-advances.index'));
        $response->assertStatus(200);
        $response->assertSee('300,000');
    }

    public function test_advance_show_is_accessible(): void
    {
        $advance = CustomerAdvance::factory()->create([
            'customer_id' => $this->customer->id,
            'amount' => 200000,
            'balance' => 200000,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('customer-advances.show', $advance));
        $response->assertStatus(200);
        $response->assertSee(number_format(200000, 0));
    }

    public function test_advance_can_be_applied_to_invoice(): void
    {
        $advance = CustomerAdvance::factory()->create([
            'customer_id' => $this->customer->id,
            'amount' => 300000,
            'balance' => 300000,
            'created_by' => $this->admin->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->admin)->post(route('invoices.store'), [
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 100000,
                ],
            ],
        ]);

        $invoice = \App\Models\Invoice::where('customer_id', $this->customer->id)->first();
        $this->assertNotNull($invoice);

        $response = $this->actingAs($this->admin)->post(route('customer-advances.apply', $advance), [
            'invoice_id' => $invoice->id,
            'amount' => 100000,
        ]);

        $response->assertRedirect();
        $advance->refresh();
        $invoice->refresh();

        $this->assertEquals(200000, $advance->balance);
        $this->assertEquals('partially_applied', $advance->status);
        $this->assertEquals(100000, $invoice->amount_paid);
        $this->assertEquals(100000, $invoice->balance_due);
        $this->assertEquals('partial', $invoice->payment_status);
    }

    public function test_advance_cannot_apply_more_than_balance(): void
    {
        $advance = CustomerAdvance::factory()->create([
            'customer_id' => $this->customer->id,
            'amount' => 50000,
            'balance' => 50000,
            'created_by' => $this->admin->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->admin)->post(route('invoices.store'), [
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 100000,
                ],
            ],
        ]);

        $invoice = \App\Models\Invoice::where('customer_id', $this->customer->id)->first();

        $response = $this->actingAs($this->admin)->post(route('customer-advances.apply', $advance), [
            'invoice_id' => $invoice->id,
            'amount' => 100000,
        ]);

        $response->assertSessionHas('error');
    }

    public function test_advance_can_be_cancelled(): void
    {
        $advance = CustomerAdvance::factory()->create([
            'customer_id' => $this->customer->id,
            'amount' => 200000,
            'balance' => 200000,
            'created_by' => $this->admin->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->admin)->post(route('customer-advances.cancel', $advance));
        $response->assertRedirect();
        $advance->refresh();
        $this->assertEquals('cancelled', $advance->status);
        $this->assertEquals(200000, $advance->balance);
    }

    public function test_cancelling_applied_advance_reverses_invoice(): void
    {
        $advance = CustomerAdvance::factory()->create([
            'customer_id' => $this->customer->id,
            'amount' => 300000,
            'balance' => 300000,
            'created_by' => $this->admin->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->admin)->post(route('invoices.store'), [
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'unit_price' => 200000,
                ],
            ],
        ]);

        $invoice = \App\Models\Invoice::where('customer_id', $this->customer->id)->first();

        $this->actingAs($this->admin)->post(route('customer-advances.apply', $advance), [
            'invoice_id' => $invoice->id,
            'amount' => 150000,
        ]);

        $advance->refresh();
        $invoice->refresh();
        $this->assertEquals(150000, $advance->balance);
        $this->assertEquals(150000, $invoice->amount_paid);

        $this->actingAs($this->admin)->post(route('customer-advances.cancel', $advance));
        $advance->refresh();
        $invoice->refresh();

        $this->assertEquals('cancelled', $advance->status);
        $this->assertEquals(300000, $advance->balance);
        $this->assertEquals(0, $invoice->amount_paid);
        $this->assertEquals(200000, $invoice->balance_due);
        $this->assertEquals('pending', $invoice->payment_status);
    }

    public function test_advance_list_filters_by_tab(): void
    {
        CustomerAdvance::factory()->create([
            'customer_id' => $this->customer->id,
            'amount' => 100000,
            'balance' => 0,
            'status' => 'applied',
            'created_by' => $this->admin->id,
        ]);

        CustomerAdvance::factory()->create([
            'customer_id' => $this->customer->id,
            'amount' => 200000,
            'balance' => 200000,
            'status' => 'completed',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('customer-advances.index', ['tab' => 'completed']));
        $response->assertStatus(200);
        $response->assertSee('200,000');
    }
}
