<?php

namespace App\Observers;

use App\Models\SalesOrder;
use App\Services\DocumentNumberingService;
use Illuminate\Support\Facades\Cache;

class SalesOrderObserver
{
    public function __construct(
        protected DocumentNumberingService $numberingService
    ) {}

    public function creating(SalesOrder $salesOrder): void
    {
        if (empty($salesOrder->so_number)) {
            $salesOrder->so_number = $this->numberingService->generateNumber('sales_order');
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
