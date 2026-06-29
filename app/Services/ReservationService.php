<?php

namespace App\Services;

use App\Models\InventoryBalance;
use App\Models\InventoryBatch;
use App\Models\SalesOrder;
use App\Models\StockReservation;
use App\Models\StockReservationItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected FifoService $fifoService,
    ) {}

    public function reserve(SalesOrder $salesOrder): StockReservation
    {
        return DB::transaction(function () use ($salesOrder) {
            $this->validateAvailability($salesOrder);

            $reservation = StockReservation::create([
                'sales_order_id' => $salesOrder->id,
                'status' => 'active',
                'reserved_at' => now(),
                'expires_at' => now()->addDays(3),
                'created_by' => auth()->id(),
            ]);

            foreach ($salesOrder->items as $item) {
                $balance = $this->inventoryService->getOrCreateBalance($item->product_id);

                $batches = InventoryBatch::where('product_id', $item->product_id)
                    ->where('status', 'active')
                    ->where('quantity_remaining', '>', 0)
                    ->orderBy('created_at')
                    ->orderBy('expiry_date')
                    ->lockForUpdate()
                    ->get();

                $remaining = $item->quantity;
                $reservationItems = [];

                foreach ($batches as $batch) {
                    if ($remaining <= 0) {
                        break;
                    }

                    $fromBatch = min($remaining, $batch->quantity_remaining);
                    $reservationItems[] = new StockReservationItem([
                        'product_id' => $item->product_id,
                        'inventory_batch_id' => $batch->id,
                        'quantity' => $fromBatch,
                    ]);

                    $remaining -= $fromBatch;
                }

                if (!empty($reservationItems)) {
                    $reservation->items()->saveMany($reservationItems);
                }

                $balance = InventoryBalance::where('id', $balance->id)->lockForUpdate()->firstOrFail();
                $newReserved = $balance->quantity_reserved + $item->quantity;
                $balance->update([
                    'quantity_reserved' => $newReserved,
                    'quantity_available' => $balance->quantity_on_hand - $newReserved,
                ]);
            }

            $salesOrder->update([
                'status' => 'reserved',
                'reserved_by' => auth()->id(),
                'reserved_at' => now(),
            ]);

            $this->invalidateCache($salesOrder->customer_id);

            return $reservation->fresh(['items', 'salesOrder']);
        });
    }

    public function release(StockReservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            foreach ($reservation->items as $item) {
                $balance = $this->inventoryService->getOrCreateBalance($item->product_id);
                $balance = InventoryBalance::where('id', $balance->id)->lockForUpdate()->firstOrFail();
                $newReserved = max(0, $balance->quantity_reserved - $item->quantity);
                $balance->update([
                    'quantity_reserved' => $newReserved,
                    'quantity_available' => $balance->quantity_on_hand - $newReserved,
                ]);
            }

            $reservation->update([
                'status' => 'released',
                'released_by' => auth()->id(),
                'released_at' => now(),
            ]);

            $this->invalidateCache($reservation->salesOrder->customer_id);
        });
    }

    public function validateAvailability(SalesOrder $salesOrder): array
    {
        $issues = [];

        foreach ($salesOrder->items as $item) {
            $balance = InventoryBalance::where('product_id', $item->product_id)->lockForUpdate()->first();
            $available = $balance ? $balance->quantity_available : 0;

            if ($available < $item->quantity) {
                $issues[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name,
                    'requested' => $item->quantity,
                    'available' => $available,
                    'shortage' => $item->quantity - $available,
                ];
            }
        }

        return $issues;
    }

    public function hasSufficientStock(SalesOrder $salesOrder): bool
    {
        $issues = $this->validateAvailability($salesOrder);
        return empty($issues);
    }

    public function invalidateCache(int $customerId): void
    {
        Cache::forget('inventory.analytics.dashboard');
    }
}
