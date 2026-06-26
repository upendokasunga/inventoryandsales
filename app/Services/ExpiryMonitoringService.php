<?php

namespace App\Services;

use App\Models\InventoryBatch;
use Illuminate\Support\Facades\Cache;

class ExpiryMonitoringService
{
    public function getExpiringBatches(int $daysAhead = 30): array
    {
        $cacheKey = "inventory.expiring.{$daysAhead}";

        return Cache::remember($cacheKey, 3600, function () use ($daysAhead) {
            $threshold = now()->addDays($daysAhead)->format('Y-m-d');

            $batches = InventoryBatch::with('product')
                ->where('status', 'active')
                ->where('quantity_remaining', '>', 0)
                ->where('expiry_date', '<=', $threshold)
                ->orderBy('expiry_date')
                ->get();

            return [
                'batches' => $batches,
                'total_products' => $batches->unique('product_id')->count(),
                'total_quantity' => $batches->sum('quantity_remaining'),
                'critical_count' => $batches->filter(fn($b) =>
                    $b->expiry_date && $b->expiry_date->isBefore(now()->addDays(7))
                )->sum('quantity_remaining'),
            ];
        });
    }

    public function markExpired(): int
    {
        $count = InventoryBatch::where('status', 'active')
            ->where('expiry_date', '<', now()->format('Y-m-d'))
            ->where('quantity_remaining', '>', 0)
            ->update(['status' => 'expired']);

        Cache::forget('inventory.expiring.*');

        return $count;
    }

    public function invalidateCache(): void
    {
        Cache::forget('inventory.expiring.*');
    }
}
