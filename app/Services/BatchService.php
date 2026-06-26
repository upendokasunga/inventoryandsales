<?php

namespace App\Services;

use App\Models\InventoryBatch;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class BatchService
{
    public function getAllPaginated(int $perPage = 20, ?array $filters = null): LengthAwarePaginator
    {
        $query = InventoryBatch::with('product');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['expiring_before'])) {
            $query->where('expiry_date', '<=', $filters['expiring_before'])
                ->where('status', 'active');
        }

        return $query->latest()->paginate($perPage);
    }

    public function createBatch(
        Product $product,
        string $batchNumber,
        float $quantity,
        float $unitCost,
        ?string $expiryDate = null,
        ?string $supplierBatch = null,
        ?string $notes = null
    ): InventoryBatch {
        return InventoryBatch::create([
            'product_id' => $product->id,
            'batch_number' => $batchNumber,
            'quantity' => $quantity,
            'quantity_remaining' => $quantity,
            'unit_cost' => $unitCost,
            'expiry_date' => $expiryDate,
            'supplier_batch' => $supplierBatch,
            'status' => 'active',
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);
    }

    public function getActiveBatches(int $productId)
    {
        return InventoryBatch::where('product_id', $productId)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->orderBy('created_at')
            ->get();
    }

    public function markExhausted(InventoryBatch $batch): void
    {
        $batch->update(['status' => 'exhausted']);
    }
}
