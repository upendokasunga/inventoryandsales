<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Product;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\ReturnNumberSequence;
use App\Models\SupplierPayment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PurchaseReturnService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected DocumentNumberingService $numberingService,
    ) {}

    public function getAllPaginated(int $perPage = 20, ?array $filters = null): LengthAwarePaginator
    {
        $query = PurchaseReturn::with(['supplier', 'items.product', 'creator', 'purchaseOrder']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): PurchaseReturn
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'];
            unset($data['items']);

            $totalAmount = 0;
            $returnItems = [];

            foreach ($items as $item) {
                $lineTotal = $item['unit_price'] * $item['quantity'];
                $totalAmount += $lineTotal;

                $returnItems[] = new PurchaseReturnItem([
                    'product_id' => $item['product_id'],
                    'product_unit_id' => $item['product_unit_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $lineTotal,
                    'reason' => $item['reason'],
                ]);
            }

            $data['total_amount'] = $totalAmount;
            $data['status'] = 'pending_approval';
            $data['created_by'] = $data['created_by'] ?? auth()->id();
            $data['return_number'] = $data['return_number'] ?? $this->generateReturnNumber();

            $return = PurchaseReturn::create($data);
            $return->items()->saveMany($returnItems);

            Cache::forget('returns.dashboard.stats');

            return $return->load(['items.product', 'supplier', 'purchaseOrder']);
        });
    }

    public function approve(PurchaseReturn $purchaseReturn): void
    {
        DB::transaction(function () use ($purchaseReturn) {
            $purchaseReturn->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $returnValue = 0;
            foreach ($purchaseReturn->items as $item) {
                $product = Product::findOrFail($item->product_id);
                $this->inventoryService->issueStock(
                    $product,
                    $item->quantity,
                    $purchaseReturn,
                    "Purchase return {$purchaseReturn->return_number}: {$item->quantity} units ({$item->reason})",
                    'purchase_return'
                );
                $returnValue += $item->quantity * $item->unit_price;
            }

            if ($returnValue > 0 && $purchaseReturn->purchaseOrder) {
                $po = $purchaseReturn->purchaseOrder;

                $inventoryAccount = Account::where('code', '1300')->first();
                $apAccount = Account::where('code', '2100')
                    ->orWhere('name', 'like', '%Accounts Payable%')
                    ->first();

                if ($inventoryAccount && $apAccount) {
                    $je = JournalEntry::create([
                        'entry_number' => 'PRJ-' . strtoupper(\Illuminate\Support\Str::random(8)),
                        'entry_date' => now()->toDateString(),
                        'type' => 'adjustment',
                        'status' => 'posted',
                        'description' => "Purchase return {$purchaseReturn->return_number} for PO #{$po->po_number}",
                        'total_debit' => $returnValue,
                        'total_credit' => $returnValue,
                        'reference_type' => PurchaseReturn::class,
                        'reference_id' => $purchaseReturn->id,
                        'created_by' => auth()->id(),
                    ]);

                    JournalEntryLine::create([
                        'journal_entry_id' => $je->id,
                        'account_id' => $apAccount->id,
                        'description' => "AP reversal — return {$purchaseReturn->return_number}",
                        'debit' => $returnValue,
                        'credit' => 0,
                    ]);

                    JournalEntryLine::create([
                        'journal_entry_id' => $je->id,
                        'account_id' => $inventoryAccount->id,
                        'description' => "Inventory returned — {$purchaseReturn->return_number}",
                        'debit' => 0,
                        'credit' => $returnValue,
                    ]);
                }

                $newBalanceDue = max(0, (float) $po->balance_due - $returnValue);
                $po->update([
                    'balance_due' => $newBalanceDue,
                ]);

                $supplierPayment = SupplierPayment::where('purchase_order_id', $po->id)->first();
                if ($supplierPayment) {
                    $newPaymentAmount = max(0, (float) $supplierPayment->amount - $returnValue);
                    $supplierPayment->update([
                        'amount' => $newPaymentAmount,
                        'notes' => ($supplierPayment->notes ? $supplierPayment->notes . "\n" : '')
                            . "Adjusted for purchase return {$purchaseReturn->return_number}: -TSh " . number_format($returnValue, 2),
                    ]);
                }
            }

            Cache::forget('returns.dashboard.stats');
        });
    }

    public function complete(PurchaseReturn $purchaseReturn): void
    {
        $purchaseReturn->update(['status' => 'completed']);
        Cache::forget('returns.dashboard.stats');
    }

    protected function generateReturnNumber(): string
    {
        return $this->numberingService->generateNumber('purchase_return', 'PR');
    }

    public function reject(PurchaseReturn $purchaseReturn): void
    {
        $purchaseReturn->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        Cache::forget('returns.dashboard.stats');
    }

    public function getStats(): array
    {
        return Cache::remember('purchase-returns.stats', 300, function () {
            return [
                'total_returns' => PurchaseReturn::count(),
                'pending_returns' => PurchaseReturn::where('status', 'pending_approval')->count(),
                'approved_returns' => PurchaseReturn::where('status', 'approved')->count(),
                'total_amount' => (float) PurchaseReturn::whereIn('status', ['approved', 'completed'])->sum('total_amount'),
            ];
        });
    }
}
