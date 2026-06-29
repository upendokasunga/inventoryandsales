<?php

namespace App\Services;

use App\Models\CreditNote;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CreditNoteService
{
    public function __construct(
        protected CreditService $creditService,
    ) {}

    public function getAllPaginated(int $perPage = 20, ?array $filters = null): LengthAwarePaginator
    {
        $query = CreditNote::with(['customer', 'salesReturn', 'creator']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('issued_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('issued_date', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): CreditNote
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = $data['created_by'] ?? auth()->id();
            $data['issued_date'] = $data['issued_date'] ?? now();

            $creditNote =             $creditNote = CreditNote::create($data);

            Cache::forget('returns.dashboard.stats');
            Cache::forget('credit.notes.stats');

            return $creditNote->load(['customer', 'salesReturn']);
        });
    }

    public function applyToInvoice(CreditNote $creditNote, \App\Models\Invoice $invoice): void
    {
        DB::transaction(function () use ($creditNote, $invoice) {
            $newAmountPaid = $invoice->amount_paid + $creditNote->amount;
            $newBalanceDue = max(0, $invoice->total - $newAmountPaid);

            $invoice->update([
                'amount_paid' => $newAmountPaid,
                'balance_due' => $newBalanceDue,
                'payment_status' => $newBalanceDue <= 0 ? 'paid' : $invoice->payment_status,
            ]);

            $creditNote->update([
                'status' => 'applied',
                'invoice_id' => $invoice->id,
            ]);

            Cache::forget('returns.dashboard.stats');
            Cache::forget('credit.notes.stats');
            Cache::forget("invoice.{$invoice->id}");
            Cache::forget('pos.dashboard.stats');
        });
    }

    public function cancel(CreditNote $creditNote): void
    {
        $creditNote->update(['status' => 'cancelled']);
        Cache::forget('returns.dashboard.stats');
        Cache::forget('credit.notes.stats');
    }

    public function getStats(): array
    {
        return Cache::remember('credit.notes.stats', 300, function () {
            return [
                'total_issued' => CreditNote::where('status', 'issued')->count(),
                'total_applied' => CreditNote::where('status', 'applied')->count(),
                'total_amount' => (float) CreditNote::whereIn('status', ['issued', 'applied'])->sum('amount'),
            ];
        });
    }
}
