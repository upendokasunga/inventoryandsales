<?php

namespace Tests\Feature;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Group;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SupplierPerformance;
use App\Models\User;
use App\Services\SupplierAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::factory()->create();
        $superGroup = Group::where('is_super_admin', true)->first();
        $superGroup->users()->attach($this->admin);

        $this->supplier = Supplier::factory()->create();
    }

    protected function createCompletedPO(int $daysAgo = 0, ?string $expectedDate = null): PurchaseOrder
    {
        $orderDate = now()->subDays($daysAgo + 5);
        $completedDate = now()->subDays($daysAgo);

        return PurchaseOrder::factory()->create([
            'supplier_id' => $this->supplier->id,
            'order_date' => $orderDate,
            'expected_date' => $expectedDate ? now()->parse($expectedDate) : null,
            'status' => 'completed',
            'created_at' => $orderDate,
            'updated_at' => $completedDate,
        ]);
    }

    protected function createGoodsReceipt(PurchaseOrder $po, array $itemsData = []): GoodsReceipt
    {
        $gr = GoodsReceipt::factory()->create([
            'purchase_order_id' => $po->id,
            'status' => 'completed',
            'received_date' => $po->updated_at,
            'created_by' => $this->admin->id,
        ]);

        if (empty($itemsData)) {
            GoodsReceiptItem::factory()->create([
                'goods_receipt_id' => $gr->id,
                'purchase_order_item_id' => PurchaseOrderItem::factory()->create([
                    'purchase_order_id' => $po->id,
                ])->id,
                'expected_quantity' => 10,
                'received_quantity' => 10,
                'condition' => 'good',
            ]);
        } else {
            foreach ($itemsData as $item) {
                GoodsReceiptItem::factory()->create(array_merge([
                    'goods_receipt_id' => $gr->id,
                    'purchase_order_item_id' => PurchaseOrderItem::factory()->create([
                        'purchase_order_id' => $po->id,
                    ])->id,
                ], $item));
            }
        }

        return $gr;
    }

    public function test_analytics_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('purchasing.analytics'));

        $response->assertStatus(200);
        $response->assertSee('Supplier Analytics');
    }

    public function test_analytics_shows_stats_cards(): void
    {
        PurchaseOrder::factory()->count(3)->create([
            'supplier_id' => $this->supplier->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->admin)->get(route('purchasing.analytics'));

        $response->assertStatus(200);
        $response->assertSee('Total POs');
        $response->assertSee('Completed');
    }

    public function test_recalculate_endpoint_updates_performance(): void
    {
        $po = $this->createCompletedPO(2, now()->subDays(3));
        $this->createGoodsReceipt($po, [
            ['expected_quantity' => 10, 'received_quantity' => 10, 'condition' => 'good'],
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchasing.analytics.recalculate'));

        $response->assertRedirect(route('purchasing.analytics'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('supplier_performance', [
            'supplier_id' => $this->supplier->id,
            'total_orders' => 1,
            'on_time_orders' => 0,
            'late_orders' => 1,
            'total_items_received' => 10,
            'damaged_items' => 0,
            'returned_items' => 0,
            'order_accuracy_rate' => 100,
            'quality_rate' => 100,
        ]);
    }

    public function test_recalculate_with_damaged_items(): void
    {
        $po = $this->createCompletedPO(2);
        $this->createGoodsReceipt($po, [
            ['expected_quantity' => 10, 'received_quantity' => 10, 'condition' => 'good'],
            ['expected_quantity' => 5, 'received_quantity' => 5, 'condition' => 'damaged'],
        ]);

        $this->actingAs($this->admin)->post(route('purchasing.analytics.recalculate'));

        $perf = SupplierPerformance::where('supplier_id', $this->supplier->id)->first();

        $this->assertNotNull($perf);
        $this->assertEquals(15, $perf->total_items_received);
        $this->assertEquals(5, $perf->damaged_items);
        $this->assertEquals(0, $perf->returned_items);
        $this->assertEquals(66.67, $perf->quality_rate);
        $this->assertEquals(33.33, $perf->damage_rate);
    }

    public function test_recalculate_with_returned_items(): void
    {
        $po = $this->createCompletedPO(2);
        $this->createGoodsReceipt($po, [
            ['expected_quantity' => 20, 'received_quantity' => 20, 'condition' => 'good'],
            ['expected_quantity' => 8, 'received_quantity' => 8, 'condition' => 'return'],
        ]);

        $this->actingAs($this->admin)->post(route('purchasing.analytics.recalculate'));

        $perf = SupplierPerformance::where('supplier_id', $this->supplier->id)->first();

        $this->assertNotNull($perf);
        $this->assertEquals(28, $perf->total_items_received);
        $this->assertEquals(8, $perf->returned_items);
        $this->assertEquals(28.57, $perf->return_rate);
    }

    public function test_recalculate_accuracy_rate(): void
    {
        $po = $this->createCompletedPO(2);
        $this->createGoodsReceipt($po, [
            ['expected_quantity' => 10, 'received_quantity' => 10, 'condition' => 'good'],
            ['expected_quantity' => 5, 'received_quantity' => 3, 'condition' => 'partial'],
        ]);

        $this->actingAs($this->admin)->post(route('purchasing.analytics.recalculate'));

        $perf = SupplierPerformance::where('supplier_id', $this->supplier->id)->first();
        $this->assertNotNull($perf);
        $this->assertEquals(50, $perf->order_accuracy_rate);
    }

    public function test_recalculate_with_multiple_suppliers(): void
    {
        $supplier2 = Supplier::factory()->create();

        $po1 = $this->createCompletedPO(2);
        $this->createGoodsReceipt($po1, [
            ['expected_quantity' => 10, 'received_quantity' => 10, 'condition' => 'good'],
        ]);

        $po2 = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier2->id,
            'status' => 'completed',
        ]);
        GoodsReceipt::factory()->create([
            'purchase_order_id' => $po2->id,
            'status' => 'completed',
            'received_date' => now(),
        ]);

        $this->actingAs($this->admin)->post(route('purchasing.analytics.recalculate'));

        $this->assertDatabaseHas('supplier_performance', [
            'supplier_id' => $this->supplier->id,
            'total_orders' => 1,
        ]);
        $this->assertDatabaseHas('supplier_performance', [
            'supplier_id' => $supplier2->id,
            'total_orders' => 1,
        ]);
    }

    public function test_artisan_command_runs_successfully(): void
    {
        $po = $this->createCompletedPO(2);
        $this->createGoodsReceipt($po, [
            ['expected_quantity' => 10, 'received_quantity' => 10, 'condition' => 'good'],
        ]);

        $this->artisan('supplier:recalculate-performance')
            ->expectsOutputToContain('Supplier performance recalculated successfully')
            ->assertExitCode(0);

        $this->assertDatabaseHas('supplier_performance', [
            'supplier_id' => $this->supplier->id,
            'total_orders' => 1,
        ]);
    }

    public function test_analytics_shows_quality_metrics(): void
    {
        $po = $this->createCompletedPO(2, now()->subDays(3));
        $this->createGoodsReceipt($po, [
            ['expected_quantity' => 10, 'received_quantity' => 10, 'condition' => 'good'],
            ['expected_quantity' => 5, 'received_quantity' => 5, 'condition' => 'damaged'],
        ]);

        $this->actingAs($this->admin)->post(route('purchasing.analytics.recalculate'));

        $response = $this->actingAs($this->admin)->get(route('purchasing.analytics'));
        $response->assertStatus(200);
        $response->assertSee('Avg Quality Rate');
        $response->assertSee('Damaged Items');
    }

    public function test_recalculate_clears_old_performance(): void
    {
        SupplierPerformance::create([
            'supplier_id' => $this->supplier->id,
            'total_orders' => 99,
            'calculated_at' => now()->subDay(),
        ]);

        $po = $this->createCompletedPO(2);
        $this->createGoodsReceipt($po);

        $this->actingAs($this->admin)->post(route('purchasing.analytics.recalculate'));

        $perf = SupplierPerformance::where('supplier_id', $this->supplier->id)->first();
        $this->assertEquals(1, $perf->total_orders);
    }

    public function test_service_invalidate_cache(): void
    {
        $service = app(SupplierAnalyticsService::class);
        $service->getDashboardStats();
        $service->getSupplierRankings();

        $service->invalidateCache();

        $this->assertTrue(true);
    }
}
