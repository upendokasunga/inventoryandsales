<?php

namespace App\Observers;

use App\Models\CreditNote;
use App\Models\CreditNoteNumberSequence;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CreditNoteObserver
{
    public function creating(CreditNote $creditNote): void
    {
        if (empty($creditNote->credit_note_number)) {
            DB::transaction(function () use ($creditNote) {
                $year = now()->year;
                $sequence = CreditNoteNumberSequence::firstOrCreate(
                    ['year' => $year],
                    ['last_number' => 0]
                );
                $sequence->lockForUpdate();
                $sequence->increment('last_number');
                $creditNote->credit_note_number = 'CN-' . $year . '-' . str_pad($sequence->fresh()->last_number, 6, '0', STR_PAD_LEFT);
            });
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
