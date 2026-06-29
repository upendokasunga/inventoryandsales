<?php

namespace App\Observers;

use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\Cache;

class PurchaseReturnObserver
{
    public function created(PurchaseReturn $return): void
    {
        Cache::forget('returns.dashboard.stats');

        activity()
            ->performedOn($return)
            ->withProperties([
                'return_number' => $return->return_number,
                'total_amount' => $return->total_amount,
                'supplier_id' => $return->supplier_id,
            ])
            ->log('Purchase Return Created');
    }

    public function updated(PurchaseReturn $return): void
    {
        Cache::forget('returns.dashboard.stats');

        if ($return->wasChanged('status')) {
            activity()
                ->performedOn($return)
                ->withProperties([
                    'return_number' => $return->return_number,
                    'status' => $return->status,
                ])
                ->log('Purchase Return ' . ucfirst($return->status));
        }
    }

    public function deleted(PurchaseReturn $return): void
    {
        Cache::forget('returns.dashboard.stats');
    }
}
