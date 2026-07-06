<?php

namespace App\Services;

use App\Models\InventoryBalance;
use App\Models\InventoryBatch;
use Illuminate\Support\Facades\Cache;

class InventoryValuationService
{
    public function getValuation(?int $productId = null): array
    {
        $cacheKey = $productId
            ? "inventory.valuation.{$productId}"
            : 'inventory.valuation.summary';

        Cache::forget($cacheKey);

        return Cache::remember($cacheKey, 3600, function () use ($productId) {
            $query = InventoryBalance::with('product');

            if ($productId) {
                $query->where('product_id', $productId);
            }

            $balances = $query->where('quantity_on_hand', '>', 0)->get();

            return [
                'total_value' => $balances->sum('total_value'),
                'total_products' => $balances->count(),
                'total_quantity' => $balances->sum('quantity_on_hand'),
                'weighted_average_cost' => $balances->sum('quantity_on_hand') > 0
                    ? round($balances->sum('total_value') / $balances->sum('quantity_on_hand'), 2)
                    : 0,
                'details' => $balances->map(fn($b) => [
                    'product_id' => $b->product_id,
                    'product_name' => $b->product?->name,
                    'sku' => $b->product?->sku,
                    'quantity_on_hand' => $b->quantity_on_hand,
                    'average_cost' => $b->average_cost,
                    'total_value' => $b->total_value,
                ])->toArray(),
            ];
        });
    }

    public function getFifoValuation(int $productId): array
    {
        $batches = InventoryBatch::where('product_id', $productId)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->orderBy('created_at')
            ->get();

        return [
            'product_id' => $productId,
            'total_batches' => $batches->count(),
            'total_quantity' => $batches->sum('quantity_remaining'),
            'total_value' => $batches->sum(fn($b) => $b->quantity_remaining * $b->unit_cost),
            'batches' => $batches->map(fn($b) => [
                'batch_number' => $b->batch_number,
                'quantity' => $b->quantity_remaining,
                'unit_cost' => $b->unit_cost,
                'value' => $b->quantity_remaining * $b->unit_cost,
                'expiry_date' => $b->expiry_date?->format('Y-m-d'),
            ]),
        ];
    }

    public function invalidateCache(?int $productId = null): void
    {
        if ($productId) {
            Cache::forget("inventory.valuation.{$productId}");
        }
        Cache::forget('inventory.valuation.summary');
    }
}
