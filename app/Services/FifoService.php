<?php

namespace App\Services;

use App\Models\InventoryBatch;
use App\Models\Product;
use Illuminate\Support\Collection;

class FifoService
{
    public function allocateCost(Product $product, float $quantity): array
    {
        $batches = InventoryBatch::where('product_id', $product->id)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->orderBy('created_at')
            ->orderBy('expiry_date')
            ->get();

        $remaining = $quantity;
        $allocations = [];

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $fromBatch = min($remaining, $batch->quantity_remaining);
            $cost = round($fromBatch * $batch->unit_cost, 2);

            $allocations[] = [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'quantity' => $fromBatch,
                'unit_cost' => $batch->unit_cost,
                'cost' => $cost,
            ];

            $batch->decrement('quantity_remaining', $fromBatch);

            if ($batch->quantity_remaining <= 0) {
                $batch->update(['status' => 'depleted']);
            }

            $remaining -= $fromBatch;
        }

        if ($remaining > 0) {
            throw new \RuntimeException(
                "Insufficient batch stock: only " . ($quantity - $remaining) . " of {$quantity} could be allocated."
            );
        }

        return $allocations;
    }

    public function getBatchCosts(Product $product): Collection
    {
        return InventoryBatch::where('product_id', $product->id)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->orderBy('created_at')
            ->orderBy('expiry_date')
            ->get(['id', 'batch_number', 'quantity_remaining', 'unit_cost', 'expiry_date']);
    }

    public function getCurrentAverageCost(Product $product): float
    {
        $batches = InventoryBatch::where('product_id', $product->id)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->get();

        $totalQty = $batches->sum('quantity_remaining');
        $totalCost = $batches->sum(fn($b) => $b->quantity_remaining * $b->unit_cost);

        return $totalQty > 0 ? round($totalCost / $totalQty, 2) : 0;
    }
}
