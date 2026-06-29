<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Models\StockReservation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FulfillmentService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected ReservationService $reservationService,
        protected CreditService $creditService,
    ) {}

    public function startPicking(SalesOrder $salesOrder): SalesOrder
    {
        if ($salesOrder->status !== 'reserved') {
            throw new \InvalidArgumentException(
                'Cannot start picking for order in status: ' . $salesOrder->status
            );
        }

        $salesOrder->update([
            'status' => 'picking',
            'picked_by' => auth()->id(),
            'picked_at' => now(),
        ]);

        Cache::forget('sales.order.stats');

        return $salesOrder->fresh();
    }

    public function markPacked(SalesOrder $salesOrder): SalesOrder
    {
        if ($salesOrder->status !== 'picking') {
            throw new \InvalidArgumentException(
                'Cannot mark as packed for order in status: ' . $salesOrder->status
            );
        }

        $salesOrder->update([
            'status' => 'packed',
            'packed_by' => auth()->id(),
            'packed_at' => now(),
        ]);

        Cache::forget('sales.order.stats');

        return $salesOrder->fresh();
    }

    public function fulfill(SalesOrder $salesOrder): SalesOrder
    {
        return DB::transaction(function () use ($salesOrder) {
            if (!in_array($salesOrder->status, ['reserved', 'packed', 'partially_fulfilled'])) {
                throw new \InvalidArgumentException(
                    'Cannot fulfill order in status: ' . $salesOrder->status
                );
            }

            $reservation = $salesOrder->reservations()
                ->where('status', 'active')
                ->latest()
                ->first();

            if (!$reservation) {
                throw new \InvalidArgumentException('No active reservation found for this order.');
            }

            $allFulfilled = true;

            foreach ($salesOrder->items as $item) {
                $remaining = $item->quantity - $item->fulfilled_quantity;

                if ($remaining <= 0) {
                    continue;
                }

                $result = $this->inventoryService->issueStock(
                    $item->product,
                    $remaining,
                    $salesOrder,
                    "Sales Order {$salesOrder->so_number} fulfillment"
                );

                $item->update([
                    'fulfilled_quantity' => $item->fulfilled_quantity + $remaining,
                ]);

                $reservationItem = $reservation->items()
                    ->where('product_id', $item->product_id)
                    ->first();

                if ($reservationItem) {
                    $reservationItem->update([
                        'quantity_fulfilled' => ($reservationItem->quantity_fulfilled ?? 0) + $remaining,
                    ]);
                }

                if ($item->fresh()->fulfilled_quantity < $item->quantity) {
                    $allFulfilled = false;
                }
            }

            $this->creditService->updateBalance(
                $salesOrder->customer,
                $salesOrder->total,
                'order'
            );

            $newStatus = $allFulfilled ? 'fulfilled' : 'partially_fulfilled';

            $salesOrder->update([
                'status' => $newStatus,
                'fulfilled_by' => auth()->id(),
                'fulfilled_at' => now(),
            ]);

            $reservation->update([
                'status' => $allFulfilled ? 'fulfilled' : 'active',
            ]);

            Cache::forget('sales.order.stats');
            $this->reservationService->invalidateCache($salesOrder->customer_id);

            return $salesOrder->fresh(['items', 'customer']);
        });
    }

    public function getFulfillmentStatus(SalesOrder $salesOrder): array
    {
        $totalItems = $salesOrder->items->count();
        $fulfilledItems = $salesOrder->items->filter(fn($i) => $i->fulfilled_quantity >= $i->quantity)->count();

        return [
            'total_items' => $totalItems,
            'fulfilled_items' => $fulfilledItems,
            'progress' => $totalItems > 0 ? round(($fulfilledItems / $totalItems) * 100, 1) : 0,
            'is_complete' => $fulfilledItems === $totalItems,
        ];
    }
}
