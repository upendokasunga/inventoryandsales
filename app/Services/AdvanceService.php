<?php

namespace App\Services;

use App\Models\AdvanceApplication;
use App\Models\CustomerAdvance;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class AdvanceService
{
    public function getAllPaginated(int $perPage = 20, ?array $filters = []): mixed
    {
        $query = CustomerAdvance::with(['customer', 'creator', 'account']);

        if ($tab = $filters['tab'] ?? null) {
            if ($tab === 'all') {
                // no filter
            } elseif ($tab === 'pending') {
                $query->whereIn('status', ['pending', 'partially_applied', 'completed']);
            } else {
                $query->where('status', $tab);
            }
        }

        if ($customerId = $filters['customer_id'] ?? null) {
            $query->where('customer_id', $customerId);
        }

        if ($dateFrom = $filters['date_from'] ?? null) {
            $query->whereDate('advance_date', '>=', $dateFrom);
        }

        if ($dateTo = $filters['date_to'] ?? null) {
            $query->whereDate('advance_date', '<=', $dateTo);
        }

        $query->latest();

        return $query->paginate($perPage);
    }

    public function create(array $data): CustomerAdvance
    {
        return DB::transaction(function () use ($data) {
            return CustomerAdvance::create([
                'customer_id' => $data['customer_id'],
                'amount' => $data['amount'],
                'balance' => $data['amount'],
                'account_id' => $data['account_id'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'advance_date' => $data['advance_date'],
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
                'status' => 'completed',
            ]);
        });
    }

    public function applyToInvoice(CustomerAdvance $advance, Invoice $invoice, float $amount): AdvanceApplication
    {
        return DB::transaction(function () use ($advance, $invoice, $amount) {
            if ($advance->balance < $amount) {
                throw new \InvalidArgumentException(
                    "Insufficient advance balance. Available: {$advance->balance}, requested: {$amount}"
                );
            }

            if ($invoice->balance_due < $amount) {
                throw new \InvalidArgumentException(
                    "Application amount exceeds invoice balance due ({$invoice->balance_due})."
                );
            }

            $newBalance = $advance->balance - $amount;
            $newStatus = $newBalance <= 0 ? 'applied' : 'partially_applied';

            $advance->update([
                'balance' => $newBalance,
                'status' => $newStatus,
            ]);

            $application = AdvanceApplication::create([
                'customer_advance_id' => $advance->id,
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'applied_by' => auth()->id(),
                'applied_at' => now(),
            ]);

            $newAmountPaid = $invoice->amount_paid + $amount;
            $newBalanceDue = $invoice->total - $newAmountPaid;
            $paymentStatus = $newBalanceDue <= 0 ? 'paid' : 'partial';

            $invoice->update([
                'amount_paid' => $newAmountPaid,
                'balance_due' => max(0, $newBalanceDue),
                'payment_status' => $paymentStatus,
            ]);

            return $application;
        });
    }

    public function cancel(CustomerAdvance $advance): void
    {
        DB::transaction(function () use ($advance) {
            if ($advance->status === 'cancelled') {
                throw new \InvalidArgumentException('Advance is already cancelled.');
            }

            $applications = $advance->applications()->with('invoice')->get();

            foreach ($applications as $app) {
                $invoice = $app->invoice;
                if ($invoice) {
                    $newAmountPaid = $invoice->amount_paid - $app->amount;
                    $newBalanceDue = $invoice->total - $newAmountPaid;
                    $paymentStatus = $newBalanceDue >= $invoice->total ? 'pending' : ($newBalanceDue <= 0 ? 'paid' : 'partial');

                    $invoice->update([
                        'amount_paid' => max(0, $newAmountPaid),
                        'balance_due' => max(0, $newBalanceDue),
                        'payment_status' => $paymentStatus,
                    ]);
                }
                $app->delete();
            }

            $advance->update([
                'status' => 'cancelled',
                'balance' => $advance->amount,
            ]);
        });
    }

    public function getStats(): array
    {
        return [
            'total_advances' => CustomerAdvance::whereIn('status', ['completed', 'partially_applied'])->sum('amount'),
            'total_applied' => AdvanceApplication::sum('amount'),
            'total_balance' => CustomerAdvance::whereIn('status', ['completed', 'partially_applied'])->sum('balance'),
            'pending_count' => CustomerAdvance::whereIn('status', ['pending', 'completed', 'partially_applied'])->count(),
        ];
    }
}
