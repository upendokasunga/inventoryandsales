<?php

namespace App\Observers;

use App\Models\Payment;
use Illuminate\Support\Facades\Cache;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        Cache::forget('pos.dashboard.stats');
        Cache::forget("invoice.{$payment->invoice_id}");

        activity()
            ->performedOn($payment)
            ->withProperties([
                'invoice_id' => $payment->invoice_id,
                'amount' => $payment->amount,
                'method' => $payment->payment_method,
            ])
            ->log('Payment Recorded');
    }

    public function deleted(Payment $payment): void
    {
        Cache::forget('pos.dashboard.stats');
        Cache::forget("invoice.{$payment->invoice_id}");
    }
}
