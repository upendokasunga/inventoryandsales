<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        protected CreditService $creditService,
    ) {}

    public function getAllPaginated(int $perPage = 20, ?array $filters = null): LengthAwarePaginator
    {
        $query = Payment::with(['invoice', 'customer', 'receiver']);

        if (isset($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('payment_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('payment_date', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function recordPayment(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $amount = $data['amount'];
            $newAmountPaid = $invoice->amount_paid + $amount;
            $newBalanceDue = max(0, $invoice->total - $newAmountPaid);
            $newPaymentStatus = $newBalanceDue <= 0 ? 'paid' : 'partial';

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'payment_method' => $data['payment_method'],
                'amount' => $amount,
                'reference_number' => $data['reference_number'] ?? null,
                'payment_date' => $data['payment_date'] ?? now(),
                'received_by' => $data['received_by'] ?? auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);

            $invoice->update([
                'amount_paid' => $newAmountPaid,
                'balance_due' => $newBalanceDue,
                'payment_status' => $newPaymentStatus,
            ]);

            if ($invoice->payment_type === 'credit' && $newPaymentStatus === 'paid') {
                $this->creditService->updateBalance(
                    $invoice->customer,
                    $amount,
                    'payment'
                );
            }

            Cache::forget('pos.dashboard.stats');
            Cache::forget("invoice.{$invoice->id}");

            return $payment;
        });
    }

    public function getPaymentMethods(): array
    {
        return Payment::METHODS;
    }
}
