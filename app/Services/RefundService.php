<?php

namespace App\Services;

use App\Models\CreditNote;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RefundService
{
    public function __construct(
        protected CreditService $creditService,
    ) {}

    public function getAllPaginated(int $perPage = 20, ?array $filters = null): LengthAwarePaginator
    {
        $query = CreditNote::with(['customer', 'salesReturn', 'creator'])
            ->whereIn('status', ['issued', 'applied']);

        if (isset($filters['refund_method'])) {
            $query->where('refund_method', $filters['refund_method']);
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

    public function processRefund(CreditNote $creditNote, string $method): CreditNote
    {
        return DB::transaction(function () use ($creditNote, $method) {
            $creditNote->update([
                'refund_method' => $method,
                'status' => 'applied',
            ]);

            $this->processRefundByMethod($creditNote, $method);

            Cache::forget('returns.dashboard.stats');
            Cache::forget('credit.notes.stats');
            Cache::forget('refunds.stats');

            return $creditNote->fresh(['customer', 'salesReturn']);
        });
    }

    protected function processRefundByMethod(CreditNote $creditNote, string $method): void
    {
        $updateType = match ($method) {
            'cash' => 'refund',
            'store_credit' => 'credit_note',
            'bank_transfer' => 'refund',
            default => throw new \InvalidArgumentException("Unsupported refund method: {$method}"),
        };

        $this->creditService->updateBalance(
            $creditNote->customer,
            $creditNote->amount,
            $updateType
        );

        $activityLog = match ($method) {
            'cash' => 'Cash refund processed',
            'store_credit' => 'Store credit applied',
            'bank_transfer' => 'Bank transfer refund processed',
        };

        activity()
            ->performedOn($creditNote)
            ->withProperties([
                'amount' => $creditNote->amount,
                'method' => $method,
                'customer_id' => $creditNote->customer_id,
            ])
            ->log($activityLog);
    }

    public function getStats(): array
    {
        return Cache::remember('refunds.stats', 300, function () {
            return [
                'total_refunds' => CreditNote::where('status', 'applied')->count(),
                'total_amount' => (float) CreditNote::where('status', 'applied')->sum('amount'),
                'cash_refunds' => (float) CreditNote::where('status', 'applied')->where('refund_method', 'cash')->sum('amount'),
                'store_credits' => (float) CreditNote::where('status', 'applied')->where('refund_method', 'store_credit')->sum('amount'),
            ];
        });
    }
}
