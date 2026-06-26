<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    public function getAllPaginated(int $perPage = 20, ?array $filters = null): LengthAwarePaginator
    {
        $query = PurchaseOrder::with(['supplier', 'creator']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
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
                $q->where('po_number', 'like', "%{$s}%")
                  ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$s}%"));
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

    public function create(array $data, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $items) {
            $data['created_by'] = auth()->id();
            $data['status'] = 'draft';

            $subtotal = 0;
            $poItems = [];

            foreach ($items as $item) {
                $lineSubtotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineSubtotal;
                $poItems[] = new PurchaseOrderItem([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $lineSubtotal,
                ]);
            }

            $data['subtotal'] = $subtotal;
            $data['tax'] = $data['tax'] ?? 0;
            $data['discount'] = $data['discount'] ?? 0;
            $data['discount_type'] = $data['discount_type'] ?? 'fixed';
            $data['total'] = $this->calculateTotal(
                $subtotal, $data['tax'], $data['discount'], $data['discount_type']
            );

            $purchaseOrder = PurchaseOrder::create($data);
            $purchaseOrder->items()->saveMany($poItems);

            Cache::forget('purchasing.order.stats');
            Cache::forget('purchasing.analytics.*');

            return $purchaseOrder->fresh(['items', 'supplier']);
        });
    }

    public function update(PurchaseOrder $purchaseOrder, array $data, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $data, $items) {
            $subtotal = 0;
            $poItems = [];

            foreach ($items as $item) {
                $lineSubtotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineSubtotal;
                $poItems[] = new PurchaseOrderItem([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $lineSubtotal,
                ]);
            }

            $data['subtotal'] = $subtotal;
            $data['tax'] = $data['tax'] ?? 0;
            $data['discount'] = $data['discount'] ?? 0;
            $data['discount_type'] = $data['discount_type'] ?? 'fixed';
            $data['total'] = $this->calculateTotal(
                $subtotal, $data['tax'], $data['discount'], $data['discount_type']
            );

            $purchaseOrder->update($data);
            $purchaseOrder->items()->delete();
            $purchaseOrder->items()->saveMany($poItems);

            Cache::forget("purchasing.order.{$purchaseOrder->id}");

            return $purchaseOrder->fresh(['items', 'supplier']);
        });
    }

    public function delete(PurchaseOrder $purchaseOrder): void
    {
        $purchaseOrder->delete();
        Cache::forget('purchasing.order.stats');
    }

    public function getStats(): array
    {
        return Cache::remember('purchasing.order.stats', 3600, function () {
            return [
                'total' => PurchaseOrder::count(),
                'draft' => PurchaseOrder::where('status', 'draft')->count(),
                'pending_approval' => PurchaseOrder::where('status', 'pending_approval')->count(),
                'approved' => PurchaseOrder::where('status', 'approved')->count(),
                'sent' => PurchaseOrder::where('status', 'sent')->count(),
                'partially_received' => PurchaseOrder::where('status', 'partially_received')->count(),
                'completed' => PurchaseOrder::where('status', 'completed')->count(),
                'cancelled' => PurchaseOrder::where('status', 'cancelled')->count(),
            ];
        });
    }
}
