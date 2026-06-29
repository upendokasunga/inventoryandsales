<?php

namespace Tests\Feature;

use App\Models\CreditNote;
use App\Models\CreditNoteNumberSequence;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Group;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceNumberSequence;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\CreditNoteService;
use App\Services\CreditService;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\PosService;
use App\Services\PurchaseReturnService;
use App\Services\RefundService;
use App\Services\SalesReturnService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class WholesalePosReturnsEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Product $product;
    protected Product $product2;
    protected Unit $unit;
    protected Customer $customer;
    protected CustomerGroup $group;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        Cache::flush();

        $this->admin = User::factory()->create();
        $superGroup = Group::where('is_super_admin', true)->first();
        $superGroup->users()->attach($this->admin);

        $this->unit = Unit::factory()->create(['name' => 'Piece', 'short_code' => 'PCS']);

        $this->product = Product::factory()->create([
            'name' => 'POS Product',
            'barcode' => '123456789012',
            'sku' => 'SKU-POS-001',
            'track_stock' => true,
            'current_stock' => 0,
        ]);

        $this->product2 = Product::factory()->create([
            'name' => 'POS Product 2',
            'barcode' => '987654321098',
            'sku' => 'SKU-POS-002',
            'track_stock' => true,
            'current_stock' => 0,
        ]);

        $this->group = CustomerGroup::factory()->create(['name' => 'Wholesale']);

        $this->customer = Customer::factory()->create([
            'name' => 'POS Customer',
            'customer_group_id' => $this->group->id,
            'credit_limit' => 5000000,
            'available_credit' => 5000000,
            'outstanding_balance' => 0,
            'credit_status' => 'active',
        ]);

        $this->supplier = Supplier::factory()->create([
            'name' => 'Return Supplier',
        ]);
    }

    // ================================================================
    //  POS BARCODE SCANNING & REDIS CACHE
    // ================================================================

    public function test_pos_index_page_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('pos.index'));

        $response->assertStatus(200);
        $response->assertSee('Point of Sale');
    }

    public function test_pos_dashboard_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('pos.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('POS Dashboard');
    }

    public function test_pos_barcode_lookup_finds_product(): void
    {
        $response = $this->actingAs($this->admin)->get('/pos/barcode?barcode=123456789012');

        $response->assertStatus(200);
        $response->assertJson(['product' => ['id' => $this->product->id]]);
    }

    public function test_pos_barcode_lookup_returns_404_for_unknown(): void
    {
        $response = $this->actingAs($this->admin)->get('/pos/barcode?barcode=NONEXISTENT');

        $response->assertStatus(404);
    }

    public function test_pos_barcode_cache_is_set_on_lookup(): void
    {
        Cache::forget("barcode.product.123456789012");

        $this->actingAs($this->admin)->get('/pos/barcode?barcode=123456789012');

        $this->assertTrue(Cache::has("barcode.product.123456789012"));
    }

    public function test_pos_barcode_cache_returns_cached_product(): void
    {
        Cache::put("barcode.product.TESTCACHE", $this->product, 86400);

        $response = $this->actingAs($this->admin)->get('/pos/barcode?barcode=TESTCACHE');

        $response->assertStatus(200);
        $response->assertJson(['product' => ['id' => $this->product->id]]);
    }

    public function test_pos_sku_lookup_finds_product(): void
    {
        $response = $this->actingAs($this->admin)->get('/pos/sku?sku=SKU-POS-001');

        $response->assertStatus(200);
        $response->assertJson(['product' => ['id' => $this->product->id]]);
    }

    public function test_pos_sku_cache_is_set_on_lookup(): void
    {
        Cache::forget("barcode.sku.SKU-POS-001");

        $this->actingAs($this->admin)->get('/pos/sku?sku=SKU-POS-001');

        $this->assertTrue(Cache::has("barcode.sku.SKU-POS-001"));
    }

    public function test_pos_customer_lookup_returns_credit_info(): void
    {
        $response = $this->actingAs($this->admin)->get("/pos/customer?customer_id={$this->customer->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'customer',
            'credit_limit',
            'outstanding_balance',
            'available_credit',
            'credit_status',
        ]);
    }

    public function test_pos_customer_shows_correct_available_credit(): void
    {
        $response = $this->actingAs($this->admin)->get("/pos/customer?customer_id={$this->customer->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('available_credit', 5000000);
    }

    // ================================================================
    //  CREDIT VALIDATION
    // ================================================================

    public function test_credit_validation_approves_within_limit(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/pos/validate-credit', [
            'customer_id' => $this->customer->id,
            'amount' => 100000,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('approved', true);
    }

    public function test_credit_validation_rejects_over_limit(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/pos/validate-credit', [
            'customer_id' => $this->customer->id,
            'amount' => 10000000,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('approved', false);
    }

    public function test_credit_validation_rejects_on_hold(): void
    {
        $this->customer->update([
            'credit_status' => 'suspended',
            'credit_hold_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->postJson('/pos/validate-credit', [
            'customer_id' => $this->customer->id,
            'amount' => 1000,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('approved', false);
    }

    // ================================================================
    //  INVOICE GENERATION
    // ================================================================

    public function test_invoice_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('invoices.index'));

        $response->assertStatus(200);
        $response->assertSee('Invoices');
    }

    public function test_invoice_create_form_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('invoices.create'));

        $response->assertStatus(200);
        $response->assertSee('Invoice');
    }

    public function test_invoice_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('invoices.store'), [
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->format('Y-m-d'),
            'payment_type' => 'cash',
            'discount' => 0,
            'tax' => 0,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 10, 'unit_price' => 5000],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales_invoices', [
            'customer_id' => $this->customer->id,
        ]);
        $this->assertDatabaseHas('sales_invoice_items', [
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 5000,
        ]);
    }

    public function test_invoice_has_invoice_number_in_correct_format(): void
    {
        InvoiceNumberSequence::create([
            'year' => now()->year,
            'last_number' => 0,
        ]);

        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $this->assertNotNull($invoice->invoice_number);
        $this->assertStringStartsWith('INV-' . now()->year . '-', $invoice->invoice_number);
        $this->assertMatchesRegularExpression('/^INV-\d{4}-\d{6}$/', $invoice->invoice_number);
    }

    public function test_invoice_calculates_totals_correctly(): void
    {
        $response = $this->actingAs($this->admin)->post(route('invoices.store'), [
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->format('Y-m-d'),
            'payment_type' => 'cash',
            'discount' => 5000,
            'tax' => 0,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 10, 'unit_price' => 5000],
            ],
        ]);

        $invoice = Invoice::first();
        $this->assertEquals(50000, $invoice->subtotal);
        $this->assertEquals(45000, $invoice->total);
        $this->assertEquals(45000, $invoice->balance_due);
    }

    public function test_invoice_show_is_accessible(): void
    {
        $invoice = Invoice::factory()->create(['customer_id' => $this->customer->id]);

        $response = $this->actingAs($this->admin)->get(route('invoices.show', $invoice));

        $response->assertStatus(200);
        $response->assertSee($invoice->invoice_number);
    }

    public function test_invoice_can_be_approved(): void
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->post(route('invoices.approve', $invoice));

        $response->assertRedirect();
        $this->assertEquals('approved', $invoice->fresh()->status);
    }

    public function test_invoice_print_is_accessible(): void
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('invoices.print', $invoice));

        $response->assertStatus(200);
        $response->assertSee($invoice->invoice_number);
    }

    public function test_invoice_receipt_is_accessible(): void
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('invoices.receipt', $invoice));

        $response->assertStatus(200);
        $response->assertSee('RECEIPT');
    }

    public function test_invoice_can_be_deleted(): void
    {
        $invoice = Invoice::factory()->create(['customer_id' => $this->customer->id]);

        $response = $this->actingAs($this->admin)->delete(route('invoices.destroy', $invoice));

        $response->assertRedirect();
        $this->assertSoftDeleted($invoice);
    }

    // ================================================================
    //  INVOICE SERVICE — CRUD
    // ================================================================

    public function test_invoice_service_creates_invoice_with_items(): void
    {
        $service = app(InvoiceService::class);

        $invoice = $service->create([
            'customer_id' => $this->customer->id,
            'payment_type' => 'cash',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 5, 'unit_price' => 2000],
            ],
        ]);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals(10000, $invoice->subtotal);
        $this->assertCount(1, $invoice->items);
    }

    public function test_invoice_service_returns_stats(): void
    {
        $service = app(InvoiceService::class);
        $stats = $service->getStats();

        $this->assertArrayHasKey('total_invoices', $stats);
        $this->assertArrayHasKey('total_paid', $stats);
        $this->assertArrayHasKey('total_pending', $stats);
    }

    public function test_invoice_service_updates_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'total' => 10000,
        ]);

        $service = app(InvoiceService::class);
        $updated = $service->update($invoice, [
            'discount' => 1000,
            'total' => 9000,
        ]);

        $this->assertEquals(1000, $updated->discount);
    }

    // ================================================================
    //  PARTIAL PAYMENTS
    // ================================================================

    public function test_payment_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('payments.index'));

        $response->assertStatus(200);
        $response->assertSee('Payments');
    }

    public function test_partial_payment_can_be_recorded(): void
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'total' => 100000,
            'amount_paid' => 0,
            'balance_due' => 100000,
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('payments.store', $invoice), [
            'amount' => 40000,
            'payment_method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 40000,
        ]);

        $invoice->refresh();
        $this->assertEquals(40000, $invoice->amount_paid);
        $this->assertEquals(60000, $invoice->balance_due);
        $this->assertEquals('partial', $invoice->payment_status);
    }

    public function test_payment_makes_invoice_paid_when_full(): void
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'total' => 50000,
            'amount_paid' => 0,
            'balance_due' => 50000,
            'payment_status' => 'pending',
        ]);

        $this->actingAs($this->admin)->post(route('payments.store', $invoice), [
            'amount' => 50000,
            'payment_method' => 'bank_transfer',
            'payment_date' => now()->format('Y-m-d'),
        ]);

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->payment_status);
        $this->assertEquals(0, $invoice->balance_due);
    }

    public function test_multiple_partial_payments_accumulate(): void
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'total' => 100000,
            'amount_paid' => 0,
            'balance_due' => 100000,
            'payment_status' => 'pending',
        ]);

        $this->actingAs($this->admin)->post(route('payments.store', $invoice), [
            'amount' => 25000, 'payment_method' => 'cash', 'payment_date' => now()->format('Y-m-d'),
        ]);
        $this->actingAs($this->admin)->post(route('payments.store', $invoice), [
            'amount' => 25000, 'payment_method' => 'mobile_money', 'payment_date' => now()->format('Y-m-d'),
        ]);

        $invoice->refresh();
        $this->assertEquals(50000, $invoice->amount_paid);
        $this->assertEquals(50000, $invoice->balance_due);
        $this->assertEquals('partial', $invoice->payment_status);
    }

    // ================================================================
    //  PAYMENT SERVICE
    // ================================================================

    public function test_payment_service_records_payment(): void
    {
        $service = app(PaymentService::class);
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'total' => 100000,
            'amount_paid' => 0,
            'balance_due' => 100000,
        ]);

        $payment = $service->recordPayment($invoice, [
            'amount' => 100000,
            'payment_method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
        ]);

        $this->assertEquals(100000, $payment->amount);
        $this->assertEquals('paid', $invoice->fresh()->payment_status);
    }

    // ================================================================
    //  POS CHECKOUT (Integration)
    // ================================================================

    public function test_pos_checkout_creates_invoice(): void
    {
        $service = app(InventoryService::class);
        $service->receiveStock($this->product, 100, 2000);

        $response = $this->actingAs($this->admin)->postJson('/pos/checkout', [
            'customer_id' => $this->customer->id,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 5, 'unit_price' => 5000],
            ],
            'payment' => [
                'amount' => 25000,
                'payment_method' => 'cash',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('sales_invoices', [
            'customer_id' => $this->customer->id,
        ]);
    }

    public function test_pos_checkout_deducts_inventory(): void
    {
        $service = app(InventoryService::class);
        $service->receiveStock($this->product, 50, 2000);

        $this->actingAs($this->admin)->postJson('/pos/checkout', [
            'customer_id' => $this->customer->id,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 10, 'unit_price' => 5000],
            ],
            'payment' => [
                'amount' => 50000,
                'payment_method' => 'cash',
            ],
        ]);

        $this->assertEquals(40, $this->product->fresh()->current_stock);
    }

    // ================================================================
    //  SALES RETURNS
    // ================================================================

    public function test_sales_return_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('sales-returns.index'));

        $response->assertStatus(200);
        $response->assertSee('Sales Returns');
    }

    public function test_sales_return_create_form_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('sales-returns.create'));

        $response->assertStatus(200);
        $response->assertSee('Create');
    }

    public function test_sales_return_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('sales-returns.store'), [
            'customer_id' => $this->customer->id,
            'reason' => 'damaged',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 5, 'unit_price' => 5000, 'reason' => 'damaged'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales_returns', [
            'customer_id' => $this->customer->id,
        ]);
        $this->assertDatabaseHas('sales_return_items', [
            'product_id' => $this->product->id,
            'quantity' => 5,
        ]);
    }

    public function test_sales_return_show_is_accessible(): void
    {
        $return = SalesReturn::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('sales-returns.show', $return));

        $response->assertStatus(200);
        $response->assertSee($return->return_number);
    }

    public function test_sales_return_approve_generates_credit_note(): void
    {
        $service = app(InventoryService::class);
        $service->receiveStock($this->product, 100, 2000);

        $return = SalesReturn::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'draft',
            'total_amount' => 25000,
        ]);

        SalesReturnItem::factory()->create([
            'sales_return_id' => $return->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => 5000,
            'line_total' => 25000,
            'reason' => 'damaged',
        ]);

        $response = $this->actingAs($this->admin)->post(route('sales-returns.approve', $return));

        $response->assertRedirect();
        $this->assertEquals('approved', $return->fresh()->status);

        $this->assertDatabaseHas('credit_notes', [
            'sales_return_id' => $return->id,
            'customer_id' => $this->customer->id,
            'amount' => 25000,
        ]);
    }

    public function test_sales_return_approve_restores_inventory(): void
    {
        $service = app(InventoryService::class);
        $service->receiveStock($this->product, 100, 2000);

        $service->issueStock($this->product, 30);

        $this->assertEquals(70, $this->product->fresh()->current_stock);

        $return = SalesReturn::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'draft',
            'total_amount' => 50000,
        ]);

        SalesReturnItem::factory()->create([
            'sales_return_id' => $return->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 5000,
            'line_total' => 50000,
            'reason' => 'damaged',
        ]);

        $returnService = app(SalesReturnService::class);
        $returnService->approve($return);

        $this->assertEquals(80, $this->product->fresh()->current_stock);
    }

    public function test_sales_return_can_be_rejected(): void
    {
        $return = SalesReturn::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->post(route('sales-returns.reject', $return));

        $response->assertRedirect();
        $this->assertEquals('rejected', $return->fresh()->status);
    }

    public function test_sales_return_can_be_completed(): void
    {
        $return = SalesReturn::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->post(route('sales-returns.complete', $return));

        $response->assertRedirect();
        $this->assertEquals('completed', $return->fresh()->status);
    }

    // ================================================================
    //  SALES RETURN SERVICE
    // ================================================================

    public function test_sales_return_service_creates_return_with_items(): void
    {
        $service = app(SalesReturnService::class);

        $return = $service->create([
            'customer_id' => $this->customer->id,
            'reason' => 'wrong_item',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 3, 'unit_price' => 10000, 'reason' => 'wrong_item'],
            ],
        ]);

        $this->assertInstanceOf(SalesReturn::class, $return);
        $this->assertEquals(30000, $return->total_amount);
        $this->assertCount(1, $return->items);
    }

    public function test_sales_return_service_returns_stats(): void
    {
        $service = app(SalesReturnService::class);
        $stats = $service->getStats();

        $this->assertArrayHasKey('total_returns', $stats);
        $this->assertArrayHasKey('pending_returns', $stats);
        $this->assertArrayHasKey('completed_returns', $stats);
    }

    // ================================================================
    //  PURCHASE RETURNS
    // ================================================================

    public function test_purchase_return_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('purchase-returns.index'));

        $response->assertStatus(200);
        $response->assertSee('Purchase Returns');
    }

    public function test_purchase_return_create_form_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('purchase-returns.create'));

        $response->assertStatus(200);
        $response->assertSee('Create');
    }

    public function test_purchase_return_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('purchase-returns.store'), [
            'supplier_id' => $this->supplier->id,
            'reason' => 'damaged',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 10, 'unit_price' => 3000, 'reason' => 'damaged'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('purchase_returns', [
            'supplier_id' => $this->supplier->id,
        ]);
    }

    public function test_purchase_return_show_is_accessible(): void
    {
        $return = PurchaseReturn::factory()->create([
            'supplier_id' => $this->supplier->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('purchase-returns.show', $return));

        $response->assertStatus(200);
        $response->assertSee($return->return_number);
    }

    public function test_purchase_return_approve_deducts_inventory(): void
    {
        $service = app(InventoryService::class);
        $service->receiveStock($this->product, 100, 2000);

        $return = PurchaseReturn::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
            'total_amount' => 30000,
        ]);

        PurchaseReturnItem::factory()->create([
            'purchase_return_id' => $return->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 3000,
            'line_total' => 30000,
            'reason' => 'damaged',
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchase-returns.approve', $return));

        $response->assertRedirect();
        $this->assertEquals('approved', $return->fresh()->status);
        $this->assertEquals(90, $this->product->fresh()->current_stock);
    }

    public function test_purchase_return_can_be_rejected(): void
    {
        $return = PurchaseReturn::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchase-returns.reject', $return));

        $response->assertRedirect();
        $this->assertEquals('rejected', $return->fresh()->status);
    }

    public function test_purchase_return_can_be_completed(): void
    {
        $return = PurchaseReturn::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchase-returns.complete', $return));

        $response->assertRedirect();
        $this->assertEquals('completed', $return->fresh()->status);
    }

    // ================================================================
    //  PURCHASE RETURN SERVICE
    // ================================================================

    public function test_purchase_return_service_creates_return(): void
    {
        $service = app(PurchaseReturnService::class);

        $return = $service->create([
            'supplier_id' => $this->supplier->id,
            'reason' => 'quality_issue',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 5, 'unit_price' => 4000, 'reason' => 'quality_issue'],
            ],
        ]);

        $this->assertInstanceOf(PurchaseReturn::class, $return);
        $this->assertEquals(20000, $return->total_amount);
    }

    // ================================================================
    //  CREDIT NOTES
    // ================================================================

    public function test_credit_note_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('credit-notes.index'));

        $response->assertStatus(200);
        $response->assertSee('Credit Notes');
    }

    public function test_credit_note_show_is_accessible(): void
    {
        $creditNote = CreditNote::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('credit-notes.show', $creditNote));

        $response->assertStatus(200);
        $response->assertSee($creditNote->credit_note_number);
    }

    public function test_credit_note_has_correct_number_format(): void
    {
        CreditNoteNumberSequence::create([
            'year' => now()->year,
            'last_number' => 0,
        ]);

        $creditNote = CreditNote::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $this->assertNotNull($creditNote->credit_note_number);
        $this->assertStringStartsWith('CN-' . now()->year . '-', $creditNote->credit_note_number);
        $this->assertMatchesRegularExpression('/^CN-\d{4}-\d{6}$/', $creditNote->credit_note_number);
    }

    // ================================================================
    //  CREDIT NOTE SERVICE
    // ================================================================

    public function test_credit_note_service_creates_credit_note(): void
    {
        $service = app(CreditNoteService::class);

        $creditNote = $service->create([
            'customer_id' => $this->customer->id,
            'amount' => 50000,
            'status' => 'issued',
        ]);

        $this->assertInstanceOf(CreditNote::class, $creditNote);
        $this->assertEquals(50000, $creditNote->amount);
    }

    public function test_credit_note_service_returns_stats(): void
    {
        $service = app(CreditNoteService::class);
        $stats = $service->getStats();

        $this->assertArrayHasKey('total_issued', $stats);
        $this->assertArrayHasKey('total_amount', $stats);
    }

    // ================================================================
    //  REFUNDS
    // ================================================================

    public function test_refund_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('refunds.index'));

        $response->assertStatus(200);
        $response->assertSee('Refunds');
    }

    public function test_cash_refund_can_be_processed(): void
    {
        $creditNote = CreditNote::factory()->create([
            'customer_id' => $this->customer->id,
            'amount' => 25000,
            'status' => 'issued',
        ]);

        $response = $this->actingAs($this->admin)->post(route('refunds.process'), [
            'credit_note_id' => $creditNote->id,
            'refund_method' => 'cash',
        ]);

        $response->assertRedirect();
        $this->assertEquals('applied', $creditNote->fresh()->status);
        $this->assertEquals('cash', $creditNote->fresh()->refund_method);
    }

    public function test_store_credit_refund_updates_customer_balance(): void
    {
        $creditNote = CreditNote::factory()->create([
            'customer_id' => $this->customer->id,
            'amount' => 50000,
            'status' => 'issued',
        ]);

        $this->actingAs($this->admin)->post(route('refunds.process'), [
            'credit_note_id' => $creditNote->id,
            'refund_method' => 'store_credit',
        ]);

        $this->assertEquals('applied', $creditNote->fresh()->status);
    }

    // ================================================================
    //  REFUND SERVICE
    // ================================================================

    public function test_refund_service_processes_cash_refund(): void
    {
        $service = app(RefundService::class);
        $creditNote = CreditNote::factory()->create([
            'customer_id' => $this->customer->id,
            'amount' => 100000,
            'status' => 'issued',
        ]);

        $result = $service->processRefund($creditNote, 'cash');

        $this->assertEquals('applied', $result->status);
        $this->assertEquals('cash', $result->refund_method);
    }

    public function test_refund_service_returns_stats(): void
    {
        $service = app(RefundService::class);
        $stats = $service->getStats();

        $this->assertArrayHasKey('total_refunds', $stats);
        $this->assertArrayHasKey('total_amount', $stats);
    }

    // ================================================================
    //  RECEIPT SERVICE
    // ================================================================

    public function test_receipt_service_returns_receipt_data(): void
    {
        $service = app(\App\Services\ReceiptService::class);
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $data = $service->getReceiptData($invoice);

        $this->assertArrayHasKey('invoice', $data);
        $this->assertArrayHasKey('business', $data);
        $this->assertArrayHasKey('is_thermal', $data);
    }

    // ================================================================
    //  POS DASHBOARD STATS
    // ================================================================

    public function test_pos_dashboard_stats_are_cached(): void
    {
        $service = app(PosService::class);
        $stats = $service->getDashboardStats();

        $this->assertArrayHasKey('today_sales', $stats);
        $this->assertArrayHasKey('invoices_issued', $stats);
        $this->assertArrayHasKey('payments_received', $stats);
        $this->assertArrayHasKey('outstanding_receivables', $stats);
    }

    // ================================================================
    //  PERMISSIONS (Non-admin cannot access)
    // ================================================================

    public function test_non_admin_cannot_access_pos(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('pos.index'));
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_invoices(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoices.index'));
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_payments(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('payments.index'));
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_sales_returns(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('sales-returns.index'));
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_purchase_returns(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('purchase-returns.index'));
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_credit_notes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('credit-notes.index'));
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_refunds(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('refunds.index'));
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_pos_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('pos.dashboard'));
        $response->assertStatus(403);
    }

    // ================================================================
    //  AUDIT LOGGING
    // ================================================================

    public function test_invoice_creation_creates_audit_log(): void
    {
        $invoice = Invoice::factory()->create(['customer_id' => $this->customer->id]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Invoice::class,
            'auditable_id' => $invoice->id,
            'event' => 'created',
        ]);
    }

    public function test_credit_note_creation_creates_audit_log(): void
    {
        $creditNote = CreditNote::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => CreditNote::class,
            'auditable_id' => $creditNote->id,
            'event' => 'created',
        ]);
    }

    public function test_sales_return_creation_creates_audit_log(): void
    {
        $return = SalesReturn::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => SalesReturn::class,
            'auditable_id' => $return->id,
            'event' => 'created',
        ]);
    }

    // ================================================================
    //  CACHE INVALIDATION
    // ================================================================

    public function test_invoice_creation_invalidates_dashboard_cache(): void
    {
        Cache::shouldReceive('forget')
            ->with('pos.dashboard.stats')
            ->atLeast()->once();
        Cache::shouldReceive('forget')
            ->with('invoices.stats')
            ->atLeast()->once();
        Cache::shouldReceive('forget')
            ->zeroOrMoreTimes();

        Invoice::factory()->create(['customer_id' => $this->customer->id]);
    }

    public function test_payment_creation_invalidates_cache(): void
    {
        $invoice = Invoice::factory()->create(['customer_id' => $this->customer->id]);

        Cache::shouldReceive('forget')
            ->with('pos.dashboard.stats')
            ->atLeast()->once();
        Cache::shouldReceive('forget')
            ->with("invoice.{$invoice->id}")
            ->atLeast()->once();
        Cache::shouldReceive('forget')
            ->zeroOrMoreTimes();

        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'customer_id' => $this->customer->id,
        ]);
    }

    // ================================================================
    //  MODEL RELATIONS
    // ================================================================

    public function test_invoice_belongs_to_customer(): void
    {
        $invoice = Invoice::factory()->create(['customer_id' => $this->customer->id]);

        $this->assertNotNull($invoice->customer);
        $this->assertEquals($this->customer->id, $invoice->customer->id);
    }

    public function test_invoice_has_items(): void
    {
        $invoice = Invoice::factory()->create(['customer_id' => $this->customer->id]);
        InvoiceItem::factory()->count(2)->create([
            'invoice_id' => $invoice->id,
            'product_id' => $this->product->id,
        ]);

        $this->assertCount(2, $invoice->fresh()->items);
    }

    public function test_invoice_has_payments(): void
    {
        $invoice = Invoice::factory()->create(['customer_id' => $this->customer->id]);
        Payment::factory()->count(2)->create([
            'invoice_id' => $invoice->id,
            'customer_id' => $this->customer->id,
        ]);

        $this->assertCount(2, $invoice->fresh()->payments);
    }

    public function test_sales_return_belongs_to_customer(): void
    {
        $return = SalesReturn::factory()->create(['customer_id' => $this->customer->id]);

        $this->assertNotNull($return->customer);
        $this->assertEquals($this->customer->id, $return->customer->id);
    }

    public function test_sales_return_has_items(): void
    {
        $return = SalesReturn::factory()->create(['customer_id' => $this->customer->id]);
        SalesReturnItem::factory()->count(3)->create([
            'sales_return_id' => $return->id,
            'product_id' => $this->product->id,
        ]);

        $this->assertCount(3, $return->fresh()->items);
    }

    public function test_credit_note_belongs_to_customer(): void
    {
        $cn = CreditNote::factory()->create(['customer_id' => $this->customer->id]);

        $this->assertNotNull($cn->customer);
        $this->assertEquals($this->customer->id, $cn->customer->id);
    }

    public function test_purchase_return_belongs_to_supplier(): void
    {
        $return = PurchaseReturn::factory()->create(['supplier_id' => $this->supplier->id]);

        $this->assertNotNull($return->supplier);
        $this->assertEquals($this->supplier->id, $return->supplier->id);
    }
}
