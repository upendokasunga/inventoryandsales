<?php

namespace App\Observers;

use App\Models\SalesOrder;
use App\Models\SoNumberSequence;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SalesOrderObserver
{
    public function creating(SalesOrder $salesOrder): void
    {
        if (empty($salesOrder->so_number)) {
            DB::transaction(function () use ($salesOrder) {
                $year = now()->year;

                $sequence = SoNumberSequence::firstOrCreate(
                    ['year' => $year],
                    ['last_number' => 0]
                );

                $sequence->lockForUpdate();
                $sequence->increment('last_number');

                $salesOrder->so_number = 'SO-' . $year . '-' . str_pad($sequence->fresh()->last_number, 6, '0', STR_PAD_LEFT);
            });
        }
    }

    public function created(SalesOrder $salesOrder): void
    {
        Cache::forget('sales.order.stats');
    }

    public function updated(SalesOrder $salesOrder): void
    {
        Cache::forget('sales.order.stats');
        Cache::forget("sales.order.{$salesOrder->id}");
    }

    public function deleted(SalesOrder $salesOrder): void
    {
        Cache::forget('sales.order.stats');
    }

    public function restored(SalesOrder $salesOrder): void
    {
        Cache::forget('sales.order.stats');
    }

    public function forceDeleted(SalesOrder $salesOrder): void
    {
        Cache::forget('sales.order.stats');
    }
}
