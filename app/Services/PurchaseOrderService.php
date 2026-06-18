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

    public function create(array $data, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $items) {
            $data['po_number'] = $this->generatePoNumber();
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
            $data['total'] = $subtotal + ($data['tax'] ?? 0);

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
            $data['total'] = $subtotal + ($data['tax'] ?? 0);

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
        return Cache::remember('purchasing.order.stats', 300, function () {
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

    protected function generatePoNumber(): string
    {
        $prefix = 'PO';
        $last = PurchaseOrder::withTrashed()->lockForUpdate()->latest('id')->first();
        $nextId = $last ? $last->id + 1 : 1;

        return $prefix . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }
}
