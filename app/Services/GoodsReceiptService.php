<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SupplierPayment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GoodsReceiptService
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

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
            $totalReceivedValue = 0;

            foreach ($receipt->items as $item) {
                if ($item->purchase_order_item_id) {
                    $poItem = $item->purchaseOrderItem;
                    if ($poItem) {
                        $newReceived = $poItem->received_quantity + $item->received_quantity;
                        $poItem->update(['received_quantity' => $newReceived]);
                    }
                }

                $product = Product::findOrFail($item->product_id);
                $unitPrice = $poItem?->unit_price ?? 0;
                $totalReceivedValue += $item->received_quantity * $unitPrice;

                $this->inventoryService->receiveStock(
                    $product,
                    $item->received_quantity,
                    $unitPrice,
                    null,
                    null,
                    $receipt,
                    "Goods receipt: {$item->received_quantity} units received"
                );
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

            $inventoryAccount = \App\Models\Account::where('code', '1300')->first();
            $apAccount = \App\Models\Account::where('code', '2100')
                ->orWhere('name', 'like', '%Accounts Payable%')
                ->first();

            if ($inventoryAccount && $apAccount && $totalReceivedValue > 0) {
                $je = JournalEntry::create([
                    'entry_date' => $receipt->receipt_date ?? now(),
                    'type' => 'purchase',
                    'status' => 'posted',
                    'description' => "Goods receipt #{$receipt->id} for PO #{$po->po_number}",
                    'total_debit' => $totalReceivedValue,
                    'total_credit' => $totalReceivedValue,
                    'reference_type' => GoodsReceipt::class,
                    'reference_id' => $receipt->id,
                    'created_by' => auth()->id(),
                ]);

                JournalEntryLine::create([
                    'journal_entry_id' => $je->id,
                    'account_id' => $inventoryAccount->id,
                    'description' => 'Inventory received',
                    'debit' => $totalReceivedValue,
                    'credit' => 0,
                ]);

                JournalEntryLine::create([
                    'journal_entry_id' => $je->id,
                    'account_id' => $apAccount->id,
                    'description' => 'Accounts Payable - Goods receipt',
                    'debit' => 0,
                    'credit' => $totalReceivedValue,
                ]);
            }

            if ($po->supplier_id && $totalReceivedValue > 0) {
                SupplierPayment::create([
                    'purchase_order_id' => $po->id,
                    'goods_receipt_id' => $receipt->id,
                    'supplier_id' => $po->supplier_id,
                    'amount' => $totalReceivedValue,
                    'status' => 'pending',
                    'payment_date' => $receipt->receipt_date ?? now(),
                    'notes' => "Auto-generated from goods receipt #{$receipt->id}",
                    'created_by' => auth()->id(),
                ]);
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
