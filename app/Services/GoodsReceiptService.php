<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GoodsReceiptService
{
    public function getAllPaginated(int $perPage = 20, ?array $filters = null): LengthAwarePaginator
    {
        $query = GoodsReceipt::with(['purchaseOrder.supplier', 'creator']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['purchase_order_id'])) {
            $query->where('purchase_order_id', $filters['purchase_order_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function createFromPO(PurchaseOrder $purchaseOrder, array $data, array $items): GoodsReceipt
    {
        return DB::transaction(function () use ($purchaseOrder, $data, $items) {
            $data['purchase_order_id'] = $purchaseOrder->id;
            $data['status'] = 'draft';
            $data['created_by'] = auth()->id();
            $data['receipt_date'] = $data['receipt_date'] ?? now()->format('Y-m-d');

            $receipt = GoodsReceipt::create($data);

            $receiptItems = [];
            foreach ($items as $item) {
                $receiptItems[] = new GoodsReceiptItem([
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'product_id' => $item['product_id'],
                    'expected_quantity' => $item['expected_quantity'],
                    'received_quantity' => $item['received_quantity'],
                    'condition' => $item['condition'] ?? 'good',
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            $receipt->items()->saveMany($receiptItems);

            return $receipt->fresh(['items', 'purchaseOrder.supplier']);
        });
    }

    public function complete(GoodsReceipt $receipt): GoodsReceipt
    {
        return DB::transaction(function () use ($receipt) {
            $receipt->update(['status' => 'completed']);

            foreach ($receipt->items as $item) {
                if ($item->purchase_order_item_id) {
                    $poItem = $item->purchaseOrderItem;
                    if ($poItem) {
                        $newReceived = $poItem->received_quantity + $item->received_quantity;
                        $poItem->update(['received_quantity' => $newReceived]);
                    }
                }
            }

            $po = $receipt->purchaseOrder;
            $allItems = $po->items;
            $totalQty = $allItems->sum('quantity');
            $totalReceived = $allItems->sum('received_quantity');

            if ($totalReceived >= $totalQty) {
                $po->update(['status' => 'completed']);
            } elseif ($totalReceived > 0) {
                $po->update(['status' => 'partially_received']);
            }

            Cache::forget('purchasing.order.stats');
            Cache::forget('purchasing.receipt.stats');

            return $receipt->fresh(['items', 'purchaseOrder']);
        });
    }

    public function getStats(): array
    {
        return Cache::remember('purchasing.receipt.stats', 300, function () {
            return [
                'total' => GoodsReceipt::count(),
                'draft' => GoodsReceipt::where('status', 'draft')->count(),
                'completed' => GoodsReceipt::where('status', 'completed')->count(),
                'cancelled' => GoodsReceipt::where('status', 'cancelled')->count(),
            ];
        });
    }
}
