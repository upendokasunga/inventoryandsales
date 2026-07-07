<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\DocumentNumberingService;
use Illuminate\Support\Facades\Cache;

class InvoiceObserver
{
    public function __construct(
        protected DocumentNumberingService $numberingService
    ) {}

    public function creating(Invoice $invoice): void
    {
        if (empty($invoice->invoice_number)) {
            $invoice->invoice_number = $this->numberingService->generateNumber('invoice');
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
