<?php

namespace App\Services;

use App\Models\InventoryBalance;
use App\Models\InventoryBatch;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InventoryAnalyticsService
{
    public function getDashboardStats(): array
    {
        return Cache::remember('inventory.analytics.dashboard', 3600, function () {
            $balances = InventoryBalance::where('quantity_on_hand', '>', 0)->get();
            $trackedProducts = Product::where('track_stock', true)->get();

            $totalProducts = Product::count();
            $trackedCount = $trackedProducts->count();
            $lowStockProducts = $trackedProducts->filter(fn($p) =>
                $p->current_stock > 0 && $p->current_stock <= $p->reorder_level
            );
            $outOfStockProducts = $trackedProducts->filter(fn($p) => $p->current_stock <= 0);

            $fastSlow = $this->getFastSlowMoving(7);

            return [
                'total_products' => $totalProducts,
                'tracked_products' => $trackedCount,
                'products_with_stock' => $balances->count(),
                'total_quantity_on_hand' => $balances->sum('quantity_on_hand'),
                'total_value' => $balances->sum('total_value'),
                'total_reserved' => $balances->sum('quantity_reserved'),
                'low_stock_count' => $lowStockProducts->count(),
                'out_of_stock_count' => $outOfStockProducts->count(),
                'active_batches' => InventoryBatch::where('status', 'active')
                    ->where('quantity_remaining', '>', 0)->count(),
                'today_transactions' => InventoryTransaction::whereDate('created_at', today())->count(),
                'fast_moving_count' => $fastSlow['fast_moving_count'],
                'slow_moving_count' => $fastSlow['slow_moving_count'],
                'dead_stock_count' => $fastSlow['dead_stock_count'],
                'stock_turnover_rate' => $this->getStockTurnoverRate(),
                'stock_coverage_days' => $this->getStockCoverageDays(),
                'reorder_candidates' => $this->getReorderCandidates(),
            ];
        });
    }

    public function getStockStatusDistribution(): array
    {
        return Cache::remember('inventory.analytics.stock_distribution', 3600, function () {
            $products = Product::where('track_stock', true)->get();

            return [
                'in_stock' => $products->filter(fn($p) => $p->current_stock > $p->reorder_level)->count(),
                'low_stock' => $products->filter(fn($p) =>
                    $p->current_stock > 0 && $p->current_stock <= $p->reorder_level
                )->count(),
                'out_of_stock' => $products->filter(fn($p) => $p->current_stock <= 0)->count(),
                'not_tracked' => Product::where('track_stock', false)->count(),
            ];
        });
    }

    public function getRecentTransactions(int $limit = 20): array
    {
        return Cache::remember("inventory.analytics.recent_transactions", 300, function () use ($limit) {
            return InventoryTransaction::with('product', 'creator')
                ->latest()
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    public function getFastSlowMoving(int $lookbackDays = 30): array
    {
        $threshold = now()->subDays($lookbackDays);
        $products = Product::where('track_stock', true)->get(['id']);
        $productIds = $products->pluck('id');

        $issuedTotals = InventoryTransaction::whereIn('product_id', $productIds)
            ->where('type', 'sales_order')
            ->where('created_at', '>=', $threshold)
            ->groupBy('product_id')
            ->select('product_id', DB::raw('SUM(ABS(quantity)) as total_issued'))
            ->pluck('total_issued', 'product_id');

        $balances = InventoryBalance::whereIn('product_id', $productIds)
            ->get()
            ->keyBy('product_id');

        $fast = 0;
        $slow = 0;
        $dead = 0;

        foreach ($products as $product) {
            $totalIssued = (float) ($issuedTotals[$product->id] ?? 0);
            $balance = $balances->get($product->id);
            $onHand = $balance ? $balance->quantity_on_hand : 0;

            if ($totalIssued == 0 && $onHand > 0) {
                $dead++;
            } elseif ($onHand > 0) {
                $turnoverRatio = $onHand > 0 ? $totalIssued / $onHand : 0;
                if ($turnoverRatio >= 1) {
                    $fast++;
                } else {
                    $slow++;
                }
            }
        }

        return [
            'fast_moving_count' => $fast,
            'slow_moving_count' => $slow,
            'dead_stock_count' => $dead,
        ];
    }

    public function getStockTurnoverRate(): float
    {
        $cogs = InventoryTransaction::where('type', 'sales_order')
            ->where('created_at', '>=', now()->subDays(30))
            ->sum('total_cost');

        $avgInventory = InventoryBalance::sum(DB::raw('(total_value)'));

        return $avgInventory > 0 ? round($cogs / ($avgInventory / count(Product::where('track_stock', true)->get()) ?: 1), 2) : 0;
    }

    public function getStockCoverageDays(): float
    {
        $dailyUsage = InventoryTransaction::where('type', 'sales_order')
            ->where('created_at', '>=', now()->subDays(30))
            ->sum(DB::raw('ABS(quantity)'));

        $dailyAvg = $dailyUsage / 30;
        $currentStock = InventoryBalance::sum('quantity_on_hand');

        return $dailyAvg > 0 ? round($currentStock / $dailyAvg, 1) : 0;
    }

    public function getReorderCandidates(): array
    {
        return Product::where('track_stock', true)
            ->whereColumn('current_stock', '<=', 'reorder_level')
            ->where('current_stock', '>', 0)
            ->limit(20)
            ->get(['id', 'name', 'sku', 'current_stock', 'reorder_level', 'safety_stock'])
            ->toArray();
    }

    public function invalidateCache(): void
    {
        Cache::forget('inventory.analytics.dashboard');
        Cache::forget('inventory.analytics.stock_distribution');
        Cache::forget('inventory.analytics.recent_transactions');
    }
}
