<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\ReturnNumberSequence;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PurchaseReturnService
{
    public function __construct(
        protected InventoryService $inventoryService,
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

            foreach ($purchaseReturn->items as $item) {
                $product = Product::findOrFail($item->product_id);
                $this->inventoryService->issueStock(
                    $product,
                    $item->quantity,
                    $purchaseReturn,
                    "Purchase return: {$item->reason}"
                );
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
        $year = now()->year;

        return DB::transaction(function () use ($year) {
            $sequence = ReturnNumberSequence::where('year', $year)
                ->where('type', 'PR')
                ->lockForUpdate()
                ->firstOrCreate(
                    ['year' => $year, 'type' => 'PR'],
                    ['last_number' => 0]
                );

            $sequence->increment('last_number');
            $sequence->fresh();

            $number = $sequence->last_number;

            return 'PR-' . $year . '-' . str_pad((string) $number, 6, '0', STR_PAD_LEFT);
        });
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
