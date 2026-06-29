<?php

namespace App\Observers;

use App\Models\CreditNote;
use App\Models\CreditNoteNumberSequence;
use App\Models\Invoice;
use App\Models\InvoiceNumberSequence;
use App\Models\Payment;
use App\Models\SalesReturn;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InvoiceObserver
{
    public function creating(Invoice $invoice): void
    {
        if (empty($invoice->invoice_number)) {
            DB::transaction(function () use ($invoice) {
                $year = now()->year;
                $sequence = InvoiceNumberSequence::firstOrCreate(
                    ['year' => $year],
                    ['last_number' => 0]
                );
                $sequence->lockForUpdate();
                $sequence->increment('last_number');
                $invoice->invoice_number = 'INV-' . $year . '-' . str_pad($sequence->fresh()->last_number, 6, '0', STR_PAD_LEFT);
            });
        }
    }

    public function created(Invoice $invoice): void
    {
        Cache::forget('pos.dashboard.stats');
        Cache::forget('invoices.stats');

        activity()
            ->performedOn($invoice)
            ->withProperties([
                'invoice_number' => $invoice->invoice_number,
                'total' => $invoice->total,
                'customer_id' => $invoice->customer_id,
            ])
            ->log('Invoice Created');
    }

    public function updated(Invoice $invoice): void
    {
        Cache::forget('pos.dashboard.stats');
        Cache::forget('invoices.stats');
        Cache::forget("invoice.{$invoice->id}");
    }

    public function deleted(Invoice $invoice): void
    {
        Cache::forget('pos.dashboard.stats');
        Cache::forget('invoices.stats');
    }

    public function restored(Invoice $invoice): void
    {
        Cache::forget('pos.dashboard.stats');
    }

    public function forceDeleted(Invoice $invoice): void
    {
        Cache::forget('pos.dashboard.stats');
    }
}
