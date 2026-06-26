<?php

namespace App\Services;

use App\Models\InventoryBalance;
use App\Models\InventoryBatch;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class InventoryAnalyticsService
{
    public function getDashboardStats(): array
    {
        return Cache::remember('inventory.analytics.dashboard', 3600, function () {
            $balances = InventoryBalance::where('quantity_on_hand', '>', 0)->get();

            $totalProducts = Product::count();
            $trackedProducts = Product::where('track_stock', true)->count();
            $lowStockProducts = Product::where('track_stock', true)
                ->whereColumn('current_stock', '<=', 'reorder_level')
                ->count();

            return [
                'total_products' => $totalProducts,
                'tracked_products' => $trackedProducts,
                'products_with_stock' => $balances->count(),
                'total_quantity_on_hand' => $balances->sum('quantity_on_hand'),
                'total_value' => $balances->sum('total_value'),
                'total_reserved' => $balances->sum('quantity_reserved'),
                'low_stock_count' => $lowStockProducts,
                'out_of_stock_count' => Product::where('track_stock', true)
                    ->where('current_stock', '<=', 0)->count(),
                'active_batches' => InventoryBatch::where('status', 'active')
                    ->where('quantity_remaining', '>', 0)->count(),
                'today_transactions' => InventoryTransaction::whereDate('created_at', today())->count(),
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

    public function invalidateCache(): void
    {
        Cache::forget('inventory.analytics.dashboard');
        Cache::forget('inventory.analytics.stock_distribution');
        Cache::forget('inventory.analytics.recent_transactions');
    }
}
