<?php

namespace App\Services;

use App\Models\InventoryBalance;
use App\Models\InventoryBatch;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(
        protected FifoService $fifoService,
        protected BatchService $batchService,
    ) {}

    public function getTransactionsPaginated(int $perPage = 20, ?array $filters = null): LengthAwarePaginator
    {
        $query = InventoryTransaction::with(['product', 'creator']);

        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function getBalance(int $productId): ?InventoryBalance
    {
        return InventoryBalance::where('product_id', $productId)->first();
    }

    public function getOrCreateBalance(int $productId): InventoryBalance
    {
        return InventoryBalance::firstOrCreate(
            ['product_id' => $productId],
            [
                'quantity_on_hand' => 0,
                'quantity_reserved' => 0,
                'quantity_available' => 0,
                'quantity_incoming' => 0,
                'average_cost' => 0,
                'total_value' => 0,
            ]
        );
    }

    public function receiveStock(
        Product $product,
        float $quantity,
        float $unitCost,
        ?string $batchNumber = null,
        ?string $expiryDate = null,
        ?object $reference = null,
        ?string $description = null,
        ?int $warehouseId = null,
        string $type = 'purchase_receipt'
    ): InventoryTransaction {
        return DB::transaction(function () use ($product, $quantity, $unitCost, $batchNumber, $expiryDate, $reference, $description, $warehouseId, $type) {
            $balance = $this->getOrCreateBalance($product->id);
            $balanceBefore = $balance->quantity_on_hand;
            $balanceAfter = $balanceBefore + $quantity;

            $totalCost = $quantity * $unitCost;
            $newAvgCost = $balanceAfter > 0
                ? (($balance->total_value + $totalCost) / $balanceAfter)
                : $unitCost;

            $balance->update([
                'quantity_on_hand' => $balanceAfter,
                'quantity_available' => $balanceAfter - $balance->quantity_reserved,
                'quantity_incoming' => max(0, $balance->quantity_incoming - $quantity),
                'average_cost' => round($newAvgCost, 2),
                'total_value' => $balance->total_value + $totalCost,
                'last_transaction_at' => now(),
            ]);

            $product->update(['current_stock' => $balanceAfter]);

            $this->batchService->createBatch(
                $product, $batchNumber ?? 'BATCH-' . now()->timestamp, $quantity, $unitCost, $expiryDate
            );

            $transaction = InventoryTransaction::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
                'type' => $type,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $description ?? "Stock received: {$quantity} units",
                'created_by' => auth()->id(),
            ]);

            $this->invalidateCache($product->id);

            return $transaction;
        });
    }

    public function issueStock(
        Product $product,
        float $quantity,
        ?object $reference = null,
        ?string $description = null,
        string $type = 'sale'
    ): array {
        return DB::transaction(function () use ($product, $quantity, $reference, $description, $type) {
            $balance = $this->getOrCreateBalance($product->id);

            if ($balance->quantity_on_hand < $quantity) {
                throw new \InvalidArgumentException("Insufficient stock for {$product->name}: {$balance->quantity_on_hand} available, {$quantity} required.");
            }

            $balanceBefore = $balance->quantity_on_hand;
            $balanceAfter = $balanceBefore - $quantity;

            $allocations = $this->fifoService->allocateCost($product, $quantity);
            $totalCost = collect($allocations)->sum(fn($a) => $a['cost']);

            $newTotalValue = max(0, $balance->total_value - $totalCost);
            $newAvgCost = $balanceAfter > 0
                ? round($newTotalValue / $balanceAfter, 2)
                : 0;

            $balance->update([
                'quantity_on_hand' => $balanceAfter,
                'quantity_available' => $balanceAfter - $balance->quantity_reserved,
                'average_cost' => $newAvgCost,
                'total_value' => $newTotalValue,
                'last_transaction_at' => now(),
            ]);

            $product->update(['current_stock' => $balanceAfter]);

            $transaction = InventoryTransaction::create([
                'product_id' => $product->id,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
                'type' => $type,
                'quantity' => -$quantity,
                'unit_cost' => $balance->average_cost,
                'total_cost' => $totalCost,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $description ?? "Stock issued: {$quantity} units",
                'created_by' => auth()->id(),
            ]);

            $this->invalidateCache($product->id);

            return ['transaction' => $transaction, 'allocations' => $allocations];
        });
    }

    public function adjustStock(
        Product $product,
        float $expectedQty,
        float $actualQty,
        float $unitCost,
        ?string $reason = null,
        ?object $reference = null
    ): InventoryTransaction {
        return DB::transaction(function () use ($product, $expectedQty, $actualQty, $unitCost, $reason, $reference) {
            $balance = $this->getOrCreateBalance($product->id);
            $difference = $actualQty - $expectedQty;

            if ($difference == 0) {
                throw new \InvalidArgumentException('No adjustment needed: quantities match.');
            }

            $balanceBefore = $balance->quantity_on_hand;
            $balanceAfter = $balanceBefore + $difference;

            if ($balanceAfter < 0) {
                throw new \InvalidArgumentException(
                    "Stock adjustment would result in negative balance for {$product->name}: " .
                    "current {$balanceBefore}, adjustment {$difference}, would result in {$balanceAfter}."
                );
            }

            $adjustmentCost = abs($difference) * $unitCost;
            if ($difference > 0) {
                $newTotalValue = $balance->total_value + $adjustmentCost;
            } else {
                $newTotalValue = max(0, $balance->total_value - $adjustmentCost);
            }

            $newAvgCost = $balanceAfter > 0
                ? round($newTotalValue / $balanceAfter, 2)
                : 0;

            $balance->update([
                'quantity_on_hand' => $balanceAfter,
                'quantity_available' => $balanceAfter - $balance->quantity_reserved,
                'average_cost' => $newAvgCost,
                'total_value' => $newTotalValue,
                'last_transaction_at' => now(),
            ]);

            $product->update(['current_stock' => $balanceAfter]);

            $transaction = InventoryTransaction::create([
                'product_id' => $product->id,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
                'type' => 'adjustment',
                'quantity' => $difference,
                'unit_cost' => $unitCost,
                'total_cost' => $adjustmentCost,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $reason ?? "Stock adjustment: {$difference} units",
                'created_by' => auth()->id(),
            ]);

            $this->invalidateCache($product->id);

            return $transaction;
        });
    }

    public function invalidateCache(int $productId): void
    {
        Cache::forget("inventory.balance.{$productId}");
        Cache::forget("inventory.analytics.dashboard");
    }
}
