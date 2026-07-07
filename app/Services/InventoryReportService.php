<?php

namespace App\Services;

use App\Models\InventoryBalance;
use App\Models\InventoryBatch;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InventoryReportService
{
    public function __construct(
        protected InventoryValuationService $valuationService,
    ) {}

    public function getCurrentStockReport(array $filters = []): array
    {
        $hash = md5(json_encode($filters));
        return Cache::remember("report.inventory.current.{$hash}", 3600, function () use ($filters) {
            $query = InventoryBalance::with('product.category');

            if (!empty($filters['product_id'])) {
                $query->where('product_id', $filters['product_id']);
            }
            if (!empty($filters['warehouse_id'])) {
                // no-op: inventory_balances has no warehouse_id column
                unset($filters['warehouse_id']);
            }
            if (!empty($filters['category_id'])) {
                $query->whereHas('product', fn($q) => $q->where('category_id', $filters['category_id']));
            }

            $balances = $query->where('quantity_on_hand', '>', 0)->take(1000)->get();

            return [
                'total_products' => $balances->count(),
                'total_quantity' => $balances->sum('quantity_on_hand'),
                'total_value' => $balances->sum('total_value'),
                'items' => $balances->map(fn($b) => [
                    'product_id' => $b->product_id,
                    'product_name' => $b->product?->name,
                    'sku' => $b->product?->sku,
                    'category' => $b->product?->category?->name,
                    'quantity_on_hand' => $b->quantity_on_hand,
                    'quantity_reserved' => $b->quantity_reserved,
                    'available' => $b->quantity_on_hand - $b->quantity_reserved,
                    'average_cost' => $b->average_cost,
                    'total_value' => $b->total_value,
                ])->toArray(),
            ];
        });
    }

    public function getValuationReport(): array
    {
        return $this->valuationService->getValuation();
    }

    public function getFifoValuationReport(?int $productId = null): array
    {
        if ($productId) {
            return $this->valuationService->getFifoValuation($productId);
        }

        $products = Product::where('track_stock', true)->pluck('id');
        $valuations = [];
        foreach ($products as $pid) {
            $valuations[] = $this->valuationService->getFifoValuation($pid);
        }
        return [
            'total_products' => count($valuations),
            'total_value' => array_sum(array_column($valuations, 'total_value')),
            'total_quantity' => array_sum(array_column($valuations, 'total_quantity')),
            'products' => $valuations,
        ];
    }

    public function getStockMovementReport(string $startDate, string $endDate): array
    {
        $key = "report.inventory.movement.{$startDate}.{$endDate}";
        return Cache::remember($key, 3600, function () use ($startDate, $endDate) {
            $transactions = InventoryTransaction::with('product:id,name,sku')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderByDesc('created_at')
                ->limit(500)
                ->get()
                ->toArray();

            $summary = InventoryTransaction::select(
                'type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(ABS(quantity)) as total_quantity'),
                DB::raw('COALESCE(SUM(ABS(total_cost)), 0) as total_cost')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('type')
                ->get()
                ->toArray();

            return compact('transactions', 'summary');
        });
    }

    public function getFastMovingProducts(int $limit = 20): array
    {
        return Cache::remember("report.inventory.fast_moving.{$limit}", 3600, function () use ($limit) {
            $threshold = now()->subDays(30);
            return Product::select(
                'id', 'name', 'sku', 'current_stock', 'reorder_level'
            )
                ->where('track_stock', true)
                ->whereHas('inventoryTransactions', fn($q) => $q
                    ->where('type', 'sales_order')
                    ->where('created_at', '>=', $threshold))
                ->withCount(['inventoryTransactions as total_issued' => fn($q) => $q
                    ->where('type', 'sales_order')
                    ->where('created_at', '>=', $threshold)
                    ->select(DB::raw('COALESCE(SUM(ABS(quantity)), 0)'))])
                ->havingRaw('total_issued > 0')
                ->orderByDesc('total_issued')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    public function getSlowMovingProducts(int $limit = 20): array
    {
        return Cache::remember("report.inventory.slow_moving.{$limit}", 3600, function () use ($limit) {
            $threshold = now()->subDays(30);
            return Product::select('id', 'name', 'sku', 'current_stock', 'reorder_level')
                ->where('track_stock', true)
                ->where('current_stock', '>', 0)
                ->whereDoesntHave('inventoryTransactions', fn($q) => $q
                    ->where('type', 'sales_order')
                    ->where('created_at', '>=', $threshold))
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    public function getDeadStock(): array
    {
        return Cache::remember('report.inventory.dead_stock', 3600, function () {
            $threshold = now()->subDays(90);
            return Product::select('id', 'name', 'sku', 'current_stock', 'reorder_level')
                ->where('track_stock', true)
                ->where('current_stock', '>', 0)
                ->whereDoesntHave('inventoryTransactions', fn($q) => $q
                    ->where('type', 'sales_order')
                    ->where('created_at', '>=', $threshold))
                ->take(1000)->get()
                ->toArray();
        });
    }

    public function getExpiryReport(int $days = 90): array
    {
        $expiryThreshold = now()->addDays($days);
        return Cache::remember("report.inventory.expiry.{$days}", 3600, function () use ($expiryThreshold) {
            $batches = InventoryBatch::with('product:id,name,sku')
                ->where('status', 'active')
                ->where('quantity_remaining', '>', 0)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', $expiryThreshold)
                ->orderBy('expiry_date')
                ->take(1000)->get()
                ->toArray();

            $summary = [
                'expiring_within_30_days' => InventoryBatch::where('status', 'active')
                    ->where('quantity_remaining', '>', 0)->whereNotNull('expiry_date')
                    ->where('expiry_date', '<=', now()->addDays(30))->count(),
                'expiring_within_90_days' => InventoryBatch::where('status', 'active')
                    ->where('quantity_remaining', '>', 0)->whereNotNull('expiry_date')
                    ->where('expiry_date', '<=', now()->addDays(90))->count(),
                'already_expired' => InventoryBatch::where('status', 'active')
                    ->where('quantity_remaining', '>', 0)->whereNotNull('expiry_date')
                    ->where('expiry_date', '<', now())->count(),
            ];

            return compact('batches', 'summary');
        });
    }

    public function getReorderCandidates(): array
    {
        return Cache::remember('report.inventory.reorder', 1800, function () {
            return Product::where('track_stock', true)
                ->whereColumn('current_stock', '<=', 'reorder_level')
                ->where('current_stock', '>', 0)
                ->orderBy('current_stock')
                ->limit(20)
                ->get(['id', 'name', 'sku', 'current_stock', 'reorder_level', 'safety_stock'])
                ->toArray();
        });
    }

    public function getLowStockReport(): array
    {
        return Cache::remember('report.inventory.low_stock', 1800, function () {
            return Product::where('track_stock', true)
                ->whereColumn('current_stock', '<=', DB::raw('reorder_level'))
                ->orderBy('current_stock')
                ->limit(50)
                ->get(['id', 'name', 'sku', 'current_stock', 'reorder_level', 'safety_stock'])
                ->toArray();
        });
    }

    public function invalidateCache(): void
    {
        Cache::forget('report.inventory.current');
        Cache::forget('report.inventory.movement');
        Cache::forget('report.inventory.fast_moving');
        Cache::forget('report.inventory.slow_moving');
        Cache::forget('report.inventory.dead_stock');
        Cache::forget('report.inventory.expiry');
        Cache::forget('report.inventory.reorder');
        Cache::forget('report.inventory.low_stock');
    }
}
