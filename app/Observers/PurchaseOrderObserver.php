<?php

namespace App\Observers;

use App\Models\PurchaseOrder;
use App\Services\DocumentNumberingService;
use Illuminate\Support\Facades\Cache;

class PurchaseOrderObserver
{
    public function __construct(
        protected DocumentNumberingService $numberingService
    ) {}

    public function creating(PurchaseOrder $purchaseOrder): void
    {
        if (empty($purchaseOrder->po_number)) {
            $purchaseOrder->po_number = $this->numberingService->generateNumber('purchase_order');
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
}
