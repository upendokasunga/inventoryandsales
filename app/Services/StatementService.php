<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerCreditTransaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StatementService
{
    public function getStatement(
        Customer $customer,
        ?string $from = null,
        ?string $to = null,
        int $perPage = 50
    ): LengthAwarePaginator {
        $query = CustomerCreditTransaction::where('customer_id', $customer->id)
            ->with('user')
            ->latest();

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query->paginate($perPage);
    }

    public function getAllTransactions(
        Customer $customer,
        ?string $from = null,
        ?string $to = null
    ): Collection {
        $query = CustomerCreditTransaction::where('customer_id', $customer->id)
            ->with('user')
            ->oldest();

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query->get();
    }

    public function generateStatementData(Customer $customer, ?string $from = null, ?string $to = null): array
    {
        $transactions = $this->getAllTransactions($customer, $from, $to);

        $openingBalance = 0;
        if ($from) {
            $lastBefore = CustomerCreditTransaction::where('customer_id', $customer->id)
                ->whereDate('created_at', '<', $from)
                ->latest()
                ->first();
            $openingBalance = $lastBefore ? $lastBefore->balance_after : 0;
        }

        $totalDebit = $transactions->whereIn('type', ['order', 'allocation'])->sum('amount');
        $totalCredit = $transactions->whereIn('type', ['payment', 'refund', 'reversal'])->sum('amount');

        return [
            'customer' => $customer,
            'transactions' => $transactions,
            'from' => $from,
            'to' => $to ?? now()->format('Y-m-d'),
            'opening_balance' => $openingBalance,
            'closing_balance' => $customer->outstanding_balance,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'generated_at' => now(),
        ];
    }
}
