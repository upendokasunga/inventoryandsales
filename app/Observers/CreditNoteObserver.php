<?php

namespace App\Observers;

use App\Models\CreditNote;
use App\Services\DocumentNumberingService;
use Illuminate\Support\Facades\Cache;

class CreditNoteObserver
{
    public function __construct(
        protected DocumentNumberingService $numberingService
    ) {}

    public function creating(CreditNote $creditNote): void
    {
        if (empty($creditNote->credit_note_number)) {
            $creditNote->credit_note_number = $this->numberingService->generateNumber('credit_note');
        }
    }

    public function created(CreditNote $creditNote): void
    {
        Cache::forget('returns.dashboard.stats');
        Cache::forget('credit.notes.stats');

        activity()
            ->performedOn($creditNote)
            ->withProperties([
                'credit_note_number' => $creditNote->credit_note_number,
                'amount' => $creditNote->amount,
                'customer_id' => $creditNote->customer_id,
            ])
            ->log('Credit Note Issued');
    }

    public function updated(CreditNote $creditNote): void
    {
        Cache::forget('returns.dashboard.stats');
        Cache::forget('credit.notes.stats');

        if ($creditNote->wasChanged('status') && $creditNote->status === 'cancelled') {
            activity()
                ->performedOn($creditNote)
                ->withProperties([
                    'credit_note_number' => $creditNote->credit_note_number,
                ])
                ->log('Credit Note Cancelled');
        }
    }

    public function deleted(CreditNote $creditNote): void
    {
        Cache::forget('returns.dashboard.stats');
        Cache::forget('credit.notes.stats');
    }
}
