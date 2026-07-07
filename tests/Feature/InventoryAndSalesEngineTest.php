<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Group;
use App\Models\InventoryBalance;
use App\Models\InventoryBatch;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SoNumberSequence;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockReservation;
use App\Models\StockReservationItem;
use App\Models\Unit;
use App\Models\User;
use App\Services\BatchService;
use App\Services\ExpiryMonitoringService;
use App\Services\FifoService;
use App\Services\FulfillmentService;
use App\Services\InventoryAnalyticsService;
use App\Services\InventoryService;
use App\Services\InventoryValuationService;
use App\Services\ReservationService;
use App\Services\SalesOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class InventoryAndSalesEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Product $product;
    protected Product $product2;
    protected Unit $unit;
    protected Customer $customer;
    protected CustomerGroup $group;

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
            'name' => 'Test Product Alpha',
            'track_stock' => true,
            'current_stock' => 0,
        ]);

        $this->product2 = Product::factory()->create([
            'name' => 'Test Product Beta',
            'track_stock' => true,
            'current_stock' => 0,
        ]);

        $this->group = CustomerGroup::factory()->create(['name' => 'Test Group']);

        $this->customer = Customer::factory()->create([
            'name' => 'Sales Test Customer',
            'customer_group_id' => $this->group->id,
            'credit_limit' => 1000000,
            'available_credit' => 1000000,
            'outstanding_balance' => 0,
        ]);
    }

    // ================================================================
    //  INVENTORY DASHBOARD
    // ================================================================

    public function test_inventory_dashboard_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('inventory.index'));

        $response->assertStatus(200);
        $response->assertSee('Inventory');
    }

    // ================================================================
    //  INVENTORY TRANSACTIONS
    // ================================================================

    public function test_transaction_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('inventory.transactions'));

        $response->assertStatus(200);
        $response->assertSee('Transactions');
    }

    public function test_inventory_receive_creates_balance_and_batch(): void
    {
        $service = app(InventoryService::class);

        $tx = $service->receiveStock($this->product, 100, 250, 'BATCH-001');

        $this->assertInstanceOf(InventoryTransaction::class, $tx);
        $this->assertEquals(100, $tx->balance_after);

        $balance = InventoryBalance::where('product_id', $this->product->id)->first();
        $this->assertEquals(100, $balance->quantity_on_hand);

        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $this->product->id,
            'type' => 'purchase_receipt',
            'quantity' => 100,
            'unit_cost' => 250,
        ]);
    }

    public function test_inventory_issue_reduces_balance(): void
    {
        $service = app(InventoryService::class);

        $service->receiveStock($this->product, 100, 200, 'B-ISS');
        $result = $service->issueStock($this->product, 30);

        $balance = InventoryBalance::where('product_id', $this->product->id)->first();
        $this->assertEquals(70, $balance->quantity_on_hand);

        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $this->product->id,
            'type' => 'sales_order',
            'quantity' => -30,
        ]);
    }

    public function test_inventory_issue_fails_when_insufficient_stock(): void
    {
        $service = app(InventoryService::class);

        $this->expectException(\InvalidArgumentException::class);

        $service->issueStock($this->product, 10);
    }

    public function test_inventory_adjust_updates_balance(): void
    {
        $service = app(InventoryService::class);

        $service->receiveStock($this->product, 100, 200);
        $tx = $service->adjustStock($this->product, 100, 120, 200, 'recount');

        $balance = InventoryBalance::where('product_id', $this->product->id)->first();
        $this->assertEquals(120, $balance->quantity_on_hand);
    }

    public function test_transactions_list_shows_all_types(): void
    {
        $service = app(InventoryService::class);
        $service->receiveStock($this->product, 50, 100, 'B-001');

        $response = $this->actingAs($this->admin)->get(route('inventory.transactions'));

        $response->assertStatus(200);
        $response->assertSee($this->product->name);
    }

    // ================================================================
    //  INVENTORY VALUATION
    // ================================================================

    public function test_valuation_page_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('inventory.valuation'));

        $response->assertStatus(200);
        $response->assertSee('Valuation');
    }

    public function test_valuation_returns_average_cost(): void
    {
        $service = app(InventoryService::class);
        $service->receiveStock($this->product, 100, 200);
        $service->receiveStock($this->product, 100, 300);

        $valuationService = app(InventoryValuationService::class);
        $valuation = $valuationService->getValuation($this->product->id);

        $this->assertArrayHasKey('weighted_average_cost', $valuation);
        $this->assertGreaterThan(0, $valuation['total_value']);
    }

    public function test_valuation_returns_fifo_value(): void
    {
        $service = app(InventoryService::class);
        $service->receiveStock($this->product, 100, 200, 'BATCH-FIFO-1');
        $service->receiveStock($this->product, 100, 300, 'BATCH-FIFO-2');

        $valuationService = app(InventoryValuationService::class);
        $fifoValue = $valuationService->getFifoValuation($this->product->id);

        $this->assertGreaterThan(0, $fifoValue['total_value']);
        $this->assertArrayHasKey('batches', $fifoValue);
    }

    public function test_valuation_page_shows_products(): void
    {
        InventoryBalance::factory()->create([
            'product_id' => $this->product->id,
            'quantity_on_hand' => 50,
            'average_cost' => 100,
            'total_value' => 5000,
        ]);

        $response = $this->actingAs($this->admin)->get(route('inventory.valuation'));

        $response->assertStatus(200);
        $response->assertSee($this->product->name);
    }

    // ================================================================
    //  INVENTORY ANALYTICS
    // ================================================================

    public function test_analytics_page_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('inventory.analytics'));

        $response->assertStatus(200);
        $response->assertSee('Analytics');
    }

    public function test_analytics_returns_dashboard_stats(): void
    {
        InventoryBalance::factory()->create([
            'product_id' => $this->product->id,
            'quantity_on_hand' => 100,
            'average_cost' => 50,
            'total_value' => 5000,
        ]);

        $analytics = app(InventoryAnalyticsService::class);
        $stats = $analytics->getDashboardStats();

        $this->assertArrayHasKey('total_products', $stats);
        $this->assertArrayHasKey('total_quantity_on_hand', $stats);
        $this->assertArrayHasKey('total_value', $stats);
    }

    public function test_analytics_returns_stock_distribution(): void
    {
        InventoryBalance::factory()->create([
            'product_id' => $this->product->id,
            'quantity_on_hand' => 200,
        ]);

        $analytics = app(InventoryAnalyticsService::class);
        $dist = $analytics->getStockStatusDistribution();

        $this->assertNotEmpty($dist);
    }

    public function test_analytics_returns_recent_transactions(): void
    {
        InventoryTransaction::factory()->create([
            'product_id' => $this->product->id,
            'type' => 'purchase_receipt',
            'quantity' => 50,
        ]);

        $analytics = app(InventoryAnalyticsService::class);
        $recent = $analytics->getRecentTransactions();

        $this->assertNotEmpty($recent);
    }

    // ================================================================
    //  BATCHES
    // ================================================================

    public function test_batch_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('inventory.batches'));

        $response->assertStatus(200);
        $response->assertSee('Batch Tracking');
    }

    public function test_batch_service_lists_active_batches(): void
    {
        InventoryBatch::factory()->create([
            'product_id' => $this->product->id,
            'status' => 'active',
            'quantity_remaining' => 50,
        ]);

        $service = app(BatchService::class);
        $batches = $service->getActiveBatches($this->product->id);

        $this->assertCount(1, $batches);
        $this->assertEquals(50, $batches->first()->quantity_remaining);
    }

    public function test_batch_service_marks_batch_depleted(): void
    {
        $batch = InventoryBatch::factory()->create([
            'product_id' => $this->product->id,
            'status' => 'active',
            'quantity_remaining' => 0,
        ]);

        $service = app(BatchService::class);
        $service->markExhausted($batch);

        $this->assertEquals('exhausted', $batch->fresh()->status);
    }

    // ================================================================
    //  EXPIRY MONITORING
    // ================================================================

    public function test_expiry_monitoring_returns_expiring_soon(): void
    {
        $expiryService = app(ExpiryMonitoringService::class);
        $expiryService->invalidateCache();

        InventoryBatch::factory()->create([
            'product_id' => $this->product->id,
            'status' => 'active',
            'quantity_remaining' => 100,
            'expiry_date' => now()->addDays(15),
        ]);

        $service = app(ExpiryMonitoringService::class);
        $result = $service->getExpiringBatches(30);

        $this->assertArrayHasKey('batches', $result);
        $this->assertGreaterThanOrEqual(1, $result['total_products']);
    }

    public function test_expiry_monitoring_ignores_non_expiring(): void
    {
        $expiryService = app(ExpiryMonitoringService::class);
        $expiryService->invalidateCache();

        InventoryBatch::factory()->create([
            'product_id' => $this->product->id,
            'status' => 'active',
            'expiry_date' => now()->addMonths(6),
        ]);

        $service = app(ExpiryMonitoringService::class);
        $result = $service->getExpiringBatches(30);

        $this->assertEquals(0, $result['total_products']);
    }

    public function test_expiry_monitoring_auto_expires(): void
    {
        InventoryBatch::factory()->create([
            'product_id' => $this->product->id,
            'status' => 'active',
            'quantity_remaining' => 50,
            'expiry_date' => now()->subDay(),
        ]);

        $service = app(ExpiryMonitoringService::class);
        $count = $service->markExpired();

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('inventory_batches', [
            'product_id' => $this->product->id,
            'status' => 'expired',
        ]);
    }

    public function test_expiry_monitoring_returns_correct_counts(): void
    {
        $expiryService = app(ExpiryMonitoringService::class);
        $expiryService->invalidateCache();

        InventoryBatch::factory()->create([
            'product_id' => $this->product->id,
            'status' => 'active',
            'quantity_remaining' => 30,
            'expiry_date' => now()->addDays(3),
        ]);

        $service = app(ExpiryMonitoringService::class);
        $result = $service->getExpiringBatches(7);

        $this->assertArrayHasKey('critical_count', $result);
        $this->assertGreaterThan(0, $result['critical_count']);
    }

    // ================================================================
    //  FIFO SERVICE
    // ================================================================

    public function test_fifo_allocates_from_earliest_batch(): void
    {
        InventoryBatch::factory()->create([
            'product_id' => $this->product->id,
            'batch_number' => 'OLD-BATCH',
            'quantity_remaining' => 100,
            'unit_cost' => 100,
            'status' => 'active',
            'created_at' => now()->subDays(10),
        ]);

        InventoryBatch::factory()->create([
            'product_id' => $this->product->id,
            'batch_number' => 'NEW-BATCH',
            'quantity_remaining' => 100,
            'unit_cost' => 200,
            'status' => 'active',
            'created_at' => now()->subDays(1),
        ]);

        $service = app(FifoService::class);

        $allocations = $service->allocateCost($this->product, 150);

        $this->assertCount(2, $allocations);
        $this->assertEquals('OLD-BATCH', $allocations[0]['batch_number']);
        $this->assertEquals(100, $allocations[0]['quantity']);
        $this->assertEquals(100, $allocations[0]['unit_cost']);
        $this->assertEquals('NEW-BATCH', $allocations[1]['batch_number']);
        $this->assertEquals(50, $allocations[1]['quantity']);
        $this->assertEquals(200, $allocations[1]['unit_cost']);
    }

    public function test_fifo_allocates_full_batch(): void
    {
        InventoryBatch::factory()->create([
            'product_id' => $this->product->id,
            'quantity_remaining' => 50,
            'unit_cost' => 150,
            'status' => 'active',
        ]);

        $service = app(FifoService::class);
        $allocations = $service->allocateCost($this->product, 50);

        $this->assertCount(1, $allocations);
        $this->assertEquals(50, $allocations[0]['quantity']);
    }

    public function test_fifo_throws_on_insufficient_stock(): void
    {
        InventoryBatch::factory()->create([
            'product_id' => $this->product->id,
            'quantity_remaining' => 10,
            'status' => 'active',
        ]);

        $service = app(FifoService::class);

        $this->expectException(\RuntimeException::class);

        $service->allocateCost($this->product, 100);
    }

    public function test_fifo_returns_average_cost(): void
    {
        InventoryBatch::factory()->create([
            'product_id' => $this->product->id,
            'quantity_remaining' => 100,
            'unit_cost' => 100,
            'status' => 'active',
        ]);
        InventoryBatch::factory()->create([
            'product_id' => $this->product->id,
            'quantity_remaining' => 100,
            'unit_cost' => 300,
            'status' => 'active',
        ]);

        $service = app(FifoService::class);
        $avgCost = $service->getCurrentAverageCost($this->product);

        $this->assertEquals(200, $avgCost);
    }

    // ================================================================
    //  STOCK ADJUSTMENTS
    // ================================================================

    public function test_stock_adjustment_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('stock-adjustments.index'));

        $response->assertStatus(200);
        $response->assertSee('Stock Adjustments');
    }

    public function test_stock_adjustment_create_form_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('stock-adjustments.create'));

        $response->assertStatus(200);
        $response->assertSee('Create');
    }

    public function test_stock_adjustment_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('stock-adjustments.store'), [
            'type' => 'positive',
            'reason' => 'recount',
            'description' => 'Found extra stock during count',
            'items' => [
                ['product_id' => $this->product->id, 'expected_quantity' => 0, 'actual_quantity' => 50, 'difference' => 50],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('stock_adjustments', [
            'type' => 'positive',
            'reason' => 'recount',
        ]);
        $this->assertDatabaseHas('stock_adjustment_items', [
            'product_id' => $this->product->id,
            'difference' => 50,
        ]);
    }

    public function test_stock_adjustment_show_is_accessible(): void
    {
        $adjustment = StockAdjustment::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('stock-adjustments.show', $adjustment));

        $response->assertStatus(200);
    }

    public function test_stock_adjustment_requires_type(): void
    {
        $response = $this->actingAs($this->admin)->post(route('stock-adjustments.store'), [
            'reason' => 'damaged',
        ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_stock_adjustment_requires_reason(): void
    {
        $response = $this->actingAs($this->admin)->post(route('stock-adjustments.store'), [
            'type' => 'negative',
        ]);

        $response->assertSessionHasErrors('reason');
    }

    public function test_stock_adjustment_can_be_deleted(): void
    {
        $adjustment = StockAdjustment::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('stock-adjustments.destroy', $adjustment));

        $response->assertRedirect(route('stock-adjustments.index'));
        $this->assertSoftDeleted($adjustment);
    }

    // ================================================================
    //  SALES DASHBOARD
    // ================================================================

    public function test_sales_dashboard_redirects_to_main(): void
    {
        $response = $this->actingAs($this->admin)->get(route('sales.dashboard'));

        $response->assertRedirect(route('dashboard'));
    }

    // ================================================================
    //  SALES ORDERS
    // ================================================================

    public function test_sales_order_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('sales.orders.index'));

        $response->assertStatus(200);
        $response->assertSee('Orders');
    }

    public function test_sales_order_create_form_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('sales.orders.create'));

        $response->assertStatus(200);
        $response->assertSee('Customer');
    }

    public function test_sales_order_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('sales.orders.store'), [
            'customer_id' => $this->customer->id,
            'order_date' => now()->format('Y-m-d'),
            'payment_terms' => 'Net 30',
            'tax' => 0,
            'discount' => 0,
            'discount_type' => 'fixed',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 5, 'unit_price' => 1000],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales_orders', [
            'customer_id' => $this->customer->id,
        ]);
        $this->assertDatabaseHas('sales_order_items', [
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => 1000,
        ]);
    }

    public function test_sales_order_requires_customer(): void
    {
        $response = $this->actingAs($this->admin)->post(route('sales.orders.store'), [
            'order_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('customer_id');
    }

    public function test_sales_order_has_so_number_in_correct_format(): void
    {
        $order = SalesOrder::factory()->create(['customer_id' => $this->customer->id]);

        $this->assertNotNull($order->so_number);
        $this->assertStringStartsWith('SO-' . now()->year . '-', $order->so_number);
        $this->assertMatchesRegularExpression('/^SO-\d{4}-\d{6}$/', $order->so_number);
    }

    public function test_sales_order_show_is_accessible(): void
    {
        $order = SalesOrder::factory()
            ->has(SalesOrderItem::factory()->count(2), 'items')
            ->create(['customer_id' => $this->customer->id]);

        $response = $this->actingAs($this->admin)->get(route('sales.orders.show', $order));

        $response->assertStatus(200);
        $response->assertSee($order->so_number);
    }

    public function test_sales_order_can_be_updated(): void
    {
        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('sales.orders.update', $order), [
            'customer_id' => $this->customer->id,
            'order_date' => now()->format('Y-m-d'),
            'payment_terms' => 'Cash',
            'tax' => 1000,
            'discount' => 200,
            'discount_type' => 'fixed',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 10, 'unit_price' => 2000],
            ],
        ]);

        $response->assertRedirect();
        $this->assertEquals('Cash', $order->fresh()->payment_terms);
        $this->assertEquals(1000, $order->fresh()->tax);
    }

    public function test_sales_order_edit_form_is_accessible(): void
    {
        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->get(route('sales.orders.edit', $order));

        $response->assertStatus(200);
        $response->assertSee($order->so_number);
    }

    public function test_sales_order_can_be_deleted(): void
    {
        $order = SalesOrder::factory()->create(['customer_id' => $this->customer->id]);

        $response = $this->actingAs($this->admin)->delete(route('sales.orders.destroy', $order));

        $response->assertRedirect(route('sales.orders.index'));
        $this->assertSoftDeleted($order);
    }

    public function test_sales_order_can_be_searched(): void
    {
        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('sales.orders.index', [
            'search' => $order->so_number,
        ]));

        $response->assertStatus(200);
        $response->assertSee($order->so_number);
    }

    public function test_sales_order_filters_by_status(): void
    {
        SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->get(route('sales.orders.index', [
            'status' => 'draft',
        ]));

        $response->assertStatus(200);
    }

    public function test_sales_order_totals_with_tax_and_discount(): void
    {
        $response = $this->actingAs($this->admin)->post(route('sales.orders.store'), [
            'customer_id' => $this->customer->id,
            'order_date' => now()->format('Y-m-d'),
            'payment_terms' => 'Net 30',
            'tax' => 500,
            'discount' => 100,
            'discount_type' => 'fixed',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 10, 'unit_price' => 1000],
            ],
        ]);

        $order = SalesOrder::first();
        $this->assertEquals(10000, $order->subtotal);
        $this->assertEquals(10400, $order->total);
    }

    public function test_sales_order_totals_with_percentage_discount(): void
    {
        $response = $this->actingAs($this->admin)->post(route('sales.orders.store'), [
            'customer_id' => $this->customer->id,
            'order_date' => now()->format('Y-m-d'),
            'payment_terms' => 'Net 30',
            'tax' => 0,
            'discount' => 10,
            'discount_type' => 'percentage',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 10, 'unit_price' => 1000],
            ],
        ]);

        $order = SalesOrder::first();
        $this->assertEquals(10000, $order->subtotal);
        $this->assertEquals(9000, $order->total);
    }

    // ================================================================
    //  SALES ORDER APPROVAL WORKFLOW
    // ================================================================

    public function test_sales_order_can_be_submitted_for_approval(): void
    {
        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'draft',
            'total' => 1000,
        ]);

        $response = $this->actingAs($this->admin)->post(route('sales.orders.submit-approval', $order));

        $response->assertRedirect(route('sales.orders.show', $order));
        $this->assertEquals('pending_approval', $order->fresh()->status);
    }

    public function test_sales_order_can_be_approved(): void
    {
        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($this->admin)->post(route('sales.orders.approve', $order));

        $response->assertRedirect(route('sales.orders.show', $order));
        $this->assertEquals('approved', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->approved_at);
    }

    public function test_sales_order_can_be_rejected(): void
    {
        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($this->admin)->post(route('sales.orders.reject', $order));

        $response->assertRedirect(route('sales.orders.show', $order));
        $this->assertEquals('draft', $order->fresh()->status);
    }

    public function test_sales_order_can_be_cancelled(): void
    {
        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->post(route('sales.orders.cancel', $order));

        $response->assertRedirect(route('sales.orders.show', $order));
        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    public function test_sales_order_cannot_submit_approved_order_again(): void
    {
        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->post(route('sales.orders.submit-approval', $order));

        $response->assertSessionHas('error');
        $this->assertEquals('approved', $order->fresh()->status);
    }

    public function test_sales_order_cannot_approve_draft_directly(): void
    {
        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->post(route('sales.orders.approve', $order));

        $response->assertSessionHas('error');
        $this->assertEquals('draft', $order->fresh()->status);
    }

    // ================================================================
    //  CREDIT INTEGRATION WITH SALES ORDERS
    // ================================================================

    public function test_sales_order_submit_rejects_when_insufficient_credit(): void
    {
        $this->customer->update(['credit_limit' => 10000, 'available_credit' => 10000]);

        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'draft',
            'total' => 50000,
        ]);

        $response = $this->actingAs($this->admin)->post(route('sales.orders.submit-approval', $order));

        $response->assertSessionHas('error');
        $this->assertEquals('draft', $order->fresh()->status);
    }

    public function test_sales_order_submit_fails_on_credit_hold(): void
    {
        $this->customer->update([
            'credit_status' => 'suspended',
            'credit_hold_at' => now(),
        ]);

        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'draft',
            'total' => 1000,
        ]);

        $response = $this->actingAs($this->admin)->post(route('sales.orders.submit-approval', $order));

        $response->assertSessionHas('error');
        $this->assertEquals('draft', $order->fresh()->status);
    }

    // ================================================================
    //  STOCK RESERVATIONS (Controller based)
    // ================================================================

    public function test_reservation_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('sales.reservations.index'));

        $response->assertStatus(200);
        $response->assertSee('Reservations');
    }

    public function test_reservation_show_is_accessible(): void
    {
        $reservation = StockReservation::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('sales.reservations.show', $reservation));

        $response->assertStatus(200);
    }

    // ================================================================
    //  RESERVATION SERVICE
    // ================================================================

    public function test_reservation_service_reserves_from_batch(): void
    {
        $service = app(InventoryService::class);
        $service->receiveStock($this->product, 100, 200, 'B-RSV-1');

        $batch = InventoryBatch::where('product_id', $this->product->id)->first();
        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'approved',
        ]);
        SalesOrderItem::factory()->create([
            'sales_order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 40,
        ]);

        $reservationService = app(ReservationService::class);
        $reservation = $reservationService->reserve($order);

        $this->assertEquals('active', $reservation->status);
        $this->assertDatabaseHas('stock_reservation_items', [
            'stock_reservation_id' => $reservation->id,
            'inventory_batch_id' => $batch->id,
        ]);
    }

    public function test_reservation_service_release_restores_availability(): void
    {
        $service = app(InventoryService::class);
        $service->receiveStock($this->product, 100, 200, 'B-RSV-2');

        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'approved',
        ]);
        SalesOrderItem::factory()->create([
            'sales_order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 30,
        ]);

        $reservationService = app(ReservationService::class);
        $reservation = $reservationService->reserve($order, now()->addDays(3));

        $reservationService->release($reservation);

        $this->assertEquals('released', $reservation->fresh()->status);
    }

    public function test_reservation_service_has_sufficient_stock_returns_false(): void
    {
        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
        ]);
        SalesOrderItem::factory()->create([
            'sales_order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 999,
        ]);

        $reservationService = app(ReservationService::class);

        $this->assertFalse($reservationService->hasSufficientStock($order));
    }

    // ================================================================
    //  FULFILLMENT SERVICE
    // ================================================================

    public function test_fulfillment_service_fulfills_order(): void
    {
        $service = app(InventoryService::class);
        $service->receiveStock($this->product, 100, 200, 'B-FUL-1');

        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'reserved',
            'total' => 50000,
        ]);
        SalesOrderItem::factory()->create([
            'sales_order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 30,
            'unit_price' => 1000,
            'subtotal' => 30000,
            'total' => 30000,
        ]);

        StockReservation::factory()->create([
            'sales_order_id' => $order->id,
            'status' => 'active',
        ]);

        $fulfillmentService = app(FulfillmentService::class);
        $fulfillmentService->fulfill($order);

        $this->assertEquals('fulfilled', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->fulfilled_at);

        $this->assertGreaterThan(0, $this->customer->fresh()->outstanding_balance);
    }

    public function test_fulfillment_service_requires_active_reservation(): void
    {
        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'reserved',
        ]);

        $fulfillmentService = app(FulfillmentService::class);

        $this->expectException(\InvalidArgumentException::class);

        $fulfillmentService->fulfill($order);
    }

    public function test_fulfillment_service_deducts_stock(): void
    {
        $service = app(InventoryService::class);
        $service->receiveStock($this->product, 100, 200, 'B-FUL-2');

        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'reserved',
            'total' => 10000,
        ]);
        SalesOrderItem::factory()->create([
            'sales_order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 20,
            'unit_price' => 500,
            'subtotal' => 10000,
            'total' => 10000,
        ]);

        StockReservation::factory()->create([
            'sales_order_id' => $order->id,
            'status' => 'active',
        ]);

        $fulfillmentService = app(FulfillmentService::class);
        $fulfillmentService->fulfill($order);

        $balance = InventoryBalance::where('product_id', $this->product->id)->first();
        $this->assertEquals(80, $balance->quantity_on_hand);
    }

    public function test_fulfillment_service_records_transactions(): void
    {
        $service = app(InventoryService::class);
        $service->receiveStock($this->product, 200, 150, 'B-FUL-3');

        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'reserved',
            'total' => 5000,
        ]);
        SalesOrderItem::factory()->create([
            'sales_order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 500,
            'subtotal' => 5000,
            'total' => 5000,
        ]);

        StockReservation::factory()->create([
            'sales_order_id' => $order->id,
            'status' => 'active',
        ]);

        $fulfillmentService = app(FulfillmentService::class);
        $fulfillmentService->fulfill($order);

        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $this->product->id,
            'type' => 'sales_order',
            'quantity' => -10,
        ]);
    }

    // ================================================================
    //  SALES ORDER SERVICE STATS
    // ================================================================

    public function test_sales_order_service_returns_stats(): void
    {
        $service = app(SalesOrderService::class);
        $stats = $service->getStats();

        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('draft', $stats);
        $this->assertArrayHasKey('fulfilled', $stats);
    }

    // ================================================================
    //  PERMISSIONS
    // ================================================================

    public function test_non_admin_cannot_access_inventory(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('inventory.index'));
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_transactions(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('inventory.transactions'));
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_stock_adjustments(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('stock-adjustments.index'));
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_sales_orders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('sales.orders.index'));
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_sales_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('sales.dashboard'));
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_reservations(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('sales.reservations.index'));
        $response->assertStatus(403);
    }

    // ================================================================
    //  AUDIT LOGGING
    // ================================================================

    public function test_stock_adjustment_creation_creates_audit_log(): void
    {
        $adjustment = StockAdjustment::factory()->create();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => StockAdjustment::class,
            'auditable_id' => $adjustment->id,
            'event' => 'created',
        ]);
    }

    public function test_sales_order_creation_creates_audit_log(): void
    {
        $order = SalesOrder::factory()->create(['customer_id' => $this->customer->id]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => SalesOrder::class,
            'auditable_id' => $order->id,
            'event' => 'created',
        ]);
    }

    // ================================================================
    //  CACHE
    // ================================================================

    public function test_inventory_analytics_are_cached(): void
    {
        $analytics = app(InventoryAnalyticsService::class);
        $stats = $analytics->getDashboardStats();

        $this->assertArrayHasKey('total_products', $stats);
        $this->assertArrayHasKey('total_quantity_on_hand', $stats);
    }

    public function test_sales_order_stats_are_cached(): void
    {
        $service = app(SalesOrderService::class);
        $stats = $service->getStats();

        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('draft', $stats);
    }

    public function test_cache_is_invalidated_on_order_change(): void
    {
        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'draft',
            'total' => 1000,
        ]);

        Cache::shouldReceive('forget')
            ->with('sales.order.stats')
            ->atLeast()->once();
        Cache::shouldReceive('forget')
            ->zeroOrMoreTimes();

        $order->update(['status' => 'fulfilled']);
    }

    // ================================================================
    //  MODELS RELATIONS
    // ================================================================

    public function test_sales_order_belongs_to_customer(): void
    {
        $order = SalesOrder::factory()->create(['customer_id' => $this->customer->id]);

        $this->assertNotNull($order->customer);
        $this->assertEquals($this->customer->id, $order->customer->id);
    }

    public function test_sales_order_has_items(): void
    {
        $order = SalesOrder::factory()
            ->has(SalesOrderItem::factory()->count(3), 'items')
            ->create(['customer_id' => $this->customer->id]);

        $this->assertCount(3, $order->items);
    }

    public function test_stock_adjustment_has_items(): void
    {
        $adjustment = StockAdjustment::factory()
            ->has(StockAdjustmentItem::factory()->count(2), 'items')
            ->create();

        $this->assertCount(2, $adjustment->items);
    }

    public function test_inventory_batch_belongs_to_product(): void
    {
        $batch = InventoryBatch::factory()->create(['product_id' => $this->product->id]);

        $this->assertNotNull($batch->product);
        $this->assertEquals($this->product->id, $batch->product->id);
    }

    public function test_inventory_balance_belongs_to_product(): void
    {
        $balance = InventoryBalance::factory()->create(['product_id' => $this->product->id]);

        $this->assertNotNull($balance->product);
        $this->assertEquals($this->product->id, $balance->product->id);
    }

    public function test_stock_reservation_belongs_to_sales_order(): void
    {
        $order = SalesOrder::factory()->create(['customer_id' => $this->customer->id]);
        $reservation = StockReservation::factory()->create(['sales_order_id' => $order->id]);

        $this->assertNotNull($reservation->salesOrder);
        $this->assertEquals($order->id, $reservation->salesOrder->id);
    }

    // ================================================================
    //  SO NUMBER SEQUENCE
    // ================================================================

    public function test_so_number_sequence_increments(): void
    {
        SoNumberSequence::create([
            'year' => now()->year,
            'last_number' => 0,
        ]);

        $order1 = SalesOrder::factory()->create(['customer_id' => $this->customer->id]);
        $order2 = SalesOrder::factory()->create(['customer_id' => $this->customer->id]);

        $this->assertMatchesRegularExpression('/SO-\d{4}-000001/', $order1->so_number);
        $this->assertMatchesRegularExpression('/SO-\d{4}-000002/', $order2->so_number);
    }

    // ================================================================
    //  INVENTORY SERVICE EDGE CASES
    // ================================================================

    public function test_inventory_receive_with_zero_quantity(): void
    {
        $service = app(InventoryService::class);

        $tx = $service->receiveStock($this->product, 0, 100);

        $balance = InventoryBalance::where('product_id', $this->product->id)->first();
        $this->assertEquals(0, $balance->quantity_on_hand);
    }

    // ================================================================
    //  SALES ORDER OBSERVER EDGE CASES
    // ================================================================

    public function test_sales_order_observer_generates_so_number_on_create(): void
    {
        $order = SalesOrder::factory()->create(['customer_id' => $this->customer->id]);

        $this->assertNotNull($order->so_number);
        $this->assertStringContainsString('SO-', $order->so_number);
    }

    public function test_sales_order_observer_does_not_overwrite_existing_so_number(): void
    {
        $order = SalesOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'so_number' => 'CUSTOM-001',
        ]);

        $this->assertEquals('CUSTOM-001', $order->fresh()->so_number);
    }
}
