<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Group;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Unit;
use App\Models\ProductUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesInvoiceUnificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Group $superGroup;
    protected Customer $customer;
    protected Product $product;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::factory()->create();
        $this->superGroup = Group::where('is_super_admin', true)->first();
        $this->superGroup->users()->attach($this->admin);

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

    public function test_invoice_creation_creates_linked_proforma(): void
    {
        $response = $this->actingAs($this->admin)->post(route('invoices.store'), [
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 50000,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales_invoices', [
            'customer_id' => $this->customer->id,
        ]);

        $invoice = \App\Models\Invoice::where('customer_id', $this->customer->id)->first();
        $this->assertEquals('approved', $invoice->status);
        $this->assertNotNull($invoice->sales_order_id);

        $so = SalesOrder::find($invoice->sales_order_id);
        $this->assertNotNull($so);
        $this->assertEquals('proforma', $so->status);
        $this->assertEquals($this->customer->id, $so->customer_id);
        $this->assertEquals(100000, $so->total);

        $this->assertCount(1, $invoice->items);
        $this->assertCount(1, $so->items);
    }

    public function test_sales_order_can_be_submitted_and_approved(): void
    {
        $so = SalesOrder::create([
            'customer_id' => $this->customer->id,
            'so_number' => 'SO-TEST-001',
            'order_date' => now(),
            'status' => 'draft',
            'subtotal' => 100000,
            'total' => 100000,
            'created_by' => $this->admin->id,
        ]);
        $so->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 50000,
            'subtotal' => 100000,
            'total' => 100000,
        ]);

        $response = $this->actingAs($this->admin)->post(route('sales.orders.submit-approval', $so));
        $response->assertRedirect();
        $this->assertEquals('pending_approval', $so->fresh()->status);

        $response = $this->actingAs($this->admin)->post(route('sales.orders.approve', $so));
        $response->assertRedirect();
        $so->refresh();
        $this->assertEquals('approved', $so->status);
        $this->assertNotNull($so->approved_by);
        $this->assertNotNull($so->approved_at);
    }

    public function test_sales_order_can_be_cancelled(): void
    {
        $so = SalesOrder::create([
            'customer_id' => $this->customer->id,
            'so_number' => 'SO-TEST-002',
            'order_date' => now(),
            'status' => 'draft',
            'subtotal' => 50000,
            'total' => 50000,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('sales.orders.cancel', $so));
        $response->assertRedirect();
        $this->assertEquals('cancelled', $so->fresh()->status);
    }

    public function test_invoice_can_be_approved(): void
    {
        $response = $this->actingAs($this->admin)->post(route('invoices.drafts'), [
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'unit_price' => 100000,
                ],
            ],
        ]);

        $invoice = \App\Models\Invoice::where('customer_id', $this->customer->id)->first();

        $response = $this->actingAs($this->admin)->post(route('invoices.approve', $invoice));
        $response->assertRedirect();
        $invoice->refresh();
        $this->assertEquals('approved', $invoice->status);
        $this->assertNotNull($invoice->approved_by);
        $this->assertNotNull($invoice->approved_at);
    }

    public function test_proforma_can_be_reverted_to_draft(): void
    {
        $response = $this->actingAs($this->admin)->post(route('invoices.drafts'), [
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 50000,
                ],
            ],
        ]);

        $invoice = \App\Models\Invoice::where('customer_id', $this->customer->id)->first();
        $this->assertEquals('draft', $invoice->status);

        $response = $this->actingAs($this->admin)->post(route('invoices.proforma', $invoice));
        $response->assertRedirect();
        $invoice->refresh();
        $this->assertEquals('proforma', $invoice->status);

        $response = $this->actingAs($this->admin)->post(route('invoices.revert-draft', $invoice));
        $response->assertRedirect();
        $invoice->refresh();
        $this->assertEquals('draft', $invoice->status);
    }

    public function test_proforma_invoice_can_be_submitted_and_approved(): void
    {
        $response = $this->actingAs($this->admin)->post(route('invoices.drafts'), [
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 50000,
                ],
            ],
        ]);

        $invoice = \App\Models\Invoice::where('customer_id', $this->customer->id)->first();
        $this->assertEquals('draft', $invoice->status);

        $response = $this->actingAs($this->admin)->post(route('invoices.proforma', $invoice));
        $response->assertRedirect();
        $invoice->refresh();
        $this->assertEquals('proforma', $invoice->status);

        $response = $this->actingAs($this->admin)->post(route('invoices.approve', $invoice));
        $response->assertRedirect();
        $invoice->refresh();
        $this->assertEquals('approved', $invoice->status);
        $this->assertNotNull($invoice->approved_by);
        $this->assertNotNull($invoice->approved_at);
    }

    public function test_non_draft_invoice_cannot_be_converted_to_proforma(): void
    {
        $response = $this->actingAs($this->admin)->post(route('invoices.store'), [
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'unit_price' => 100000,
                ],
            ],
        ]);

        $invoice = \App\Models\Invoice::where('customer_id', $this->customer->id)->first();
        $this->assertEquals('approved', $invoice->status);

        $response = $this->actingAs($this->admin)->post(route('invoices.proforma', $invoice));
        $response->assertSessionHas('error');
        $invoice->refresh();
        $this->assertEquals('approved', $invoice->status);
    }
}
