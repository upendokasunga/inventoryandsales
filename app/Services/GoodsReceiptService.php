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
        $query = GoodsReceipt::with(['purchaseOrder.supplier', 'warehouse', 'creator']);

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

            $this->ensureWarehouse($receipt);

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
                $poItem = null;
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
                    "Goods receipt: {$item->received_quantity} units received",
                    $receipt->warehouse_id
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
                    'entry_number' => 'PJE-' . strtoupper(\Illuminate\Support\Str::random(8)),
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
                $supplierPayment = SupplierPayment::where('purchase_order_id', $po->id)->first();
                if ($supplierPayment) {
                    $supplierPayment->update([
                        'amount' => $totalReceivedValue,
                        'goods_receipt_id' => $receipt->id,
                        'notes' => "Updated from goods receipt #{$receipt->id}",
                    ]);
                } else {
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
            }

            Cache::forget('purchasing.order.stats');
            Cache::forget('purchasing.receipt.stats');

            return $receipt->fresh(['items', 'purchaseOrder', 'warehouse']);
        });
    }

    protected function ensureWarehouse(GoodsReceipt $receipt): void
    {
        if ($receipt->warehouse_id) {
            return;
        }

        $mainStore = \App\Models\Warehouse::where('code', 'MAIN')->first();
        if ($mainStore) {
            $receipt->update(['warehouse_id' => $mainStore->id]);
        }
    }

    public function getStats(): array
    {
        return Cache::remember('purchasing.receipt.stats', 300, function () {
            return [
                'total' => GoodsReceipt::count(),
                'completed' => GoodsReceipt::where('status', 'completed')->count(),
                'cancelled' => GoodsReceipt::where('status', 'cancelled')->count(),
            ];
        });
    }
}
