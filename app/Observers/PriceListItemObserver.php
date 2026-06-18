<?php

namespace App\Observers;

use App\Models\PriceListItem;
use App\Services\PricingService;

class PriceListItemObserver
{
    public function saved(PriceListItem $item): void
    {
        app(PricingService::class)->invalidateCache();
    }

    public function deleted(PriceListItem $item): void
    {
        app(PricingService::class)->invalidateCache();
    }
}