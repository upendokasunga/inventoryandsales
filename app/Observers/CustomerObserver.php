<?php

namespace App\Observers;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerObserver
{
    public function creating(Customer $customer): void
    {
        if (empty($customer->code)) {
            DB::transaction(function () use ($customer) {
                $last = Customer::withTrashed()->lockForUpdate()->latest('id')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $customer->code = 'CUS-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
            });
        }
    }
}
