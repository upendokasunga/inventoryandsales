<?php

namespace App\Observers;

use App\Models\SalesReturn;
use Illuminate\Support\Facades\Cache;

class ReturnObserver
{
    public function created(SalesReturn $return): void
    {
        Cache::forget('returns.dashboard.stats');

        activity()
            ->performedOn($return)
            ->withProperties([
                'return_number' => $return->return_number,
                'total_amount' => $return->total_amount,
                'customer_id' => $return->customer_id,
            ])
            ->log('Sales Return Created');
    }

    public function updated(SalesReturn $return): void
    {
        Cache::forget('returns.dashboard.stats');

        if ($return->wasChanged('status')) {
            activity()
                ->performedOn($return)
                ->withProperties([
                    'return_number' => $return->return_number,
                    'status' => $return->status,
                ])
                ->log('Sales Return ' . ucfirst($return->status));
        }
    }

    public function deleted(SalesReturn $return): void
    {
        Cache::forget('returns.dashboard.stats');
    }
}
