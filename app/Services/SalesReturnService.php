<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ReturnNumberSequence;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SalesReturnService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected CreditNoteService $creditNoteService,
    ) {}

    public function getAllPaginated(int $perPage = 20, ?array $filters = null): LengthAwarePaginator
    {
        $query = SalesReturn::with(['customer', 'items.product', 'creator', 'invoice']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): SalesReturn
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'];
            unset($data['items']);

            $totalAmount = 0;
            $returnItems = [];

            foreach ($items as $item) {
                $lineTotal = $item['unit_price'] * $item['quantity'];
                $totalAmount += $lineTotal;

                $returnItems[] = new SalesReturnItem([
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

            $return = SalesReturn::create($data);
            $return->items()->saveMany($returnItems);

            Cache::forget('returns.dashboard.stats');

            return $return->load(['items.product', 'customer', 'invoice']);
        });
    }

    public function approve(SalesReturn $salesReturn): void
    {
        DB::transaction(function () use ($salesReturn) {
            $salesReturn->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            foreach ($salesReturn->items as $item) {
                $product = Product::findOrFail($item->product_id);
                $this->inventoryService->receiveStock(
                    $product,
                    $item->quantity,
                    0,
                    null,
                    null,
                    $salesReturn,
                    "Sales return: {$item->reason}"
                );
            }

            $this->creditNoteService->create([
                'customer_id' => $salesReturn->customer_id,
                'sales_return_id' => $salesReturn->id,
                'amount' => $salesReturn->total_amount,
                'issued_date' => now(),
                'status' => 'issued',
                'created_by' => auth()->id(),
            ]);

            Cache::forget('returns.dashboard.stats');
        });
    }

    public function reject(SalesReturn $salesReturn): void
    {
        $salesReturn->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        Cache::forget('returns.dashboard.stats');
    }

    public function complete(SalesReturn $salesReturn): void
    {
        $salesReturn->update(['status' => 'completed']);
        Cache::forget('returns.dashboard.stats');
    }

    protected function generateReturnNumber(): string
    {
        $year = now()->year;

        return DB::transaction(function () use ($year) {
            $sequence = ReturnNumberSequence::where('year', $year)
                ->where('type', 'SR')
                ->lockForUpdate()
                ->firstOrCreate(
                    ['year' => $year, 'type' => 'SR'],
                    ['last_number' => 0]
                );

            $sequence->increment('last_number');
            $sequence->fresh();

            $number = $sequence->last_number;

            return 'SR-' . $year . '-' . str_pad((string) $number, 6, '0', STR_PAD_LEFT);
        });
    }

    public function getStats(): array
    {
        return Cache::remember('returns.dashboard.stats', 300, function () {
            return [
                'total_returns' => SalesReturn::count(),
                'pending_returns' => SalesReturn::where('status', 'pending_approval')->count(),
                'approved_returns' => SalesReturn::where('status', 'approved')->count(),
                'completed_returns' => SalesReturn::where('status', 'completed')->count(),
                'total_refund_value' => (float) SalesReturn::whereIn('status', ['approved', 'completed'])->sum('total_amount'),
            ];
        });
    }
}
