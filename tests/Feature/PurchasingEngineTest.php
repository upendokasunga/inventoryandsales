<?php

namespace Tests\Feature;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Group;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseSuggestion;
use App\Models\Supplier;
use App\Models\SupplierPerformance;
use App\Models\SupplierPriceHistory;
use App\Models\User;
use App\Services\GoodsReceiptService;
use App\Services\PurchaseApprovalService;
use App\Services\PurchaseOrderService;
use App\Services\PurchaseSuggestionService;
use App\Services\SupplierAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PurchasingEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Supplier $supplier;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::factory()->create();
        $superGroup = Group::where('is_super_admin', true)->first();
        $superGroup->users()->attach($this->admin);

        $this->supplier = Supplier::factory()->create(['name' => 'Test Supplier']);
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'track_stock' => true,
            'reorder_level' => 50,
            'current_stock' => 10,
            'safety_stock' => 5,
        ]);
    }

    // Purchase Suggestions

    public function test_suggestion_index_is_accessible(): void
    {
        PurchaseSuggestion::factory()->create(['product_id' => $this->product->id]);

        $response = $this->actingAs($this->admin)->get(route('purchasing.suggestions.index'));

        $response->assertStatus(200);
        $response->assertSee('Purchase Suggestions');
    }

    public function test_suggestion_create_form_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('purchasing.suggestions.create'));

        $response->assertStatus(200);
        $response->assertSee('Product');
    }

    public function test_suggestion_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('purchasing.suggestions.store'), [
            'product_id' => $this->product->id,
            'suggested_quantity' => 100,
            'reason' => 'Low stock alert',
        ]);

        $response->assertRedirect(route('purchasing.suggestions.index'));
        $this->assertDatabaseHas('purchase_suggestions', [
            'product_id' => $this->product->id,
            'suggested_quantity' => 100,
        ]);
    }

    public function test_suggestion_requires_product(): void
    {
        $response = $this->actingAs($this->admin)->post(route('purchasing.suggestions.store'), [
            'suggested_quantity' => 100,
        ]);

        $response->assertSessionHasErrors('product_id');
    }

    public function test_suggestion_show_is_accessible(): void
    {
        $suggestion = PurchaseSuggestion::factory()->create(['product_id' => $this->product->id]);

        $response = $this->actingAs($this->admin)->get(route('purchasing.suggestions.show', $suggestion));

        $response->assertStatus(200);
        $response->assertSee($this->product->name);
    }

    public function test_suggestion_can_be_approved(): void
    {
        $suggestion = PurchaseSuggestion::factory()->create([
            'product_id' => $this->product->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchasing.suggestions.approve', $suggestion));

        $response->assertRedirect(route('purchasing.suggestions.index'));
        $this->assertEquals('approved', $suggestion->fresh()->status);
        $this->assertNotNull($suggestion->fresh()->reviewed_at);
    }

    public function test_suggestion_can_be_rejected(): void
    {
        $suggestion = PurchaseSuggestion::factory()->create([
            'product_id' => $this->product->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchasing.suggestions.reject', $suggestion), [
            'notes' => 'Not needed at this time',
        ]);

        $response->assertRedirect(route('purchasing.suggestions.index'));
        $this->assertEquals('rejected', $suggestion->fresh()->status);
    }

    public function test_suggestion_can_be_converted(): void
    {
        $suggestion = PurchaseSuggestion::factory()->create([
            'product_id' => $this->product->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchasing.suggestions.convert', $suggestion));

        $response->assertRedirect();
        $this->assertEquals('converted', $suggestion->fresh()->status);
    }

    public function test_suggestion_auto_generate_creates_suggestions(): void
    {
        Product::factory()->count(3)->create([
            'track_stock' => true,
            'reorder_level' => 25,
            'current_stock' => 5,
        ]);

        $service = app(PurchaseSuggestionService::class);
        $created = $service->generateSuggestions();

        $this->assertCount(4, $created);
    }

    public function test_suggestion_auto_generate_uses_correct_formula(): void
    {
        $service = app(PurchaseSuggestionService::class);
        $created = $service->generateSuggestions();

        $this->assertCount(1, $created);
        $expected = max(0, 50 - 10 + 5);
        $this->assertEquals($expected, $created[0]->suggested_quantity);
    }

    public function test_suggestion_auto_generate_skips_when_stock_above_reorder(): void
    {
        $this->product->update(['current_stock' => 100]);

        Product::factory()->create([
            'track_stock' => true,
            'reorder_level' => 50,
            'current_stock' => 100,
        ]);

        $service = app(PurchaseSuggestionService::class);
        $created = $service->generateSuggestions();

        $this->assertCount(0, $created);
    }

    public function test_suggestion_auto_generate_skips_existing_pending(): void
    {
        PurchaseSuggestion::factory()->create([
            'product_id' => $this->product->id,
            'status' => 'pending',
        ]);

        $service = app(PurchaseSuggestionService::class);
        $created = $service->generateSuggestions();

        $this->assertCount(0, $created);
    }

    // Purchase Orders

    public function test_order_index_is_accessible(): void
    {
        PurchaseOrder::factory()->create(['supplier_id' => $this->supplier->id]);

        $response = $this->actingAs($this->admin)->get(route('purchasing.orders.index'));

        $response->assertStatus(200);
        $response->assertSee('Purchase Orders');
    }

    public function test_order_create_form_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('purchasing.orders.create'));

        $response->assertStatus(200);
        $response->assertSee('Supplier');
    }

    public function test_order_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post(route('purchasing.orders.store'), [
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->format('Y-m-d'),
            'tax' => 0,
            'discount' => 0,
            'discount_type' => 'fixed',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 10, 'unit_price' => 500],
            ],
        ]);

        $response->assertRedirect(route('purchasing.orders.index'));
        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $this->supplier->id,
        ]);
        $this->assertDatabaseHas('purchase_order_items', [
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 500,
        ]);
    }

    public function test_order_requires_supplier(): void
    {
        $response = $this->actingAs($this->admin)->post(route('purchasing.orders.store'), [
            'order_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('supplier_id');
    }

    public function test_order_has_po_number_in_correct_format(): void
    {
        $order = PurchaseOrder::factory()->create(['supplier_id' => $this->supplier->id]);

        $this->assertNotNull($order->po_number);
        $this->assertStringStartsWith('PO-' . now()->year . '-', $order->po_number);
        $this->assertMatchesRegularExpression('/^PO-\d{4}-\d{6}$/', $order->po_number);
    }

    public function test_order_show_is_accessible(): void
    {
        $order = PurchaseOrder::factory()
            ->has(PurchaseOrderItem::factory()->count(2), 'items')
            ->create(['supplier_id' => $this->supplier->id]);

        $response = $this->actingAs($this->admin)->get(route('purchasing.orders.show', $order));

        $response->assertStatus(200);
        $response->assertSee($order->po_number);
    }

    public function test_order_can_be_updated_with_discount(): void
    {
        $order = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('purchasing.orders.update', $order), [
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->format('Y-m-d'),
            'tax' => 500,
            'discount' => 100,
            'discount_type' => 'fixed',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 20, 'unit_price' => 600],
            ],
        ]);

        $response->assertRedirect(route('purchasing.orders.show', $order));
        $this->assertEquals(500, $order->fresh()->tax);
        $this->assertEquals(100, $order->fresh()->discount);
        $this->assertEquals('fixed', $order->fresh()->discount_type);
    }

    public function test_order_edit_form_is_accessible(): void
    {
        $order = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->get(route('purchasing.orders.edit', $order));

        $response->assertStatus(200);
        $response->assertSee($order->po_number);
    }

    public function test_order_can_be_deleted(): void
    {
        $order = PurchaseOrder::factory()->create(['supplier_id' => $this->supplier->id]);

        $response = $this->actingAs($this->admin)->delete(route('purchasing.orders.destroy', $order));

        $response->assertRedirect(route('purchasing.orders.index'));
        $this->assertSoftDeleted($order);
    }

    public function test_order_can_be_searched(): void
    {
        $order = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('purchasing.orders.index', [
            'search' => $order->po_number,
        ]));

        $response->assertStatus(200);
        $response->assertSee($order->po_number);
    }

    public function test_order_filters_by_status(): void
    {
        PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->get(route('purchasing.orders.index', [
            'status' => 'draft',
        ]));

        $response->assertStatus(200);
    }

    public function test_order_totals_with_discount(): void
    {
        $response = $this->actingAs($this->admin)->post(route('purchasing.orders.store'), [
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->format('Y-m-d'),
            'tax' => 50,
            'discount' => 10,
            'discount_type' => 'percentage',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 10, 'unit_price' => 100],
            ],
        ]);

        $order = PurchaseOrder::first();
        $this->assertEquals(1000, $order->subtotal);
        $this->assertEquals(950, $order->total);
    }

    // Approval Workflow

    public function test_order_can_be_submitted_for_approval(): void
    {
        $order = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchasing.orders.submit-approval', $order));

        $response->assertRedirect(route('purchasing.orders.show', $order));
        $this->assertEquals('pending_approval', $order->fresh()->status);
    }

    public function test_order_can_be_approved(): void
    {
        $order = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchasing.orders.approve', $order));

        $response->assertRedirect(route('purchasing.orders.show', $order));
        $this->assertEquals('approved', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->approved_at);
    }

    public function test_order_can_be_rejected(): void
    {
        $order = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchasing.orders.reject', $order));

        $response->assertRedirect(route('purchasing.orders.show', $order));
        $this->assertEquals('draft', $order->fresh()->status);
    }

    public function test_order_can_be_sent_to_supplier(): void
    {
        $order = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchasing.orders.send', $order));

        $response->assertRedirect(route('purchasing.orders.show', $order));
        $this->assertEquals('sent', $order->fresh()->status);
    }

    public function test_order_can_be_cancelled(): void
    {
        $order = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchasing.orders.cancel', $order));

        $response->assertRedirect(route('purchasing.orders.show', $order));
        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    public function test_order_cannot_submit_approved_order_again(): void
    {
        $order = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchasing.orders.submit-approval', $order));

        $response->assertSessionHas('error');
        $this->assertEquals('approved', $order->fresh()->status);
    }

    public function test_order_cannot_approve_draft_directly(): void
    {
        $order = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchasing.orders.approve', $order));

        $response->assertSessionHas('error');
        $this->assertEquals('draft', $order->fresh()->status);
    }

    // Goods Receiving

    public function test_receipt_index_is_accessible(): void
    {
        $receipt = GoodsReceipt::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('purchasing.receipts.index'));

        $response->assertStatus(200);
        $response->assertSee('Goods Receipts');
    }

    public function test_receipt_create_form_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('purchasing.receipts.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Goods Receipt');
    }

    public function test_receipt_can_be_created(): void
    {
        $order = PurchaseOrder::factory()
            ->has(PurchaseOrderItem::factory()->count(2), 'items')
            ->create(['supplier_id' => $this->supplier->id, 'status' => 'sent']);

        $items = $order->items->map(fn($i) => [
            'purchase_order_item_id' => $i->id,
            'product_id' => $i->product_id,
            'expected_quantity' => $i->quantity,
            'received_quantity' => $i->quantity,
            'condition' => 'good',
        ])->toArray();

        $response = $this->actingAs($this->admin)->post(route('purchasing.receipts.store'), [
            'purchase_order_id' => $order->id,
            'receipt_date' => now()->format('Y-m-d'),
            'items' => $items,
        ]);

        $response->assertRedirect(route('purchasing.receipts.index'));
        $this->assertDatabaseHas('goods_receipts', [
            'purchase_order_id' => $order->id,
        ]);
    }

    public function test_receipt_has_receipt_number(): void
    {
        $order = PurchaseOrder::factory()
            ->has(PurchaseOrderItem::factory(), 'items')
            ->create(['supplier_id' => $this->supplier->id, 'status' => 'sent']);

        $items = $order->items->map(fn($i) => [
            'purchase_order_item_id' => $i->id,
            'product_id' => $i->product_id,
            'expected_quantity' => $i->quantity,
            'received_quantity' => $i->quantity,
        ])->toArray();

        $this->actingAs($this->admin)->post(route('purchasing.receipts.store'), [
            'purchase_order_id' => $order->id,
            'receipt_date' => now()->format('Y-m-d'),
            'items' => $items,
        ]);

        $receipt = GoodsReceipt::first();
        $this->assertNotNull($receipt->receipt_number);
        $this->assertStringStartsWith('GR-' . now()->year . '-', $receipt->receipt_number);
    }

    public function test_receipt_show_is_accessible(): void
    {
        $receipt = GoodsReceipt::factory()
            ->has(GoodsReceiptItem::factory()->count(2), 'items')
            ->create();

        $response = $this->actingAs($this->admin)->get(route('purchasing.receipts.show', $receipt));

        $response->assertStatus(200);
        $response->assertSee('Receipt');
    }

    public function test_receipt_complete_updates_po_quantities(): void
    {
        $poItem = PurchaseOrderItem::factory()->create([
            'quantity' => 50,
            'received_quantity' => 0,
        ]);
        $order = $poItem->purchaseOrder;
        $order->update(['status' => 'sent']);

        $receipt = GoodsReceipt::factory()->create([
            'purchase_order_id' => $order->id,
            'status' => 'draft',
        ]);
        GoodsReceiptItem::factory()->create([
            'goods_receipt_id' => $receipt->id,
            'product_id' => $poItem->product_id,
            'purchase_order_item_id' => $poItem->id,
            'expected_quantity' => 50,
            'received_quantity' => 50,
        ]);

        $response = $this->actingAs($this->admin)->patch(route('purchasing.receipts.complete', $receipt));

        $response->assertRedirect(route('purchasing.receipts.show', $receipt));
        $this->assertEquals('completed', $receipt->fresh()->status);
        $this->assertEquals(50, $poItem->fresh()->received_quantity);
        $this->assertEquals('completed', $order->fresh()->status);
    }

    public function test_receipt_partial_receive_sets_partial_status(): void
    {
        $poItem = PurchaseOrderItem::factory()->create([
            'quantity' => 100,
            'received_quantity' => 0,
        ]);
        $order = $poItem->purchaseOrder;
        $order->update(['status' => 'sent']);

        $receipt = GoodsReceipt::factory()->create([
            'purchase_order_id' => $order->id,
            'status' => 'draft',
        ]);
        GoodsReceiptItem::factory()->create([
            'goods_receipt_id' => $receipt->id,
            'product_id' => $poItem->product_id,
            'purchase_order_item_id' => $poItem->id,
            'expected_quantity' => 100,
            'received_quantity' => 30,
        ]);

        $this->actingAs($this->admin)->patch(route('purchasing.receipts.complete', $receipt));

        $this->assertEquals('partially_received', $order->fresh()->status);
        $this->assertEquals(30, $poItem->fresh()->received_quantity);
    }

    // Supplier Price History

    public function test_supplier_price_history_can_be_created_with_change_tracking(): void
    {
        $history = SupplierPriceHistory::create([
            'supplier_id' => $this->supplier->id,
            'product_id' => $this->product->id,
            'unit_price' => 1500,
            'previous_price' => 1200,
            'price_change' => 300,
            'currency' => 'TZS',
            'effective_date' => now()->format('Y-m-d'),
        ]);

        $this->assertDatabaseHas('supplier_price_history', [
            'supplier_id' => $this->supplier->id,
            'unit_price' => 1500,
            'price_change' => 300,
        ]);
    }

    // Supplier Performance

    public function test_supplier_performance_is_calculated(): void
    {
        PurchaseOrder::factory()->count(3)->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'completed',
            'total' => 50000,
        ]);

        $analytics = app(SupplierAnalyticsService::class);
        $analytics->recalculatePerformance();

        $this->assertDatabaseHas('supplier_performance', [
            'supplier_id' => $this->supplier->id,
            'total_orders' => 3,
            'total_purchase_value' => 150000,
        ]);
    }

    public function test_supplier_performance_tracks_on_time_rate(): void
    {
        $order = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'completed',
            'expected_date' => now()->addDays(7),
        ]);
        $order->update(['status' => 'completed']);

        $analytics = app(SupplierAnalyticsService::class);
        $analytics->recalculatePerformance();

        $perf = SupplierPerformance::where('supplier_id', $this->supplier->id)->first();
        $this->assertNotNull($perf);
        $this->assertGreaterThanOrEqual(0, $perf->on_time_rate);
    }

    // Supplier Analytics

    public function test_analytics_page_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('purchasing.analytics'));

        $response->assertStatus(200);
        $response->assertSee('Supplier Analytics');
    }

    public function test_analytics_returns_dashboard_stats(): void
    {
        PurchaseOrder::factory()->count(3)->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'completed',
        ]);

        $analytics = app(SupplierAnalyticsService::class);
        $stats = $analytics->getDashboardStats();

        $this->assertArrayHasKey('total_pos', $stats);
        $this->assertArrayHasKey('completed_pos', $stats);
        $this->assertArrayHasKey('total_spent', $stats);
        $this->assertArrayHasKey('active_suppliers', $stats);
        $this->assertEquals(3, $stats['completed_pos']);
    }

    public function test_analytics_returns_supplier_rankings(): void
    {
        PurchaseOrder::factory()->count(5)->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'completed',
            'total' => 10000,
        ]);

        $analytics = app(SupplierAnalyticsService::class);
        $rankings = $analytics->getSupplierRankings();

        $this->assertNotEmpty($rankings);
        $this->assertEquals(5, $rankings[0]['order_count']);
    }

    public function test_analytics_returns_purchase_trends(): void
    {
        PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'completed',
            'order_date' => now()->format('Y-m-d'),
        ]);

        $analytics = app(SupplierAnalyticsService::class);
        $trends = $analytics->getPurchaseTrends();

        $this->assertNotEmpty($trends);
        $this->assertEquals(1, $trends[0]['order_count']);
    }

    // Permissions

    public function test_non_admin_cannot_access_suggestions(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('purchasing.suggestions.index'));
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_orders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('purchasing.orders.index'));
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_receipts(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('purchasing.receipts.index'));
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_analytics(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('purchasing.analytics'));
        $response->assertStatus(403);
    }

    // Audit Logging

    public function test_suggestion_creation_creates_audit_log(): void
    {
        $suggestion = PurchaseSuggestion::factory()->create([
            'product_id' => $this->product->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => PurchaseSuggestion::class,
            'auditable_id' => $suggestion->id,
            'event' => 'created',
        ]);
    }

    public function test_order_creation_creates_audit_log(): void
    {
        $order = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => PurchaseOrder::class,
            'auditable_id' => $order->id,
            'event' => 'created',
        ]);
    }

    public function test_receipt_creation_creates_audit_log(): void
    {
        $receipt = GoodsReceipt::factory()->create();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => GoodsReceipt::class,
            'auditable_id' => $receipt->id,
            'event' => 'created',
        ]);
    }

    // Cache

    public function test_suggestion_stats_are_cached(): void
    {
        $service = app(PurchaseSuggestionService::class);
        $stats = $service->getStats();

        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('pending', $stats);
        $this->assertArrayHasKey('approved', $stats);
    }

    public function test_order_stats_are_cached(): void
    {
        $service = app(PurchaseOrderService::class);
        $stats = $service->getStats();

        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('draft', $stats);
        $this->assertArrayHasKey('completed', $stats);
    }

    public function test_analytics_cache_is_invalidated_on_po_change(): void
    {
        Cache::shouldReceive('forget')
            ->with('purchasing.analytics.dashboard')
            ->atLeast()->once();
        Cache::shouldReceive('forget')
            ->zeroOrMoreTimes();

        $order = PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'draft',
        ]);
        $order->update(['status' => 'completed']);
    }
}
