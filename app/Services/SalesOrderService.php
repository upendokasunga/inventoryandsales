<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SoNumberSequence;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SalesOrderService
{
    public function __construct(
        protected CreditService $creditService,
        protected PricingService $pricingService,
        protected InventoryService $inventoryService,
        protected ReservationService $reservationService,
    ) {}

    public function getAllPaginated(int $perPage = 20, ?array $filters = null): LengthAwarePaginator
    {
        $query = SalesOrder::with(['customer', 'creator']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('order_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('order_date', '<=', $filters['date_to']);
        }

        if (isset($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('so_number', 'like', "%{$s}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$s}%"));
            });
        }

        return $query->latest()->paginate($perPage);
    }

    protected function calculateTotal(float $subtotal, float $tax, float $discount, string $discountType): float
    {
        $afterDiscount = $subtotal;

        if ($discountType === 'percentage' && $discount > 0) {
            $afterDiscount = $subtotal - ($subtotal * $discount / 100);
        } elseif ($discount > 0) {
            $afterDiscount = max(0, $subtotal - $discount);
        }

        return $afterDiscount + $tax;
    }

    public function create(array $data, array $items): SalesOrder
    {
        return DB::transaction(function () use ($data, $items) {
            $data['created_by'] = auth()->id();
            $data['status'] = 'draft';

            $subtotal = 0;
            $soItems = [];

            foreach ($items as $item) {
                $lineSubtotal = $item['quantity'] * $item['unit_price'];
                $lineDiscount = $item['discount'] ?? 0;
                $lineTax = $item['tax'] ?? 0;
                $lineTotal = $lineSubtotal - $lineDiscount + $lineTax;

                $subtotal += $lineSubtotal;

                $soItems[] = new SalesOrderItem([
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $lineSubtotal,
                    'discount' => $lineDiscount,
                    'tax' => $lineTax,
                    'total' => $lineTotal,
                ]);
            }

            $data['subtotal'] = $subtotal;
            $data['tax'] = $data['tax'] ?? 0;
            $data['discount'] = $data['discount'] ?? 0;
            $data['discount_type'] = $data['discount_type'] ?? 'fixed';
            $data['total'] = $this->calculateTotal(
                $subtotal, $data['tax'], $data['discount'], $data['discount_type']
            );

            $salesOrder = SalesOrder::create($data);
            $salesOrder->items()->saveMany($soItems);

            Cache::forget('sales.order.stats');

            return $salesOrder->fresh(['items', 'customer']);
        });
    }

    public function update(SalesOrder $salesOrder, array $data, array $items): SalesOrder
    {
        return DB::transaction(function () use ($salesOrder, $data, $items) {
            $subtotal = 0;
            $soItems = [];

            foreach ($items as $item) {
                $lineSubtotal = $item['quantity'] * $item['unit_price'];
                $lineDiscount = $item['discount'] ?? 0;
                $lineTax = $item['tax'] ?? 0;
                $lineTotal = $lineSubtotal - $lineDiscount + $lineTax;

                $subtotal += $lineSubtotal;

                $soItems[] = new SalesOrderItem([
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $lineSubtotal,
                    'discount' => $lineDiscount,
                    'tax' => $lineTax,
                    'total' => $lineTotal,
                ]);
            }

            $data['subtotal'] = $subtotal;
            $data['tax'] = $data['tax'] ?? 0;
            $data['discount'] = $data['discount'] ?? 0;
            $data['discount_type'] = $data['discount_type'] ?? 'fixed';
            $data['total'] = $this->calculateTotal(
                $subtotal, $data['tax'], $data['discount'], $data['discount_type']
            );

            $salesOrder->update($data);
            $salesOrder->items()->delete();
            $salesOrder->items()->saveMany($soItems);

            Cache::forget("sales.order.{$salesOrder->id}");

            return $salesOrder->fresh(['items', 'customer']);
        });
    }

    public function delete(SalesOrder $salesOrder): void
    {
        $salesOrder->delete();
        Cache::forget('sales.order.stats');
    }

    public function getStats(): array
    {
        return Cache::remember('sales.order.stats', 3600, function () {
            return [
                'total' => SalesOrder::count(),
                'draft' => SalesOrder::where('status', 'draft')->count(),
                'pending_approval' => SalesOrder::where('status', 'pending_approval')->count(),
                'approved' => SalesOrder::where('status', 'approved')->count(),
                'reserved' => SalesOrder::where('status', 'reserved')->count(),
                'partially_fulfilled' => SalesOrder::where('status', 'partially_fulfilled')->count(),
                'fulfilled' => SalesOrder::where('status', 'fulfilled')->count(),
                'cancelled' => SalesOrder::where('status', 'cancelled')->count(),
            ];
        });
    }
}
