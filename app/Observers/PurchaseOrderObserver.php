<?php

namespace App\Observers;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Cache;

class PurchaseOrderObserver
{
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
}
