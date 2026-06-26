<?php

namespace App\Observers;

use App\Models\PoNumberSequence;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PurchaseOrderObserver
{
    public function creating(PurchaseOrder $purchaseOrder): void
    {
        if (empty($purchaseOrder->po_number)) {
            $purchaseOrder->po_number = $this->generatePoNumber();
        }
    }

    public function updated(PurchaseOrder $purchaseOrder): void
    {
        Cache::forget('purchasing.order.stats');
        Cache::forget('purchasing.analytics.dashboard');
        Cache::forget('purchasing.analytics.supplier_rankings');
    }

    public function deleted(PurchaseOrder $purchaseOrder): void
    {
        Cache::forget('purchasing.order.stats');
        Cache::forget('purchasing.analytics.dashboard');
    }

    protected function generatePoNumber(): string
    {
        $year = now()->year;

        return DB::transaction(function () use ($year) {
            $sequence = PoNumberSequence::lockForUpdate()->firstOrCreate(
                ['year' => $year],
                ['last_number' => 0]
            );

            $sequence->increment('last_number');

            return 'PO-' . $year . '-' . str_pad($sequence->last_number, 6, '0', STR_PAD_LEFT);
        });
    }
}
