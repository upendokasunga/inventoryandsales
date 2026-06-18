<?php

namespace App\Observers;

use App\Models\PriceList;
use App\Services\PricingService;

class PriceListObserver
{
    public function saved(PriceList $priceList): void
    {
        app(PricingService::class)->invalidateCache();
    }

    public function deleted(PriceList $priceList): void
    {
        app(PricingService::class)->invalidateCache();
    }

    public function restored(PriceList $priceList): void
    {
        app(PricingService::class)->invalidateCache();
    }

    public function forceDeleted(PriceList $priceList): void
    {
        app(PricingService::class)->invalidateCache();
    }
}